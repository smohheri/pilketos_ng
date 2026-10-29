<?php

if (! function_exists('current_admin')) {
    function current_admin(): ?array
    {
        $id = session('admin_id');
        if ($id === null) {
            return null;
        }

        return [
            'id' => (int) $id,
            'name' => (string) session('admin_name'),
            'email' => (string) session('admin_email'),
            'role' => (string) session('admin_role'),
        ];
    }
}

if (! function_exists('current_student')) {
    function current_student(): ?array
    {
        $id = session('student_id');
        if ($id === null) {
            return null;
        }

        return [
            'id' => (int) $id,
            'nis' => (string) session('student_nis'),
            'name' => (string) session('student_name'),
            'class_name' => (string) session('student_class'),
            'whatsapp_number' => (string) session('student_whatsapp'),
        ];
    }
}

if (! function_exists('admin_is')) {
    function admin_is(string $role): bool
    {
        return session('admin_role') === $role;
    }
}

if (! function_exists('is_admin')) {
    function is_admin(): bool
    {
        return session('admin_role') === 'admin';
    }
}

if (! function_exists('set_admin_session')) {
    function set_admin_session(array $user): void
    {
        $session = session();
        $session->set([
            'admin_id' => (int) $user['id'],
            'admin_name' => $user['name'],
            'admin_email' => $user['email'],
            'admin_role' => $user['role'],
            'admin_election_id' => $user['role'] === 'panitia' ? (int) ($user['election_id'] ?? 0) : null,
        ]);
    }
}

if (! function_exists('set_student_session')) {
    function set_student_session(array $student): void
    {
        $session = session();
        $session->set([
            'student_id' => (int) $student['id'],
            'student_nis' => $student['nis'],
            'student_name' => $student['full_name'],
            'student_class' => $student['class_name'],
            'student_whatsapp' => $student['whatsapp_number'],
        ]);
    }
}

if (! function_exists('clear_student_session')) {
    function clear_student_session(): void
    {
        session()->remove([
            'student_id', 'student_nis', 'student_name', 'student_class',
            'student_whatsapp', 'student_after_login', 'pending_votes',
            'pending_vote_candidate', 'pending_vote_election',
        ]);

        foreach (array_keys(session()->get() ?? []) as $key) {
            if (str_starts_with((string) $key, 'vote_receipt_')) {
                session()->remove($key);
            }
        }
    }
}