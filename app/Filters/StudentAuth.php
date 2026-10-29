<?php

namespace App\Filters;

use App\Models\StudentModel;
use App\Models\VoterAccountModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class StudentAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session('student_id') === null) {
            if ($request->is('get')) {
                $path = ltrim($request->getUri()->getPath(), '/');
                if (str_starts_with($path, 'voting') || str_starts_with($path, 'akun')) {
                    $intended = '/' . $path;
                    $query = $request->getUri()->getQuery();
                    if ($query !== '') {
                        $intended .= '?' . $query;
                    }
                    session()->set('student_after_login', $intended);
                }
            }

            return redirect()->to('/masuk')->with('error', 'Masuk untuk melanjutkan ke bilik suara.');
        }

        $studentId = (int) session('student_id');
        $student = (new StudentModel())->where('id', $studentId)->where('is_active', 1)->first();
        $account = (new VoterAccountModel())->forStudent($studentId);

        if ($student === null || $account === null || $account['account_status'] !== 'active') {
            clear_student_session();

            return redirect()->to('/masuk')->with('error', 'Akun pemilih tidak aktif. Silakan aktivasi atau hubungi panitia.');
        }

        session()->set([
            'student_nis' => $student['nis'],
            'student_name' => $student['full_name'],
            'student_class' => $student['class_name'],
            'student_whatsapp' => $student['whatsapp_number'],
        ]);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}