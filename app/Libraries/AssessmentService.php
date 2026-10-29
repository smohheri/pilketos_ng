<?php

namespace App\Libraries;

use App\Models\AssessmentCriterionModel;
use App\Models\CandidateAssessmentModel;

class AssessmentService
{
    public const MODE_VOTE_ONLY = 'vote_only';
    public const MODE_COMBINED = 'combined';

    public function mode(?array $election): string
    {
        $mode = (string) ($election['result_mode'] ?? self::MODE_VOTE_ONLY);

        return in_array($mode, [self::MODE_VOTE_ONLY, self::MODE_COMBINED], true)
            ? $mode
            : self::MODE_VOTE_ONLY;
    }

    public function modeLabel(string $mode): string
    {
        return $mode === self::MODE_COMBINED ? 'Suara + penilaian' : 'Suara voting saja';
    }

    /**
     * @return array{0: float, 1: float} [vote weight, assessment weight]
     */
    public function weights(?array $election): array
    {
        $vote = max(0.0, min(100.0, (float) ($election['vote_weight'] ?? 50)));
        $assessment = max(0.0, min(100.0, (float) ($election['assessment_weight'] ?? 50)));
        $total = $vote + $assessment;

        if ($total <= 0) {
            return [50.0, 50.0];
        }

        return [round(($vote / $total) * 100, 2), round(($assessment / $total) * 100, 2)];
    }

    public function criteria(int $electionId, bool $activeOnly = true): array
    {
        return (new AssessmentCriterionModel())->forElection($electionId, $activeOnly);
    }

    public function scores(int $electionId): array
    {
        return (new CandidateAssessmentModel())->scoresForElection($electionId);
    }

    /**
     * Enrich candidates with vote shares, assessment scores, final scores, and ranking.
     *
     * @param array<int, array<string, mixed>> $candidates
     */
    public function evaluate(?array $election, array $candidates, int $totalVotes): array
    {
        $requestedMode = $this->mode($election);
        [$voteWeight, $assessmentWeight] = $this->weights($election);
        $criteria = $election !== null ? $this->criteria((int) $election['id']) : [];
        $scores = $criteria !== [] && $election !== null ? $this->scores((int) $election['id']) : [];

        $weightTotal = 0.0;
        foreach ($criteria as $criterion) {
            $weightTotal += max(0.0, (float) $criterion['weight']);
        }
        $hasAssessment = $criteria !== [] && $weightTotal > 0;
        $mode = $requestedMode === self::MODE_COMBINED && $hasAssessment
            ? self::MODE_COMBINED
            : self::MODE_VOTE_ONLY;
        $fallback = $requestedMode === self::MODE_COMBINED && ! $hasAssessment;

        foreach ($candidates as &$candidate) {
            $candidateId = (int) $candidate['id'];
            $votes = (int) ($candidate['votes'] ?? 0);
            $candidate['votes'] = $votes;
            $candidate['share'] = $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 2) : 0.0;

            $scored = 0;
            $weighted = 0.0;
            foreach ($criteria as $criterion) {
                $criterionId = (int) $criterion['id'];
                $maxScore = max(0.01, (float) $criterion['max_score']);
                $weight = max(0.0, (float) $criterion['weight']);
                if (isset($scores[$candidateId][$criterionId])) {
                    $scored++;
                    $weighted += (min($maxScore, max(0.0, (float) $scores[$candidateId][$criterionId])) / $maxScore) * $weight;
                }
            }

            $candidate['assessment_score'] = $hasAssessment
                ? round(($weighted / $weightTotal) * 100, 2)
                : null;
            $candidate['assessment_scored'] = $scored;
            $candidate['assessment_total'] = count($criteria);
            $candidate['assessment_complete'] = $criteria !== [] && $scored === count($criteria);
            $candidate['final_score'] = $mode === self::MODE_COMBINED && $candidate['assessment_score'] !== null
                ? round(($candidate['share'] * $voteWeight + $candidate['assessment_score'] * $assessmentWeight) / 100, 2)
                : null;
        }
        unset($candidate);

        $primaryScore = static function (array $candidate) use ($mode): float {
            return $mode === self::MODE_COMBINED
                ? (float) ($candidate['final_score'] ?? 0)
                : (float) ($candidate['votes'] ?? 0);
        };

        usort($candidates, static function (array $a, array $b) use ($primaryScore, $mode): int {
            $comparison = $primaryScore($b) <=> $primaryScore($a);
            if ($comparison !== 0) {
                return $comparison;
            }
            if ($mode === self::MODE_COMBINED) {
                $assessment = ((float) ($b['assessment_score'] ?? 0)) <=> ((float) ($a['assessment_score'] ?? 0));
                if ($assessment !== 0) {
                    return $assessment;
                }
            }

            return (int) $a['candidate_number'] <=> (int) $b['candidate_number'];
        });

        $rank = 0;
        $position = 0;
        $previousScore = null;
        foreach ($candidates as &$candidate) {
            $position++;
            $score = $primaryScore($candidate);
            if ($previousScore === null || abs($score - $previousScore) > 0.0001) {
                $rank = $position;
                $previousScore = $score;
            }
            $candidate['rank'] = $rank;
            $candidate['is_leader'] = $rank === 1;
        }
        unset($candidate);

        $activeCandidates = array_filter(
            $candidates,
            static fn (array $candidate): bool => (int) ($candidate['is_active'] ?? 1) === 1
        );
        $assessmentComplete = $criteria !== []
            && $activeCandidates !== []
            && count(array_filter(
                $activeCandidates,
                static fn (array $candidate): bool => (bool) $candidate['assessment_complete']
            )) === count($activeCandidates);

        return [
            'requested_mode' => $requestedMode,
            'result_mode' => $mode,
            'mode_label' => $this->modeLabel($mode),
            'mode_fallback' => $fallback,
            'vote_weight' => $voteWeight,
            'assessment_weight' => $assessmentWeight,
            'criteria' => $criteria,
            'criteria_count' => count($criteria),
            'assessment_weight_total' => round($weightTotal, 2),
            'assessment_complete' => $assessmentComplete,
            'candidates' => $candidates,
        ];
    }
}
