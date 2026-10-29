<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ElectionService;
use App\Libraries\ReportChartService;
use App\Libraries\ReportService;
use App\Libraries\StudentImportService;
use App\Models\CandidateAssessmentModel;
use App\Models\SchoolProfileModel;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class Report extends BaseController
{
    public function index()
    {
        $electionService = new ElectionService();
        $election = admin_current_election();
        $report = (new ReportService())->overview($election);
        $byClass = $election !== null ? (new ReportService())->participationByClass((int) $election['id']) : [];

        return view('admin/reports/index', [
            'election' => $election,
            'phase' => $electionService->phase($election),
            'report' => $report,
            'byClass' => $byClass,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function export()
    {
        $election = admin_current_election();
        if ($election === null) {
            return redirect()->to('/admin/laporan')->with('error', 'Belum ada data pemilihan untuk diekspor.');
        }

        $reportService = new ReportService();
        $report = $reportService->overview($election);
        $byClass = $reportService->participationByClass((int) $election['id']);
        $combined = ($report['result_mode'] ?? 'vote_only') === 'combined';
        $scoreFmt = static fn ($value): string => $value === null ? '-' : number_format((float) $value, 2, ',', '.');

        $spreadsheet = new Spreadsheet();
        $summary = $spreadsheet->getActiveSheet();
        $summary->setTitle('Ringkasan');
        $summary->setCellValue('A1', 'LAPORAN PEMILIHAN — ' . $election['title']);
        $summary->mergeCells('A1:B1');
        $summary->getStyle('A1')->getFont()->setBold(true)->setSize(15);
        $summary->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFC93C');
        $summary->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $summary->getRowDimension(1)->setRowHeight(30);

        $rows = [
            ['Periode pemungutan suara', format_date_range($election['start_at'], $election['end_at'])],
            ['Waktu pengumuman hasil', format_date_id($election['result_publish_at'], true)],
            ['Zona waktu', $election['timezone']],
            ['Mode hasil', $report['mode_label'] ?? 'Suara voting saja'],
        ];
        if ($combined) {
            $rows[] = ['Bobot suara / penilaian', $scoreFmt($report['vote_weight'] ?? 0) . '% / ' . $scoreFmt($report['assessment_weight'] ?? 0) . '%'];
            $rows[] = ['Kelengkapan penilaian', ! empty($report['assessment_complete']) ? 'Lengkap' : 'Belum lengkap'];
        }
        $rows = array_merge($rows, [
            ['Siswa terdaftar (aktif)', $report['registered']],
            ['Pemilih memenuhi syarat', $report['eligible']],
            ['Sudah memilih', $report['voted']],
            ['Belum memilih', $report['not_voted']],
            ['Tingkat partisipasi', $report['participation'] . '%'],
            ['Total suara sah', $report['total_votes']],
            ['Diekspor pada', format_date_id(date('Y-m-d H:i:s'), true)],
        ]);
        $summary->fromArray($rows, null, 'A3');
        $summary->getColumnDimension('A')->setWidth(34);
        $summary->getColumnDimension('B')->setWidth(44);

        $results = $spreadsheet->createSheet();
        $results->setTitle('Hasil Kandidat');
        $headers = $combined
            ? ['No. Urut', 'Nama Kandidat', 'Kelas', 'Peringkat', 'Perolehan Suara', 'Persentase', 'Nilai Penilaian', 'Nilai Akhir']
            : ['No. Urut', 'Nama Kandidat', 'Kelas', 'Perolehan Suara', 'Persentase'];
        $lastColumn = $combined ? 'H' : 'E';
        $results->fromArray($headers, null, 'A1');
        $results->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);
        $results->getStyle('A1:' . $lastColumn . '1')->getFont()->getColor()->setRGB('FFF8F0');
        $results->getStyle('A1:' . $lastColumn . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('101828');
        $row = 2;
        foreach ($report['candidates'] as $candidate) {
            $values = [
                $candidate['candidate_number'],
                $candidate['full_name'],
                $candidate['class_name'],
            ];
            if ($combined) {
                $values[] = $candidate['rank'];
            }
            $values[] = $candidate['votes'];
            $values[] = $candidate['share'] . '%';
            if ($combined) {
                $values[] = $scoreFmt($candidate['assessment_score']);
                $values[] = $scoreFmt($candidate['final_score']);
            }
            $results->fromArray($values, null, 'A' . $row);
            $row++;
        }
        $widths = $combined
            ? ['A' => 12, 'B' => 32, 'C' => 16, 'D' => 11, 'E' => 20, 'F' => 14, 'G' => 18, 'H' => 14]
            : ['A' => 12, 'B' => 32, 'C' => 16, 'D' => 20, 'E' => 14];
        foreach ($widths as $column => $width) {
            $results->getColumnDimension($column)->setWidth($width);
        }

        $classes = $spreadsheet->createSheet();
        $classes->setTitle('Partisipasi Kelas');
        $classes->fromArray(['Kelas', 'Pemilih Terdaftar', 'Sudah Memilih', 'Partisipasi'], null, 'A1');
        $classes->getStyle('A1:D1')->getFont()->setBold(true);
        $classes->getStyle('A1:D1')->getFont()->getColor()->setRGB('FFF8F0');
        $classes->getStyle('A1:D1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('101828');
        $row = 2;
        foreach ($byClass as $item) {
            $eligible = (int) $item['eligible'];
            $voted = (int) $item['voted'];
            $classes->fromArray([
                $item['class_name'],
                $eligible,
                $voted,
                ($eligible > 0 ? round(($voted / $eligible) * 100, 1) : 0) . '%',
            ], null, 'A' . $row);
            $row++;
        }
        foreach (['A' => 16, 'B' => 22, 'C' => 18, 'D' => 16] as $column => $width) {
            $classes->getColumnDimension($column)->setWidth($width);
        }

        if ($combined && ! empty($report['assessment_criteria'])) {
            $assessment = $spreadsheet->createSheet();
            $assessment->setTitle('Penilaian');
            $headers = ['No. Urut', 'Nama Kandidat', 'Kelas'];
            foreach ($report['assessment_criteria'] as $criterion) {
                $headers[] = $criterion['name'] . ' (maks ' . $scoreFmt($criterion['max_score']) . ', bobot ' . $scoreFmt($criterion['weight']) . ')';
            }
            $headers[] = 'Nilai Penilaian';
            $headers[] = 'Nilai Akhir';
            $headers[] = 'Peringkat';
            $assessment->fromArray($headers, null, 'A1');
            $lastColumn = $assessment->getHighestColumn();
            $assessment->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);
            $assessment->getStyle('A1:' . $lastColumn . '1')->getFont()->getColor()->setRGB('FFF8F0');
            $assessment->getStyle('A1:' . $lastColumn . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('101828');
            $scores = (new CandidateAssessmentModel())->scoresForElection((int) $election['id']);
            $row = 2;
            foreach ($report['candidates'] as $candidate) {
                $values = [
                    $candidate['candidate_number'],
                    $candidate['full_name'],
                    $candidate['class_name'],
                ];
                foreach ($report['assessment_criteria'] as $criterion) {
                    $values[] = isset($scores[(int) $candidate['id']][(int) $criterion['id']])
                        ? (float) $scores[(int) $candidate['id']][(int) $criterion['id']]
                        : '-';
                }
                $values[] = $scoreFmt($candidate['assessment_score']);
                $values[] = $scoreFmt($candidate['final_score']);
                $values[] = $candidate['rank'];
                $assessment->fromArray($values, null, 'A' . $row);
                $row++;
            }
            $assessment->getColumnDimension('A')->setWidth(12);
            $assessment->getColumnDimension('B')->setWidth(32);
            $assessment->getColumnDimension('C')->setWidth(16);
            $criteriaCount = count($report['assessment_criteria']);
            for ($index = 0; $index < $criteriaCount; $index++) {
                $assessment->getColumnDimension(Coordinate::stringFromColumnIndex(4 + $index))->setWidth(22);
            }
            $assessment->getColumnDimension(Coordinate::stringFromColumnIndex(4 + $criteriaCount))->setWidth(18);
            $assessment->getColumnDimension(Coordinate::stringFromColumnIndex(5 + $criteriaCount))->setWidth(16);
            $assessment->getColumnDimension(Coordinate::stringFromColumnIndex(6 + $criteriaCount))->setWidth(11);
        }

        $this->audit('report.export', 'election', (int) $election['id']);

        $writer = new StudentImportService();
        $writer->writeXlsx($spreadsheet, 'Laporan_CAKRA_2026.xlsx');
    }

    public function pdf()
    {
        $election = admin_current_election();
        if ($election === null) {
            return redirect()->to('/admin/laporan')->with('error', 'Belum ada data pemilihan untuk diekspor.');
        }

        $electionService = new ElectionService();
        $reportService = new ReportService();
        $report = $reportService->overview($election);
        $byClass = $reportService->participationByClass((int) $election['id']);
        $profile = (new SchoolProfileModel())->active();
        $charts = new ReportChartService();

        $html = view('admin/reports/pdf', [
            'election' => $election,
            'phase' => $electionService->phase($election),
            'report' => $report,
            'byClass' => $byClass,
            'profile' => $profile,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
            'logo' => $this->logoDataUri(),
            'candidateChart' => $charts->candidateBars($report['candidates'], $report['result_mode'] ?? 'vote_only'),
            'participationChart' => $charts->participationDonut((float) $report['participation'], (int) $report['voted'], (int) $report['not_voted']),
            'generatedAt' => format_date_id(date('Y-m-d H:i:s'), true),
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $this->audit('report.export_pdf', 'election', (int) $election['id']);

        $filename = 'Laporan_' . preg_replace('/[^A-Za-z0-9]+/', '_', (string) $election['title']) . '_' . date('Ymd') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    private function logoDataUri(): ?string
    {
        $path = school_logo_path();
        if (preg_match('#^https?://#i', $path)) {
            return null;
        }

        $file = FCPATH . ltrim($path, '/');
        if (! is_file($file)) {
            return null;
        }

        $mime = school_logo_mime() ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($file));
    }
}