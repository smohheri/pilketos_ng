<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Audit extends BaseController
{
    public function index()
    {
        $q = trim((string) $this->request->getGet('q'));
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = 20;
        $db = db_connect();

        $builder = $db->table('audit_logs a')
            ->select('a.*, u.name AS actor_name')
            ->join('users u', 'u.id = a.actor_id', 'left');

        if ($q !== '') {
            $builder->groupStart()
                ->like('a.action', $q)
                ->orLike('a.entity_type', $q)
                ->orLike('u.name', $q)
                ->groupEnd();
        }

        $total = (clone $builder)->countAllResults();
        $logs = $builder->orderBy('a.id', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        foreach ($logs as &$log) {
            $meta = json_decode((string) ($log['metadata'] ?? ''), true);
            $log['meta_array'] = is_array($meta) ? $meta : [];
        }
        unset($log);

        return view('admin/audit/index', [
            'logs' => $logs,
            'q' => $q,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }
}