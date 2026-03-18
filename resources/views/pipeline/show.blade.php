@extends('layouts.app')

@section('title', $lead->full_name)
@section('page-title', $lead->full_name)

@section('topbar-actions')
    <a href="{{ route('pipeline.edit', $lead) }}" class="btn btn-secondary btn-sm">Edit</a>
@endsection

@section('content')

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;max-width:1000px;">

    <div>
        {{-- Status pipeline --}}
        <div class="card mb-4">
            <div class="card-title">Status pipeline</div>
            <div style="display:flex;gap:0;align-items:center;">
                @foreach(\App\Models\Lead::STATUSES as $val => $label)
                @php
                    $statuses = array_keys(\App\Models\Lead::STATUSES);
                    $currentIdx = array_search($lead->status, $statuses);
                    $thisIdx = array_search($val, $statuses);
                    $isActive = $val === $lead->status;
                    $isPast = $thisIdx < $currentIdx;
                @endphp
                <button onclick="updateStatus('{{ $val }}')"
                        style="flex:1;padding:8px 4px;font-size:11px;font-family:var(--mono);
                               border:1px solid {{ $isActive ? 'var(--accent)' : 'var(--border2)' }};
                               background:{{ $isActive ? 'rgba(0,212,170,.15)' : ($isPast ? 'rgba(0,212,170,.05)' : 'transparent') }};
                               color:{{ $isActive ? 'var(--accent)' : ($isPast ? 'var(--text2)' : 'var(--text3)') }};
                               cursor:pointer;transition:all .15s;text-align:center;
                               border-radius:{{ $loop->first ? '4px 0 0 4px' : ($loop->last ? '0 4px 4px 0' : '0') }};
                               margin-left:{{ $loop->first ? '0' : '-1px' }};">
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- Contact & loan info --}}
        <div class="card mb-4">
            <div class="card-title">Kontaktní & úvěrová data</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                @php
                    $fields = [
                        ['Telefon', $lead->phone],
                        ['Email', $lead->email],
                        ['Region', $lead->region],
                        ['Zdroj', $lead->source],
                        ['Typ', $lead->type],
                        ['Poradce', $lead->advisor?->name . ' (' . $lead->advisor?->initials . ')'],
                    ];
                @endphp
                @foreach($fields as [$label, $val])
                <div>
                    <div style="font-size:11px;color:var(--text3);font-family:var(--mono);letter-spacing:.06em;margin-bottom:3px;">{{ $label }}</div>
                    <div style="font-size:13px;color:var(--text);">{{ $val ?? '—' }}</div>
                </div>
                @endforeach
            </div>
        </div>

        @if($lead->notes)
        <div class="card">
            <div class="card-title">Poznámky</div>
            <div style="font-size:13px;color:var(--text2);white-space:pre-wrap;">{{ $lead->notes }}</div>
        </div>
        @endif
    </div>

    {{-- EV sidebar --}}
    <div>
        <div class="card mb-4">
            <div class="card-title">EV Model</div>
            <div style="margin-bottom:16px;">
                <div style="font-size:11px;color:var(--text3);margin-bottom:4px;">Expected Value</div>
                <div class="mono" style="font-size:28px;font-weight:600;color:var(--accent);">
                    {{ $lead->ev_value ? number_format($lead->ev_value, 0, ',', ' ') . ' Kč' : '—' }}
                </div>
            </div>
            @php
                $evFields = [
                    ['Výše úvěru', $lead->loan_amount ? number_format($lead->loan_amount, 0, ',', ' ') . ' Kč' : '—'],
                    ['Splatnost', $lead->loan_maturity_years ? $lead->loan_maturity_years . ' let' : '—'],
                    ['Úrok', $lead->interest_rate ? $lead->interest_rate . ' %' : '—'],
                    ['Provize', $lead->commission_pct ? ($lead->commission_pct * 100) . ' %' : '—'],
                    ['Pravděpodobnost', $lead->probability ? round($lead->probability * 100) . ' %' : '—'],
                    ['Hodnota nem.', $lead->property_value ? number_format($lead->property_value, 0, ',', ' ') . ' Kč' : '—'],
                ];
            @endphp
            @foreach($evFields as [$l, $v])
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:12px;">
                <span style="color:var(--text3);">{{ $l }}</span>
                <span class="mono" style="color:var(--text);">{{ $v }}</span>
            </div>
            @endforeach
        </div>

        <div class="card">
            <div class="card-title">Timeline</div>
            @php
                $timeline = [
                    ['Přidán', $lead->created_at],
                    ['Přiřazen', $lead->assigned_at],
                    ['Kontaktován', $lead->contacted_at],
                    ['Kvalifikován', $lead->qualified_at],
                    ['Schválen', $lead->approved_at],
                    ['Uzavřen', $lead->closed_at],
                    ['Čerpáno', $lead->disbursed_at],
                ];
            @endphp
            @foreach($timeline as [$l, $d])
            @if($d)
            <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid var(--border);font-size:12px;">
                <span style="color:var(--text3);">{{ $l }}</span>
                <span class="mono" style="color:var(--text);">{{ \Carbon\Carbon::parse($d)->format('d.m.Y') }}</span>
            </div>
            @endif
            @endforeach
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function updateStatus(status) {
    fetch('{{ route('pipeline.update-status', $lead) }}', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
        },
        body: JSON.stringify({ status }),
    })
    .then(r => r.json())
    .then(data => { if (data.ok) location.reload(); });
}
</script>
@endpush
