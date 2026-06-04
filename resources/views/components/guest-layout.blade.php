@props([
    'title'    => 'Connexion',
    'pretitle' => 'Espace RH',
    'sub'      => '',
    'footnote' => '',
])

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — Itrizel RH</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body {
            margin: 0; padding: 0;
            font-family: 'Inter', system-ui, sans-serif;
            color: #0F172A;
            height: 100vh; overflow: hidden;
        }
        :root {
            --accent: #2563EB;
            --terra:  #C2410C;
            --ink:    #0F172A;
            --muted:  #94A3B8;
            --border: #E2E8F0;
            --danger: #DC2626;
            --success:#059669;
            --mono:   'IBM Plex Mono', monospace;
        }

        .stage { display: grid; grid-template-columns: 1.1fr 1fr; height: 100vh; }

        /* ─── LEFT ─── */
        .auth-left { position: relative; background: #0B131C; overflow: hidden; }
        .horizon-fallback {
            position: absolute; inset: 0; pointer-events: none;
            background: linear-gradient(180deg, #0B131C 0%, #1E293B 50%, #3B2418 78%, #5C3320 92%, #C2410C 100%);
        }
        .horizon-fallback svg { position: absolute; inset: 0; width: 100%; height: 100%; }

        .auth-photo {
            position: absolute; inset: 0;
            background-image: url('{{ asset('images/login-bg.jpg') }}');
            background-size: cover; background-position: center; background-repeat: no-repeat;
        }
        .auth-overlay {
            position: absolute; inset: 0; pointer-events: none;
            background: linear-gradient(180deg, rgba(11,19,28,0.50) 0%, rgba(11,19,28,0) 30%, rgba(11,19,28,0) 55%, rgba(11,19,28,0.85) 100%);
        }
        .auth-seal {
            position: absolute; top: 32px; left: 36px;
            display: flex; align-items: center; gap: 11px; color: #fff; z-index: 2;
        }
        .auth-seal .mark {
            width: 32px; height: 32px; border-radius: 8px;
            background: var(--accent);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 12px; letter-spacing: -0.5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
        }
        .auth-seal .name { font-size: 13px; font-weight: 600; line-height: 1.2; }
        .auth-seal .sub  { font-size: 10px; color: rgba(255,255,255,0.55); letter-spacing: 0.4px; margin-top: 1px; }

        .auth-cartouche {
            position: absolute; left: 36px; right: 36px; bottom: 32px;
            color: #fff; z-index: 2;
        }
        .auth-cartouche .eyebrow { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
        .auth-cartouche .eyebrow .bar { width: 18px; height: 1.5px; background: var(--terra); }
        .auth-cartouche .eyebrow span {
            font-family: var(--mono); font-size: 10px; letter-spacing: 2px;
            text-transform: uppercase; color: rgba(255,255,255,0.55);
        }
        .auth-cartouche h2 {
            font-size: 24px; font-weight: 600; letter-spacing: -0.5px;
            line-height: 1.15; margin: 0 0 8px; max-width: 480px;
        }
        .auth-cartouche .body {
            font-size: 12px; color: rgba(255,255,255,0.55);
            line-height: 1.55; max-width: 420px;
        }
        .auth-cartouche .meta {
            display: flex; gap: 24px; margin-top: 22px;
            padding-top: 18px; border-top: 1px solid rgba(255,255,255,0.12);
        }
        .auth-cartouche .meta .item {
            font-family: var(--mono); font-size: 10px;
            color: rgba(255,255,255,0.45); letter-spacing: 0.5px;
        }
        .auth-cartouche .meta .item strong {
            display: block; color: rgba(255,255,255,0.85);
            font-weight: 500; text-transform: uppercase; letter-spacing: 1px;
            font-size: 9px; margin-bottom: 3px;
        }

        /* ─── RIGHT ─── */
        .auth-right {
            background: #FAFAF8;
            display: flex; align-items: center; justify-content: center;
            padding: 40px; overflow-y: auto;
        }
        .auth-card { width: 100%; max-width: 380px; }

        .auth-pretitle {
            font-size: 10px; font-weight: 600; color: #64748B;
            text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 10px;
        }
        .auth-title {
            font-size: 24px; font-weight: 600; letter-spacing: -0.4px; margin: 0 0 6px;
        }
        .auth-sub {
            font-size: 13px; color: #64748B; margin-bottom: 30px; line-height: 1.55;
        }

        .alert-status {
            background: rgba(5,150,105,0.10);
            border: 1px solid rgba(5,150,105,0.25);
            color: #047857;
            padding: 10px 14px; border-radius: 7px; font-size: 12px;
            margin-bottom: 16px; line-height: 1.5;
        }
        .form-group { margin-bottom: 16px; }
        .form-label {
            display: block; font-size: 12px; font-weight: 500; color: #334155; margin-bottom: 7px;
        }
        .form-control {
            width: 100%; padding: 11px 13px;
            border: 1px solid var(--border); border-radius: 8px;
            font-family: inherit; font-size: 14px; color: var(--ink);
            background: #fff; outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
        }
        .form-error { font-size: 11px; color: var(--danger); margin-top: 5px; }

        .form-footer {
            display: flex; align-items: center; justify-content: space-between;
            margin: 4px 0 22px; gap: 10px; flex-wrap: wrap;
        }
        .remember-label {
            display: flex; align-items: center; gap: 7px;
            font-size: 12px; color: #64748B; cursor: pointer; user-select: none;
        }
        .remember-label input { accent-color: var(--accent); margin: 0; }
        .auth-link {
            font-size: 12px; color: var(--accent);
            text-decoration: none; font-weight: 500;
        }
        .auth-link:hover { text-decoration: underline; }

        .btn-auth {
            width: 100%; padding: 12px;
            background: var(--ink); color: #fff;
            border: none; border-radius: 8px;
            font-family: inherit; font-size: 14px; font-weight: 600;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: background 0.15s;
            text-decoration: none;
        }
        .btn-auth:hover { background: #1E293B; }
        .btn-auth .accent-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--terra); }
        .btn-secondary-auth {
            width: 100%; padding: 11px;
            background: transparent; color: #334155;
            border: 1px solid var(--border); border-radius: 8px;
            font-family: inherit; font-size: 13px; font-weight: 500;
            cursor: pointer; text-align: center; text-decoration: none;
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            transition: background 0.15s;
        }
        .btn-secondary-auth:hover { background: #F1F5F9; }

        .auth-status-icon {
            width: 48px; height: 48px; border-radius: 12px;
            background: rgba(37,99,235,0.10); color: var(--accent);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 16px;
        }
        .auth-status-icon.success { background: rgba(5,150,105,0.12); color: var(--success); }
        .auth-status-icon svg { width: 22px; height: 22px; }

        .auth-footnote {
            margin-top: 28px; padding-top: 18px; border-top: 1px solid #E5E5E0;
            font-size: 11px; color: var(--muted); text-align: center; line-height: 1.6;
        }
        .auth-footnote a { color: #475569; text-decoration: none; }
        .auth-footnote a:hover { color: var(--ink); }

        @media (max-width: 800px) {
            .stage { grid-template-columns: 1fr; }
            .auth-left { display: none; }
        }
    </style>

    @stack('head')
</head>
<body>

<div class="stage">

    {{-- LEFT --}}
    <div class="auth-left">
        <div class="horizon-fallback" aria-hidden="true">
            <svg preserveAspectRatio="xMidYMid slice" viewBox="0 0 800 1000">
                <g fill="rgba(255,255,255,0.5)">
                    <circle cx="120" cy="120" r="0.8"/><circle cx="260" cy="80"  r="0.6"/>
                    <circle cx="420" cy="160" r="0.8"/><circle cx="580" cy="100" r="0.6"/>
                    <circle cx="690" cy="180" r="0.7"/><circle cx="190" cy="220" r="0.6"/>
                    <circle cx="370" cy="260" r="0.5"/><circle cx="640" cy="280" r="0.7"/>
                </g>
                <path d="M0,720 Q120,710 220,718 Q340,728 460,712 Q560,700 660,716 Q740,728 800,722 L800,760 L0,760 Z"
                      fill="rgba(0,0,0,0.4)"/>
                <g stroke="rgba(252,211,77,0.10)" stroke-width="1" fill="none">
                    <path d="M0,800 Q200,790 400,800 T800,800"/>
                    <path d="M0,840 Q200,832 400,840 T800,840"/>
                    <path d="M0,880 Q200,874 400,880 T800,880"/>
                </g>
                <ellipse cx="400" cy="730" rx="200" ry="30" fill="rgba(252,211,77,0.18)"/>
                <ellipse cx="400" cy="730" rx="100" ry="14" fill="rgba(252,211,77,0.28)"/>
            </svg>
        </div>

        <div class="auth-photo" aria-hidden="true"></div>
        <div class="auth-overlay"></div>

        <div class="auth-seal">
            <img src="{{ asset('images/itrizel-mark.png') }}" alt="Itrizel" style="height:40px;width:auto;">
            <div>
                <div class="name">Itrizel RH</div>
                <div class="sub">Système de gestion RH</div>
            </div>
        </div>

        <div class="auth-cartouche">
            <div class="eyebrow"><span class="bar"></span><span>Accès réservé · agents habilités</span></div>
            <h2>Gestion des Ressources Humaines</h2>
            <div class="body">
                Plateforme de gestion du personnel, contrats, présences, congés, paie, évaluations et formations.
            </div>
            <div class="meta">
                <div class="item"><strong>Éditeur</strong>Itrizel</div>
                <div class="item"><strong>Version</strong>v{{ config('app.version', '1.0.0') }}</div>
                <div class="item"><strong>Environnement</strong>{{ ucfirst(app()->environment()) }}</div>
            </div>
        </div>
    </div>

    {{-- RIGHT --}}
    <div class="auth-right">
        <div class="auth-card">

            {{-- Badge RH --}}
            <div style="text-align:center;margin-bottom:32px;">
                <div style="display:inline-block;">
                    <div style="font-size:32px;font-weight:800;color:#0F172A;letter-spacing:1px;line-height:1;">RH</div>
                    <div style="display:flex;gap:3px;height:3px;margin-top:8px;border-radius:2px;overflow:hidden;">
                        <div style="flex:1;background:#C0392B;"></div>
                        <div style="flex:1;background:#27AE60;"></div>
                        <div style="flex:1;background:#F1C40F;"></div>
                    </div>
                </div>
            </div>

            @if($pretitle)
                <div class="auth-pretitle">{{ $pretitle }}</div>
            @endif

            <h2 class="auth-title">{{ $title }}</h2>

            @if($sub)
                <p class="auth-sub">{{ $sub }}</p>
            @endif

            {{ $slot }}

            @if($footnote)
                <div class="auth-footnote">{!! $footnote !!}</div>
            @endif
        </div>
    </div>

</div>

</body>
</html>
