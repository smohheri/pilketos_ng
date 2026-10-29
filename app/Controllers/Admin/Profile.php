<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\UploadService;
use App\Models\ElectionModel;
use App\Models\SchoolProfileModel;

class Profile extends BaseController
{
    public function index()
    {
        return view('admin/profile/index', [
            'profile' => (new SchoolProfileModel())->active(),
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function update()
    {
        $rules = [
            'school_name' => 'required|max_length[180]',
            'short_name' => 'permit_empty|max_length[80]',
            'description' => 'permit_empty',
            'vision' => 'permit_empty',
            'mission' => 'permit_empty',
            'official_website' => 'permit_empty|max_length[255]',
            'official_contact' => 'permit_empty|max_length[255]',
            'address' => 'permit_empty',
            'footer_text' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new SchoolProfileModel();
        $profile = $model->active();
        $uploads = new UploadService();

        $data = [
            'school_name' => trim((string) $this->request->getPost('school_name')),
            'short_name' => trim((string) $this->request->getPost('short_name')),
            'description' => trim((string) $this->request->getPost('description')),
            'vision' => trim((string) $this->request->getPost('vision')),
            'mission' => trim((string) $this->request->getPost('mission')),
            'official_website' => trim((string) $this->request->getPost('official_website')),
            'official_contact' => trim((string) $this->request->getPost('official_contact')),
            'address' => trim((string) $this->request->getPost('address')),
            'footer_text' => trim((string) $this->request->getPost('footer_text')),
            'updated_by' => (int) session('admin_id'),
        ];

        $oldSchoolName = trim((string) ($profile['school_name'] ?? ''));
        $oldShortName = trim((string) ($profile['short_name'] ?? ''));
        if ($oldSchoolName !== '' && ($oldSchoolName !== $data['school_name'] || $oldShortName !== $data['short_name'])) {
            $legacyNames = array_values(array_unique(array_filter([$oldSchoolName, $oldShortName])));
            $replaceSchoolName = static function (?string $text) use ($legacyNames, $data): string {
                return str_replace($legacyNames, $data['school_name'], (string) $text);
            };

            foreach (['description', 'vision', 'mission', 'footer_text'] as $field) {
                $data[$field] = $replaceSchoolName($data[$field] ?? '');
            }

            $elections = new ElectionModel();
            foreach ($elections->findAll() as $election) {
                $elections->update((int) $election['id'], [
                    'title' => $replaceSchoolName($election['title'] ?? ''),
                    'description' => $replaceSchoolName($election['description'] ?? ''),
                ]);
            }
        }

        foreach (['logo' => 'branding', 'hero' => 'branding'] as $field => $folder) {
            $file = $this->request->getFile($field);
            if ($file !== null && $file->isValid() && ! $file->hasMoved()) {
                $result = $uploads->image($file, $folder);
                if (! $result['ok']) {
                    return redirect()->back()->withInput()->with('error', ucfirst($field) . ': ' . $result['message']);
                }

                $old = $field === 'logo' ? ($profile['logo_path'] ?? null) : ($profile['hero_image_path'] ?? null);
                if ($old !== null && $old !== $result['path']) {
                    $uploads->delete($old);
                }

                $data[$field === 'logo' ? 'logo_path' : 'hero_image_path'] = $result['path'];
            }
        }

        if ($profile === null) {
            $id = $model->insert($data, true);
            $this->audit('profile.create', 'school_profile', (int) $id);
        } else {
            $model->update((int) $profile['id'], $data);
            $id = (int) $profile['id'];
            $this->audit('profile.update', 'school_profile', $id);
        }

        return redirect()->to('/admin/profil')->with('success', 'Profil sekolah berhasil diperbarui.');
    }
}