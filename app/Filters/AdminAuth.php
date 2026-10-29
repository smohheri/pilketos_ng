<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session('admin_id') === null) {
            return redirect()->to('/admin/login')->with('error', 'Silakan masuk terlebih dahulu.');
        }

        $user = (new UserModel())->where('id', (int) session('admin_id'))->where('is_active', 1)->first();
        if ($user === null) {
            session()->remove(['admin_id', 'admin_name', 'admin_email', 'admin_role', 'admin_election_id']);

            return redirect()->to('/admin/login')->with('error', 'Sesi berakhir. Silakan masuk kembali.');
        }

        $sessionData = [
            'admin_name' => $user['name'],
            'admin_email' => $user['email'],
            'admin_role' => $user['role'],
        ];

        if ($user['role'] === 'panitia') {
            $electionId = (int) ($user['election_id'] ?? 0);
            if ($electionId <= 0) {
                session()->remove(['admin_id', 'admin_name', 'admin_email', 'admin_role', 'admin_election_id']);

                return redirect()->to('/admin/login')->with('error', 'Akun panitia belum ditugaskan ke pemilihan. Hubungi admin.');
            }

            $sessionData['admin_election_id'] = $electionId;
        }

        session()->set($sessionData);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}