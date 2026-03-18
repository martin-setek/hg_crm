<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HG CRM — Login</title>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0e17; --bg2: #0f1420; --bg3: #151c2c;
            --border: #1e2a3d; --border2: #243248;
            --text: #e2e8f0; --text2: #8899aa; --text3: #4a5a6e;
            --accent: #00d4aa; --danger: #ef4444;
            --mono: 'IBM Plex Mono', monospace;
            --sans: 'IBM Plex Sans', sans-serif;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--sans);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        /* Grid background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(var(--border) 1px, transparent 1px),
                linear-gradient(90deg, var(--border) 1px, transparent 1px);
            background-size: 40px 40px;
            opacity: .3;
            pointer-events: none;
        }

        .login-box {
            background: var(--bg2);
            border: 1px solid var(--border2);
            border-radius: 8px;
            padding: 40px;
            width: 380px;
            position: relative;
            z-index: 1;
        }

        .login-logo {
            font-family: var(--mono);
            font-size: 20px;
            font-weight: 600;
            color: var(--accent);
            margin-bottom: 4px;
            letter-spacing: .06em;
        }
        .login-logo span { color: var(--text3); font-weight: 400; font-size: 14px; }
        .login-sub {
            font-size: 12px;
            color: var(--text3);
            font-family: var(--mono);
            margin-bottom: 32px;
            letter-spacing: .04em;
        }

        .form-group { margin-bottom: 16px; }
        .form-label {
            display: block;
            font-size: 11px;
            color: var(--text2);
            margin-bottom: 6px;
            font-family: var(--mono);
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        .form-input {
            width: 100%;
            background: var(--bg3);
            border: 1px solid var(--border2);
            border-radius: 4px;
            padding: 10px 14px;
            color: var(--text);
            font-size: 14px;
            font-family: var(--sans);
            outline: none;
            transition: border-color .15s;
        }
        .form-input:focus { border-color: var(--accent); }

        .btn-login {
            width: 100%;
            padding: 11px;
            background: var(--accent);
            color: #0a0e17;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            font-family: var(--sans);
            cursor: pointer;
            letter-spacing: .04em;
            margin-top: 8px;
            transition: background .15s;
        }
        .btn-login:hover { background: #00bfa0; }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            font-size: 12px;
            color: var(--text2);
            cursor: pointer;
        }
        .remember input { accent-color: var(--accent); }

        .error-msg {
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.3);
            color: var(--danger);
            border-radius: 4px;
            padding: 10px 14px;
            font-size: 12px;
            margin-bottom: 16px;
        }

        .login-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 11px;
            color: var(--text3);
            font-family: var(--mono);
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="login-logo">HG_CRM <span>/ pfs</span></div>
    <div class="login-sub">PFS Pipeline — Zaloto / HypoGO</div>

    @if($errors->any())
        <div class="error-msg">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-input" required autofocus autocomplete="email">
        </div>
        <div class="form-group">
            <label class="form-label">Heslo</label>
            <input type="password" name="password" class="form-input" required autocomplete="current-password">
        </div>
        <label class="remember">
            <input type="checkbox" name="remember" value="1"> Zapamatovat přihlášení
        </label>
        <button type="submit" class="btn-login">Přihlásit se →</button>
    </form>

    <div class="login-footer">Offgrid Holdings · {{ date('Y') }}</div>
</div>

</body>
</html>
