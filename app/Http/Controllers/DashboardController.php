<?php

namespace App\Http\Controllers;

use App\Models\Advisor;
use App\Models\Lead;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $monthStart = Carbon::now()->startOfMonth();

        $stats = [
            'leads_total'      => Lead::count(),
            'leads_this_month' => Lead::where('created_at', '>=', $monthStart)->count(),
            'leads_active'     => Lead::active()->count(),
            'leads_warm'       => Lead::warm()->count(),
            'ev_total'         => (float) Lead::active()->sum('ev_value'),
            'ev_approved'      => (float) Lead::where('status', 'approved')->sum('ev_value'),
            'closed_won_month' => Lead::where('status', 'closed_won')
                ->where('closed_at', '>=', $monthStart)->count(),
        ];

        // Per-advisor — safe, no withSum closure (SQLite compat)
        $advisors = Advisor::active()->get()->map(function (Advisor $a) {
            $leads = $a->leads()->get();
            $a->total_leads = $leads->count();
            $a->warm_leads  = $leads->whereIn('status', ['qualified', 'in_progress', 'approved'])->count();
            $a->total_ev    = (float) $leads->whereNotIn('status', ['closed_lost'])->sum('ev_value');
            return $a;
        })->sortByDesc('total_ev')->values();

        $statusCounts = Lead::selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $trend = Lead::selectRaw('DATE(created_at) as date, COUNT(*) as cnt')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('cnt', 'date');

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
