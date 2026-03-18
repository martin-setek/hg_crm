@extends('layouts.app')

@section('title', 'Nový lead')
@section('page-title', 'Nový lead')

@section('content')

<div style="max-width:760px;">
<form method="POST" action="{{ route('pipeline.store') }}">
    @csrf

    <div class="card mb-6">
        <div class="card-title">Kontaktní údaje</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Jméno *</label>
                <input type="text" name="first_name" value="{{ old('first_name') }}"
                       class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Příjmení *</label>
                <input type="text" name="last_name" value="{{ old('last_name') }}"
                       class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Telefon</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Region</label>
                <select name="region" class="form-input">
                    <option value="">— vybrat —</option>
                    @foreach(\App\Models\Lead::REGIONS as $r)
                        <option value="{{ $r }}" {{ old('region') == $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Zdroj</label>
                <select name="source" class="form-input">
                    <option value="">—</option>
                    @foreach(['PPC','SMS','Organic','Referral','Other'] as $s)
                        <option value="{{ $s }}" {{ old('source') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-title">Pipeline</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Poradce (PFS)</label>
                <select name="advisor_id" class="form-input">
                    <option value="">— nepřiřazen —</option>
                    @foreach($advisors as $a)
                        <option value="{{ $a->id }}" {{ old('advisor_id') == $a->id ? 'selected' : '' }}>
                            {{ $a->initials }} – {{ $a->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Status *</label>
                <select name="status" class="form-input" required>
                    @foreach(\App\Models\Lead::STATUSES as $val => $label)
                        <option value="{{ $val }}" {{ old('status', 'new') == $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Typ</label>
                <select name="type" class="form-input">
                    @foreach(['HG','SMS','Exter'] as $t)
                        <option value="{{ $t }}" {{ old('type', 'HG') == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-title">EV Model — úvěrová data</div>
        <div class="form-grid-3">
            <div class="form-group">
                <label class="form-label">Výše úvěru (Kč)</label>
                <input type="number" name="loan_amount" value="{{ old('loan_amount') }}"
                       class="form-input" min="0" step="1000" placeholder="3 500 000">
            </div>
            <div class="form-group">
                <label class="form-label">Hodnota nemovitosti (Kč)</label>
                <input type="number" name="property_value" value="{{ old('property_value') }}"
                       class="form-input" min="0" step="1000">
            </div>
            <div class="form-group">
                <label class="form-label">Splatnost (roky)</label>
                <input type="number" name="loan_maturity_years" value="{{ old('loan_maturity_years') }}"
                       class="form-input" min="1" max="40" placeholder="30">
            </div>
            <div class="form-group">
                <label class="form-label">Úroková sazba (%)</label>
                <input type="number" name="interest_rate" value="{{ old('interest_rate') }}"
                       class="form-input" min="0" max="20" step="0.01" placeholder="4.89">
            </div>
            <div class="form-group">
                <label class="form-label">Provize (výchozí 0.6%)</label>
                <input type="number" name="commission_pct" value="{{ old('commission_pct', '0.006') }}"
                       class="form-input" min="0" max="1" step="0.001">
            </div>
            <div class="form-group">
                <label class="form-label">Pravděpodobnost (0–1)</label>
                <input type="number" name="probability" value="{{ old('probability') }}"
                       class="form-input" min="0" max="1" step="0.01"
                       placeholder="auto dle statusu">
            </div>
        </div>
        <div style="font-size:12px;color:var(--text3);margin-top:8px;">
            EV = provize × výše úvěru × pravděpodobnost — počítá se automaticky při uložení
        </div>
    </div>

    <div class="card mb-6">
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Poznámky</label>
            <textarea name="notes" class="form-input" rows="3">{{ old('notes') }}</textarea>
        </div>
    </div>

    @if($errors->any())
        <div class="flash flash-error">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div style="display:flex;gap:12px;">
        <button type="submit" class="btn btn-primary">Uložit lead</button>
        <a href="{{ route('pipeline.index') }}" class="btn btn-secondary">Zrušit</a>
    </div>
</form>
</div>
@endsection
