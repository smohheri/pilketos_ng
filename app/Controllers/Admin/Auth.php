<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        if (session('admin_id') !== null) {
            return redirect()->to('/admin');
        }

        return view('admin/auth/login', [
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function attempt()
    {
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            return redirect()->back()->withInput()->with('error', 'Email dan kata sandi wajib diisi.');
        }

        $throttler = service('throttler');
        $key = 'admin-login-' . md5($this->clientIp() . '|' . $email);
        if ($throttler->check($key, 5, 10 * MINUTE) === false) {
            return redirect()->back()->withInput()->with('error', 'Terlalu banyak percobaan masuk. Coba lagi dalam beberapa menit.');
        }

        $user = (new UserModel())->findActiveByEmail($email);
        if ($user === null || ! password_verify($password, (string) $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Email atau kata sandi tidak sesuai.');
        }

        (new UserModel())->update((int) $user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);
        set_admin_session($user);
        session()->regenerate(true);
        $this->audit('auth.login', 'user', (int) $user['id']);

        return redirect()->to('/admin')->with('success', 'Selamat datang, ' . $user['name'] . '.');
    }

    public function logout()
    {
        $admin = current_admin();
        if ($admin !== null) {
            $this->audit('auth.logout', 'user', $admin['id']);
        }

        session()->remove(['admin_id', 'admin_name', 'admin_email', 'admin_role', 'admin_election_id']);
        session()->regenerate(true);
        session()->setFlashdata('success', 'Anda telah keluar dari panel panitia.');

        return redirect()->to('/admin/login');
    }
}