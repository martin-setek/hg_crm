@extends('layouts.app')

@section('title', 'Pipeline')
@section('page-title', 'Pipeline / Leady')

@section('topbar-actions')
    <a href="{{ route('pipeline.create') }}" class="btn btn-primary btn-sm">+ Nový lead</a>
@endsection

@section('content')

{{-- Filters --}}
<form method="GET" action="{{ route('pipeline.index') }}" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
    <input type="text" name="search" value="{{ request('search') }}"
           class="form-input" style="width:200px;" placeholder="Jméno / telefon / email">

    <select name="status" class="form-input" style="width:160px;">
        <option value="">Všechny statusy</option>
        @foreach(\App\Models\Lead::STATUSES as $val => $label)
            <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>

    <select name="advisor_id" class="form-input" style="width:180px;">
        <option value="">Všichni poradci</option>
        @foreach($advisors as $a)
            <option value="{{ $a->id }}" {{ request('advisor_id') == $a->id ? 'selected' : '' }}>
                {{ $a->initials }} – {{ $a->name }}
            </option>
        @endforeach
    </select>

    <select name="region" class="form-input" style="width:160px;">
        <option value="">Všechny regiony</option>
        @foreach(\App\Models\Lead::REGIONS as $r)
            <option value="{{ $r }}" {{ request('region') == $r ? 'selected' : '' }}>{{ $r }}</option>
        @endforeach
    </select>

    <button type="submit" class="btn btn-secondary">Filtrovat</button>
    @if(request()->hasAny(['search','status','advisor_id','region']))
        <a href="{{ route('pipeline.index') }}" class="btn btn-secondary">✕ Reset</a>
    @endif
</form>

<div class="card" style="padding:0;">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Klient</th>
                <th>Telefon</th>
                <th>Poradce</th>
                <th>Region</th>
                <th>Status</th>
                <th style="text-align:right;">Úvěr (Kč)</th>
                <th style="text-align:right;">EV (Kč)</th>
                <th>Přidán</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($leads as $lead)
            <tr>
                <td class="mono muted" style="font-size:11px;">{{ $lead->id }}</td>
                <td>
                    <a href="{{ route('pipeline.show', $lead) }}"
                       style="color:var(--text);text-decoration:none;font-weight:500;">
                        {{ $lead->full_name }}
                    </a>
                    @if($lead->type && $lead->type !== 'HG')
                        <span class="badge badge-yellow" style="margin-left:4px;font-size:10px;">{{ $lead->type }}</span>
                    @endif
                </td>
                <td class="mono" style="font-size:12px;">{{ $lead->phone ?? '—' }}</td>
                <td>
                    @if($lead->advisor)
                        <a href="{{ route('advisors.show', $lead->advisor) }}"
                           class="badge badge-blue" style="text-decoration:none;">
                            {{ $lead->advisor->initials }}
                        </a>
                    @else
                        <span class="muted">—</span>
                    @endif
                </td>
                <td style="font-size:12px;">{{ $lead->region ?? '—' }}</td>
                <td>
                    <span class="badge badge-{{ $lead->status_color }}">{{ $lead->status_label }}</span>
                </td>
                <td class="text-right mono" style="font-size:12px;">
                    {{ $lead->loan_amount ? number_format($lead->loan_amount, 0, ',', ' ') : '—' }}
                </td>
                <td class="text-right mono" style="color:var(--accent);font-size:12px;">
                    {{ $lead->ev_value ? number_format($lead->ev_value, 0, ',', ' ') : '—' }}
                </td>
                <td class="muted" style="font-size:11px;">{{ $lead->created_at->format('d.m.Y') }}</td>
                <td>
                    <a href="{{ route('pipeline.edit', $lead) }}" class="btn btn-secondary btn-sm">Edit</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="color:var(--text3);text-align:center;padding:32px;">
                    Žádné leady nenalezeny.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;">
    <span class="muted" style="font-size:12px;">
        {{ $leads->total() }} leadů celkem
    </span>
    {{ $leads->links() }}
</div>

@endsection
