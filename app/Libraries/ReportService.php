<?php

namespace App\Libraries;

use App\Models\CandidateModel;
use App\Models\ElectionVoterModel;
use App\Models\StudentModel;
use App\Models\VoteModel;

class ReportService
{
    public function overview(?array $election): array
    {
        if ($election === null) {
            return [
                'registered' => 0,
                'eligible' => 0,
                'voted' => 0,
                'not_voted' => 0,
                'participation' => 0.0,
                'total_votes' => 0,
                'candidates' => [],
                'requested_mode' => AssessmentService::MODE_VOTE_ONLY,
                'result_mode' => AssessmentService::MODE_VOTE_ONLY,
                'mode_label' => 'Suara voting saja',
                'mode_fallback' => false,
                'vote_weight' => 50.0,
                'assessment_weight' => 50.0,
                'assessment_criteria' => [],
                'assessment_criteria_count' => 0,
                'assessment_weight_total' => 0.0,
                'assessment_complete' => false,
            ];
        }

        $electionId = (int) $election['id'];
        $students = new StudentModel();
        $voters = new ElectionVoterModel();
        $voteModel = new VoteModel();
        $candidateModel = new CandidateModel();

        $counts = $voters->counts($electionId);
        $candidates = $candidateModel->forElection($electionId, false);
        $voteCounts = [];
        foreach ($voteModel->countsByCandidate($electionId) as $row) {
            $voteCounts[(int) $row['candidate_id']] = (int) $row['total'];
        }

        foreach ($candidates as &$candidate) {
            $candidate['votes'] = $voteCounts[(int) $candidate['id']] ?? 0;
        }
        unset($candidate);

        $totalVotes = $voteModel->total($electionId);
        $assessment = (new AssessmentService())->evaluate($election, $candidates, $totalVotes);

        return [
            'registered' => $students->where('is_active', 1)->countAllResults(),
            'eligible' => $counts['eligible'],
            'voted' => $counts['voted'],
            'not_voted' => $counts['not_voted'],
            'participation' => $counts['participation'],
            'total_votes' => $totalVotes,
            'candidates' => $assessment['candidates'],
            'requested_mode' => $assessment['requested_mode'],
            'result_mode' => $assessment['result_mode'],
            'mode_label' => $assessment['mode_label'],
            'mode_fallback' => $assessment['mode_fallback'],
            'vote_weight' => $assessment['vote_weight'],
            'assessment_weight' => $assessment['assessment_weight'],
            'assessment_criteria' => $assessment['criteria'],
            'assessment_criteria_count' => $assessment['criteria_count'],
            'assessment_weight_total' => $assessment['assessment_weight_total'],
            'assessment_complete' => $assessment['assessment_complete'],
        ];
    }

    public function participationByClass(int $electionId): array
    {
        $db = db_connect();

        return $db->query(
            'SELECT s.class_name,
                    COUNT(*) AS eligible,
                    COALESCE(SUM(ev.has_voted), 0) AS voted
             FROM election_voters ev
             JOIN students s ON s.id = ev.student_id
             WHERE ev.election_id = ? AND ev.eligibility_status = "eligible"
             GROUP BY s.class_name
             ORDER BY s.class_name ASC',
            [$electionId]
        )->getResultArray();
    }
}
