<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\StudentImportService;
use App\Models\ElectionModel;
use CodeIgniter\HTTP\Files\UploadedFile;

class ImportStudent extends BaseController
{
    public function index()
    {
        $this->cleanupTempFiles();
        $election = (new ElectionModel())->orderBy('id', 'DESC')->first();

        return view('admin/students/import', [
            'election' => $election,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function preview()
    {
        $file = $this->request->getFile('file');
        if (! $file instanceof UploadedFile) {
            return redirect()->to('/admin/siswa/import')->with('error', 'Pilih file Excel atau CSV yang akan diimpor.');
        }

        $check = $this->validateUpload($file);
        if (is_string($check)) {
            return redirect()->to('/admin/siswa/import')->with('error', $check);
        }

        $path = $this->storeTemp($file, $check);
        if ($path === null) {
            return redirect()->to('/admin/siswa/import')->with('error', 'File gagal disimpan sementara. Coba lagi.');
        }

        $parsed = (new StudentImportService())->parse($path, $check);
        if (! $parsed['ok']) {
            @unlink($path);

            return redirect()->to('/admin/siswa/import')->with('error', $parsed['message']);
        }

        session()->set([
            'import_path' => $path,
            'import_ext' => $check,
            'import_name' => $file->getClientName(),
            'import_summary' => $parsed['summary'],
        ]);

        $election = (new ElectionModel())->orderBy('id', 'DESC')->first();

        return view('admin/students/import_preview', [
            'parsed' => $parsed,
            'fileName' => $file->getClientName(),
            'election' => $election,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function confirm()
    {
        $path = (string) (session('import_path') ?? '');
        $ext = (string) (session('import_ext') ?? '');
        $base = realpath(WRITEPATH . 'uploads/imports');
        $real = $path !== '' ? realpath($path) : false;

        if ($path === '' || $real === false || $base === false || ! str_starts_with($real, $base) || ! is_file($real)) {
            $this->clearImportSession();

            return redirect()->to('/admin/siswa/import')->with('error', 'Berkas sementara tidak ditemukan. Silakan unggah ulang.');
        }

        $election = (new ElectionModel())->orderBy('id', 'DESC')->first();
        $service = new StudentImportService();
        $result = $service->import($real, $ext, $election !== null ? (int) $election['id'] : null);

        if (! $result['ok']) {
            return redirect()->to('/admin/siswa/import')->with('error', $result['message']);
        }

        @unlink($real);
        $this->clearImportSession();
        $this->audit('student.import', 'student', null, [
            'inserted' => $result['inserted'],
            'skipped' => $result['skipped'],
            'file_extension' => $ext,
        ]);

        return view('admin/students/import_result', [
            'result' => $result,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function template()
    {
        $service = new StudentImportService();
        $service->writeXlsx($service->buildTemplate(), 'Template_Import_Siswa_CAKRA_2026.xlsx');
    }

    private function validateUpload(UploadedFile $file): array|string
    {
        if ($file->getError() === UPLOAD_ERR_INI_SIZE || $file->getSize() > StudentImportService::MAX_BYTES) {
            return 'Ukuran file maksimal 2 MB.';
        }
        if (! $file->isValid() || $file->hasMoved()) {
            return 'File tidak valid atau gagal diunggah.';
        }

        $extension = strtolower($file->getClientExtension() ?: $file->guessExtension() ?: '');
        if (! in_array($extension, ['xlsx', 'csv'], true)) {
            return 'Format file harus .xlsx atau .csv.';
        }

        $mime = strtolower((string) $file->getMimeType());
        $allowed = $extension === 'xlsx'
            ? [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/zip',
            ]
            : [
                'text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel',
            ];

        if (! in_array($mime, $allowed, true)) {
            return 'Tipe file tidak diizinkan. Gunakan file Excel/CSV asli sesuai template.';
        }

        return [
            'extension' => $extension,
            'name' => $file->getRandomName(),
        ];
    }

    private function storeTemp(UploadedFile $file, array $info): ?string
    {
        $dir = WRITEPATH . 'uploads/imports';
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            return null;
        }

        $name = 'import-' . date('YmdHis') . '-' . bin2hex(random_bytes(5)) . '.' . $info['extension'];
        $file->move($dir, $name, true);

        return $dir . DIRECTORY_SEPARATOR . $name;
    }

    private function cleanupTempFiles(): void
    {
        $dir = WRITEPATH . 'uploads/imports';
        if (! is_dir($dir)) {
            return;
        }

        foreach (glob($dir . DIRECTORY_SEPARATOR . 'import-*') ?: [] as $file) {
            if (is_file($file) && filemtime($file) < time() - 3600) {
                @unlink($file);
            }
        }
    }

    private function clearImportSession(): void
    {
        session()->remove(['import_path', 'import_ext', 'import_name', 'import_summary']);
    }
}