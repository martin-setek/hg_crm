<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class PipelineSnapshot extends Model
{
    protected $fillable = [
        'advisor_id', 'snapshot_date',
        'leads_total', 'leads_new', 'leads_contacted', 'leads_qualified',
        'leads_in_progress', 'leads_approved', 'leads_closed_won', 'leads_closed_lost',
        'total_ev', 'warm_rate', 'conversion_rate',
    ];

    protected $casts = [
        'snapshot_date'   => 'date',
        'total_ev'        => 'decimal:2',
        'warm_rate'       => 'decimal:4',
        'conversion_rate' => 'decimal:4',
    ];

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(Advisor::class);
    }

    /**
     * Build or update today's snapshot for all advisors.
     */
    public static function captureToday(): void
    {
        $today = Carbon::today();

        Advisor::with('leads')->active()->each(function (Advisor $advisor) use ($today) {
            $leads = $advisor->leads;

            $total     = $leads->count();
            $closedWon = $leads->where('status', 'closed_won')->count();
            $warm      = $leads->whereIn('status', ['qualified', 'in_progress', 'approved'])->count();

            PipelineSnapshot::updateOrCreate(
                ['advisor_id' => $advisor->id, 'snapshot_date' => $today],
                [
                    'leads_total'       => $total,
                    'leads_new'         => $leads->where('status', 'new')->count(),
                    'leads_contacted'   => $leads->where('status', 'contacted')->count(),
                    'leads_qualified'   => $leads->where('status', 'qualified')->count(),
                    'leads_in_progress' => $leads->where('status', 'in_progress')->count(),
                    'leads_approved'    => $leads->where('status', 'approved')->count(),
                    'leads_closed_won'  => $closedWon,
                    'leads_closed_lost' => $leads->where('status', 'closed_lost')->count(),
                    'total_ev'          => $leads->whereNotIn('status', ['closed_lost'])->sum('ev_value'),
                    'warm_rate'         => $total > 0 ? round($warm / $total, 4) : null,
                    'conversion_rate'   => $total > 0 ? round($closedWon / $total, 4) : null,
                ]
            );
        });
    }

    /**
     * Delta vs previous snapshot N days ago.
     */
    public static function deltaForAdvisor(int $advisorId, int $daysBack = 7): array
    {
        $latest = static::where('advisor_id', $advisorId)
            ->orderByDesc('snapshot_date')->first();

        $previous = static::where('advisor_id', $advisorId)
            ->where('snapshot_date', '<=', Carbon::now()->subDays($daysBack))
            ->orderByDesc('snapshot_date')->first();

        if (! $latest || ! $previous) return [];

        return [
            'leads_delta' => $latest->leads_total - $previous->leads_total,
            'ev_delta'    => (float)$latest->total_ev - (float)$previous->total_ev,
            'warm_delta'  => (float)$latest->warm_rate - (float)$previous->warm_rate,
        ];
    }
}
