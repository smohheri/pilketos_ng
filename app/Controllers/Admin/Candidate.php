<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ElectionService;
use App\Libraries\UploadService;
use App\Models\CandidateModel;
use App\Models\VoteModel;

class Candidate extends BaseController
{
    public function index()
    {
        $election = admin_current_election();
        $candidates = [];
        $voteCounts = [];
        if ($election !== null) {
            $candidates = (new CandidateModel())->forElection((int) $election['id'], false);
            foreach ((new VoteModel())->countsByCandidate((int) $election['id']) as $row) {
                $voteCounts[(int) $row['candidate_id']] = (int) $row['total'];
            }
        }

        return view('admin/candidates/index', [
            'election' => $election,
            'candidates' => $candidates,
            'voteCounts' => $voteCounts,
            'phase' => $election !== null ? (new ElectionService())->phase($election) : 'draft',
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function create()
    {
        $election = $this->election();
        if ($election === null) {
            return redirect()->to('/admin/pemilihan')->with('error', 'Buat konfigurasi pemilihan terlebih dahulu.');
        }

        $nextNumber = 1;
        $last = (new CandidateModel())->where('election_id', $election['id'])->orderBy('candidate_number', 'DESC')->first();
        if ($last !== null) {
            $nextNumber = (int) $last['candidate_number'] + 1;
        }

        return view('admin/candidates/form', [
            'election' => $election,
            'candidate' => null,
            'nextNumber' => $nextNumber,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function store()
    {
        $election = $this->election();
        if ($election === null) {
            return redirect()->to('/admin/pemilihan')->with('error', 'Buat konfigurasi pemilihan terlebih dahulu.');
        }
        if ($locked = $this->locked($election)) {
            return $locked;
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $number = (int) $this->request->getPost('candidate_number');
        $duplicate = (new CandidateModel())
            ->where('election_id', $election['id'])
            ->where('candidate_number', $number)
            ->first();
        if ($duplicate !== null) {
            return redirect()->back()->withInput()->with('error', 'Nomor urut sudah digunakan kandidat lain.');
        }

        $data = $this->payload((int) $election['id']);
        $file = $this->request->getFile('photo');
        if ($file !== null && $file->isValid() && ! $file->hasMoved()) {
            $upload = (new UploadService())->image($file, 'candidates');
            if (! $upload['ok']) {
                return redirect()->back()->withInput()->with('error', $upload['message']);
            }
            $data['photo_path'] = $upload['path'];
        }

        $id = (int) (new CandidateModel())->insert($data, true);
        $this->audit('candidate.create', 'candidate', $id, ['number' => $number]);

        return redirect()->to('/admin/kandidat')->with('success', 'Kandidat berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $election = $this->election();
        $candidate = $election !== null ? (new CandidateModel())->findForElection($id, (int) $election['id']) : null;
        if ($election === null || $candidate === null) {
            return redirect()->to('/admin/kandidat')->with('error', 'Kandidat tidak ditemukan.');
        }

        return view('admin/candidates/form', [
            'election' => $election,
            'candidate' => $candidate,
            'nextNumber' => (int) $candidate['candidate_number'],
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function update(int $id)
    {
        $election = $this->election();
        $candidate = $election !== null ? (new CandidateModel())->findForElection($id, (int) $election['id']) : null;
        if ($election === null || $candidate === null) {
            return redirect()->to('/admin/kandidat')->with('error', 'Kandidat tidak ditemukan.');
        }
        if ($locked = $this->locked($election)) {
            return $locked;
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $number = (int) $this->request->getPost('candidate_number');
        $duplicate = (new CandidateModel())
            ->where('election_id', $election['id'])
            ->where('candidate_number', $number)
            ->where('id !=', $id)
            ->first();
        if ($duplicate !== null) {
            return redirect()->back()->withInput()->with('error', 'Nomor urut sudah digunakan kandidat lain.');
        }

        $data = $this->payload((int) $election['id']);
        $file = $this->request->getFile('photo');
        if ($file !== null && $file->isValid() && ! $file->hasMoved()) {
            $upload = (new UploadService())->image($file, 'candidates');
            if (! $upload['ok']) {
                return redirect()->back()->withInput()->with('error', $upload['message']);
            }
            if (! empty($candidate['photo_path'])) {
                (new UploadService())->delete($candidate['photo_path']);
            }
            $data['photo_path'] = $upload['path'];
        }

        (new CandidateModel())->update($id, $data);
        $this->audit(
            $this->isLocked($election) ? 'candidate.update_after_start' : 'candidate.update',
            'candidate',
            $id,
            ['number' => $number]
        );

        return redirect()->to('/admin/kandidat')->with('success', 'Data kandidat berhasil diperbarui.');
    }

    public function toggle(int $id)
    {
        $election = $this->election();
        $candidate = $election !== null ? (new CandidateModel())->findForElection($id, (int) $election['id']) : null;
        if ($election === null || $candidate === null) {
            return redirect()->to('/admin/kandidat')->with('error', 'Kandidat tidak ditemukan.');
        }
        if ($locked = $this->locked($election)) {
            return $locked;
        }

        $newStatus = (int) $candidate['is_active'] === 1 ? 0 : 1;
        (new CandidateModel())->update($id, ['is_active' => $newStatus]);
        $this->audit($newStatus ? 'candidate.activate' : 'candidate.deactivate', 'candidate', $id);

        return redirect()->to('/admin/kandidat')->with('success', 'Status kandidat diperbarui.');
    }

    public function delete(int $id)
    {
        $election = $this->election();
        $candidate = $election !== null ? (new CandidateModel())->findForElection($id, (int) $election['id']) : null;
        if ($election === null || $candidate === null) {
            return redirect()->to('/admin/kandidat')->with('error', 'Kandidat tidak ditemukan.');
        }
        if ($locked = $this->locked($election)) {
            return $locked;
        }

        $votes = (new VoteModel())->where('candidate_id', $id)->countAllResults();
        if ($votes > 0) {
            return redirect()->to('/admin/kandidat')->with('error', 'Kandidat sudah memiliki suara dan tidak dapat dihapus. Nonaktifkan saja untuk menjaga catatan historis.');
        }

        (new CandidateModel())->delete($id);
        (new UploadService())->delete($candidate['photo_path'] ?? null);
        $this->audit('candidate.delete', 'candidate', $id);

        return redirect()->to('/admin/kandidat')->with('success', 'Kandidat berhasil dihapus.');
    }

    private function election(): ?array
    {
        return admin_current_election();
    }

    private function isLocked(?array $election): bool
    {
        return in_array((new ElectionService())->phase($election), ['ongoing', 'closed', 'results'], true);
    }

    private function locked(?array $election)
    {
        if ($this->isLocked($election) && ! is_admin()) {
            return redirect()->to('/admin/kandidat')->with('error', 'Perubahan kandidat setelah pemilihan dimulai hanya dapat dilakukan oleh Admin dan akan dicatat pada audit log.');
        }

        return null;
    }

    private function rules(): array
    {
        return [
            'candidate_number' => 'required|is_natural_no_zero|less_than_equal_to[999]',
            'full_name' => 'required|min_length[3]|max_length[150]',
            'class_name' => 'required|max_length[50]',
            'tagline' => 'permit_empty|max_length[180]',
            'vision' => 'permit_empty',
            'mission' => 'permit_empty',
            'program_description' => 'permit_empty',
        ];
    }

    private function payload(int $electionId): array
    {
        return [
            'election_id' => $electionId,
            'candidate_number' => (int) $this->request->getPost('candidate_number'),
            'full_name' => trim((string) $this->request->getPost('full_name')),
            'class_name' => trim((string) $this->request->getPost('class_name')),
            'tagline' => trim((string) $this->request->getPost('tagline')),
            'vision' => trim((string) $this->request->getPost('vision')),
            'mission' => trim((string) $this->request->getPost('mission')),
            'program_description' => trim((string) $this->request->getPost('program_description')),
            'is_active' => $this->request->getPost('is_active') === '1' ? 1 : 0,
        ];
    }
}