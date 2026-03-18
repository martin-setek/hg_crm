<!DOCTYPE html>
<html lang="cs" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HG CRM') — PFS Pipeline</title>

    <!-- Fonts: IBM Plex Mono + IBM Plex Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg:         #0a0e17;
            --bg2:        #0f1420;
            --bg3:        #151c2c;
            --border:     #1e2a3d;
            --border2:    #243248;
            --text:       #e2e8f0;
            --text2:      #8899aa;
            --text3:      #4a5a6e;
            --accent:     #00d4aa;
            --accent2:    #0099ff;
            --warn:       #f59e0b;
            --danger:     #ef4444;
            --success:    #10b981;
            --mono:       'IBM Plex Mono', monospace;
            --sans:       'IBM Plex Sans', sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { height: 100%; }

        body {
            font-family: var(--sans);
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            line-height: 1.6;
        }

        /* Layout */
        .app-shell { display: flex; height: 100vh; overflow: hidden; }

        /* Sidebar */
        .sidebar {
            width: 220px;
            flex-shrink: 0;
            background: var(--bg2);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 0;
        }

        .sidebar-logo {
            padding: 20px 20px 16px;
            border-bottom: 1px solid var(--border);
            font-family: var(--mono);
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .08em;
            color: var(--accent);
        }
        .sidebar-logo span { color: var(--text2); font-weight: 400; }

        .sidebar-nav { flex: 1; padding: 12px 0; }

        .nav-section {
            padding: 6px 16px 4px;
            font-size: 10px;
            font-family: var(--mono);
            letter-spacing: .12em;
            color: var(--text3);
            text-transform: uppercase;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 20px;
            color: var(--text2);
            text-decoration: none;
            font-size: 13px;
            font-weight: 400;
            transition: all .15s;
            border-left: 2px solid transparent;
        }
        .nav-item:hover { color: var(--text); background: var(--bg3); }
        .nav-item.active {
            color: var(--accent);
            background: rgba(0,212,170,.07);
            border-left-color: var(--accent);
            font-weight: 500;
        }
        .nav-icon { width: 16px; opacity: .8; }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--border);
            font-size: 12px;
            color: var(--text3);
        }
        .sidebar-footer a {
            color: var(--text2);
            text-decoration: none;
            font-size: 12px;
        }
        .sidebar-footer a:hover { color: var(--danger); }

        /* Main */
        .main-content { flex: 1; overflow-y: auto; display: flex; flex-direction: column; }

        .topbar {
            padding: 16px 28px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--bg2);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .topbar-title {
            font-family: var(--mono);
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
            letter-spacing: .04em;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 12px;
            color: var(--text2);
            font-family: var(--mono);
        }

        .page-body { padding: 28px; flex: 1; }

        /* Flash messages */
        .flash {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
            border: 1px solid;
        }
        .flash-success { background: rgba(16,185,129,.1); border-color: rgba(16,185,129,.3); color: var(--success); }
        .flash-error   { background: rgba(239,68,68,.1);  border-color: rgba(239,68,68,.3);  color: var(--danger); }

        /* Cards */
        .card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 20px;
        }
        .card-title {
            font-family: var(--mono);
            font-size: 11px;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--text3);
            margin-bottom: 12px;
        }

        /* Stat tiles */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 24px; }
        .stat-tile {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 16px 18px;
        }
        .stat-label { font-size: 11px; color: var(--text3); font-family: var(--mono); letter-spacing: .06em; margin-bottom: 6px; }
        .stat-value { font-family: var(--mono); font-size: 22px; font-weight: 600; color: var(--text); }
        .stat-value.accent  { color: var(--accent); }
        .stat-value.warn    { color: var(--warn); }
        .stat-value.success { color: var(--success); }
        .stat-delta { font-size: 11px; color: var(--text3); margin-top: 4px; font-family: var(--mono); }
        .stat-delta.up   { color: var(--success); }
        .stat-delta.down { color: var(--danger); }

        /* Tables */
        .data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .data-table th {
            font-family: var(--mono);
            font-size: 10px;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--text3);
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        .data-table td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
            vertical-align: middle;
        }
        .data-table tr:hover td { background: var(--bg3); }
        .data-table tr:last-child td { border-bottom: none; }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-family: var(--mono);
            font-weight: 500;
            letter-spacing: .04em;
        }
        .badge-gray    { background: rgba(148,163,184,.12); color: #94a3b8; }
        .badge-blue    { background: rgba(59,130,246,.15);  color: #60a5fa; }
        .badge-yellow  { background: rgba(245,158,11,.15);  color: #fbbf24; }
        .badge-orange  { background: rgba(249,115,22,.15);  color: #fb923c; }
        .badge-green   { background: rgba(16,185,129,.15);  color: #34d399; }
        .badge-emerald { background: rgba(52,211,153,.2);   color: #6ee7b7; }
        .badge-red     { background: rgba(239,68,68,.15);   color: #f87171; }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            transition: all .15s;
            font-family: var(--sans);
        }
        .btn-primary { background: var(--accent); color: #0a0e17; border-color: var(--accent); }
        .btn-primary:hover { background: #00bfa0; }
        .btn-secondary { background: transparent; color: var(--text2); border-color: var(--border2); }
        .btn-secondary:hover { color: var(--text); border-color: var(--text3); }
        .btn-danger { background: rgba(239,68,68,.15); color: var(--danger); border-color: rgba(239,68,68,.3); }
        .btn-danger:hover { background: rgba(239,68,68,.25); }
        .btn-sm { padding: 4px 10px; font-size: 12px; }

        /* Forms */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 12px; color: var(--text2); margin-bottom: 6px; font-family: var(--mono); letter-spacing: .04em; }
        .form-input {
            width: 100%;
            background: var(--bg3);
            border: 1px solid var(--border2);
            border-radius: 4px;
            padding: 8px 12px;
            color: var(--text);
            font-size: 13px;
            font-family: var(--sans);
            transition: border-color .15s;
            outline: none;
        }
        .form-input:focus { border-color: var(--accent); }
        .form-input::placeholder { color: var(--text3); }
        select.form-input { cursor: pointer; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }

        /* Pagination */
        .pagination { display: flex; gap: 4px; align-items: center; margin-top: 16px; }
        .pagination a, .pagination span {
            padding: 4px 10px;
            border: 1px solid var(--border);
            border-radius: 3px;
            font-size: 12px;
            font-family: var(--mono);
            color: var(--text2);
            text-decoration: none;
            transition: all .15s;
        }
        .pagination a:hover { border-color: var(--accent); color: var(--accent); }
        .pagination .active span { background: var(--accent); color: #0a0e17; border-color: var(--accent); }

        /* Utils */
        .mono  { font-family: var(--mono); }
        .muted { color: var(--text2); }
        .flex  { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .mb-4 { margin-bottom: 16px; }
        .mb-6 { margin-bottom: 24px; }
        .text-right { text-align: right; }

        /* EV bar */
        .ev-bar { height: 3px; background: var(--border2); border-radius: 2px; margin-top: 4px; }
        .ev-bar-fill { height: 100%; background: var(--accent); border-radius: 2px; transition: width .3s; }

        /* Mini sparkline placeholder */
        .sparkline { display: inline-block; width: 60px; height: 20px; vertical-align: middle; }
    </style>

    @stack('styles')
</head>
<body>
<div class="app-shell">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-logo">
            HG_CRM<span>/pfs</span>
        </div>
        <div class="sidebar-nav">
            <div class="nav-section">Přehled</div>
            <a href="{{ route('dashboard') }}"
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="1" y="1" width="6" height="6" rx="1"/>
                    <rect x="9" y="1" width="6" height="6" rx="1"/>
                    <rect x="1" y="9" width="6" height="6" rx="1"/>
                    <rect x="9" y="9" width="6" height="6" rx="1"/>
                </svg>
                Dashboard
            </a>

            <div class="nav-section" style="margin-top:8px;">Pipeline</div>
            <a href="{{ route('pipeline.index') }}"
               class="nav-item {{ request()->routeIs('pipeline.*') ? 'active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M2 4h12M2 8h8M2 12h5"/>
                </svg>
                Leady
            </a>
            <a href="{{ route('pipeline.create') }}"
               class="nav-item">
                <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="8" cy="8" r="6"/><path d="M8 5v6M5 8h6"/>
                </svg>
                Přidat lead
            </a>

            <div class="nav-section" style="margin-top:8px;">Poradci</div>
            <a href="{{ route('advisors.index') }}"
               class="nav-item {{ request()->routeIs('advisors.*') ? 'active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="8" cy="5" r="3"/>
                    <path d="M2 14c0-3.314 2.686-5 6-5s6 1.686 6 5"/>
                </svg>
                PFS Poradci
            </a>
        </div>
        <div class="sidebar-footer">
            {{ auth()->user()->name ?? '' }}<br>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" style="background:none;border:none;cursor:pointer;color:inherit;font-size:12px;padding:0;">
                    Odhlásit se →
                </button>
            </form>
        </div>
    </nav>

    <!-- Main -->
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
            <div class="topbar-right">
                <span>{{ now()->format('d.m.Y') }}</span>
                @yield('topbar-actions')
            </div>
        </div>

        <div class="page-body">
            @if(session('success'))
                <div class="flash flash-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="flash flash-error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

@stack('scripts')
</body>
</html>
