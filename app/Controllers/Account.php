<?php

namespace App\Controllers;

use App\Libraries\ElectionService;
use App\Models\ElectionVoterModel;
use App\Models\SchoolProfileModel;
use App\Models\StudentModel;
use App\Models\VoterAccountModel;

class Account extends BaseController
{
    public function index()
    {
        $studentId = (int) session('student_id');
        $student = (new StudentModel())->find($studentId);
        $account = (new VoterAccountModel())->forStudent($studentId);
        $electionService = new ElectionService();

        $elections = $electionService->publicElections();
        if ($elections === []) {
            $fallback = $electionService->current();
            if ($fallback !== null) {
                $elections = [$fallback];
            }
        }

        $groups = [];
        foreach ($elections as $item) {
            $phase = $electionService->phase($item);
            $voters = new ElectionVoterModel();
            $voter = $voters->forStudent((int) $item['id'], $studentId);
            if ($voter === null) {
                $electionService->ensureVoterEligibility((int) $item['id'], $studentId);
                $voter = $voters->forStudent((int) $item['id'], $studentId);
            }
            $hasVoted = $voter !== null && (int) $voter['has_voted'] === 1;
            $eligible = $voter !== null && $voter['eligibility_status'] === 'eligible';

            $groups[] = [
                'election' => $item,
                'phase' => $phase,
                'meta' => phase_meta($phase),
                'voter' => $voter,
                'has_voted' => $hasVoted,
                'eligible' => $eligible,
                'can_vote' => $phase === 'ongoing' && $eligible && ! $hasVoted,
            ];
        }

        $slug = $this->request->getGet('election');
        $slug = is_string($slug) ? trim($slug) : '';
        $selected = null;

        foreach ($groups as $group) {
            if ($slug !== '' && ($group['election']['slug'] ?? '') === $slug) {
                $selected = $group;
                break;
            }
        }

        if ($selected === null) {
            $selected = $groups[0] ?? null;
        }

        return view('vote/account', [
            'profile' => (new SchoolProfileModel())->active(),
            'student' => $student,
            'account' => $account,
            'election' => $selected['election'] ?? null,
            'phase' => $selected['phase'] ?? 'draft',
            'voter' => $selected['voter'] ?? null,
            'elections' => $groups,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }
}
