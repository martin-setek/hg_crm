@extends('layouts.app')

@section('title', 'PFS Poradci')
@section('page-title', 'PFS Poradci (14)')

@section('content')

<div class="card" style="padding:0;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Zkr.</th>
                <th>Jméno</th>
                <th style="text-align:right;">Leady</th>
                <th style="text-align:right;">Warm rate</th>
                <th style="text-align:right;">EV pipeline (Kč)</th>
                <th>Δ 7d leady</th>
                <th>Δ 7d EV</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($advisors as $advisor)
            <tr>
                <td>
                    <span class="badge badge-blue" style="font-size:12px;">{{ $advisor->initials }}</span>
                </td>
                <td style="font-weight:500;">{{ $advisor->name }}</td>
                <td class="text-right mono">{{ $advisor->leads_count }}</td>
                <td class="text-right">
                    <span class="{{ $advisor->warm_rate_pct >= 30 ? 'badge badge-green' : ($advisor->warm_rate_pct >= 15 ? 'badge badge-yellow' : 'badge badge-gray') }}">
                        {{ $advisor->warm_rate_pct }}%
                    </span>
                </td>
                <td class="text-right mono" style="color:var(--accent);">
                    {{ number_format($advisor->total_ev ?? 0, 0, ',', ' ') }}
                </td>
                <td class="mono" style="font-size:12px;">
                    @if(isset($advisor->delta['leads_delta']))
                        @php $d = $advisor->delta['leads_delta']; @endphp
                        <span class="{{ $d > 0 ? 'stat-delta up' : ($d < 0 ? 'stat-delta down' : '') }}">
                            {{ $d > 0 ? '+' : '' }}{{ $d }}
                        </span>
                    @else
                        <span style="color:var(--text3);">—</span>
                    @endif
                </td>
                <td class="mono" style="font-size:12px;">
                    @if(isset($advisor->delta['ev_delta']))
                        @php $ed = $advisor->delta['ev_delta']; @endphp
                        <span class="{{ $ed > 0 ? 'stat-delta up' : ($ed < 0 ? 'stat-delta down' : '') }}">
                            {{ $ed > 0 ? '+' : '' }}{{ number_format($ed, 0, ',', ' ') }}
                        </span>
                    @else
                        <span style="color:var(--text3);">—</span>
                    @endif
                </td>
                <td>
                    @if($advisor->active)
                        <span class="badge badge-green">Aktivní</span>
                    @else
                        <span class="badge badge-gray">Neaktivní</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('advisors.show', $advisor) }}" class="btn btn-secondary btn-sm">Detail →</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
