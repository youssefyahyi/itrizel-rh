<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Itrizel-RH</title>

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0F1923">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Itrizel RH">
    <link rel="apple-touch-icon" href="/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/icon-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/rh.css', 'resources/js/app.js'])
    <style>
    /* ═══════════════════════════════════════════════════
       DESIGN SYSTEM — Itrizel RH (du prototype ZIP)
       Inline pour résister au reset Tailwind
    ═══════════════════════════════════════════════════ */
    :root {
      --bg:#F7F8FA; --surface:#FFFFFF; --surface-soft:#F8FAFC;
      --sidebar-bg:#0F1923; --sidebar-hover:rgba(255,255,255,0.06); --sidebar-active:rgba(255,255,255,0.10); --sidebar-border:rgba(255,255,255,0.07);
      --accent:#2563EB; --accent-hover:#1D4ED8; --accent-light:#EFF6FF; --accent-soft:rgba(37,99,235,0.12);
      --success:#059669; --success-light:#ECFDF5; --warning:#D97706; --warning-light:#FFFBEB;
      --danger:#DC2626; --danger-light:#FEF2F2; --purple:#7C3AED; --purple-light:#F5F3FF;
      --text-primary:#0F172A; --text-secondary:#64748B; --text-muted:#94A3B8;
      --border:#E2E8F0; --border-light:#F1F5F9; --border-hover:#CBD5E1;
      --shadow-sm:0 1px 2px rgba(0,0,0,0.04), 0 1px 6px rgba(0,0,0,0.03);
      --shadow:0 2px 8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
      --shadow-lg:0 8px 24px rgba(0,0,0,0.08), 0 2px 8px rgba(0,0,0,0.04);
      --radius-sm:6px; --radius:10px; --radius-lg:14px;
      --font:'DM Sans', system-ui, sans-serif;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; }
    body { font-family: var(--font); font-size: 13px; color: var(--text-primary); -webkit-font-smoothing: antialiased; background: var(--bg); }

    /* ── Structure ── */
    .app  { display: flex; flex-direction: column; height: 100vh; overflow: hidden; }
    .body { display: flex; flex: 1; overflow: hidden; }
    .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: var(--bg); }
    .content { flex: 1; overflow-y: auto; }

    /* ── Topbar ── */
    .topbar { height: 52px; background: var(--surface); border-bottom: 1px solid var(--border); display: flex; align-items: center; flex-shrink: 0; z-index: 50; }
    .topbar-logo { width: 220px; min-width: 220px; padding: 0 16px; display: flex; align-items: center; gap: 9px; height: 100%; flex-shrink: 0; background: var(--sidebar-bg); transition: width 0.25s ease, min-width 0.25s ease, padding 0.25s ease; overflow: hidden; }
    .topbar-logo.slim { width: 64px; min-width: 64px; padding: 0; justify-content: center; gap: 0; }
    .topbar-logo img { height: 32px; width: auto; display: block; flex-shrink: 0; }
    .logo-lockup { display: flex; flex-direction: column; line-height: 1; white-space: nowrap; transition: opacity 0.15s; }
    .topbar-logo.slim .logo-lockup { opacity: 0; width: 0; overflow: hidden; }
    .logo-name { font-size: 18px; font-weight: 700; color: #fff; letter-spacing: -0.3px; }
    .topbar-brand { display: flex; align-items: center; gap: 14px; padding: 0 18px; height: 100%; border-right: 1px solid var(--border); flex-shrink: 0; }
    .brand-product { display: flex; flex-direction: column; gap: 3px; }
    .brand-name { font-size: 16px; font-weight: 700; color: var(--text-primary); letter-spacing: 0.5px; line-height: 1; }
    .brand-tricolor { display: flex; gap: 2px; height: 2px; width: 38px; border-radius: 2px; overflow: hidden; }
    .brand-tricolor i { flex: 1; }
    .brand-tricolor i:nth-child(1) { background: #C0392B; }
    .brand-tricolor i:nth-child(2) { background: #27AE60; }
    .brand-tricolor i:nth-child(3) { background: #F1C40F; }
    .workspace { display: flex; align-items: center; gap: 8px; padding: 5px 9px; border: 1px solid var(--border); border-radius: var(--radius-sm); cursor: pointer; transition: all 0.15s; }
    .workspace:hover { background: var(--bg); border-color: var(--border-hover); }
    .ws-mark { width: 24px; height: 24px; border-radius: 6px; background: var(--accent-light); color: var(--accent); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .ws-mark svg { width: 14px; height: 14px; }
    .ws-text { display: flex; flex-direction: column; line-height: 1.2; }
    .ws-label { font-size: 9px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
    .ws-name  { font-size: 12px; font-weight: 600; color: var(--text-primary); }
    .ws-chev  { color: var(--text-muted); }
    .topbar-center { flex: 1; display: flex; justify-content: center; }
    .topbar-search { display: flex; align-items: center; gap: 6px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0px 8px; width: 360px; font-size: 11px; transition: border-color 0.15s, box-shadow 0.15s; }
    .topbar-search:focus-within { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }
    .topbar-search input { border: none; background: transparent; font-size: 12px; color: var(--text-primary); outline: none; width: 100%; font-family: inherit; }
    .topbar-search input::placeholder { color: var(--text-muted); }
    .kbd { color: var(--text-muted); font-size: 10px; border: 1px solid var(--border); border-radius: 3px; padding: 1px 4px; flex-shrink: 0; }
    .topbar-right { display: flex; align-items: center; gap: 6px; padding: 0 16px; }
    .tb-icon { width: 30px; height: 30px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-secondary); position: relative; transition: all 0.15s; }
    .tb-icon:hover { background: var(--bg); }
    .tb-icon svg { width: 15px; height: 15px; }
    .tb-assist { display: flex; align-items: center; gap: 5px; padding: 0 10px; height: 30px; border-radius: var(--radius-sm); font-size: 12px; color: var(--text-secondary); cursor: pointer; transition: all 0.15s; }
    .tb-assist:hover { background: var(--bg); }
    .notif-dot { position: absolute; top: 5px; right: 5px; width: 6px; height: 6px; background: var(--danger); border-radius: 50%; border: 1.5px solid var(--surface); }
    .tb-avatar { width: 28px; height: 28px; border-radius: 50%; background: var(--accent); color: #fff; font-size: 11px; font-weight: 600; display: flex; align-items: center; justify-content: center; cursor: pointer; }
    .sep-v { width: 1px; height: 18px; background: var(--border); }

    /* ── Sidebar ── */
    .sidebar { width: 220px; background: var(--sidebar-bg); display: flex; flex-direction: column; flex-shrink: 0; transition: width 0.25s ease; overflow: hidden; }
    .sidebar.slim { width: 64px; }
    .sidebar-nav { flex: 1; overflow-y: auto; overflow-x: hidden; padding: 8px 0; }
    .nav-toggle { display: flex; justify-content: flex-end; padding: 6px 10px 2px; }
    .sidebar.slim .nav-toggle { justify-content: center; }
    .nav-toggle-btn { background: none; border: none; color: rgba(255,255,255,0.3); cursor: pointer; padding: 4px 6px; border-radius: 4px; display: flex; transition: all 0.15s; }
    .nav-toggle-btn:hover { background: var(--sidebar-hover); color: rgba(255,255,255,0.8); }
    .nav-section { font-size: 10px; font-weight: 600; color: rgba(255,255,255,0.3); text-transform: uppercase; letter-spacing: 1px; padding: 14px 18px 4px; white-space: nowrap; overflow: hidden; transition: all 0.2s; }
    .sidebar.slim .nav-section { opacity: 0; height: 0; padding: 0; margin: 0; }
    .nav-item { display: flex; align-items: center; gap: 10px; padding: 9px 12px; margin: 1px 8px; border-radius: var(--radius-sm); color: rgba(255,255,255,0.55); font-size: 12.5px; cursor: pointer; white-space: nowrap; position: relative; transition: all 0.15s; text-decoration: none; }
    .nav-item:hover  { background: var(--sidebar-hover); color: rgba(255,255,255,0.9); }
    .nav-item.active { background: var(--sidebar-active); color: #fff; font-weight: 500; }
    .nav-item.active::before { content:''; position:absolute; left:-8px; top:50%; transform:translateY(-50%); width:3px; height:16px; background:var(--accent); border-radius:0 3px 3px 0; }
    .sidebar.slim .nav-item.active::before { left:0; }
    .nav-chevron { margin-left:auto; transition: transform .15s; flex-shrink:0; }
    .nav-chevron.rotate { transform: rotate(0deg); }
    .nav-submenu { padding: 2px 0 4px 0; }
    .nav-sub-item { display:flex; align-items:center; gap:8px; padding:7px 12px 7px 36px; margin:1px 8px; border-radius:var(--radius-sm); color:rgba(255,255,255,0.45); font-size:12px; cursor:pointer; text-decoration:none; transition:all .15s; }
    .nav-sub-item:hover { background:var(--sidebar-hover); color:rgba(255,255,255,0.85); }
    .nav-sub-item.active { color:rgba(255,255,255,0.95); font-weight:500; background:rgba(255,255,255,0.08); }
    .sidebar.slim .nav-item { justify-content: center; padding: 9px 0; margin: 2px 8px; }
    .nav-icon { width: 16px; height: 16px; flex-shrink: 0; }
    .nav-text { overflow: hidden; transition: opacity 0.15s; }
    .sidebar.slim .nav-text { opacity: 0; width: 0; }
    .sidebar-user { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-top: 1px solid var(--sidebar-border); flex-shrink: 0; overflow: hidden; }
    .sidebar.slim .sidebar-user { justify-content: center; padding: 12px 0; }
    .sidebar.slim .u-info, .sidebar.slim .u-logout { display: none; }
    .u-avatar { width: 30px; height: 30px; border-radius: 50%; background: var(--accent); color: #fff; font-size: 11px; font-weight: 600; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .u-name { font-size: 12px; font-weight: 600; color: #fff; }
    .u-role { font-size: 10px; color: rgba(255,255,255,0.35); }

    /* ── List card ── */
    .list-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; }
    .list-toolbar { display: flex; align-items: center; gap: 8px; padding: 12px 16px; background: var(--surface-soft); border-bottom: 1px solid var(--border-light); flex-wrap: wrap; }
    .search-box { display: flex; align-items: center; gap: 6px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 6px 10px; min-width: 240px; transition: border-color 0.15s, box-shadow 0.15s; }
    .search-box:focus-within { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }
    .search-box input { border: none; background: transparent; font-size: 12px; outline: none; width: 100%; color: var(--text-primary); font-family: inherit; }
    .search-box input::placeholder { color: var(--text-muted); }
    .tb-btn { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 12px; color: var(--text-secondary); background: var(--surface); cursor: pointer; font-family: inherit; transition: all 0.15s; }
    .tb-btn:hover { background: var(--bg); border-color: var(--border-hover); }
    .tb-btn.active { border-color: var(--accent); color: var(--accent); background: var(--accent-light); }
    .tb-btn svg { width: 13px; height: 13px; }
    .tb-count { background: var(--accent); color: #fff; font-size: 10px; padding: 1px 5px; border-radius: 10px; font-weight: 600; }
    .tb-spacer { flex: 1; }
    .chip { display: inline-flex; align-items: center; gap: 5px; background: var(--accent-light); color: var(--accent); font-size: 11px; padding: 4px 9px; border-radius: 20px; }
    .chip button { background: none; border: none; color: var(--accent); cursor: pointer; font-size: 13px; padding: 0; line-height: 1; opacity: 0.6; }
    .chip button:hover { opacity: 1; }

    /* ── Tableau ── */
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    thead th { padding: 10px 16px; text-align: left; font-size: 11px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.6px; background: var(--surface); border-bottom: 1px solid var(--border); white-space: nowrap; cursor: grab; user-select: none; transition: all 0.15s; }
    thead th:hover { background: var(--surface-soft); color: var(--text-primary); }
    thead th .th-in { display: inline-flex; align-items: center; gap: 5px; }
    thead th.tr .th-in { flex-direction: row-reverse; }
    thead th.tc .th-in { justify-content: center; }
    thead th .dh { color: var(--text-secondary); opacity: 1; font-size: 11px; cursor: grab; transition: color 0.15s; }
    thead th:hover .dh { color: var(--text-primary); }
    thead th .sort { width: 11px; height: 11px; color: var(--text-muted); opacity: 0; transition: opacity 0.15s; flex-shrink: 0; }
    thead th:hover .sort { opacity: 0.45; }
    thead th.sorted .sort { opacity: 1; color: var(--accent); }
    tbody tr { border-bottom: 1px solid var(--border-light); transition: background 0.15s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--bg); }
    tbody td { padding: 11px 16px; color: var(--text-secondary); vertical-align: middle; }
    input[type=checkbox] { width: 13px; height: 13px; accent-color: var(--accent); cursor: pointer; }
    .link { color: var(--accent); font-weight: 500; text-decoration: none; }
    .link:hover { color: var(--accent-hover); text-decoration: underline; }
    .badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 500; }
    .badge .dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
    .bg  { background: var(--success-light); color: var(--success); }
    .bb  { background: var(--accent-light);  color: var(--accent); }
    .by  { background: var(--warning-light); color: var(--warning); }
    .bgr { background: var(--border-light);  color: var(--text-secondary); }
    .br  { background: var(--danger-light);  color: var(--danger); }
    .bp  { background: var(--purple-light);  color: var(--purple); }
    .bl  { background: var(--accent-light);  color: var(--accent); }
    .mono   { font-size: 12px; color: var(--text-muted); letter-spacing: 0.2px; }
    .amount { font-weight: 500; color: var(--text-primary); font-size: 13px; font-variant-numeric: tabular-nums; }
    .tr  { text-align: right; }
    .tc  { text-align: center; }
    .muted { color: var(--text-muted); font-size: 12px; }

    /* ── Pagination ── */
    .pagination { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-top: 1px solid var(--border-light); background: var(--surface-soft); }
    .pag-info { font-size: 12px; color: var(--text-secondary); }
    .pag-btns { display: flex; gap: 4px; }
    .pag-btn { padding: 5px 10px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 12px; background: var(--surface); cursor: pointer; color: var(--text-secondary); font-family: inherit; transition: all 0.15s; text-decoration: none; display: inline-flex; align-items: center; }
    .pag-btn:hover { border-color: var(--border-hover); background: var(--surface-soft); }
    .pag-btn.active { background: var(--accent); color: #fff; border-color: var(--accent); }
    .pag-btn[disabled] { opacity: 0.4; cursor: default; }

    /* ══════════════════════════════════════════════
       RESPONSIVE MOBILE  ≤ 768px
    ══════════════════════════════════════════════ */

    /* Éléments visibles uniquement sur mobile */
    .mobile-burger { display: none; }
    .mobile-brand  { display: none; }
    .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 150; }
    .bottom-nav { display: none; }
    .sidebar-mobile-header { display: none; }

    @media (max-width: 768px) {

        /* ── Topbar mobile ── */
        .topbar-logo  { display: none; }
        .topbar-brand { display: none; }
        .topbar-center { display: none; }
        .tb-assist { display: none; }

        .topbar { padding: 0 12px; gap: 10px; }

        .mobile-burger {
            display: flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: var(--radius-sm);
            background: none; border: none; cursor: pointer;
            color: var(--text-secondary); flex-shrink: 0;
        }
        .mobile-burger:hover { background: var(--bg); }
        .mobile-burger svg { width: 20px; height: 20px; }

        .mobile-brand {
            display: flex; align-items: center; gap: 8px; flex: 1;
        }
        .mobile-brand img { height: 26px; width: auto; }
        .mobile-brand-text { font-size: 15px; font-weight: 700; color: var(--text-primary); }
        .mobile-brand-sub  { font-size: 10px; font-weight: 600; color: var(--text-muted); letter-spacing: 0.5px; }

        /* ── Sidebar drawer ── */
        .sidebar {
            position: fixed !important;
            top: 0; left: 0; bottom: 0;
            z-index: 200;
            width: 280px !important;
            min-width: 0 !important;
            transform: translateX(-100%);
            transition: transform 0.25s ease;
            overflow-y: auto;
        }
        .sidebar.mobile-open { transform: translateX(0); }
        /* Annuler TOTALEMENT le mode slim — classe is-mobile sur body */
        body.is-mobile .sidebar,
        body.is-mobile .sidebar.slim { width: 280px !important; transform: translateX(-100%); }
        body.is-mobile .sidebar.mobile-open,
        body.is-mobile .sidebar.slim.mobile-open { transform: translateX(0) !important; }
        body.is-mobile .sidebar *[class*="nav-text"],
        body.is-mobile .sidebar .nav-text { opacity: 1 !important; width: auto !important; overflow: visible !important; display: block !important; }
        body.is-mobile .sidebar .nav-section { opacity: 1 !important; height: auto !important; padding: 14px 18px 4px !important; margin: 0 !important; }
        body.is-mobile .sidebar .nav-item { justify-content: flex-start !important; padding: 9px 12px !important; margin: 1px 8px !important; }
        body.is-mobile .sidebar .sidebar-user { justify-content: flex-start !important; padding: 12px 14px !important; }
        body.is-mobile .sidebar .u-info,
        body.is-mobile .sidebar .u-logout { display: block !important; }
        /* Cacher le bouton collapse sur mobile */
        .sidebar .nav-toggle { display: none !important; }
        /* En-tête du drawer avec bouton fermer */
        .sidebar-mobile-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 14px 10px;
            border-bottom: 1px solid var(--sidebar-border);
        }
        .sidebar-mobile-header-brand { display: flex; align-items: center; gap: 8px; }
        .sidebar-mobile-header-brand img { height: 28px; }
        .sidebar-mobile-header-brand span { font-size: 16px; font-weight: 700; color: #fff; }
        .sidebar-mobile-close {
            width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
            background: var(--sidebar-hover); border: none; border-radius: var(--radius-sm);
            color: rgba(255,255,255,0.6); cursor: pointer;
        }
        .sidebar-overlay { display: block; opacity: 0; pointer-events: none; transition: opacity 0.25s; }
        .sidebar-overlay.active { opacity: 1; pointer-events: auto; }

        /* ── Body & contenu ── */
        .body { position: relative; }
        #admin-panel { display: none !important; }
        .content { padding-bottom: 64px; } /* espace pour la bottom nav */

        /* Afficher l'en-tête du drawer */
        .sidebar-mobile-header { display: flex; }

        /* ── Toolbar liste ── */
        .list-toolbar { flex-direction: column; align-items: stretch; gap: 6px; }
        .search-box { min-width: 0; width: 100%; }
        .tb-spacer { display: none; }

        /* ── Tables → scroll horizontal ── */
        .table-wrap { -webkit-overflow-scrolling: touch; }
        thead th, tbody td { padding: 10px 12px; font-size: 12px; }

        /* ── Pagination mobile ── */
        .pag-info { display: none; }
        .pagination { justify-content: center; }

        /* ── Bottom navigation ── */
        .bottom-nav {
            display: flex;
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 100;
            background: var(--surface);
            border-top: 1px solid var(--border);
            height: 60px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.06);
        }
        .bn-item {
            flex: 1; display: flex; flex-direction: column; align-items: center;
            justify-content: center; gap: 3px; text-decoration: none;
            color: var(--text-muted); font-size: 10px; font-weight: 500;
            cursor: pointer; background: none; border: none; font-family: inherit;
            transition: color 0.15s;
        }
        .bn-item.active { color: var(--accent); }
        .bn-item svg { width: 20px; height: 20px; }
        .bn-item span { font-size: 10px; }
    }
    </style>
</head>
<body data-admin-panel="{{ request()->routeIs('admin.*') ? 'open' : 'closed' }}">
<div class="app">

    {{-- ══ TOPBAR ══ --}}
    <div class="topbar">

        {{-- Burger mobile (caché sur desktop) --}}
        <button class="mobile-burger" onclick="mobileToggleSidebar()" aria-label="Menu">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        {{-- Brand mobile (caché sur desktop) --}}
        <div class="mobile-brand">
            <img src="{{ asset('images/itrizel-mark.png') }}" alt="Itrizel">
            <div>
                <div class="mobile-brand-text">Itrizel</div>
                <div class="mobile-brand-sub">RH</div>
            </div>
        </div>

        <div class="topbar-logo" id="topbar-logo">
            <img src="{{ asset('images/itrizel-mark.png') }}" alt="Itrizel">
            <div class="logo-lockup">
                <span class="logo-name">Itrizel</span>
            </div>
        </div>
        <div class="topbar-brand">
            <div class="brand-product">
                <span class="brand-name">RH</span>
                <span class="brand-tricolor"><i></i><i></i><i></i></span>
            </div>
            <div class="workspace" title="Changer d'espace">
                <div class="ws-mark">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div class="ws-text">
                    <span class="ws-label">Etablissement</span>
                    <span class="ws-name">[Etablissement]</span>
                </div>
                <svg class="ws-chev" width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>
        <div class="topbar-center">
            <div class="topbar-search">
                <svg width="13" height="13" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" placeholder="Recherche globale...">
                <span class="kbd">Ctrl K</span>
            </div>
        </div>
        <div class="topbar-right">
            <div class="tb-assist" onclick="gcmToggleAssistance()" style="cursor:pointer;">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Assistance
            </div>
            <div class="tb-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="notif-dot"></span>
            </div>
            @can('admin')
            <button type="button" class="tb-icon" id="admin-panel-toggle" onclick="gcmToggleAdminPanel()" title="Administration"
                    style="{{ request()->routeIs('admin.*') ? 'background:var(--bg);color:var(--accent);' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
            @endcan
            <div class="sep-v"></div>
            <div style="position:relative;">
                <div class="tb-avatar" id="avatar-btn" onclick="gcmToggleAvatarMenu()" title="Mon compte" style="cursor:pointer;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div id="avatar-menu" style="display:none;position:absolute;top:calc(100% + 8px);right:0;width:200px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-lg);z-index:300;overflow:hidden;">
                    <div style="padding:12px 14px;border-bottom:1px solid var(--border-light);">
                        <div style="font-size:12px;font-weight:600;color:var(--text-primary);">{{ auth()->user()->name }}</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ auth()->user()->email }}</div>
                    </div>
                    <div style="padding:4px 0;">
                        <a href="{{ route('profile.edit') }}" style="display:flex;align-items:center;gap:10px;padding:9px 14px;font-size:13px;color:var(--text-secondary);text-decoration:none;transition:background 0.15s;" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='transparent'">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Mon profil
                        </a>
                        <div style="height:1px;background:var(--border-light);margin:4px 0;"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" style="display:flex;align-items:center;gap:10px;width:100%;padding:9px 14px;font-size:13px;color:var(--danger);background:none;border:none;cursor:pointer;font-family:inherit;transition:background 0.15s;" onmouseover="this.style.background='var(--danger-light)'" onmouseout="this.style.background='transparent'">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ BODY ══ --}}
    <div class="body">

        {{-- SIDEBAR --}}
        <aside class="sidebar" id="sidebar">

            {{-- En-tête drawer (visible uniquement sur mobile) --}}
            <div class="sidebar-mobile-header">
                <div class="sidebar-mobile-header-brand">
                    <img src="{{ asset('images/itrizel-mark.png') }}" alt="Itrizel">
                    <span>Itrizel RH</span>
                </div>
                <button class="sidebar-mobile-close" onclick="mobileToggleSidebar()" aria-label="Fermer">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="sidebar-nav">
                <div class="nav-toggle">
                    <button class="nav-toggle-btn" onclick="toggleNav()" title="Réduire / agrandir">
                        <svg id="toggle-icon" width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                        </svg>
                    </button>
                </div>

                <div class="nav-section">Principal</div>
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Tableau de bord">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="nav-text">Tableau de bord</span>
                </a>

                <div class="nav-section">Personnel</div>
                <a href="{{ route('personnel.index') }}" class="nav-item {{ request()->routeIs('personnel.*') ? 'active' : '' }}" title="Personnel">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="nav-text">Personnel</span>
                </a>
                <a href="{{ route('contrats.index') }}" class="nav-item {{ request()->routeIs('contrats.*') ? 'active' : '' }}" title="Contrats">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="nav-text">Contrats</span>
                </a>

                <div class="nav-section">Absences & Temps</div>
                <a href="{{ route('absences.index') }}" class="nav-item {{ request()->routeIs('absences.*') ? 'active' : '' }}" title="Absences">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    <span class="nav-text">Absences</span>
                </a>
                <a href="{{ route('conges.index') }}" class="nav-item {{ request()->routeIs('conges.*') ? 'active' : '' }}" title="Congés">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="nav-text">Congés</span>
                </a>

                <div class="nav-section">Rémunération</div>
                <a href="{{ route('paie.index') }}" class="nav-item {{ request()->routeIs('paie.*') ? 'active' : '' }}" title="Paie">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span class="nav-text">Paie</span>
                </a>

                <div class="nav-section">Développement</div>
                <a href="{{ route('evaluations.index') }}" class="nav-item {{ request()->routeIs('evaluations.*') ? 'active' : '' }}" title="Évaluations">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span class="nav-text">Évaluations</span>
                </a>
                <a href="{{ route('formations.index') }}" class="nav-item {{ request()->routeIs('formations.*') ? 'active' : '' }}" title="Formations">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span class="nav-text">Formations</span>
                </a>

                <div class="nav-section">Documents</div>
                <a href="{{ route('documents.index') }}" class="nav-item {{ request()->routeIs('documents.*') ? 'active' : '' }}" title="Documents">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span class="nav-text">Documents</span>
                </a>
                {{-- Paramétrage avec sous-menu --}}
                <div x-data="{ open: {{ request()->routeIs('parametrage.*') ? 'true' : 'false' }} }">
                    <button type="button" @click="open = !open"
                        class="nav-item nav-item-toggle {{ request()->routeIs('parametrage.*') ? 'active' : '' }}"
                        title="Paramétrage" style="width:100%;border:none;background:none;text-align:left;cursor:pointer;">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        <span class="nav-text">Paramétrage</span>
                        <svg class="nav-chevron" :class="open ? 'rotate' : ''" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-transition.duration.100ms class="nav-submenu">
                        <a href="{{ route('parametrage.organisation.index') }}"
                           class="nav-sub-item {{ request()->routeIs('parametrage.organisation.*') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Organisation
                        </a>
                        <a href="{{ route('parametrage.emplois.index') }}"
                           class="nav-sub-item {{ request()->routeIs('parametrage.emplois.*') ? 'active' : '' }}">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Référentiel des emplois
                        </a>
                    </div>
                </div>
            </div>

        </aside>

        {{-- CONTENU --}}
        <div class="main">
            <div class="content">
                {{ $slot }}
            </div>
        </div>

        @can('admin')
        {{-- ══ PANEL ADMINISTRATION (sidebar droite fixée dans le flux) ══ --}}
        <aside id="admin-panel"
               style="width:240px;min-width:240px;background:var(--surface);border-left:1px solid var(--border);display:flex;flex-direction:column;flex-shrink:0;overflow:hidden;transition:width 0.25s ease,min-width 0.25s ease;">

            {{-- En-tête --}}
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 18px;border-bottom:1px solid var(--border-light);">
                <div style="font-size:13px;font-weight:600;color:var(--text-primary);">Administration</div>
                <button onclick="gcmToggleAdminPanel()" style="background:none;border:none;cursor:pointer;color:var(--text-muted);padding:4px;border-radius:4px;display:flex;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav style="flex:1;overflow-y:auto;padding:8px 0;">

                <div style="padding:5px 18px 3px;font-size:10px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;">Comptes</div>

                <a href="{{ route('admin.users.index') }}"
                   style="display:flex;align-items:center;gap:10px;padding:9px 18px;color:{{ request()->routeIs('admin.users.*') ? 'var(--accent)' : 'var(--text-secondary)' }};font-size:13px;text-decoration:none;background:{{ request()->routeIs('admin.users.*') ? 'var(--accent-light)' : 'transparent' }};border-radius:6px;margin:1px 8px;transition:background 0.15s;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Utilisateurs
                    <span style="margin-left:auto;background:var(--border-light);color:var(--text-muted);font-size:10px;padding:1px 6px;border-radius:10px;">{{ $adminUserCount ?? 0 }}</span>
                </a>

                <a href="{{ route('admin.teams.index') }}"
                   style="display:flex;align-items:center;gap:10px;padding:9px 18px;color:{{ request()->routeIs('admin.teams.*') ? 'var(--accent)' : 'var(--text-secondary)' }};font-size:13px;text-decoration:none;background:{{ request()->routeIs('admin.teams.*') ? 'var(--accent-light)' : 'transparent' }};border-radius:6px;margin:1px 8px;transition:background 0.15s;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Équipes
                    <span style="margin-left:auto;background:var(--border-light);color:var(--text-muted);font-size:10px;padding:1px 6px;border-radius:10px;">{{ $adminTeamCount ?? 0 }}</span>
                </a>

            </nav>

            {{-- Pied --}}
            <div style="padding:14px 18px;border-top:1px solid var(--border-light);">
                <div style="font-size:11px;color:var(--text-muted);">{{ auth()->user()->name }}</div>
                <div style="font-size:10px;color:var(--text-muted);margin-top:2px;">{{ auth()->user()->role_label }}</div>
            </div>
        </aside>
        @endcan

    </div>
</div>

{{-- Overlay sidebar mobile --}}
<div class="sidebar-overlay" id="sidebar-overlay" onclick="mobileToggleSidebar()"></div>

{{-- Bottom navigation mobile --}}
<nav class="bottom-nav">
    <a href="{{ route('dashboard') }}" class="bn-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        <span>Accueil</span>
    </a>
    <a href="{{ route('personnel.index') }}" class="bn-item {{ request()->routeIs('personnel.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <span>Personnel</span>
    </a>
    <a href="{{ route('conges.index') }}" class="bn-item {{ request()->routeIs('conges.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <span>Congés</span>
    </a>
    <a href="{{ route('paie.index') }}" class="bn-item {{ request()->routeIs('paie.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        <span>Paie</span>
    </a>
    <button class="bn-item" onclick="mobileToggleSidebar()">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
        <span>Menu</span>
    </button>
</nav>

{{-- Popup assistance (ancrée sous le bouton topbar) --}}
<div class="assistance-popup" id="assistance-popup" style="position:fixed;top:56px;right:16px;bottom:auto;">
    <div class="assistance-popup-header"><h4>Assistance GCM</h4><p>Envoyez un message à l'équipe technique</p></div>
    <div class="assistance-popup-body">
        <div class="form-group"><label class="form-label">Sujet</label><input type="text" id="assist-sujet" class="form-control" placeholder="Ex : Problème de connexion..."></div>
        <div class="form-group"><label class="form-label">Message</label><textarea id="assist-message" class="form-control" rows="3" placeholder="Décrivez votre problème..."></textarea></div>
    </div>
    <div class="assistance-popup-footer">
        <button class="btn btn-ghost btn-sm" onclick="gcmToggleAssistance()">Annuler</button>
        <button class="btn btn-primary btn-sm" onclick="gcmEnvoyerAssistance()">Envoyer</button>
    </div>
</div>

<script>
function toggleNav() {
    var sidebar = document.getElementById('sidebar');
    var logo    = document.getElementById('topbar-logo');
    var icon    = document.getElementById('toggle-icon');
    var slim    = sidebar.classList.toggle('slim');
    logo.classList.toggle('slim', slim);
    icon.innerHTML = slim
        ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>'
        : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>';
    localStorage.setItem('gcm-slim', slim ? '1' : '');
}
(function() {
    if (localStorage.getItem('gcm-slim')) {
        var s = document.getElementById('sidebar'), l = document.getElementById('topbar-logo'), i = document.getElementById('toggle-icon');
        if (s) s.classList.add('slim');
        if (l) l.classList.add('slim');
        if (i) i.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>';
    }
})();
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function switchTab(name, btn) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) e.target.classList.remove('open');
});
function gcmToggleAssistance() { document.getElementById('assistance-popup').classList.toggle('open'); }
function gcmEnvoyerAssistance() {
    var sujet = document.getElementById('assist-sujet').value || 'Demande assistance GCM';
    var msg   = document.getElementById('assist-message').value || '';
    var user  = '{{ auth()->user()->name ?? "" }}';
    window.location.href = 'mailto:youssefyahyi@gmail.com?subject=' + encodeURIComponent('[GCM] ' + sujet) + '&body=' + encodeURIComponent('Utilisateur: ' + user + '\n\n' + msg);
    setTimeout(() => gcmToggleAssistance(), 500);
}
document.addEventListener('click', function(e) {
    var popup = document.getElementById('assistance-popup');
    var btn   = document.querySelector('.tb-assist');
    if (popup && !popup.contains(e.target) && btn && !btn.contains(e.target)) popup.classList.remove('open');
});
function gcmToggle(id, e) {
    e?.stopPropagation();
    var el = document.getElementById(id), open = el.style.display === 'block';
    document.querySelectorAll('[id^="dd-"]').forEach(d => d.style.display = 'none');
    el.style.display = open ? 'none' : 'block';
}
function gcmSetFilter(name, val) {
    var f = document.getElementById('f-' + name);
    if (f) { f.value = val; var form = f.closest('form'); if (form) form.submit(); }
}
function gcmRemoveFilter(name) { gcmSetFilter(name, ''); }
document.addEventListener('click', function(e) {
    if (!e.target.closest('[id^="dd-"]') && !e.target.closest('[id^="btn-cols-"]'))
        document.querySelectorAll('[id^="dd-"]').forEach(d => d.style.display = 'none');
});
function gcmToggleAvatarMenu() {
    var m = document.getElementById('avatar-menu');
    m.style.display = m.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
    var menu = document.getElementById('avatar-menu');
    var btn  = document.getElementById('avatar-btn');
    if (menu && !menu.contains(e.target) && btn && !btn.contains(e.target))
        menu.style.display = 'none';
});
window.addEventListener('pageshow', e => { if (e.persisted) window.location.reload(); });

