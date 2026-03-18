<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'advisor_id', 'first_name', 'last_name', 'phone', 'email',
        'region', 'source', 'type', 'status',
        'loan_amount', 'loan_maturity_years', 'property_value',
        'interest_rate', 'commission_pct',
        'ev_value', 'probability',
        'assigned_at', 'contacted_at', 'qualified_at',
        'approved_at', 'closed_at', 'disbursed_at',
        'notes',
    ];

    protected $casts = [
        'loan_amount'       => 'decimal:2',
        'property_value'    => 'decimal:2',
        'interest_rate'     => 'decimal:3',
        'commission_pct'    => 'decimal:4',
        'ev_value'          => 'decimal:2',
        'probability'       => 'decimal:4',
        'assigned_at'       => 'date',
        'contacted_at'      => 'date',
        'qualified_at'      => 'date',
        'approved_at'       => 'date',
        'closed_at'         => 'date',
        'disbursed_at'      => 'date',
    ];

    // Status pipeline order
    public const STATUSES = [
        'new'          => 'Nový',
        'contacted'    => 'Kontaktován',
        'qualified'    => 'Kvalifikován',
        'in_progress'  => 'V řešení',
        'approved'     => 'Schváleno',
        'closed_won'   => 'Uzavřeno ✓',
        'closed_lost'  => 'Ztraceno ✗',
    ];

    // Probability defaults per status (for EV model)
    public const STATUS_PROBABILITY = [
        'new'          => 0.05,
        'contacted'    => 0.10,
        'qualified'    => 0.25,
        'in_progress'  => 0.50,
        'approved'     => 0.80,
        'closed_won'   => 1.00,
        'closed_lost'  => 0.00,
    ];

    public const REGIONS = [
        'Praha', 'Středočeský', 'Jihočeský', 'Plzeňský',
        'Karlovarský', 'Ústecký', 'Liberecký', 'Královéhradecký',
        'Pardubický', 'Vysočina', 'Jihomoravský', 'Olomoucký',
        'Zlínský', 'Moravskoslezský',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(Advisor::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeWarm($query)
    {
        return $query->whereIn('status', ['qualified', 'in_progress', 'approved']);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['closed_won', 'closed_lost']);
    }

    public function scopeForAdvisor($query, $advisorId)
    {
        return $query->where('advisor_id', $advisorId);
    }

    // ── EV Model ───────────────────────────────────────────────────────────────

    /**
     * Recalculate and persist EV.
     * EV = commission_pct × loan_amount × probability
     */
    public function recalculateEv(): void
    {
        $probability = $this->probability
            ?? self::STATUS_PROBABILITY[$this->status]
            ?? 0.05;

        if ($this->loan_amount && $this->loan_amount > 0) {
            $this->ev_value  = round((float)$this->commission_pct * (float)$this->loan_amount * $probability, 2);
            $this->probability = $probability;
        }
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'new'          => 'gray',
            'contacted'    => 'blue',
            'qualified'    => 'yellow',
            'in_progress'  => 'orange',
            'approved'     => 'green',
            'closed_won'   => 'emerald',
            'closed_lost'  => 'red',
            default        => 'gray',
        };
    }

    // ── Boot ───────────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::saving(function (Lead $lead) {
            // Auto-set probability from status if not manually set
            if (! $lead->isDirty('probability')) {
                $lead->probability = self::STATUS_PROBABILITY[$lead->status] ?? 0.05;
            }
            $lead->recalculateEv();
        });
    }
}
