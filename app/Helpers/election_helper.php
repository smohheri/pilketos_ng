<?php

use App\Models\ElectionModel;

if (! function_exists('election_type_options')) {
    function election_type_options(): array
    {
        return [
            'osis' => 'OSIS',
            'poltar' => 'Poltar',
        ];
    }
}

if (! function_exists('election_type_label')) {
    function election_type_label(?string $type): string
    {
        $options = election_type_options();

        return $options[$type ?? ''] ?? ($type !== null && $type !== '' ? strtoupper($type) : 'Pemilihan');
    }
}

if (! function_exists('election_type_badge')) {
    function election_type_badge(?string $type): string
    {
        return $type === 'poltar' ? 'badge-warm' : 'badge-cool';
    }
}

if (! function_exists('admin_current_election')) {
    function admin_current_election(): ?array
    {
        $model = new ElectionModel();
        $id = (int) session('admin_election_id');

        if ($id > 0) {
            $election = $model->find($id);
            if ($election !== null) {
                return $election;
            }
        }

        if (! is_admin()) {
            return null;
        }

        $election = $model->where('type', 'osis')->orderBy('id', 'ASC')->first()
            ?? $model->orderBy('id', 'ASC')->first();

        if ($election !== null) {
            session()->set('admin_election_id', (int) $election['id']);
        }

        return $election;
    }
}

if (! function_exists('admin_elections')) {
    function admin_elections(): array
    {
        if (! is_admin()) {
            $election = admin_current_election();

            return $election !== null ? [$election] : [];
        }

        return (new ElectionModel())->orderBy('id', 'ASC')->findAll();
    }
}

if (! function_exists('election_voting_url')) {
    function election_voting_url(?array $election): string
    {
        $slug = (string) ($election['slug'] ?? '');

        return base_url('voting' . ($slug !== '' ? '?election=' . rawurlencode($slug) : ''));
    }
}

if (! function_exists('election_results_url')) {
    function election_results_url(?array $election): string
    {
        $slug = (string) ($election['slug'] ?? '');

        return base_url('hasil' . ($slug !== '' ? '/' . rawurlencode($slug) : ''));
    }
}

if (! function_exists('election_done_url')) {
    function election_done_url(?array $election): string
    {
        $slug = (string) ($election['slug'] ?? '');

        return base_url('voting/selesai' . ($slug !== '' ? '?election=' . rawurlencode($slug) : ''));
    }
}
