// ── Modals ──────────────────────────────────────────────────────────────────
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

// ── Assistance popup ─────────────────────────────────────────────────────────
function gcmToggleAssistance() { document.getElementById('assistance-popup').classList.toggle('open'); }
function gcmEnvoyerAssistance() {
    var sujet = document.getElementById('assist-sujet').value || 'Demande assistance GCM';
    var msg   = document.getElementById('assist-message').value || '';
    var user  = document.body.dataset.user || '';
    window.location.href = 'mailto:youssefyahyi@gmail.com?subject=' + encodeURIComponent('[GCM] ' + sujet) + '&body=' + encodeURIComponent('Utilisateur: ' + user + '\n\n' + msg);
    setTimeout(() => gcmToggleAssistance(), 500);
}
document.addEventListener('click', function(e) {
    var popup = document.getElementById('assistance-popup');
    var btn   = document.querySelector('.tb-assist');
    if (popup && !popup.contains(e.target) && btn && !btn.contains(e.target)) popup.classList.remove('open');
});

// ── Dropdowns / filtres ──────────────────────────────────────────────────────
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

// ── Menu avatar ──────────────────────────────────────────────────────────────
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

// ── Navigation et mobile ─────────────────────────────────────────────────────
window.addEventListener('pageshow', e => { if (e.persisted) window.location.reload(); });

function mobileToggleSidebar() {
    var sidebar  = document.getElementById('sidebar');
    var overlay  = document.getElementById('sidebar-overlay');
    var isOpen   = sidebar.classList.contains('mobile-open');
    if (!isOpen) {
        sidebar.classList.remove('slim');
        var logo = document.getElementById('topbar-logo');
        if (logo) logo.classList.remove('slim');
    }
    sidebar.classList.toggle('mobile-open', !isOpen);
    overlay.classList.toggle('active', !isOpen);
    document.body.style.overflow = isOpen ? '' : 'hidden';
}
(function() {
    if (window.innerWidth <= 768) {
        document.body.classList.add('is-mobile');
        var s = document.getElementById('sidebar');
        if (s) s.classList.remove('slim');
    }
    window.addEventListener('resize', function() {
        document.body.classList.toggle('is-mobile', window.innerWidth <= 768);
    });
})();
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.nav-group').forEach(function(group) {
        group.addEventListener('mouseenter', function() {
            var flyout = group.querySelector('.flyout');
            if (!flyout) return;
            flyout.style.top = '0';
            flyout.style.bottom = 'auto';
            var rect = group.getBoundingClientRect();
            if (rect.top + flyout.scrollHeight > window.innerHeight - 8) {
                flyout.style.top = 'auto';
                flyout.style.bottom = '0';
            }
        });
    });
    document.querySelectorAll('.sidebar .nav-item, .sidebar .nav-sub-item').forEach(function(el) {
        el.addEventListener('click', function() {
            if (window.innerWidth <= 768) mobileToggleSidebar();
        });
    });
});

// expose globalement pour les onclick inline dans le HTML
window.openModal            = openModal;
window.closeModal           = closeModal;
window.switchTab            = switchTab;
window.gcmToggleAssistance  = gcmToggleAssistance;
window.gcmEnvoyerAssistance = gcmEnvoyerAssistance;
window.gcmToggle            = gcmToggle;
window.gcmSetFilter         = gcmSetFilter;
window.gcmRemoveFilter      = gcmRemoveFilter;
window.gcmToggleAvatarMenu  = gcmToggleAvatarMenu;
window.mobileToggleSidebar  = mobileToggleSidebar;
