<?php

namespace App\Controllers;

use App\Libraries\ElectionService;
use App\Libraries\OtpService;
use App\Models\StudentModel;
use App\Models\VoterAccountModel;

class Auth extends BaseController
{
    public function activation()
    {
        if (session('student_id') !== null) {
            return redirect()->to('/akun');
        }

        return view('auth/activation', [
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function sendActivation()
    {
        $nis = trim((string) $this->request->getPost('nis'));
        $dob = trim((string) $this->request->getPost('date_of_birth'));

        if ($nis === '' || $dob === '') {
            return redirect()->back()->withInput()->with('error', 'NIS dan tanggal lahir wajib diisi.');
        }

        $student = (new StudentModel())
            ->where('nis', $nis)
            ->where('date_of_birth', $dob)
            ->where('is_active', 1)
            ->first();

        if ($student === null) {
            return redirect()->back()->withInput()->with('error', 'Data siswa tidak cocok. Periksa kembali NIS dan tanggal lahir, atau hubungi panitia.');
        }

        $studentId = (int) $student['id'];
        $accounts = new VoterAccountModel();
        $account = $accounts->forStudent($studentId);

        if ($account !== null && $account['account_status'] === 'active') {
            return redirect()->to('/masuk')->with('success', 'Akun Anda sudah aktif. Silakan masuk menggunakan nomor WhatsApp terdaftar.');
        }

        if ($account === null) {
            $accounts->insert([
                'student_id' => $studentId,
                'account_status' => 'pending',
            ]);
        }

        (new ElectionService())->ensureEligibilityForAll($studentId);

        $otp = (new OtpService())->request($student, 'activation', $this->clientIp());
        if (! $otp['ok']) {
            return redirect()->back()->withInput()->with('error', $otp['message']);
        }

        $this->startOtpSession($student, 'activation', $otp['demo_otp'] ?? null);

        return redirect()->to('/aktivasi/otp')->with('success', $otp['message']);
    }

    public function activationOtp()
    {
        return $this->handleOtp('activation');
    }

    public function login()
    {
        if (session('student_id') !== null) {
            return redirect()->to('/akun');
        }

        return view('auth/login', [
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function sendLogin()
    {
        $raw = trim((string) $this->request->getPost('whatsapp_number'));
        $number = normalize_wa_number($raw);

        $genericMessage = 'Jika nomor terdaftar dan akun sudah aktif, kode OTP akan dikirim ke WhatsApp tersebut.';

        if ($number === '') {
            return redirect()->back()->withInput()->with('error', 'Masukkan nomor WhatsApp yang terdaftar.');
        }

        $student = (new StudentModel())->where('whatsapp_number', $number)->where('is_active', 1)->first();
        if ($student === null) {
            return redirect()->back()->withInput()->with('error', $genericMessage);
        }

        $account = (new VoterAccountModel())->forStudent((int) $student['id']);
        if ($account === null || $account['account_status'] !== 'active') {
            return redirect()->back()->withInput()->with('error', 'Akun belum aktif. Lakukan aktivasi akun terlebih dahulu.');
        }

        $otp = (new OtpService())->request($student, 'login', $this->clientIp());
        if (! $otp['ok']) {
            return redirect()->back()->withInput()->with('error', $otp['message']);
        }

        $this->startOtpSession($student, 'login', $otp['demo_otp'] ?? null);

        return redirect()->to('/masuk/otp')->with('success', $otp['message']);
    }

    public function loginOtp()
    {
        return $this->handleOtp('login');
    }

    public function logout()
    {
        clear_student_session();
        session()->regenerate(true);
        session()->setFlashdata('success', 'Anda telah keluar dari akun pemilih.');

        return redirect()->to('/masuk');
    }

    protected function handleOtp(string $purpose)
    {
        $studentId = (int) (session('otp_student_id') ?? 0);
        $sessionPurpose = (string) (session('otp_purpose') ?? '');
        $flow = (string) (session('otp_flow') ?? '');

        if ($studentId <= 0 || $sessionPurpose !== $purpose) {
            return redirect()->to($purpose === 'activation' ? '/aktivasi' : '/masuk')
                ->with('error', 'Sesi OTP tidak ditemukan. Silakan mulai kembali.');
        }

        if ($this->request->is('post')) {
            $code = preg_replace('/\D+/', '', (string) $this->request->getPost('otp')) ?? '';

            if (strlen($code) !== 6) {
                return redirect()->back()->with('error', 'Masukkan 6 digit kode OTP.');
            }

            $result = (new OtpService())->verify($studentId, $purpose, $code);
            if (! $result['ok']) {
                return redirect()->back()->with('error', $result['message']);
            }

            $student = (new StudentModel())->where('id', $studentId)->where('is_active', 1)->first();
            if ($student === null) {
                return redirect()->to('/masuk')->with('error', 'Akun tidak tersedia. Hubungi panitia.');
            }

            $accounts = new VoterAccountModel();
            $account = $accounts->forStudent($studentId);

            if ($purpose === 'activation') {
                $accounts->update($account['id'], [
                    'account_status' => 'active',
                    'activated_at' => date('Y-m-d H:i:s'),
                    'last_login_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $accounts->update($account['id'], [
                    'last_login_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $electionService = new ElectionService();
            $electionService->ensureEligibilityForAll($studentId);
            $votingElection = $electionService->firstVotingOpenForStudent($studentId);

            $intended = session('student_after_login');
            $this->clearOtpSession();
            set_student_session($student);
            session()->regenerate(true);
            session()->remove('student_after_login');

            $message = $purpose === 'activation'
                ? 'Akun berhasil diaktifkan. Selamat datang di CAKRA 2026.'
                : 'Berhasil masuk.';

            $target = $votingElection !== null ? election_voting_url($votingElection) : '/akun';
            if (is_string($intended)
                && str_starts_with($intended, '/')
                && ! str_starts_with($intended, '//')
                && ! in_array($intended, ['/voting', '/voting/'], true)) {
                $target = $intended;
            }

            return redirect()->to($target)->with('success', $message);
        }

        $student = (new StudentModel())->find($studentId);
        if ($student === null) {
            $this->clearOtpSession();

            return redirect()->to('/masuk')->with('error', 'Sesi OTP tidak valid.');
        }

        return view('auth/otp', [
            'appName' => app_setting('app_name', 'CAKRA 2026'),
            'flow' => $flow,
            'purpose' => $purpose,
            'contact' => (string) session('otp_contact'),
            'demoOtp' => (string) (session('otp_demo') ?? ''),
        ]);
    }

    protected function startOtpSession(array $student, string $purpose, ?string $demoOtp): void
    {
        session()->set([
            'otp_student_id' => (int) $student['id'],
            'otp_purpose' => $purpose,
            'otp_flow' => $purpose,
            'otp_contact' => mask_phone($student['whatsapp_number']),
            'otp_demo' => $demoOtp,
        ]);
    }

    protected function clearOtpSession(): void
    {
        session()->remove(['otp_student_id', 'otp_purpose', 'otp_flow', 'otp_contact', 'otp_demo']);
    }
}