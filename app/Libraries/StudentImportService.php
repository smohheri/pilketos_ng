<?php

namespace App\Libraries;

use App\Models\ElectionVoterModel;
use App\Models\StudentModel;
use App\Models\VoterAccountModel;
use DateTimeImmutable;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentImportService
{
    public const MAX_BYTES = 2 * 1024 * 1024;

    public const HEADERS = ['NIS', 'Nama Lengkap', 'Tanggal Lahir', 'Kelas', 'WhatsApp'];

    /**
     * @return array{
     *   ok: bool, message: string, rows: array<int, array>, errors: array<int, array>,
     *   summary: array{total:int, valid:int, duplicate:int, invalid:int}, has_fatal: bool
     * }
     */
    public function parse(string $filePath, string $extension): array
    {
        $empty = [
            'ok' => false, 'message' => '', 'rows' => [], 'errors' => [],
            'summary' => ['total' => 0, 'valid' => 0, 'duplicate' => 0, 'invalid' => 0],
            'has_fatal' => true,
        ];

        try {
            $reader = IOFactory::createReader(strtolower($extension) === 'csv' ? 'Csv' : 'Xlsx');
            $reader->setReadDataOnly(true);
            if (method_exists($reader, 'setIncludeCharts')) {
                $reader->setIncludeCharts(false);
            }
            $spreadsheet = $reader->load($filePath);
        } catch (\Throwable $e) {
            return array_merge($empty, ['message' => 'File tidak dapat dibaca. Pastikan format .xlsx atau .csv sesuai template.']);
        }

        $sheet = $spreadsheet->getSheet(0);
        $highestRow = (int) $sheet->getHighestDataRow();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        if ($highestRow < 1) {
            return array_merge($empty, ['message' => 'File kosong. Gunakan template yang disediakan.']);
        }

        $headerMap = [];
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $header = $this->normalizeHeader((string) $sheet->getCell($letter . '1')->getFormattedValue());
            if ($header !== '') {
                $headerMap[$header] = $col;
            }
        }

        $required = [
            'nis' => 'NIS',
            'namalengkap' => 'Nama Lengkap',
            'tanggallahir' => 'Tanggal Lahir',
            'kelas' => 'Kelas',
            'whatsapp' => 'WhatsApp',
        ];

        $missing = [];
        foreach ($required as $key => $label) {
            if (! isset($headerMap[$key])) {
                $missing[] = $label;
            }
        }
        if ($missing !== []) {
            return array_merge($empty, [
                'message' => 'Header tidak sesuai template. Kolom tidak ditemukan: ' . implode(', ', $missing) . '.',
            ]);
        }

        $students = new StudentModel();
        $rows = [];
        $errors = [];
        $seenNis = [];
        $summary = ['total' => 0, 'valid' => 0, 'duplicate' => 0, 'invalid' => 0];

        for ($rowNumber = 2; $rowNumber <= $highestRow; $rowNumber++) {
            $values = [];
            foreach ($required as $key => $label) {
                $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($headerMap[$key]);
                $cell = $sheet->getCell($letter . $rowNumber);
                $values[$key] = $key === 'tanggallahir'
                    ? $this->parseDate($cell->getValue(), $cell->getFormattedValue())
                    : trim((string) $cell->getFormattedValue());
            }

            if (implode('', $values) === '') {
                continue;
            }

            $summary['total']++;
            $messages = [];

            $nis = preg_replace('/\s+/', '', $values['nis']) ?? '';
            if ($nis === '' || ! preg_match('/^[A-Za-z0-9._\/-]{3,30}$/', $nis)) {
                $messages[] = 'NIS wajib diisi dengan 3–30 karakter huruf/angka.';
            }
            if (isset($seenNis[$nis])) {
                $messages[] = 'NIS duplikat di dalam file (baris ' . $seenNis[$nis] . ').';
            } elseif ($nis !== '') {
                $seenNis[$nis] = $rowNumber;
            }

            $name = trim($values['namalengkap']);
            if (mb_strlen($name) < 3 || mb_strlen($name) > 150) {
                $messages[] = 'Nama lengkap wajib diisi (3–150 karakter).';
            }

            $dob = $values['tanggallahir'];
            if ($dob === '') {
                $messages[] = 'Tanggal lahir wajib diisi dengan format YYYY-MM-DD.';
            }

            $className = trim($values['kelas']);
            if ($className === '' || mb_strlen($className) > 50) {
                $messages[] = 'Kelas wajib diisi (maksimal 50 karakter).';
            }

            $whatsapp = $this->normalizeWhatsApp($values['whatsapp']);
            if ($whatsapp === '') {
                $messages[] = 'Nomor WhatsApp tidak valid. Gunakan format 08xx atau 62xx.';
            }

            $duplicateDb = false;
            if ($messages === [] && $nis !== '') {
                $existing = $students->findByNis($nis);
                if ($existing !== null) {
                    $duplicateDb = true;
                    $summary['duplicate']++;
                }
            }

            if ($messages !== []) {
                $summary['invalid']++;
                $status = 'invalid';
                $errors[] = ['row' => $rowNumber, 'nis' => $nis, 'messages' => $messages];
            } elseif ($duplicateDb) {
                $status = 'duplicate';
            } else {
                $status = 'valid';
                $summary['valid']++;
            }

            $rows[] = [
                'row' => $rowNumber,
                'nis' => $nis,
                'full_name' => $name,
                'date_of_birth' => $dob,
                'class_name' => $className,
                'whatsapp_number' => $whatsapp,
                'status' => $status,
                'messages' => $messages,
            ];
        }

        if ($summary['total'] === 0) {
            return array_merge($empty, ['message' => 'Tidak ada baris data yang ditemukan di bawah header.']);
        }

        return [
            'ok' => true,
            'message' => 'File berhasil divalidasi.',
            'rows' => $rows,
            'errors' => $errors,
            'summary' => $summary,
            'has_fatal' => $summary['invalid'] > 0,
        ];
    }

    /**
     * @return array{ok: bool, message: string, inserted: int, skipped: int}
     */
    public function import(string $filePath, string $extension, ?int $electionId): array
    {
        $parsed = $this->parse($filePath, $extension);
        if (! $parsed['ok'] || $parsed['has_fatal']) {
            return [
                'ok' => false,
                'message' => 'Import dibatalkan karena masih ada baris yang tidak valid.',
                'inserted' => 0,
                'skipped' => 0,
            ];
        }

        $db = db_connect();
        $students = new StudentModel();
        $accounts = new VoterAccountModel();
        $voters = new ElectionVoterModel();

        $db->transBegin();
        $inserted = 0;
        $skipped = 0;

        try {
            foreach ($parsed['rows'] as $row) {
                if ($row['status'] !== 'valid') {
                    $skipped++;
                    continue;
                }

                if ($students->findByNis($row['nis']) !== null) {
                    $skipped++;
                    continue;
                }

                $studentId = (int) $students->insert([
                    'nis' => $row['nis'],
                    'full_name' => $row['full_name'],
                    'date_of_birth' => $row['date_of_birth'],
                    'class_name' => $row['class_name'],
                    'whatsapp_number' => $row['whatsapp_number'],
                    'is_active' => 1,
                ], true);

                if ($studentId <= 0) {
                    throw new \RuntimeException('Gagal menyimpan siswa.');
                }

                if ($accounts->forStudent($studentId) === null) {
                    $accounts->insert([
                        'student_id' => $studentId,
                        'account_status' => 'pending',
                    ]);
                }

                if ($electionId !== null && $voters->forStudent($electionId, $studentId) === null) {
                    $voters->insert([
                        'election_id' => $electionId,
                        'student_id' => $studentId,
                        'eligibility_status' => 'eligible',
                        'has_voted' => 0,
                    ]);
                }

                $inserted++;
            }

            if (! $db->transStatus()) {
                throw new \RuntimeException('Transaksi import gagal.');
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            return [
                'ok' => false,
                'message' => 'Import gagal diproses. Tidak ada data yang disimpan.',
                'inserted' => 0,
                'skipped' => 0,
            ];
        }

        return [
            'ok' => true,
            'message' => 'Import selesai.',
            'inserted' => $inserted,
            'skipped' => $skipped,
        ];
    }

    public function buildTemplate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Siswa');

        foreach (self::HEADERS as $index => $header) {
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($letter . '1', $header);
        }

        $this->styleHeader($sheet, 'A1:E1');
        $widths = ['A' => 20, 'B' => 32, 'C' => 20, 'D' => 16, 'E' => 24];
        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        for ($row = 2; $row <= 1000; $row++) {
            $sheet->setCellValueExplicit('A' . $row, '', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $row, '', DataType::TYPE_STRING);
            $sheet->getStyle('A' . $row)->getNumberFormat()->setFormatCode('@');
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('@');
        }

        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:E1');

        $guide = $spreadsheet->createSheet();
        $guide->setTitle('Petunjuk');
        $lines = [
            ['PETUNJUK IMPORT DATA SISWA — CAKRA 2026'],
            ['1. Isi data mulai baris ke-2 pada sheet "Data Siswa". Jangan mengubah header.'],
            ['2. NIS dan WhatsApp disimpan sebagai teks agar angka 0 di awal tidak hilang.'],
            ['3. Tanggal lahir gunakan format YYYY-MM-DD, contoh 2008-01-15.'],
            ['4. Nomor WhatsApp gunakan format 08xx atau 62xx. Sistem menormalkan otomatis.'],
            ['5. NIS harus unik. Baris dengan NIS yang sudah terdaftar akan dilewati.'],
            ['6. File maksimal 2 MB dan hanya format .xlsx atau .csv.'],
            ['7. Semua baris harus valid sebelum import dapat dikonfirmasi.'],
            ['8. Data siswa adalah data pribadi. Simpan dan bagikan hanya melalui kanal resmi sekolah.'],
        ];
        foreach ($lines as $index => $line) {
            $guide->setCellValue('A' . ($index + 1), $line[0]);
        }
        $guide->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $guide->getColumnDimension('A')->setWidth(110);
        $guide->getStyle('A1:A9')->getAlignment()->setWrapText(true);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    public function buildExport(array $rows): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Siswa');

        $headers = ['NIS', 'Nama Lengkap', 'Tanggal Lahir', 'Kelas', 'WhatsApp', 'Status Akun', 'Sudah Memilih'];
        foreach ($headers as $index => $header) {
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($letter . '1', $header);
        }
        $this->styleHeader($sheet, 'A1:G1');

        $rowNumber = 2;
        foreach ($rows as $row) {
            $sheet->setCellValueExplicit('A' . $rowNumber, (string) $row['nis'], DataType::TYPE_STRING);
            $sheet->setCellValue('B' . $rowNumber, $row['full_name']);
            $sheet->setCellValueExplicit('C' . $rowNumber, (string) $row['date_of_birth'], DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $rowNumber, $row['class_name']);
            $sheet->setCellValueExplicit('E' . $rowNumber, (string) $row['whatsapp_number'], DataType::TYPE_STRING);
            $sheet->setCellValue('F' . $rowNumber, $row['account_status'] ?? 'pending');
            $sheet->setCellValue('G' . $rowNumber, ! empty($row['has_voted']) ? 'Ya' : 'Belum');
            $rowNumber++;
        }

        foreach (['A' => 20, 'B' => 32, 'C' => 16, 'D' => 16, 'E' => 22, 'F' => 18, 'G' => 18] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:G1');

        return $spreadsheet;
    }

    public function writeXlsx(Spreadsheet $spreadsheet, string $filename): void
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    protected function styleHeader(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFF8F0'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '101828'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);
    }

    protected function normalizeHeader(string $header): string
    {
        $header = mb_strtolower(trim($header));
        $header = preg_replace('/[^a-z0-9]/', '', $header) ?? '';

        return match ($header) {
            'nis', 'nomorinduk', 'nomorinduk siswa', 'nomorinduk' => 'nis',
            'namalengkap', 'nama' => $header === 'nama' ? 'namalengkap' : 'namalengkap',
            'tanggallahir', 'tgllahir' => 'tanggallahir',
            'kelas' => 'kelas',
            'whatsapp', 'nowhatsapp', 'nomorwhatsapp', 'nowa' => 'whatsapp',
            default => $header,
        };
    }

    protected function normalizeWhatsApp(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        } elseif (str_starts_with($digits, '620')) {
            $digits = '62' . substr($digits, 3);
        } elseif (! str_starts_with($digits, '62')) {
            $digits = '62' . $digits;
        }

        return preg_match('/^62[0-9]{8,13}$/', $digits) ? $digits : '';
    }

    protected function parseDate(mixed $rawValue, string $formatted): string
    {
        if (is_numeric($rawValue) && (float) $rawValue > 1) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $rawValue)->format('Y-m-d');
            } catch (\Throwable) {
                return '';
            }
        }

        $value = trim($formatted);
        if ($value === '') {
            return '';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

            return $date !== false && $date->format('Y-m-d') === $value ? $value : '';
        }

        if (preg_match('#^(\d{1,2})[/-](\d{1,2})[/-](\d{4})$#', $value, $matches)) {
            $date = DateTimeImmutable::createFromFormat('!d-m-Y', $matches[1] . '-' . $matches[2] . '-' . $matches[3]);

            return $date !== false ? $date->format('Y-m-d') : '';
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? '' : date('Y-m-d', $timestamp);
    }
}