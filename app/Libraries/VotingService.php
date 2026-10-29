<?php

namespace App\Libraries;

use App\Models\CandidateModel;
use App\Models\ElectionModel;
use App\Models\VoteModel;

class VotingService
{
    protected ElectionService $elections;

    public function __construct()
    {
        $this->elections = new ElectionService();
    }

    /**
     * Cast a vote atomically. The votes table never receives a student identifier.
     *
     * @return array{status: string, message: string, receipt?: string}
     */
    public function cast(int $electionId, int $studentId, int $candidateId): array
    {
        $electionModel = new ElectionModel();
        $election = $electionModel->find($electionId);

        if ($election === null || ! $this->elections->isVotingOpen($election)) {
            return ['status' => 'closed', 'message' => 'Pemungutan suara sedang tidak dibuka.'];
        }

        $candidateModel = new CandidateModel();
        $candidate = $candidateModel->findForElection($candidateId, $electionId);
        if ($candidate === null || (int) $candidate['is_active'] !== 1) {
            return ['status' => 'invalid_candidate', 'message' => 'Kandidat tidak tersedia untuk pemilihan ini.'];
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $voter = $db->query(
                'SELECT id, has_voted, eligibility_status FROM election_voters
                 WHERE election_id = ? AND student_id = ? FOR UPDATE',
                [$electionId, $studentId]
            )->getRowArray();

            if ($voter === null || $voter['eligibility_status'] !== 'eligible') {
                $db->transRollback();

                return ['status' => 'not_eligible', 'message' => 'Akun Anda belum terdaftar sebagai pemilih yang memenuhi syarat.'];
            }

            if ((int) $voter['has_voted'] === 1) {
                $db->transRollback();

                return ['status' => 'already', 'message' => 'Suara Anda sudah tercatat sebelumnya.'];
            }

            $receipt = $this->generateReceipt();
            $hash = $this->hashReceipt($receipt);
            $now = date('Y-m-d H:i:s');

            $inserted = $db->table('votes')->insert([
                'election_id' => $electionId,
                'candidate_id' => $candidateId,
                'receipt_code_hash' => $hash,
                'cast_at' => $now,
                'created_at' => $now,
            ]);

            $updated = $db->table('election_voters')
                ->where('id', $voter['id'])
                ->where('has_voted', 0)
                ->update([
                    'has_voted' => 1,
                    'voted_at' => $now,
                    'updated_at' => $now,
                ]);

            if (! $inserted || ! $updated || $db->affectedRows() !== 1 || ! $db->transStatus()) {
                throw new \RuntimeException('Vote transaction failed.');
            }

            $db->transCommit();

            return [
                'status' => 'ok',
                'message' => 'Suara berhasil dicatat.',
                'receipt' => $receipt,
            ];
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Vote transaction failed for election {election}: {message}', [
                'election' => $electionId,
                'message' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'message' => 'Suara tidak dapat diproses saat ini. Silakan coba lagi.',
            ];
        }
    }

    public function verifyReceipt(string $receipt): ?array
    {
        $hash = $this->hashReceipt($receipt);
        $vote = (new VoteModel())->findByReceiptHash($hash);

        if ($vote === null) {
            return null;
        }

        return [
            'valid' => true,
            'cast_at' => $vote['cast_at'],
        ];
    }

    public function hashReceipt(string $receipt): string
    {
        $salt = (string) env('cakra.receiptSalt', 'cakra-2026-receipt');

        return hash_hmac('sha256', strtoupper(trim($receipt)), $salt);
    }

    protected function generateReceipt(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $chunks = [];
        for ($chunk = 0; $chunk < 2; $chunk++) {
            $text = '';
            for ($i = 0; $i < 4; $i++) {
                $text .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $chunks[] = $text;
        }

        return 'CK26-' . implode('-', $chunks);
    }
}