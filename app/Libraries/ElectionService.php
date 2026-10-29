<?php

namespace App\Libraries;

use App\Models\ElectionModel;
use App\Models\ElectionVoterModel;
use App\Models\StudentModel;

class ElectionService
{
    protected ElectionModel $elections;
    protected ElectionVoterModel $voters;

    public function __construct()
    {
        $this->elections = new ElectionModel();
        $this->voters = new ElectionVoterModel();
    }

    public function current(): ?array
    {
        return $this->elections->current();
    }

    public function publicElections(): array
    {
        return $this->elections->publicElections();
    }

    public function defaultPublic(): ?array
    {
        return $this->elections->defaultPublic() ?? $this->elections->current();
    }

    public function publicBySlug(?string $slug): ?array
    {
        if ($slug === null || trim($slug) === '') {
            return $this->defaultPublic();
        }

        return $this->elections->publicBySlug(trim($slug));
    }

    public function firstVotingOpen(): ?array
    {
        foreach ($this->publicElections() as $election) {
            if ($this->isVotingOpen($election)) {
                return $election;
            }
        }

        return null;
    }

    public function firstVotingOpenForStudent(int $studentId, ?int $excludeElectionId = null): ?array
    {
        foreach ($this->publicElections() as $election) {
            $electionId = (int) $election['id'];
            if ($excludeElectionId !== null && $electionId === $excludeElectionId) {
                continue;
            }
            if (! $this->isVotingOpen($election)) {
                continue;
            }

            $voter = $this->voters->forStudent($electionId, $studentId);
            if ($voter === null
                || (($voter['eligibility_status'] ?? '') === 'eligible'
                    && (int) ($voter['has_voted'] ?? 0) === 0)) {
                return $election;
            }
        }

        return null;
    }

    public function ensureEligibilityForAll(int $studentId): void
    {
        foreach ($this->publicElections() as $election) {
            $this->ensureVoterEligibility((int) $election['id'], $studentId);
        }
    }

    /**
     * Server-authoritative election phase. The browser clock is never trusted.
     */
    public function phase(?array $election): string
    {
        if ($election === null) {
            return 'draft';
        }

        $status = $election['status'] ?? 'draft';
        if ($status === 'draft') {
            return 'draft';
        }

        $now = time();
        $start = strtotime((string) $election['start_at']);
        $end = strtotime((string) $election['end_at']);
        $publish = ! empty($election['result_publish_at']) ? strtotime((string) $election['result_publish_at']) : null;

        if ($status === 'closed') {
            return ($publish !== null && $now >= $publish) ? 'results' : 'closed';
        }

        if ($now < $start) {
            return 'scheduled';
        }
        if ($now <= $end) {
            return 'ongoing';
        }

        return ($publish !== null && $now >= $publish) ? 'results' : 'closed';
    }

    public function isVotingOpen(?array $election): bool
    {
        return $this->phase($election) === 'ongoing';
    }

    public function resultsVisible(?array $election): bool
    {
        return $this->phase($election) === 'results';
    }

    public function syncEligibility(int $electionId): int
    {
        $db = db_connect();
        $students = new StudentModel();
        $count = 0;

        foreach ($students->where('is_active', 1)->findAll() as $student) {
            $existing = $this->voters->forStudent($electionId, (int) $student['id']);
            if ($existing !== null) {
                continue;
            }

            $db->table('election_voters')->insert([
                'election_id' => $electionId,
                'student_id' => $student['id'],
                'eligibility_status' => 'eligible',
                'has_voted' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $count++;
        }

        return $count;
    }

    public function ensureVoterEligibility(int $electionId, int $studentId): void
    {
        if ($this->voters->forStudent($electionId, $studentId) !== null) {
            return;
        }

        $this->voters->insert([
            'election_id' => $electionId,
            'student_id' => $studentId,
            'eligibility_status' => 'eligible',
            'has_voted' => 0,
        ]);
    }

    public function statusLabel(?array $election): string
    {
        return phase_meta($this->phase($election))['label'];
    }
}