<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\StudentImportService;
use App\Models\ElectionModel;
use App\Models\StudentModel;

class Student extends BaseController
{
    public function index()
    {
        $election = (new ElectionModel())->orderBy('id', 'DESC')->first();
        $electionId = $election !== null ? (int) $election['id'] : 0;
        $keyword = trim((string) $this->request->getGet('q'));
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = 15;

        $db = db_connect();
        $builder = $db->table('students s')
            ->select('s.*, va.account_status, va.activated_at, ev.has_voted, ev.voted_at')
            ->join('voter_accounts va', 'va.student_id = s.id', 'left')
            ->join('election_voters ev', 'ev.student_id = s.id AND ev.election_id = ' . $electionId, 'left');

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('s.nis', $keyword)
                ->orLike('s.full_name', $keyword)
                ->orLike('s.class_name', $keyword)
                ->groupEnd();
        }

        $total = (clone $builder)->countAllResults();
        $rows = $builder->orderBy('s.class_name', 'ASC')
            ->orderBy('s.full_name', 'ASC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        $stats = $db->table('students s')
            ->select('COUNT(*) AS total, COALESCE(SUM(s.is_active), 0) AS active', false)
            ->get()
            ->getRowArray();

        $activated = $db->table('voter_accounts')->where('account_status', 'active')->countAllResults();
        $voted = $election !== null
            ? $db->table('election_voters')->where('election_id', $electionId)->where('has_voted', 1)->countAllResults()
            : 0;

        return view('admin/students/index', [
            'election' => $election,
            'rows' => $rows,
            'keyword' => $keyword,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'stats' => [
                'total' => (int) ($stats['total'] ?? 0),
                'active' => (int) ($stats['active'] ?? 0),
                'activated' => $activated,
                'voted' => $voted,
            ],
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function edit(int $id)
    {
        $student = (new StudentModel())->find($id);
        if ($student === null) {
            return redirect()->to('/admin/siswa')->with('error', 'Data siswa tidak ditemukan.');
        }

        return view('admin/students/form', [
            'student' => $student,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function update(int $id)
    {
        $student = (new StudentModel())->find($id);
        if ($student === null) {
            return redirect()->to('/admin/siswa')->with('error', 'Data siswa tidak ditemukan.');
        }

        $rules = [
            'nis' => 'required|min_length[3]|max_length[30]|alpha_numeric_punct',
            'full_name' => 'required|min_length[3]|max_length[150]',
            'date_of_birth' => 'required|valid_date[Y-m-d]',
            'class_name' => 'required|max_length[50]',
            'whatsapp_number' => 'required|max_length[30]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $nis = trim((string) $this->request->getPost('nis'));
        $duplicate = (new StudentModel())->where('nis', $nis)->where('id !=', $id)->first();
        if ($duplicate !== null) {
            return redirect()->back()->withInput()->with('error', 'NIS sudah digunakan siswa lain.');
        }

        $whatsapp = normalize_wa_number((string) $this->request->getPost('whatsapp_number'));
        if ($whatsapp === '') {
            return redirect()->back()->withInput()->with('error', 'Nomor WhatsApp tidak valid. Gunakan format 08xx atau 62xx.');
        }

        (new StudentModel())->update($id, [
            'nis' => $nis,
            'full_name' => trim((string) $this->request->getPost('full_name')),
            'date_of_birth' => (string) $this->request->getPost('date_of_birth'),
            'class_name' => trim((string) $this->request->getPost('class_name')),
            'whatsapp_number' => $whatsapp,
        ]);
        $this->audit('student.update', 'student', $id);

        return redirect()->to('/admin/siswa')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function toggle(int $id)
    {
        $student = (new StudentModel())->find($id);
        if ($student === null) {
            return redirect()->to('/admin/siswa')->with('error', 'Data siswa tidak ditemukan.');
        }

        $status = (int) $student['is_active'] === 1 ? 0 : 1;
        (new StudentModel())->update($id, ['is_active' => $status]);
        $this->audit($status ? 'student.activate' : 'student.deactivate', 'student', $id);

        return redirect()->to('/admin/siswa')->with('success', 'Status siswa diperbarui.');
    }

    public function export()
    {
        $election = (new ElectionModel())->orderBy('id', 'DESC')->first();
        $electionId = $election !== null ? (int) $election['id'] : 0;

        $rows = db_connect()->table('students s')
            ->select('s.*, va.account_status, ev.has_voted')
            ->join('voter_accounts va', 'va.student_id = s.id', 'left')
            ->join('election_voters ev', 'ev.student_id = s.id AND ev.election_id = ' . $electionId, 'left')
            ->orderBy('s.class_name', 'ASC')
            ->orderBy('s.full_name', 'ASC')
            ->get()
            ->getResultArray();

        $this->audit('student.export', 'student', null, ['rows' => count($rows)]);

        $service = new StudentImportService();
        $spreadsheet = $service->buildExport($rows);
        $service->writeXlsx($spreadsheet, 'Data_Siswa_CAKRA_2026.xlsx');
    }
}