<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ElectionModel;
use App\Models\UserModel;

class User extends BaseController
{
    public function index()
    {
        $elections = (new ElectionModel())->orderBy('id', 'ASC')->findAll();
        $electionTypes = [];
        foreach ($elections as $election) {
            $electionTypes[(int) $election['id']] = election_type_label($election['type'] ?? null);
        }

        return view('admin/users/index', [
            'users' => (new UserModel())->orderBy('role', 'ASC')->orderBy('name', 'ASC')->findAll(),
            'elections' => $elections,
            'electionTypes' => $electionTypes,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[120]',
            'email' => 'required|valid_email|max_length[190]|is_unique[users.email]',
            'password' => 'required|min_length[8]|max_length[72]',
            'role' => 'required|in_list[admin,panitia]',
            'election_id' => 'permit_empty|is_natural_no_zero',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $role = (string) $this->request->getPost('role');
        $electionId = (int) $this->request->getPost('election_id');
        if ($role === 'panitia' && $electionId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Pilih pemilihan untuk akun panitia.');
        }
        if ($role === 'admin') {
            $electionId = 0;
        }

        $id = (int) (new UserModel())->insert([
            'name' => trim((string) $this->request->getPost('name')),
            'email' => strtolower(trim((string) $this->request->getPost('email'))),
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'role' => $role,
            'election_id' => $electionId > 0 ? $electionId : null,
            'is_active' => 1,
        ], true);
        $this->audit('user.create', 'user', $id, ['role' => $role, 'election_id' => $electionId ?: null]);

        return redirect()->to('/admin/pengguna')->with('success', 'Akun panitia berhasil dibuat.');
    }

    public function update(int $id)
    {
        $user = (new UserModel())->find($id);
        if ($user === null) {
            return redirect()->to('/admin/pengguna')->with('error', 'Akun tidak ditemukan.');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[120]',
            'email' => 'required|valid_email|max_length[190]|is_unique[users.email,id,' . $id . ']',
            'role' => 'required|in_list[admin,panitia]',
            'password' => 'permit_empty|min_length[8]|max_length[72]',
            'election_id' => 'permit_empty|is_natural_no_zero',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $isSelf = $id === (int) session('admin_id');
        $role = $isSelf ? 'admin' : (string) $this->request->getPost('role');
        $active = $isSelf ? 1 : ($this->request->getPost('is_active') === '1' ? 1 : 0);
        $electionId = $role === 'panitia' ? (int) $this->request->getPost('election_id') : 0;

        if ($role === 'panitia' && $electionId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Pilih pemilihan untuk akun panitia.');
        }

        $data = [
            'name' => trim((string) $this->request->getPost('name')),
            'email' => strtolower(trim((string) $this->request->getPost('email'))),
            'role' => $role,
            'election_id' => $electionId > 0 ? $electionId : null,
            'is_active' => $active,
        ];

        $password = (string) $this->request->getPost('password');
        if ($password !== '') {
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        (new UserModel())->update($id, $data);
        if ($isSelf) {
            session()->set('admin_name', $data['name']);
            session()->set('admin_email', $data['email']);
        }
        $this->audit('user.update', 'user', $id, ['role' => $role, 'election_id' => $electionId ?: null]);

        return redirect()->to('/admin/pengguna')->with('success', 'Akun diperbarui.');
    }

    public function toggle(int $id)
    {
        $user = (new UserModel())->find($id);
        if ($user === null) {
            return redirect()->to('/admin/pengguna')->with('error', 'Akun tidak ditemukan.');
        }
        if ($id === (int) session('admin_id')) {
            return redirect()->to('/admin/pengguna')->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $status = (int) $user['is_active'] === 1 ? 0 : 1;
        (new UserModel())->update($id, ['is_active' => $status]);
        $this->audit($status ? 'user.activate' : 'user.deactivate', 'user', $id);

        return redirect()->to('/admin/pengguna')->with('success', 'Status akun diperbarui.');
    }
}