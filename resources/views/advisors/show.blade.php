@extends('layouts.app')

@section('title', $advisor->name)
@section('page-title', $advisor->initials . ' — ' . $advisor->name)

@section('content')

{{-- Top stats --}}
<div class="stat-grid mb-6" style="grid-template-columns:repeat(5,1fr);">
    <div class="stat-tile">
        <div class="stat-label">Leady celkem</div>
        <div class="stat-value">{{ $advisor->leads->count() }}</div>
    </div>
    <div class="stat-tile">
        <div class="stat-label">Warm leady</div>
        <div class="stat-value warn">{{ $statusCounts->only(['qualified','in_progress','approved'])->sum() }}</div>
    </div>
    <div class="stat-tile">
        <div class="stat-label">Warm rate</div>
        @php $wr = $advisor->warmRate(); @endphp
        <div class="stat-value {{ $wr >= 0.3 ? 'success' : ($wr >= 0.15 ? 'warn' : '') }}">
            {{ round($wr * 100, 1) }}%
        </div>
    </div>
    <div class="stat-tile">
        <div class="stat-label">EV pipeline (Kč)</div>
        <div class="stat-value accent">{{ number_format($advisor->currentEv(), 0, ',', ' ') }}</div>
    </div>
    <div class="stat-tile">
        <div class="stat-label">Uzavřeno ✓</div>
        <div class="stat-value success">{{ $statusCounts->get('closed_won', 0) }}</div>
        @if(isset($delta['leads_delta']))
        <div class="stat-delta {{ $delta['leads_delta'] > 0 ? 'up' : 'down' }}">
            Δ7d: {{ $delta['leads_delta'] > 0 ? '+' : '' }}{{ $delta['leads_delta'] }}
        </div>
        @endif
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">

    {{-- Status distribution --}}
    <div class="card">
        <div class="card-title">Distribuce statusů</div>
        @php
            $total = $statusCounts->sum() ?: 1;
            $statusColors = [
                'new'=>'#4a5a6e','contacted'=>'#3b82f6','qualified'=>'#f59e0b',
                'in_progress'=>'#f97316','approved'=>'#10b981',
                'closed_won'=>'#34d399','closed_lost'=>'#ef4444',
            ];
        @endphp
        @foreach(\App\Models\Lead::STATUSES as $val => $label)
        @php $cnt = $statusCounts->get($val, 0); @endphp
        <div style="margin-bottom:10px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                <span style="font-size:12px;color:var(--text2);">{{ $label }}</span>
                <span class="mono" style="font-size:12px;">{{ $cnt }}</span>
            </div>
            <div class="ev-bar">
                <div class="ev-bar-fill" style="width:{{ round($cnt/$total*100) }}%;background:{{ $statusColors[$val] ?? 'var(--accent)' }};"></div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- 30d EV trend --}}
    <div class="card">
        <div class="card-title">EV trend (30 dní)</div>
        @if($snapshots->count() > 1)
        <div style="position:relative;height:120px;">
            @php
                $maxEv = $snapshots->max('total_ev') ?: 1;
                $pts = $snapshots->values();
                $w = 100 / max($pts->count() - 1, 1);
            @endphp
            <svg viewBox="0 0 100 100" preserveAspectRatio="none" style="width:100%;height:100%;">
                <defs>
                    <linearGradient id="evGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#00d4aa" stop-opacity=".3"/>
                        <stop offset="100%" stop-color="#00d4aa" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <polyline
                    points="{{ $pts->map(fn($s,$i) => round($i*$w,2) . ',' . round((1 - $s->total_ev/$maxEv)*90 + 5, 2))->implode(' ') }}"
                    fill="none" stroke="#00d4aa" stroke-width="1.5" vector-effect="non-scaling-stroke"/>
                <polygon
                    points="{{ $pts->map(fn($s,$i) => round($i*$w,2) . ',' . round((1 - $s->total_ev/$maxEv)*90 + 5, 2))->implode(' ') }} 100,100 0,100"
                    fill="url(#evGrad)"/>
            </svg>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text3);font-family:var(--mono);margin-top:4px;">
            <span>{{ $snapshots->first()->snapshot_date->format('d.m') }}</span>
            <span>{{ $snapshots->last()->snapshot_date->format('d.m') }}</span>
        </div>
        @else
        <div style="color:var(--text3);font-size:12px;padding:20px 0;">
            Nedostatek dat (min. 2 snapshoty).
        </div>
        @endif
    </div>
</div>

{{-- Lead list --}}
<div class="card" style="padding:0;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
        <span class="card-title" style="margin:0;">Leady poradce</span>
        <a href="{{ route('pipeline.index', ['advisor_id' => $advisor->id]) }}" class="btn btn-secondary btn-sm">Filtrovat v pipeline →</a>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Klient</th>
                <th>Region</th>
                <th>Status</th>
                <th style="text-align:right;">Úvěr (Kč)</th>
                <th style="text-align:right;">EV (Kč)</th>
                <th>Přidán</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($advisor->leads->take(50) as $lead)
            <tr>
                <td><a href="{{ route('pipeline.show', $lead) }}" style="color:var(--text);text-decoration:none;">{{ $lead->full_name }}</a></td>
                <td style="font-size:12px;">{{ $lead->region ?? '—' }}</td>
                <td><span class="badge badge-{{ $lead->status_color }}">{{ $lead->status_label }}</span></td>
                <td class="text-right mono" style="font-size:12px;">{{ $lead->loan_amount ? number_format($lead->loan_amount,0,',',' ') : '—' }}</td>
                <td class="text-right mono" style="color:var(--accent);font-size:12px;">{{ $lead->ev_value ? number_format($lead->ev_value,0,',',' ') : '—' }}</td>
                <td class="muted" style="font-size:11px;">{{ $lead->created_at->format('d.m.Y') }}</td>
                <td><a href="{{ route('pipeline.edit', $lead) }}" class="btn btn-secondary btn-sm">Edit</a></td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;color:var(--text3);padding:24px;">Žádné leady.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
