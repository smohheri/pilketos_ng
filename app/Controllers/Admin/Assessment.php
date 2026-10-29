<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ElectionService;
use App\Libraries\ReportService;
use App\Models\AssessmentCriterionModel;
use App\Models\CandidateAssessmentModel;
use App\Models\CandidateModel;

class Assessment extends BaseController
{
    public function index()
    {
        $election = $this->election();
        if ($election === null) {
            return view('admin/assessment/index', [
                'election' => null,
                'phase' => 'draft',
                'criteria' => [],
                'activeCriteria' => [],
                'candidates' => [],
                'scores' => [],
                'report' => null,
                'reportCandidates' => [],
                'appName' => app_setting('app_name', 'CAKRA 2026'),
            ]);
        }

        $electionId = (int) $election['id'];
        $criteriaModel = new AssessmentCriterionModel();
        $candidates = (new CandidateModel())->forElection($electionId, false);
        $report = (new ReportService())->overview($election);
        $reportCandidates = [];
        foreach ($report['candidates'] as $candidate) {
            $reportCandidates[(int) $candidate['id']] = $candidate;
        }

        return view('admin/assessment/index', [
            'election' => $election,
            'phase' => (new ElectionService())->phase($election),
            'criteria' => $criteriaModel->forElection($electionId, false),
            'activeCriteria' => $criteriaModel->forElection($electionId, true),
            'candidates' => $candidates,
            'scores' => (new CandidateAssessmentModel())->matrixForElection($electionId),
            'report' => $report,
            'reportCandidates' => $reportCandidates,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function storeCriterion()
    {
        $election = $this->election();
        if ($election === null) {
            return redirect()->to('/admin/pemilihan')->with('error', 'Buat konfigurasi pemilihan terlebih dahulu.');
        }

        $payload = $this->criterionPayload();
        if (is_string($payload)) {
            return redirect()->back()->withInput()->with('error', $payload);
        }

        $criteriaModel = new AssessmentCriterionModel();
        $payload['election_id'] = (int) $election['id'];
        if ($payload['sort_order'] < 1) {
            $payload['sort_order'] = $criteriaModel->nextSortOrder((int) $election['id']);
        }

        $id = (int) $criteriaModel->insert($payload, true);
        $this->audit('assessment_criterion.create', 'assessment_criterion', $id, [
            'weight' => $payload['weight'],
            'max_score' => $payload['max_score'],
        ]);

        return redirect()->to('/admin/penilaian')->with('success', 'Variabel penilaian berhasil ditambahkan.');
    }

    public function updateCriterion(int $id)
    {
        $election = $this->election();
        $criterion = $election !== null
            ? (new AssessmentCriterionModel())->forElectionAndId($id, (int) $election['id'])
            : null;
        if ($election === null || $criterion === null) {
            return redirect()->to('/admin/penilaian')->with('error', 'Variabel penilaian tidak ditemukan.');
        }

        $payload = $this->criterionPayload();
        if (is_string($payload)) {
            return redirect()->back()->withInput()->with('error', $payload);
        }

        $highestScore = (new CandidateAssessmentModel())->maxScoreForCriterion($id);
        if ($highestScore > $payload['max_score']) {
            return redirect()->back()->withInput()->with(
                'error',
                'Skor maksimum tidak boleh lebih kecil dari nilai tersimpan tertinggi (' . number_format($highestScore, 2, ',', '.') . ').'
            );
        }

        (new AssessmentCriterionModel())->update($id, $payload);
        $this->audit('assessment_criterion.update', 'assessment_criterion', $id);

        return redirect()->to('/admin/penilaian')->with('success', 'Variabel penilaian diperbarui.');
    }

    public function toggleCriterion(int $id)
    {
        $election = $this->election();
        $criterion = $election !== null
            ? (new AssessmentCriterionModel())->forElectionAndId($id, (int) $election['id'])
            : null;
        if ($election === null || $criterion === null) {
            return redirect()->to('/admin/penilaian')->with('error', 'Variabel penilaian tidak ditemukan.');
        }

        $active = (int) $criterion['is_active'] === 1 ? 0 : 1;
        (new AssessmentCriterionModel())->update($id, ['is_active' => $active]);
        $this->audit($active ? 'assessment_criterion.activate' : 'assessment_criterion.deactivate', 'assessment_criterion', $id);

        return redirect()->to('/admin/penilaian')->with('success', 'Status variabel penilaian diperbarui.');
    }

    public function deleteCriterion(int $id)
    {
        $election = $this->election();
        $criterion = $election !== null
            ? (new AssessmentCriterionModel())->forElectionAndId($id, (int) $election['id'])
            : null;
        if ($election === null || $criterion === null) {
            return redirect()->to('/admin/penilaian')->with('error', 'Variabel penilaian tidak ditemukan.');
        }

        (new AssessmentCriterionModel())->delete($id);
        $this->audit('assessment_criterion.delete', 'assessment_criterion', $id);

        return redirect()->to('/admin/penilaian')->with('success', 'Variabel penilaian dan seluruh nilainya dihapus.');
    }

    public function saveScores()
    {
        $election = $this->election();
        if ($election === null) {
            return redirect()->to('/admin/pemilihan')->with('error', 'Buat konfigurasi pemilihan terlebih dahulu.');
        }

        $posted = $this->request->getPost('scores');
        if (! is_array($posted)) {
            return redirect()->back()->with('error', 'Tidak ada nilai penilaian yang dikirim.');
        }

        $electionId = (int) $election['id'];
        $candidates = (new CandidateModel())->forElection($electionId, false);
        $criteria = (new AssessmentCriterionModel())->forElection($electionId, true);
        $candidateIds = array_map(static fn (array $candidate): int => (int) $candidate['id'], $candidates);
        $candidateNames = [];
        foreach ($candidates as $candidate) {
            $candidateNames[(int) $candidate['id']] = $candidate['full_name'];
        }

        $values = [];
        $cleared = [];
        $errors = [];
        foreach ($criteria as $criterion) {
            $criterionId = (int) $criterion['id'];
            $maxScore = (float) $criterion['max_score'];
            foreach ($candidateIds as $candidateId) {
                $raw = $posted[$candidateId][$criterionId] ?? null;
                if ($raw === null || trim((string) $raw) === '') {
                    $cleared[] = [$candidateId, $criterionId];
                    continue;
                }
                if (! is_numeric($raw)) {
                    $errors[] = $candidateNames[$candidateId] . ' / ' . $criterion['name'] . ': nilai harus berupa angka.';
                    continue;
                }

                $score = (float) $raw;
                if ($score < 0 || $score > $maxScore) {
                    $errors[] = $candidateNames[$candidateId] . ' / ' . $criterion['name'] . ': nilai harus antara 0 dan ' . number_format($maxScore, 2, ',', '.') . '.';
                    continue;
                }
                $values[] = [$candidateId, $criterionId, $score];
            }
        }

        if ($errors !== []) {
            return redirect()->back()->withInput()->with('error', implode(' ', array_slice($errors, 0, 5)));
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $model = new CandidateAssessmentModel();
            $assessedBy = (int) session('admin_id');
            foreach ($values as [$candidateId, $criterionId, $score]) {
                $model->put($candidateId, $criterionId, $score, $assessedBy);
            }
            foreach ($cleared as [$candidateId, $criterionId]) {
                $model->remove($candidateId, $criterionId);
            }

            if (! $db->transStatus()) {
                throw new \RuntimeException('Assessment transaction failed.');
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Assessment save failed for election {election}: {message}', [
                'election' => $electionId,
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->withInput()->with('error', 'Nilai penilaian gagal disimpan. Silakan coba lagi.');
        }

        $this->audit('assessment.save_scores', 'election', $electionId, [
            'saved' => count($values),
            'cleared' => count($cleared),
            'criteria' => count($criteria),
            'candidates' => count($candidates),
        ]);

        return redirect()->to('/admin/penilaian')->with('success', 'Nilai penilaian berhasil disimpan.');
    }

    private function election(): ?array
    {
        return admin_current_election();
    }

    private function criterionPayload(): array|string
    {
        $name = trim((string) $this->request->getPost('name'));
        if ($name === '' || mb_strlen($name) > 120) {
            return 'Nama variabel wajib diisi (maksimal 120 karakter).';
        }

        $weightRaw = $this->request->getPost('weight');
        $maxRaw = $this->request->getPost('max_score');
        if (! is_numeric($weightRaw) || (float) $weightRaw <= 0) {
            return 'Bobot variabel harus berupa angka lebih dari 0.';
        }
        if (! is_numeric($maxRaw) || (float) $maxRaw <= 0) {
            return 'Skor maksimum harus berupa angka lebih dari 0.';
        }

        $weight = (float) $weightRaw;
        $maxScore = (float) $maxRaw;
        if ($weight > 1000 || $maxScore > 1000) {
            return 'Bobot dan skor maksimum maksimal 1.000.';
        }

        return [
            'name' => $name,
            'description' => trim((string) $this->request->getPost('description')),
            'weight' => round($weight, 2),
            'max_score' => round($maxScore, 2),
            'sort_order' => max(0, (int) $this->request->getPost('sort_order')),
            'is_active' => $this->request->getPost('is_active') === '1' ? 1 : 0,
        ];
    }
}
