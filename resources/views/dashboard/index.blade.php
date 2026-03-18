@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('topbar-actions')
    <a href="{{ route('pipeline.create') }}" class="btn btn-primary btn-sm">+ Nový lead</a>
@endsection

@section('content')

{{-- Stat tiles --}}
<div class="stat-grid">
    <div class="stat-tile">
        <div class="stat-label">Leady celkem</div>
        <div class="stat-value">{{ number_format($stats['leads_total']) }}</div>
        <div class="stat-delta">{{ $stats['leads_this_month'] }} tento měsíc</div>
    </div>
    <div class="stat-tile">
        <div class="stat-label">Aktivní leady</div>
        <div class="stat-value accent">{{ number_format($stats['leads_active']) }}</div>
    </div>
    <div class="stat-tile">
        <div class="stat-label">Warm leady</div>
        <div class="stat-value warn">{{ number_format($stats['leads_warm']) }}</div>
        <div class="stat-delta">qualified + in_progress + approved</div>
    </div>
    <div class="stat-tile">
        <div class="stat-label">EV pipeline (Kč)</div>
        <div class="stat-value success">{{ number_format($stats['ev_total'], 0, ',', ' ') }}</div>
    </div>
    <div class="stat-tile">
        <div class="stat-label">EV schváleno (Kč)</div>
        <div class="stat-value">{{ number_format($stats['ev_approved'], 0, ',', ' ') }}</div>
    </div>
    <div class="stat-tile">
        <div class="stat-label">Uzavřeno tento měsíc</div>
        <div class="stat-value success">{{ $stats['closed_won_month'] }}</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">

    {{-- Status distribution --}}
    <div class="card">
        <div class="card-title">Distribuce statusů</div>
        @php
            $total = $statusCounts->sum() ?: 1;
            $statusColors = [
                'new' => '#4a5a6e', 'contacted' => '#3b82f6', 'qualified' => '#f59e0b',
                'in_progress' => '#f97316', 'approved' => '#10b981',
                'closed_won' => '#34d399', 'closed_lost' => '#ef4444',
            ];
            $statusLabels = \App\Models\Lead::STATUSES;
        @endphp
        @foreach($statusCounts as $status => $count)
        <div style="margin-bottom:10px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="font-size:12px;color:var(--text2);">{{ $statusLabels[$status] ?? $status }}</span>
                <span class="mono" style="font-size:12px;color:var(--text);">{{ $count }}</span>
            </div>
            <div class="ev-bar">
                <div class="ev-bar-fill" style="width:{{ round($count/$total*100) }}%;background:{{ $statusColors[$status] ?? 'var(--accent)' }};"></div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Top regions --}}
    <div class="card">
        <div class="card-title">Regiony (top 10)</div>
        @php $maxR = $byRegion->max() ?: 1; @endphp
        @foreach($byRegion->take(10) as $region => $cnt)
        <div style="margin-bottom:8px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                <span style="font-size:12px;color:var(--text2);">{{ $region }}</span>
                <span class="mono" style="font-size:12px;color:var(--text);">{{ $cnt }}</span>
            </div>
            <div class="ev-bar">
                <div class="ev-bar-fill" style="width:{{ round($cnt/$maxR*100) }}%;"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Advisor table --}}
<div class="card">
    <div class="card-title">PFS Poradci — EV přehled</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Poradce</th>
                <th>Zkratka</th>
                <th style="text-align:right;">Leady</th>
                <th style="text-align:right;">Warm</th>
                <th style="text-align:right;">Warm rate</th>
                <th style="text-align:right;">EV (Kč)</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($advisors as $advisor)
            <tr>
                <td>{{ $advisor->name }}</td>
                <td><span class="badge badge-blue">{{ $advisor->initials }}</span></td>
                <td class="text-right mono">{{ $advisor->total_leads ?? 0 }}</td>
                <td class="text-right mono">{{ $advisor->warm_leads ?? 0 }}</td>
                <td class="text-right">
                    @php $wr = $advisor->total_leads > 0 ? round(($advisor->warm_leads/$advisor->total_leads)*100, 1) : 0; @endphp
                    <span class="{{ $wr >= 30 ? 'badge badge-green' : ($wr >= 15 ? 'badge badge-yellow' : 'badge badge-gray') }}">
                        {{ $wr }}%
                    </span>
                </td>
                <td class="text-right mono" style="color:var(--accent);">
                    {{ number_format($advisor->total_ev ?? 0, 0, ',', ' ') }}
                </td>
                <td class="text-right">
                    <a href="{{ route('advisors.show', $advisor) }}" class="btn btn-secondary btn-sm">Detail →</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="color:var(--text3);text-align:center;padding:24px;">Žádní poradci</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
