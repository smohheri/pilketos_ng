<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ElectionService;
use App\Libraries\ReportService;
use App\Models\AuditLogModel;
use App\Models\ElectionScheduleModel;
use App\Models\SchoolProfileModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $electionService = new ElectionService();
        $election = admin_current_election();
        $phase = $electionService->phase($election);
        $report = (new ReportService())->overview($election);

        $schedules = $election !== null
            ? (new ElectionScheduleModel())->forElection((int) $election['id'])
            : [];

        $nextStage = null;
        foreach ($schedules as $schedule) {
            if (! empty($schedule['end_at']) && strtotime((string) $schedule['end_at']) >= time()) {
                $nextStage = $schedule;
                break;
            }
            if (strtotime((string) $schedule['start_at']) >= time()) {
                $nextStage = $schedule;
                break;
            }
        }

        $recentLogs = is_admin()
            ? (new AuditLogModel())->orderBy('id', 'DESC')->limit(6)->findAll()
            : [];

        return view('admin/dashboard', [
            'profile' => (new SchoolProfileModel())->active(),
            'election' => $election,
            'phase' => $phase,
            'report' => $report,
            'nextStage' => $nextStage,
            'recentLogs' => $recentLogs,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }
}