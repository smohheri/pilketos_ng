<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ElectionService;
use App\Models\ElectionModel;
use App\Models\ElectionScheduleModel;

class Election extends BaseController
{
    public function index()
    {
        $election = admin_current_election();
        $schedules = $election !== null
            ? (new ElectionScheduleModel())->forElection((int) $election['id'])
            : [];

        return view('admin/election/index', [
            'election' => $election,
            'elections' => admin_elections(),
            'schedules' => $schedules,
            'phase' => $election !== null ? (new ElectionService())->phase($election) : 'draft',
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function select(int $id)
    {
        if (! is_admin()) {
            return redirect()->to('/admin')->with('error', 'Hanya Admin yang dapat berpindah pemilihan.');
        }

        $election = (new ElectionModel())->find($id);
        if ($election === null) {
            return redirect()->to('/admin')->with('error', 'Pemilihan tidak ditemukan.');
        }

        session()->set('admin_election_id', (int) $election['id']);

        return redirect()->back()->with('success', 'Pemilihan aktif: ' . election_type_label($election['type']) . '.');
    }

    public function update()
    {
        $rules = [
            'title' => 'required|min_length[3]|max_length[180]',
            'description' => 'permit_empty',
            'start_at' => 'required',
            'end_at' => 'required',
            'result_publish_at' => 'required',
            'status' => 'required|in_list[draft,published,closed]',
            'timezone' => 'required|max_length[64]',
            'result_mode' => 'permit_empty|in_list[vote_only,combined]',
            'vote_weight' => 'permit_empty|numeric',
            'assessment_weight' => 'permit_empty|numeric',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $start = $this->dateTime((string) $this->request->getPost('start_at'));
        $end = $this->dateTime((string) $this->request->getPost('end_at'));
        $publish = $this->dateTime((string) $this->request->getPost('result_publish_at'));

        if ($start === null || $end === null || $publish === null) {
            return redirect()->back()->withInput()->with('error', 'Format tanggal dan waktu tidak valid.');
        }
        if ($end <= $start) {
            return redirect()->back()->withInput()->with('error', 'Waktu selesai harus setelah waktu mulai.');
        }
        if ($publish < $end) {
            return redirect()->back()->withInput()->with('error', 'Waktu pengumuman hasil tidak boleh sebelum pemungutan suara selesai.');
        }

        $timezone = (string) $this->request->getPost('timezone');
        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            $timezone = 'Asia/Makassar';
        }

        $resultMode = (string) $this->request->getPost('result_mode');
        if (! in_array($resultMode, ['vote_only', 'combined'], true)) {
            $resultMode = 'vote_only';
        }

        $voteWeight = $this->request->getPost('vote_weight');
        $assessmentWeight = $this->request->getPost('assessment_weight');
        $voteWeight = is_numeric($voteWeight) ? round((float) $voteWeight, 2) : 50.0;
        $assessmentWeight = is_numeric($assessmentWeight) ? round((float) $assessmentWeight, 2) : 50.0;
        if ($voteWeight < 0 || $voteWeight > 100 || $assessmentWeight < 0 || $assessmentWeight > 100) {
            return redirect()->back()->withInput()->with('error', 'Bobot suara dan penilaian harus berada di antara 0 dan 100.');
        }
        if ($resultMode === 'combined' && abs(($voteWeight + $assessmentWeight) - 100) > 0.01) {
            return redirect()->back()->withInput()->with('error', 'Total bobot suara dan penilaian harus tepat 100%.');
        }
        if ($resultMode === 'vote_only' && $voteWeight + $assessmentWeight <= 0) {
            $voteWeight = 50.0;
            $assessmentWeight = 50.0;
        }

        $data = [
            'title' => trim((string) $this->request->getPost('title')),
            'description' => trim((string) $this->request->getPost('description')),
            'start_at' => $start,
            'end_at' => $end,
            'result_publish_at' => $publish,
            'status' => (string) $this->request->getPost('status'),
            'timezone' => $timezone,
            'result_mode' => $resultMode,
            'vote_weight' => $voteWeight,
            'assessment_weight' => $assessmentWeight,
        ];

        $model = new ElectionModel();
        $election = admin_current_election();
        if ($election === null) {
            return redirect()->to('/admin/pemilihan')->with('error', 'Konfigurasi pemilihan belum tersedia.');
        }

        $id = (int) $election['id'];
        $model->update($id, $data);
        $this->audit('election.update', 'election', $id, [
            'type' => $election['type'] ?? null,
            'status' => $data['status'],
            'result_mode' => $data['result_mode'],
            'vote_weight' => $data['vote_weight'],
            'assessment_weight' => $data['assessment_weight'],
        ]);

        if ($data['status'] !== 'draft') {
            (new ElectionService())->syncEligibility($id);
        }

        return redirect()->to('/admin/pemilihan')->with('success', 'Konfigurasi pemilihan berhasil disimpan.');
    }

    public function syncVoters()
    {
        $election = admin_current_election();
        if ($election === null) {
            return redirect()->to('/admin/pemilihan')->with('error', 'Konfigurasi pemilihan belum tersedia.');
        }

        $added = (new ElectionService())->syncEligibility((int) $election['id']);
        $this->audit('voter.sync', 'election', (int) $election['id'], ['added' => $added]);

        return redirect()->to('/admin/pemilihan')->with('success', $added . ' pemilih baru berhasil disinkronkan.');
    }

    public function storeSchedule()
    {
        $election = admin_current_election();
        if ($election === null) {
            return redirect()->to('/admin/pemilihan')->with('error', 'Konfigurasi pemilihan belum tersedia.');
        }

        $payload = $this->schedulePayload();
        if (is_string($payload)) {
            return redirect()->back()->withInput()->with('error', $payload);
        }

        $payload['election_id'] = (int) $election['id'];
        $id = (int) (new ElectionScheduleModel())->insert($payload, true);
        $this->audit('schedule.create', 'election_schedule', $id);

        return redirect()->to('/admin/pemilihan')->with('success', 'Tahapan pemilihan berhasil ditambahkan.');
    }

    public function updateSchedule(int $id)
    {
        $election = admin_current_election();
        $schedule = $election !== null
            ? (new ElectionScheduleModel())->where('id', $id)->where('election_id', $election['id'])->first()
            : null;
        if ($election === null || $schedule === null) {
            return redirect()->to('/admin/pemilihan')->with('error', 'Tahapan tidak ditemukan.');
        }

        $payload = $this->schedulePayload();
        if (is_string($payload)) {
            return redirect()->back()->withInput()->with('error', $payload);
        }

        (new ElectionScheduleModel())->update($id, $payload);
        $this->audit('schedule.update', 'election_schedule', $id);

        return redirect()->to('/admin/pemilihan')->with('success', 'Tahapan pemilihan diperbarui.');
    }

    public function deleteSchedule(int $id)
    {
        $election = admin_current_election();
        $schedule = $election !== null
            ? (new ElectionScheduleModel())->where('id', $id)->where('election_id', $election['id'])->first()
            : null;
        if ($election === null || $schedule === null) {
            return redirect()->to('/admin/pemilihan')->with('error', 'Tahapan tidak ditemukan.');
        }

        (new ElectionScheduleModel())->delete($id);
        $this->audit('schedule.delete', 'election_schedule', $id);

        return redirect()->to('/admin/pemilihan')->with('success', 'Tahapan pemilihan dihapus.');
    }

    private function schedulePayload(): array|string
    {
        $title = trim((string) $this->request->getPost('title'));
        if ($title === '' || mb_strlen($title) > 180) {
            return 'Judul tahapan wajib diisi (maksimal 180 karakter).';
        }

        $start = $this->dateTime((string) $this->request->getPost('start_at'));
        if ($start === null) {
            return 'Waktu mulai tahapan tidak valid.';
        }

        $endRaw = trim((string) $this->request->getPost('end_at'));
        $end = $endRaw === '' ? null : $this->dateTime($endRaw);
        if ($endRaw !== '' && $end === null) {
            return 'Waktu selesai tahapan tidak valid.';
        }
        if ($end !== null && $end < $start) {
            return 'Waktu selesai tahapan tidak boleh sebelum waktu mulai.';
        }

        return [
            'title' => $title,
            'description' => trim((string) $this->request->getPost('description')),
            'start_at' => $start,
            'end_at' => $end,
            'sort_order' => (int) $this->request->getPost('sort_order'),
        ];
    }

    private function dateTime(string $value): ?string
    {
        $value = trim(str_replace('T', ' ', $value));
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp);
    }
}