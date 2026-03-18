<?php

namespace App\Http\Controllers;

use App\Models\Advisor;
use App\Models\Lead;
use App\Models\PipelineSnapshot;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today     = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        // Global stats
        $stats = [
            'leads_total'     => Lead::count(),
            'leads_this_month'=> Lead::where('created_at', '>=', $monthStart)->count(),
            'leads_active'    => Lead::active()->count(),
            'leads_warm'      => Lead::warm()->count(),
            'ev_total'        => Lead::active()->sum('ev_value'),
            'ev_approved'     => Lead::where('status', 'approved')->sum('ev_value'),
            'closed_won_month'=> Lead::where('status', 'closed_won')
                ->where('closed_at', '>=', $monthStart)->count(),
        ];

        // Per-advisor summary (14 brokers)
        $advisors = Advisor::active()
            ->withCount(['leads as total_leads', 'leads as warm_leads' => function($q) {
                $q->warm();
            }])
            ->withSum(['leads as total_ev' => function($q) {
                $q->active();
            }], 'ev_value')
            ->orderByDesc('total_ev')
            ->get();

        // Status distribution for pipeline chart
        $statusCounts = Lead::selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        // 30-day lead intake trend (daily)
        $trend = Lead::selectRaw('DATE(created_at) as date, COUNT(*) as cnt')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('cnt', 'date');

        // Region distribution
        $byRegion = Lead::selectRaw('region, COUNT(*) as cnt')
            ->whereNotNull('region')
            ->groupBy('region')
            ->orderByDesc('cnt')
            ->pluck('cnt', 'region');

        return view('dashboard.index', compact(
            'stats', 'advisors', 'statusCounts', 'trend', 'byRegion'
        ));
    }
}