function mobileToggleSidebar() {
    var sidebar  = document.getElementById('sidebar');
    var overlay  = document.getElementById('sidebar-overlay');
    var isOpen   = sidebar.classList.contains('mobile-open');
    if (!isOpen) {
        // Forcer le mode plein (annuler slim) quand on ouvre sur mobile
        sidebar.classList.remove('slim');
        var logo = document.getElementById('topbar-logo');
        if (logo) logo.classList.remove('slim');
    }
    sidebar.classList.toggle('mobile-open', !isOpen);
    overlay.classList.toggle('active', !isOpen);
    document.body.style.overflow = isOpen ? '' : 'hidden';
}
// Appliquer is-mobile sur body + retirer slim dès le chargement
(function() {
    if (window.innerWidth <= 768) {
        document.body.classList.add('is-mobile');
        var s = document.getElementById('sidebar');
        if (s) s.classList.remove('slim');
    }
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            document.body.classList.add('is-mobile');
        } else {
            document.body.classList.remove('is-mobile');
        }
    });
})();
// Fermer le drawer quand on clique un lien nav sur mobile
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.sidebar .nav-item, .sidebar .nav-sub-item').forEach(function(el) {
        el.addEventListener('click', function() {
            if (window.innerWidth <= 768) mobileToggleSidebar();
        });
    });
});
</script>

@can('admin')
<script>
function gcmToggleAdminPanel() {
    var panel = document.getElementById('admin-panel');
    var btn   = document.getElementById('admin-panel-toggle');
    if (!panel) return;
    var hidden = panel.style.width === '0px';
    panel.style.width    = hidden ? '240px' : '0px';
    panel.style.minWidth = hidden ? '240px' : '0px';
    if (btn) btn.style.color = hidden ? 'var(--accent)' : '';
}

// Sur les pages admin/profil le panel s'affiche automatiquement
(function() {
    if (document.body.dataset.adminPanel !== 'open') {
        var panel = document.getElementById('admin-panel');
        if (panel) { panel.style.width = '0px'; panel.style.minWidth = '0px'; }
    }
})();
</script>
@endcan

{{-- PWA Service Worker --}}
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(reg => console.log('SW enregistré:', reg.scope))
            .catch(err => console.warn('SW échec:', err));
    });
}
</script>

</body>
</html>
