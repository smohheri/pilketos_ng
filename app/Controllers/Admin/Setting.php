<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\WhatsAppService;
use App\Models\SystemSettingModel;

class Setting extends BaseController
{
    public function index()
    {
        return view('admin/settings/index', [
            'settings' => (new SystemSettingModel())->allAsArray(),
            'whatsapp' => (new WhatsAppService())->config(),
            'pageTitle' => 'Pengaturan',
            'pageSubtitle' => 'Konfigurasi identitas aplikasi, kontak bantuan, dan gateway WhatsApp.',
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function update()
    {
        if (! $this->validate([
            'app_name' => 'required|max_length[100]',
            'app_tagline' => 'permit_empty|max_length[180]',
            'support_email' => 'permit_empty|valid_email|max_length[190]',
            'support_whatsapp' => 'permit_empty|max_length[30]',
            'school_year' => 'permit_empty|max_length[30]',
            'announcement' => 'permit_empty|max_length[255]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new SystemSettingModel();
        $userId = (int) session('admin_id');

        $whatsapp = trim((string) $this->request->getPost('support_whatsapp'));
        if ($whatsapp !== '') {
            $whatsapp = normalize_wa_number($whatsapp);
        }

        foreach (['app_name', 'app_tagline', 'support_email', 'support_whatsapp', 'school_year', 'announcement'] as $key) {
            $value = $key === 'support_whatsapp'
                ? $whatsapp
                : trim((string) $this->request->getPost($key));
            $model->put($key, $value, $userId);
        }

        $this->audit('setting.update', 'system_setting', null);

        return redirect()->to('/admin/pengaturan')->with('success', 'Pengaturan aplikasi diperbarui.');
    }

    public function openwa()
    {
        return view('admin/settings/openwa', [
            'whatsapp' => (new WhatsAppService())->config(),
            'demoMode' => filter_var(env('cakra.demoMode', false), FILTER_VALIDATE_BOOLEAN),
            'pageTitle' => 'WhatsApp Gateway',
            'pageSubtitle' => 'Hubungkan CAKRA ke server OpenWA untuk pengiriman kode OTP.',
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function updateOpenwa()
    {
        $baseUrl = trim((string) $this->request->getPost('base_url'));
        $sessionId = trim((string) $this->request->getPost('session_id'));
        $endpoint = trim((string) $this->request->getPost('endpoint'));
        $payloadStyle = (string) $this->request->getPost('payload_style');
        $enabled = $this->request->getPost('enabled') === '1';
        $verifySsl = $this->request->getPost('verify_ssl') === '1';

        if ($baseUrl !== '' && (filter_var($baseUrl, FILTER_VALIDATE_URL) === false
            || ! in_array(strtolower((string) parse_url($baseUrl, PHP_URL_SCHEME)), ['http', 'https'], true))) {
            return redirect()->back()->withInput()->with('error', 'URL server OpenWA tidak valid. Gunakan format seperti http://localhost:2785.');
        }

        if ($enabled && $baseUrl === '') {
            return redirect()->back()->withInput()->with('error', 'Isi URL server OpenWA sebelum mengaktifkan gateway.');
        }

        $baseUrl = $baseUrl !== '' ? rtrim($baseUrl, '/') : '';
        $endpoint = $endpoint !== '' ? '/' . ltrim($endpoint, '/') : '/sendText';
        $payloadStyle = $payloadStyle === 'flat' ? 'flat' : 'args';

        $model = new SystemSettingModel();
        $userId = (int) session('admin_id');

        $values = [
            'openwa_enabled' => $enabled ? '1' : '0',
            'openwa_base_url' => $baseUrl,
            'openwa_session_id' => $sessionId,
            'openwa_endpoint' => $endpoint,
            'openwa_payload_style' => $payloadStyle,
            'openwa_verify_ssl' => $verifySsl ? '1' : '0',
        ];

        foreach (['api_key', 'user', 'pass_auth'] as $secret) {
            $value = trim((string) $this->request->getPost($secret));
            if ($value !== '') {
                $values['openwa_' . $secret] = $value;
            }
        }

        foreach ($values as $key => $value) {
            $model->put($key, $value, $userId);
        }

        $this->audit('setting.openwa.update', 'system_setting', null, [
            'enabled' => $enabled,
            'base_url' => $baseUrl,
            'session_id' => $sessionId,
            'session_mode' => $sessionId !== '' ? 'openwa' : 'legacy',
        ]);

        return redirect()->to('/admin/pengaturan/openwa')->with('success', 'Pengaturan WhatsApp diperbarui.');
    }

    public function testOpenwa()
    {
        $result = (new WhatsAppService())->testConnection();

        return redirect()
            ->to('/admin/pengaturan/openwa')
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }
}