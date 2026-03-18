<?php

namespace App\Http\Controllers;

use App\Models\Advisor;
use App\Models\Lead;
use App\Models\PipelineSnapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdvisorController extends Controller
{
    public function index()
    {
        $advisors = Advisor::withCount('leads')
            ->withSum(['leads as total_ev' => fn($q) => $q->active()], 'ev_value')
            ->orderBy('initials')
            ->get()
            ->map(function (Advisor $a) {
                $a->warm_rate_pct = round($a->warmRate() * 100, 1);
                $a->delta = PipelineSnapshot::deltaForAdvisor($a->id);
                return $a;
            });

        return view('advisors.index', compact('advisors'));
    }

    public function show(Advisor $advisor)
    {
        $advisor->load(['leads' => fn($q) => $q->latest()]);

        $statusCounts = $advisor->leads
            ->groupBy('status')
            ->map->count();

        // 30-day history snapshots
        $snapshots = PipelineSnapshot::where('advisor_id', $advisor->id)
            ->where('snapshot_date', '>=', Carbon::now()->subDays(30))
            ->orderBy('snapshot_date')
            ->get();

        $delta = PipelineSnapshot::deltaForAdvisor($advisor->id, 7);

        return view('advisors.show', compact('advisor', 'statusCounts', 'snapshots', 'delta'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'initials' => 'required|string|max:5|unique:advisors',
            'name'     => 'required|string|max:150',
            'email'    => 'nullable|email|max:150',
            'phone'    => 'nullable|string|max:20',
            'notes'    => 'nullable|string',
        ]);

        Advisor::create($data);

        return redirect()->route('advisors.index')
            ->with('success', 'Poradce byl přidán.');
    }

    public function update(Request $request, Advisor $advisor)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:150',
            'email'  => 'nullable|email|max:150',
            'phone'  => 'nullable|string|max:20',
            'active' => 'boolean',
            'notes'  => 'nullable|string',
        ]);

        $advisor->update($data);

        return redirect()->route('advisors.show', $advisor)
            ->with('success', 'Poradce byl aktualizován.');
    }
}
