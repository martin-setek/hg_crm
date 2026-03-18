<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Advisor extends Model
{
    protected $fillable = [
        'initials', 'name', 'email', 'phone', 'active', 'notes',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(PipelineSnapshot::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // ── Computed ───────────────────────────────────────────────────────────────

    /**
     * Latest EV total for this advisor.
     */
    public function currentEv(): float
    {
        return (float) $this->leads()
            ->whereNotIn('status', ['closed_lost'])
            ->sum('ev_value');
    }

    /**
     * Warm rate: leads in qualified+ states / total assigned leads.
     */
    public function warmRate(): float
    {
        $total = $this->leads()->count();
        if ($total === 0) return 0.0;

        $warm = $this->leads()
            ->whereIn('status', ['qualified', 'in_progress', 'approved', 'closed_won'])
            ->count();

        return round($warm / $total, 4);
    }

    /**
     * 14 PFS brokers seeded by initials.
     */
    public static function allInitials(): array
    {
        return ['GA', 'LD', 'MM', 'KD', 'VJ', 'KK', 'RD', 'LR', 'RK', 'AV', 'RF', 'AM', 'EM', 'PČ'];
    }

    public static function nameMap(): array
    {
        return [
            'GA' => 'Gabriela Adamcová',
            'LD' => 'Lenka Dolejš Michálková',
            'MM' => 'Martin Micka',
            'KD' => 'Kristýna Dočkalová',
            'VJ' => 'Veronika Janikovičová',
            'KK' => 'Karel Kučera',
            'RD' => 'Romana Danilevičová',
            'LR' => 'Lenka Rebrošová',
            'RK' => 'Rostislav Kubíček',
            'AV' => 'Adam Vaškeба',
            'RF' => 'Radek Fiala',
            'AM' => 'Albert Matějka',
            'EM' => 'Eva Marková',
            'PČ' => 'Petr Čajan',
        ];
    }
}
