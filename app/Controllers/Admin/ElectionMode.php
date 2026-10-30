<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ElectionService;
use App\Models\CandidateModel;
use App\Models\ElectionModel;
use App\Models\SystemSettingModel;

class ElectionMode extends BaseController
{
    public function index()
    {
        $elections = admin_elections();
        $service = new ElectionService();
        $candidateModel = new CandidateModel();

        $items = [];
        foreach ($elections as $election) {
            $items[] = [
                'election' => $election,
                'phase' => $service->phase($election),
                'candidateCount' => count($candidateModel->forElection((int) $election['id'], false)),
            ];
        }

        return view('admin/mode/index', [
            'items' => $items,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
            'pageTitle' => 'Mode Aplikasi',
            'pageSubtitle' => 'Aktifkan salah satu atau kedua pemilihan yang berjalan di aplikasi.',
        ]);
    }

    public function update()
    {
        $enabled = (array) $this->request->getPost('enabled');
        $enabledIds = array_values(array_map('intval', array_filter($enabled, 'is_numeric')));

        if ($enabledIds === []) {
            return redirect()->back()->withInput()->with('error', 'Aktifkan minimal satu pemilihan.');
        }

        $labels = (array) $this->request->getPost('labels');
        foreach (array_keys(election_type_options()) as $type) {
            $label = trim((string) ($labels[$type] ?? ''));
            if ($label !== '' && mb_strlen($label) > 60) {
                return redirect()->back()->withInput()->with('error', 'Nama pemilihan maksimal 60 karakter.');
            }
        }

        $model = new ElectionModel();
        $all = $model->orderBy('id', 'ASC')->findAll();

        $changed = [];
        foreach ($all as $election) {
            $id = (int) $election['id'];
            $shouldEnable = in_array($id, $enabledIds, true) ? 1 : 0;

            if ((int) ($election['is_enabled'] ?? 0) !== $shouldEnable) {
                $model->update($id, ['is_enabled' => $shouldEnable]);
                $changed[(string) ($election['type'] ?? $id)] = (bool) $shouldEnable;
            }
        }

        $settings = new SystemSettingModel();
        $userId = (int) session('admin_id');
        $labelChanges = [];
        foreach (array_keys(election_type_options()) as $type) {
            $label = trim((string) ($labels[$type] ?? ''));
            $settings->put('election_label_' . $type, $label, $userId);
            $labelChanges[$type] = $label;
        }

        $this->audit('election.mode.update', 'election', null, [
            'enabled' => $changed,
            'labels' => $labelChanges,
        ]);

        return redirect()->to('/admin/mode-aplikasi')->with('success', 'Mode aplikasi dan nama pemilihan berhasil diperbarui.');
    }
}