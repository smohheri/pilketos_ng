<?php

namespace App\Controllers;

use App\Libraries\ElectionService;
use App\Libraries\QrService;
use App\Libraries\ReportService;
use App\Models\CandidateModel;
use App\Models\ElectionModel;
use App\Models\ElectionScheduleModel;
use App\Models\FaqModel;
use App\Models\SchoolProfileModel;

class Landing extends BaseController
{
    public function index()
    {
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
            $report = $electionService->resultsVisible($item)
                ? (new ReportService())->overview($item)
                : null;

            $groups[] = [
                'election' => $item,
                'phase' => $phase,
                'candidates' => (new CandidateModel())->forElection((int) $item['id']),
                'schedules' => (new ElectionScheduleModel())->forElection((int) $item['id']),
                'report' => $report,
                'combined' => ($report['result_mode'] ?? 'vote_only') === 'combined',
                'meta' => phase_meta($phase),
            ];
        }

        $primary = $groups[0] ?? null;
        foreach ($groups as $group) {
            if (($group['election']['type'] ?? '') === 'osis') {
                $primary = $group;
                break;
            }
        }

        $faqs = (new FaqModel())->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return view('landing/index', [
            'profile' => (new SchoolProfileModel())->active(),
            'election' => $primary['election'] ?? null,
            'phase' => $primary['phase'] ?? 'draft',
            'candidates' => $primary['candidates'] ?? [],
            'schedules' => $primary['schedules'] ?? [],
            'report' => $primary['report'] ?? null,
            'elections' => $groups,
            'faqs' => $faqs,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function candidate(int $id)
    {
        $candidate = (new CandidateModel())->find($id);
        if ($candidate === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $election = (new ElectionModel())->find((int) $candidate['election_id']);
        if ($election === null
            || ($election['status'] ?? 'draft') === 'draft'
            || (int) ($election['is_enabled'] ?? 1) !== 1) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $others = array_values(array_filter(
            (new CandidateModel())->forElection((int) $election['id']),
            static fn (array $item): bool => (int) $item['id'] !== $id
        ));

        return view('landing/candidate', [
            'profile' => (new SchoolProfileModel())->active(),
            'election' => $election,
            'phase' => (new ElectionService())->phase($election),
            'candidate' => $candidate,
            'others' => $others,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function results(?string $slug = null)
    {
        $electionService = new ElectionService();
        $elections = $electionService->publicElections();

        if ($slug !== null && trim($slug) !== '') {
            $election = $electionService->publicBySlug($slug);
            if ($election === null) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }
        } else {
            $election = $electionService->defaultPublic();
        }

        $visible = $election !== null && $electionService->resultsVisible($election);
        $report = $visible ? (new ReportService())->overview($election) : null;

        if ($elections === [] && $election !== null) {
            $elections = [$election];
        }

        return view('landing/results', [
            'profile' => (new SchoolProfileModel())->active(),
            'election' => $election,
            'elections' => $elections,
            'phase' => $electionService->phase($election),
            'visible' => $visible,
            'report' => $report,
            'appName' => app_setting('app_name', 'CAKRA 2026'),
        ]);
    }

    public function qr()
    {
        $svg = (new QrService())->svg(base_url('/'), 8);

        return $this->response
            ->setHeader('Content-Type', 'image/svg+xml; charset=UTF-8')
            ->setHeader('Cache-Control', 'public, max-age=3600')
            ->setBody($svg);
    }
}
