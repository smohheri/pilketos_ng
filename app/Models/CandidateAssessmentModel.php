<?php

namespace App\Models;

use CodeIgniter\Model;

class CandidateAssessmentModel extends Model
{
    protected $table = 'candidate_assessments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['candidate_id', 'criterion_id', 'score', 'notes', 'assessed_by'];

    /**
     * @return array<int, array<int, array<string, mixed>>> [candidate_id][criterion_id] => row
     */
    public function matrixForElection(int $electionId): array
    {
        $rows = $this->select('candidate_assessments.*')
            ->join('candidates', 'candidates.id = candidate_assessments.candidate_id')
            ->join('assessment_criteria', 'assessment_criteria.id = candidate_assessments.criterion_id')
            ->where('candidates.election_id', $electionId)
            ->where('assessment_criteria.election_id', $electionId)
            ->findAll();

        $matrix = [];
        foreach ($rows as $row) {
            $matrix[(int) $row['candidate_id']][(int) $row['criterion_id']] = $row;
        }

        return $matrix;
    }

    /**
     * @return array<int, array<int, float>> [candidate_id][criterion_id] => score
     */
    public function scoresForElection(int $electionId): array
    {
        $scores = [];
        foreach ($this->matrixForElection($electionId) as $candidateId => $criteria) {
            foreach ($criteria as $criterionId => $row) {
                $scores[$candidateId][$criterionId] = (float) $row['score'];
            }
        }

        return $scores;
    }

    public function forCandidateAndCriterion(int $candidateId, int $criterionId): ?array
    {
        return $this->where('candidate_id', $candidateId)
            ->where('criterion_id', $criterionId)
            ->first();
    }

    public function put(int $candidateId, int $criterionId, float $score, ?int $assessedBy = null, ?string $notes = null): void
    {
        $row = $this->forCandidateAndCriterion($candidateId, $criterionId);
        if ($row === null) {
            $this->insert([
                'candidate_id' => $candidateId,
                'criterion_id' => $criterionId,
                'score' => $score,
                'notes' => $notes,
                'assessed_by' => $assessedBy,
            ]);

            return;
        }

        $this->update((int) $row['id'], [
            'score' => $score,
            'notes' => $notes,
            'assessed_by' => $assessedBy,
        ]);
    }

    public function remove(int $candidateId, int $criterionId): void
    {
        $row = $this->forCandidateAndCriterion($candidateId, $criterionId);
        if ($row !== null) {
            $this->delete((int) $row['id']);
        }
    }

    public function maxScoreForCriterion(int $criterionId): float
    {
        $row = $this->selectMax('score')
            ->where('criterion_id', $criterionId)
            ->first();

        return (float) ($row['score'] ?? 0);
    }

    public function scoredCountForCriterion(int $criterionId): int
    {
        return $this->where('criterion_id', $criterionId)->countAllResults();
    }
}
