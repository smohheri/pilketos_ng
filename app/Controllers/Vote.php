<?php

namespace App\Controllers;

use App\Libraries\ElectionService;
use App\Libraries\VotingService;
use App\Models\CandidateModel;
use App\Models\ElectionVoterModel;
use App\Models\SchoolProfileModel;
use App\Models\StudentModel;

class Vote extends BaseController
{
    public function index()
    {
        $student = $this->student();
        $electionService = new ElectionService();
        $election = $this->requestedElection($electionService);
        $phase = $electionService->phase($election);

        $voter = null;
        $candidates = [];
        if ($election !== null) {
            $voters = new ElectionVoterModel();
            $voter = $voters->forStudent((int) $election['id'], (int) $student['id']);
            if ($voter === null) {
                $electionService->ensureVoterEligibility((int) $election['id'], (int) $student['id']);
                $voter = $voters->forStudent((int) $election['id'], (int) $student['id']);
            }
            $candidates = (new CandidateModel())->forElection((int) $election['id']);
        }

        if ($voter !== null && (int) $voter['has_voted'] === 1) {
            $next = $electionService->firstVotingOpenForStudent((int) $student['id'], (int) $election['id']);
            if ($next !== null) {
                return redirect()
                    ->to(election_voting_url($next))
                    ->with('success', 'Suara ' . election_type_label($election['type'] ?? null) . ' tercatat. Lanjut memilih ' . election_type_label($next['type'] ?? null) . '.');
            }

            return redirect()->to(election_done_url($election));
        }

        if ($election !== null && $phase !== 'ongoing') {
            $next = $electionService->firstVotingOpenForStudent((int) $student['id'], (int) $election['id']);
            if ($next !== null) {
                return redirect()
                    ->to(election_voting_url($next))
                    ->with('success', 'Lanjut memilih ' . election_type_label($next['type'] ?? null) . '.');
            }
        }

        if ($election === null || $phase !== 'ongoing' || $voter === null || $voter['eligibility_status'] !== 'eligible') {
            return view('vote/status', [
                'profile' => (new SchoolProfileModel())->active(),
                'election' => $election,
                'elections' => $electionService->publicElections(),
                'phase' => $phase,
                'voter' => $voter,
                'appName' => app_setting('app_name', 'CAKRA 2026'),
            ]);
        }

        return view('vote/index', [
            'profile' => (new SchoolProfileModel())->active(),
            'election' => $election,
            'elections' => $electionService->publicElections(),
            'phase' => $phase,
            'candidates' => $candidates,
            'student' => $student,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function confirm()
    {
        $student = $this->student();
        $electionService = new ElectionService();
        $election = $this->requestedElection($electionService);

        if ($election === null || ! $electionService->isVotingOpen($election)) {
            return redirect()
                ->to($election !== null ? election_voting_url($election) : '/voting')
                ->with('error', 'Pemungutan suara sedang tidak dibuka.');
        }

        $candidateId = (int) $this->request->getPost('candidate_id');
        $candidate = (new CandidateModel())->findForElection($candidateId, (int) $election['id']);
        if ($candidate === null || (int) $candidate['is_active'] !== 1) {
            return redirect()->to(election_voting_url($election))->with('error', 'Kandidat tidak tersedia.');
        }

        $voters = new ElectionVoterModel();
        $voter = $voters->forStudent((int) $election['id'], (int) $student['id']);
        if ($voter === null) {
            $electionService->ensureVoterEligibility((int) $election['id'], (int) $student['id']);
            $voter = $voters->forStudent((int) $election['id'], (int) $student['id']);
        }
        if ($voter === null || (int) $voter['has_voted'] === 1) {
            $next = $electionService->firstVotingOpenForStudent((int) $student['id'], (int) $election['id']);

            return redirect()->to($next !== null ? election_voting_url($next) : election_done_url($election));
        }

        $pending = session('pending_votes');
        $pending = is_array($pending) ? $pending : [];
        $pending[(int) $election['id']] = $candidateId;

        session()->set([
            'pending_votes' => $pending,
            'pending_vote_candidate' => $candidateId,
            'pending_vote_election' => (int) $election['id'],
        ]);

        return view('vote/confirm', [
            'profile' => (new SchoolProfileModel())->active(),
            'election' => $election,
            'elections' => $electionService->publicElections(),
            'candidate' => $candidate,
            'student' => $student,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function submit()
    {
        $student = $this->student();
        $electionService = new ElectionService();
        $election = $this->requestedElection($electionService);

        if ($election === null) {
            $this->clearPending();

            return redirect()->to('/voting')->with('error', 'Pemilihan tidak ditemukan.');
        }

        $electionId = (int) $election['id'];
        $pending = session('pending_votes');
        $pending = is_array($pending) ? $pending : [];
        $candidateId = (int) ($pending[$electionId] ?? 0);

        if ($candidateId <= 0
            && (int) (session('pending_vote_election') ?? 0) === $electionId) {
            $candidateId = (int) (session('pending_vote_candidate') ?? 0);
        }

        if ($candidateId <= 0) {
            $this->clearPending($electionId);

            return redirect()->to(election_voting_url($election))->with('error', 'Pilihan tidak ditemukan. Silakan pilih kandidat kembali.');
        }

        if (! $electionService->isVotingOpen($election)) {
            $this->clearPending($electionId);

            return redirect()->to(election_voting_url($election))->with('error', 'Waktu pemungutan suara telah berakhir.');
        }

        $electionService->ensureVoterEligibility($electionId, (int) $student['id']);
        $result = (new VotingService())->cast($electionId, (int) $student['id'], $candidateId);
        $this->clearPending($electionId);

        if ($result['status'] === 'ok' && isset($result['receipt'])) {
            session()->set('vote_receipt_' . $electionId, $result['receipt']);
        }

        if ($result['status'] === 'ok' || $result['status'] === 'already') {
            $next = $electionService->firstVotingOpenForStudent((int) $student['id'], $electionId);
            $message = $result['status'] === 'ok'
                ? 'Suara ' . election_type_label($election['type'] ?? null) . ' berhasil dicatat.'
                : 'Suara ' . election_type_label($election['type'] ?? null) . ' sudah tercatat sebelumnya.';

            if ($next !== null) {
                return redirect()
                    ->to(election_voting_url($next))
                    ->with('success', $message . ' Lanjut memilih ' . election_type_label($next['type'] ?? null) . '.');
            }

            return redirect()->to(election_done_url($election))->with('success', $message . ' Semua pemilihan yang tersedia sudah selesai.');
        }

        return redirect()->to(election_voting_url($election))->with('error', $result['message']);
    }

    public function done()
    {
        $student = $this->student();
        $electionService = new ElectionService();
        $election = $this->requestedElection($electionService);
        $voter = $election !== null
            ? (new ElectionVoterModel())->forStudent((int) $election['id'], (int) $student['id'])
            : null;

        if ($election === null) {
            return redirect()->to('/voting')->with('error', 'Pemilihan tidak ditemukan.');
        }

        if ($voter === null || (int) $voter['has_voted'] !== 1) {
            return redirect()->to(election_voting_url($election))->with('error', 'Suara belum tercatat.');
        }

        $receipt = (string) (session('vote_receipt_' . (int) $election['id']) ?? '');

        return view('vote/done', [
            'profile' => (new SchoolProfileModel())->active(),
            'election' => $election,
            'elections' => $electionService->publicElections(),
            'nextElection' => $electionService->firstVotingOpenForStudent((int) $student['id'], (int) $election['id']),
            'receipt' => $receipt,
            'student' => $student,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    private function requestedElection(ElectionService $service): ?array
    {
        $slug = $this->request->getPost('election') ?? $this->request->getGet('election');
        $slug = is_string($slug) ? trim($slug) : '';

        return $slug !== ''
            ? $service->publicBySlug($slug)
            : $service->defaultPublic();
    }

    private function student(): array
    {
        return (new StudentModel())->find((int) session('student_id')) ?? [];
    }

    private function clearPending(?int $electionId = null): void
    {
        if ($electionId !== null) {
            $pending = session('pending_votes');
            if (is_array($pending)) {
                unset($pending[$electionId]);
                session()->set('pending_votes', $pending);
            }
        } else {
            session()->remove('pending_votes');
        }

        session()->remove(['pending_vote_candidate', 'pending_vote_election']);
    }
}
