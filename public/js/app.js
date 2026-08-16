/* ============================================================
   EDUNEX Core JS
   Theme, toasts, modals, dropdowns, tabs, keyboard shortcuts,
   notification polling, tiny SVG charts, autosave, search
   ============================================================ */

(function () {
  'use strict';

  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];

  /* ---------------- Theme ---------------- */
  const theme = {
    get current() { return localStorage.getItem('edunex-theme') || document.documentElement.dataset.theme || 'dark'; },
    set(t) {
      document.documentElement.dataset.theme = t;
      localStorage.setItem('edunex-theme', t);
      document.querySelectorAll('[data-theme-toggle]').forEach(el => { el.innerHTML = t === 'dark' ? ico('sun') : ico('moon'); });
    },
    toggle() { this.set(this.current === 'dark' ? 'light' : 'dark'); }
  };
  document.documentElement.dataset.theme = theme.current;
  window.EdunexTheme = theme;

  /* Inline SVG icon set for JS-generated UI (mirrors includes/icons.php) */
  const ICO = {
    sun: '<circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.9 4.9 6.3 6.3m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
    moon: '<path d="M20 13.5A8 8 0 0 1 10.5 4a8 8 0 1 0 9.5 9.5Z"/>',
    'check-circle': '<circle cx="12" cy="12" r="8.5"/><path d="m8.5 12.2 2.4 2.4 4.6-4.8"/>',
    'ban-circle': '<circle cx="12" cy="12" r="8.5"/><path d="M5.6 5.6l12.8 12.8"/>',
    alert: '<path d="M12 4 2.5 20h19L12 4Z"/><path d="M12 10v4.5m0 1.6v.2"/>',
    info: '<circle cx="12" cy="12" r="8.5"/><path d="M12 11v5m0-6.8v.2"/>',
    shield: '<path d="M12 3 5 6v5c0 5 3 7.5 7 10 4-2.5 7-5 7-10V6l-7-3Z"/>',
    graduation: '<path d="m2.5 9 9.5-5 9.5 5-9.5 5-9.5-5Z"/><path d="M6.5 11.8v4.2c0 1.7 2.5 3 5.5 3s5.5-1.3 5.5-3v-4.2"/><path d="M22 9v5"/>',
    user: '<circle cx="12" cy="8" r="3.5"/><path d="M5 20c.6-3.8 3-6 7-6s6.4 2.2 7 6"/>',
    'users-card': '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10" r="2"/><circle cx="15.5" cy="9" r="1.6"/><path d="M5 17c.4-2 1.8-3 3.5-3s3.1 1 3.5 3M15 17c.3-1.6 6-2.4 6-2.4"/>',
    users: '<circle cx="9" cy="8" r="3.2"/><path d="M3.5 20c.4-3.6 2.5-5.5 5.5-5.5s5.1 1.9 5.5 5.5M16 4.9a3.2 3.2 0 0 1 0 6.2M17.3 14.6c2.1.6 3.2 2.3 3.5 4.9"/>',
    trash: '<path d="M4 7h16M9 7V4h6v3M6.5 7l1 13h9l1-13"/><path d="M10 11v5m4-5v5"/>',
    eye: '<path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12Z"/><circle cx="12" cy="12" r="2.8"/>',
    bolt: '<path d="M13 3 5 13h5l-1 8 8-11h-5l1-7Z"/>',
    calendar: '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v4m8-4v4"/>',
    tag: '<path d="M3 4h11.5L21 14.5 14.5 21 3 10.5V4Z"/><circle cx="8.5" cy="8.5" r="1.3"/>',
    mail: '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
    phone: '<path d="M5 4h4l1.5 4-2.2 1.6c1 2.2 3 4.2 5.2 5.2L15 12.5 19 14v4a2 2 0 0 1-2 2C9.9 20 4 14.1 4 6a2 2 0 0 1 1-2Z"/>',
    star: '<path d="m12 3 2.7 5.8 6.3.7-4.7 4.3 1.3 6.2-5.6-3.2-5.6 3.2 1.3-6.2L3 9.5l6.3-.7L12 3Z"/>',
    school: '<path d="m3 13 9-6 9 6"/><path d="M6 19v-8h12v8"/><path d="M6 19h12"/>',
    books: '<path d="M4 19.5 5.5 6.8l6.2-1.7 1.8 12-6.2 1.7L4 19.5Z"/><path d="M11.5 5.3l1.8-1.8 5.6 1.5 1 6.4-3.2.9"/><path d="M9.8 8.2l4.1-1.1m-4.7 3.8 4.1-1.1"/>',
    folder: '<path d="M3 6a2 2 0 0 1 2-2h5l2 3h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6Z"/>',
    file: '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z"/><path d="M14 3v5h5"/>',
    megaphone: '<path d="M4 10v4h3l8 5V5l-8 5H4Z"/><path d="M15 9.5a3 3 0 0 1 0 5"/>',
    close: '<path d="m6 6 12 12M18 6 6 18"/>',
    pause: '<path d="M9 6v12M15 6v12"/>',
    loader: '<path d="M12 4v3m0 10v3M4 12h3m10 0h3M6 6l2 2m8 8 2 2M18 6l-2 2M8 16l-2 2"/><circle cx="12" cy="12" r="8.5"/>',
    empty: '<path d="M9 12h6M12 9v6"/><circle cx="12" cy="12" r="8.5"/>',
  };
  function ico(name, cls) { return '<svg class="ico ' + (cls || '') + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + (ICO[name] || ICO.empty) + '</svg>'; }

  /* ── Theme ─────────────────────────────────────────────── */

  /* ---------------- Toasts ---------------- */
  function toast(msg, type = 'info', title = '') {
    let wrap = $('.toast-wrap');
    if (!wrap) { wrap = document.createElement('div'); wrap.className = 'toast-wrap'; document.body.appendChild(wrap); }
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    const icons = { success: ico('check-circle'), error: ico('ban-circle'), warning: ico('alert'), info: ico('info') };
    t.innerHTML = `<span>${icons[type] || icons.info}</span><div><b>${escapeHtml(title || type[0].toUpperCase() + type.slice(1))}</b><br>${escapeHtml(msg)}</div><button class="toast-close">✕</button>`;
    t.querySelector('.toast-close').onclick = () => t.remove();
    wrap.appendChild(t);
    setTimeout(() => { t.style.transition = 'all .3s'; t.style.opacity = '0'; t.style.transform = 'translateX(24px)'; setTimeout(() => t.remove(), 300); }, 4200);
  }
  window.toast = toast;
  function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
  window.escapeHtml = escapeHtml;

  /* ---------------- Flash messages from server ---------------- */
  const flashes = window.EDUNEX_FLASHES || [];
  flashes.forEach(f => toast(f.msg, f.type));

  /* ---------------- Modal ---------------- */
  function openModal(id) { const m = document.getElementById(id); if (m) m.classList.add('open'); }
  function closeModal(id) { const m = document.getElementById(id); if (m) m.classList.remove('open'); }
  window.openModal = openModal; window.closeModal = closeModal;
  document.addEventListener('click', e => {
    if (e.target.classList && e.target.classList.contains('modal-backdrop')) e.target.classList.remove('open');
    const closer = e.target.closest('[data-close-modal]');
    if (closer) {
      const id = closer.dataset.closeModal;
      if (id) closeModal(id);                                   // .modal-dialog#id variant
      const bd = e.target.closest('.modal-backdrop');
      if (bd) bd.classList.remove('open');                       // .modal-backdrop variant
    }
    if (e.target.closest('[data-open-modal]')) openModal(e.target.closest('[data-open-modal]').dataset.openModal);
  });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') $$('.modal-backdrop.open').forEach(m => m.classList.remove('open')); });

  /* ---------------- Dropdowns ---------------- */
  document.addEventListener('click', e => {
    const dd = e.target.closest('.dropdown');
    $$('.dropdown.open').forEach(d => { if (d !== dd) d.classList.remove('open'); });
    if (dd) { e.stopPropagation(); dd.classList.toggle('open'); }
  });

  /* ---------------- Tabs ---------------- */
  document.addEventListener('click', e => {
    const tab = e.target.closest('.tab[data-tab]');
    if (!tab) return;
    const group = tab.closest('.tabs');
    $$('.tab', group).forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    const target = tab.dataset.tab;
    $$('[data-tab-panel]').forEach(p => p.style.display = p.dataset.tabPanel === target ? '' : 'none');
  });

  /* ---------------- FAQ ---------------- */
  document.addEventListener('click', e => {
    const f = e.target.closest('.faq-item');
    if (f) f.classList.toggle('open');
  });

  /* ---------------- Keyboard shortcuts ---------------- */
  document.addEventListener('keydown', e => {
    if (e.ctrlKey && e.key.toLowerCase() === 'k') { e.preventDefault(); const s = $('#global-search'); if (s) s.focus(); }
    if (e.ctrlKey && e.key.toLowerCase() === 'm') { e.preventDefault(); const b = $('.menu-btn'); if (b) b.click(); }
    if (e.ctrlKey && e.key.toLowerCase() === 'd') { e.preventDefault(); theme.toggle(); }
    if (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) { e.preventDefault(); const s = $('#global-search'); if (s) s.focus(); }
  });

  /* ---------------- Sidebar mobile ---------------- */
  document.addEventListener('click', e => {
    const btn = e.target.closest('.menu-btn');
    if (btn) { e.preventDefault(); $('.sidebar').classList.toggle('open'); }
  });

  /* ---------------- Notification polling ---------------- */
  async function pollNotifications() {
    if (!window.EDUNEX_USER) return;
    try {
      const r = await fetch(EDUNEX.API + '/api/notifications/poll');
      const data = await r.json();
      const bell = $('#notif-bell');
      if (data.unread !== undefined) {
        let badge = bell ? bell.querySelector('.dot') : null;
        if (badge) badge.style.display = data.unread > 0 ? '' : 'none';
        const cnt = bell ? bell.querySelector('.notif-count') : null;
        if (cnt) cnt.textContent = data.unread;
      }
      if (data.toast) { toast(data.toast.body, data.toast.type, data.toast.title); playPing(); }
    } catch (err) { /* offline */ }
  }
  function playPing() {
    try {
      const ctx = new (window.AudioContext || window.webkitAudioContext)();
      const o = ctx.createOscillator(); const g = ctx.createGain();
      o.connect(g); g.connect(ctx.destination);
      o.frequency.value = 880; g.gain.value = 0.06;
      o.start(); setTimeout(() => { o.stop(); ctx.close(); }, 180);
    } catch (e) {}
  }
  if (window.EDUNEX_USER) { setInterval(pollNotifications, 20000); document.addEventListener('DOMContentLoaded', pollNotifications); }

  /* ---------------- Confirm dialogs ---------------- */
  document.addEventListener('click', e => {
    const c = e.target.closest('[data-confirm]');
    if (c && !confirm(c.dataset.confirm || 'Are you sure?')) e.preventDefault();
  });

  /* ---------------- Tiny SVG Charts ---------------- */
  function chart(el, data, opts = {}) {
    if (!el) return;
    const W = opts.width || 320, H = opts.height || 140;
    const pad = { l: 34, r: 8, t: 10, b: 22 };
    const max = Math.max(...data.map(d => d.v), 1);
    const iw = W - pad.l - pad.r, ih = H - pad.t - pad.b;
    const step = data.length > 1 ? iw / (data.length - 1) : iw;
    const pts = data.map((d, i) => [pad.l + i * step, pad.t + ih - (d.v / max) * ih]);
    let path = pts.map((p, i) => (i ? 'L' : 'M') + p[0].toFixed(1) + ' ' + p[1].toFixed(1)).join(' ');
    const area = path + ` L ${pts[pts.length - 1][0]} ${pad.t + ih} L ${pad.l} ${pad.t + ih} Z`;
    const color = opts.color || getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#0d9488';
    let svg = `<svg viewBox="0 0 ${W} ${H}" style="width:100%;height:auto">
      <defs><linearGradient id="g${el.id || 'c'}" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0" stop-color="${color}" stop-opacity=".28"/><stop offset="1" stop-color="${color}" stop-opacity="0"/></linearGradient></defs>
      <path d="${area}" fill="url(#g${el.id || 'c'})"/>
      <path d="${path}" fill="none" stroke="${color}" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
      ${pts.map((p, i) => `<circle cx="${p[0]}" cy="${p[1]}" r="3" fill="${color}"><title>${escapeHtml(String(data[i].l || ''))}: ${data[i].v}</title></circle>`).join('')}
    </svg>`;
    el.innerHTML = svg;
  }
  window.chart = chart;

  function donut(el, pct, color) {
    if (!el) return;
    const c = color || getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#0d9488';
    const R = 34, C = 2 * Math.PI * R;
    el.innerHTML = `<svg viewBox="0 0 90 90" style="width:90px;height:90px">
      <circle cx="45" cy="45" r="${R}" fill="none" stroke="var(--bg-hover)" stroke-width="10"/>
      <circle cx="45" cy="45" r="${R}" fill="none" stroke="${c}" stroke-width="10" stroke-linecap="round"
        stroke-dasharray="${(pct / 100 * C).toFixed(1)} ${C.toFixed(1)}" transform="rotate(-90 45 45)"/>
      <text x="45" y="49" text-anchor="middle" font-size="16" font-weight="800" fill="var(--text)">${Math.round(pct)}%</text>
    </svg>`;
  }
  window.donut = donut;

  /* ---------------- EdunexChart API (labels/values) ---------------- */
  function cssVar(n) { return getComputedStyle(document.documentElement).getPropertyValue(n).trim() || '#0d9488'; }
  function chartWrap(el) {
    el.classList.add('chart-wrap');
    let tip = el.querySelector('.chart-tip');
    if (!tip) { tip = document.createElement('div'); tip.className = 'chart-tip'; el.appendChild(tip); }
    return tip;
  }
  function showTip(tip, x, y, html) { tip.innerHTML = html; tip.style.left = x + 'px'; tip.style.top = y + 'px'; tip.classList.add('on'); }
  function hideTip(tip) { if (tip) tip.classList.remove('on'); }
  function easeOut(p) { return 1 - Math.pow(1 - p, 3); }
  function animateDraw(path, dur) {
    let len = 0;
    try { len = path.getTotalLength(); } catch (e) { return; }
    path.style.strokeDasharray = len; path.style.strokeDashoffset = len;
    path.getBoundingClientRect();
    const t0 = performance.now();
    (function step(t) {
      const e = easeOut(Math.min((t - t0) / dur, 1));
      path.style.strokeDashoffset = len * (1 - e);
      if (t - t0 < dur) requestAnimationFrame(step);
    })(t0);
  }
  function animateRects(el, f, dur) {
    const t0 = performance.now();
    (function step(t) {
      const e = easeOut(Math.min((t - t0) / dur, 1));
      el.querySelectorAll('[data-anim]').forEach(n => {
        const attrs = f(+n.dataset.anim, e);
        for (const k in attrs) n.setAttribute(k, attrs[k]);
      });
      if (t - t0 < dur) requestAnimationFrame(step);
    })(t0);
  }
  function chartTipAt(svg, wrap, e) {
    const r = svg.getBoundingClientRect(), w = wrap.getBoundingClientRect();
    return { x: e.clientX - w.left, y: e.clientY - w.top, vx: (e.clientX - r.left) / r.width, vy: (e.clientY - r.top) / r.height };
  }

  window.EdunexChart = {
    line(el, { labels = [], values = [] } = {}, color, opts = {}) {
      if (!el) return;
      labels = labels || []; values = values || [];
      if (!values.length) { el.innerHTML = '<p class="muted small">No data yet</p>'; return; }
      const ma = (opts.ma || []).map(Number);
      const W = opts.width || 480, H = opts.height || 150;
      const pad = { l: 36, r: 12, t: 12, b: 26 };
      const max = Math.max(...values.map(Number), ...ma, 1);
      const iw = W - pad.l - pad.r, ih = H - pad.t - pad.b;
      const step = labels.length > 1 ? iw / (labels.length - 1) : iw;
      const pts = values.map((v, i) => [pad.l + i * step, pad.t + ih - ((Number(v) || 0) / max) * ih]);
      const path = pts.map((p, i) => (i ? 'L' : 'M') + p[0].toFixed(1) + ' ' + p[1].toFixed(1)).join(' ');
      const area = path + ` L ${pts[pts.length - 1][0]} ${pad.t + ih} L ${pad.l} ${pad.t + ih} Z`;
      const col = color || cssVar('--accent');
      const gid = (el.id || 'c') + '-' + Math.random().toString(36).slice(2, 6);
      let maPath = '';
      if (ma.length) maPath = ma.map((v, i) => { const x = pad.l + i * step, y = pad.t + ih - ((Number(v) || 0) / max) * ih; return (i ? 'L' : 'M') + x.toFixed(1) + ' ' + y.toFixed(1); }).join(' ');
      el.innerHTML = `<svg viewBox="0 0 ${W} ${H}" style="width:100%;height:auto">
        <defs><linearGradient id="g${gid}" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0" stop-color="${col}" stop-opacity=".28"/><stop offset="1" stop-color="${col}" stop-opacity="0"/></linearGradient></defs>
        <path d="${area}" fill="url(#g${gid})"/>
        <path class="chart-line" d="${path}" fill="none" stroke="${col}" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
        ${maPath ? `<path class="chart-ma" d="${maPath}" fill="none" stroke="${col}" stroke-width="1.5" stroke-dasharray="4 4" opacity=".55"/>` : ''}
        ${pts.map((p, i) => `<circle class="chart-point" data-i="${i}" cx="${p[0]}" cy="${p[1]}" r="3" fill="${col}"><title>${escapeHtml(String(labels[i] || ''))}: ${values[i]}</title></circle>`).join('')}
        ${labels.map((l, i) => i % 5 === 0 || i === labels.length - 1 ? `<text x="${pts[i][0]}" y="${H - 7}" font-size="8.5" fill="var(--text-faint)" text-anchor="middle">${escapeHtml(String(l).slice(5))}</text>` : '').join('')}
      </svg>`;
      chartWrap(el);
      const lineEl = el.querySelector('.chart-line');
      if (lineEl) animateDraw(lineEl, opts.dur || 700);
      const svg = el.querySelector('svg'), tip = el.querySelector('.chart-tip');
      svg.addEventListener('mousemove', e => {
        const t = chartTipAt(svg, el, e);
        const mx = t.vx * W;
        let bi = 0, bd = 1e9;
        pts.forEach((p, i) => { const d = Math.abs(p[0] - mx); if (d < bd) { bd = d; bi = i; } });
        el.querySelectorAll('.chart-point').forEach((c, i) => {
          c.setAttribute('r', i === bi ? '4.5' : '2.8');
          if (i === bi) c.style.stroke = cssVar('--bg');
        });
        showTip(tip, t.x, t.y - 34, `<b>${escapeHtml(String(labels[bi]))}</b> · ${values[bi]} enrollment${values[bi] == 1 ? '' : 's'}`);
      });
      svg.addEventListener('mouseleave', () => { hideTip(tip); el.querySelectorAll('.chart-point').forEach(c => { c.setAttribute('r', '3'); c.style.stroke = 'none'; }); });
    },
    donut(el, { labels = [], values = [] } = {}, color) {
      if (!el) return;
      labels = labels || []; values = values || [];
      const total = values.reduce((a, b) => a + (Number(b) || 0), 0);
      if (!total) { el.innerHTML = '<p class="muted small">No data yet</p>'; return; }
      const W = 90, R = 34, C = 2 * Math.PI * R;
      const palette = ['var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)', 'var(--chart-6)'];
      const segs = [];
      let acc = 0;
      values.forEach((v, i) => {
        const pct = ((Number(v) || 0) / total) * 100;
        if (pct <= 0) return;
        const len = (pct / 100) * C, off = (acc / 100) * C;
        const c = color || palette[i % palette.length];
        const stroke = /^var\(/.test(c) ? cssVar(c.slice(4, -1)) : c;
        segs.push({ i, len, off, stroke, pct });
        acc += pct;
      });
      chartWrap(el);
      el.innerHTML = `<svg viewBox="0 0 ${W} ${W}" style="width:110px;height:110px;display:block">
        <circle cx="45" cy="45" r="${R}" fill="none" stroke="var(--bg-hover)" stroke-width="12"/>
        ${segs.map(s => `<circle class="donut-seg" data-anim="${segs.indexOf(s)}" data-i="${s.i}" cx="45" cy="45" r="${R}" fill="none" stroke="${s.stroke}" stroke-width="12" stroke-dasharray="0 ${C.toFixed(1)}" stroke-dashoffset="${(-s.off).toFixed(1)}" transform="rotate(-90 45 45)"><title>${escapeHtml(String(labels[s.i] || ''))}: ${values[s.i]}</title></circle>`).join('')}
        <text x="45" y="46" text-anchor="middle" font-size="16" font-weight="800" fill="var(--text)">${total}</text>
        <text x="45" y="57" text-anchor="middle" font-size="7" fill="var(--text-faint)">students</text>
      </svg>`;
      chartWrap(el);
      animateRects(el, (i, e) => ({ 'stroke-dasharray': (segs[i].len * e).toFixed(1) + ' ' + C.toFixed(1) }), 750);
      const svg = el.querySelector('svg'), tip = el.querySelector('.chart-tip');
      svg.addEventListener('mousemove', e => {
        const seg = e.target.closest('.donut-seg');
        el.querySelectorAll('.donut-seg').forEach(s => s.setAttribute('stroke-width', seg && s === seg ? '15' : '12'));
        if (seg) {
          const d = segs.find(s => s.i == seg.dataset.i);
          const t = chartTipAt(svg, el, e);
          showTip(tip, t.x, t.y - 32, `<b>${escapeHtml(String(labels[d.i]))}</b> · ${values[d.i]} student${values[d.i] == 1 ? '' : 's'} · ${d.pct.toFixed(1)}%`);
        }
      });
      svg.addEventListener('mouseleave', () => { hideTip(tip); el.querySelectorAll('.donut-seg').forEach(s => s.setAttribute('stroke-width', '12')); });
    },
    bars(el, { labels = [], values = [] } = {}, color, opts = {}) {
      if (!el) return;
      labels = labels || []; values = values || [];
      const nums = values.map(Number);
      if (!nums.length || nums.every(v => !v)) { el.innerHTML = '<p class="muted small">No data yet</p>'; return; }
      const W = opts.width || 480, H = opts.height || 170;
      const pad = { l: 8, r: 8, t: 14, b: 28 };
      const max = Math.max(...nums, 1);
      const iw = W - pad.l - pad.r, ih = H - pad.t - pad.b;
      const n = nums.length, gap = iw / n, bw = Math.min(52, gap * 0.6);
      const palette = ['var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)', 'var(--chart-6)'];
      chartWrap(el);
      let bars = '';
      nums.forEach((v, i) => {
        const x = pad.l + i * gap + (gap - bw) / 2;
        const c = color || palette[i % palette.length];
        const stroke = /^var\(/.test(c) ? cssVar(c.slice(4, -1)) : c;
        bars += `<rect class="chart-bar" data-anim="${i}" data-i="${i}" x="${x.toFixed(1)}" y="${pad.t + ih}" width="${bw.toFixed(1)}" height="0" rx="6" fill="${stroke}"><title>${escapeHtml(String(labels[i] || ''))}: ${v}</title></rect>`;
        bars += `<text x="${(x + bw / 2).toFixed(1)}" y="${H - 8}" font-size="8.5" fill="var(--text-faint)" text-anchor="middle">${escapeHtml(String(labels[i]).slice(0, 14))}</text>`;
      });
      el.innerHTML = `<svg viewBox="0 0 ${W} ${H}" style="width:100%;height:auto">${bars}</svg>`;
      chartWrap(el);
      animateRects(el, (i, e) => { const h = (nums[i] / max) * ih * e; return { height: h.toFixed(1), y: (pad.t + ih - h).toFixed(1) }; }, 650);
      const svg = el.querySelector('svg'), tip = el.querySelector('.chart-tip');
      svg.addEventListener('mousemove', e => {
        const bar = e.target.closest('.chart-bar');
        el.querySelectorAll('.chart-bar').forEach(b => b.setAttribute('opacity', bar && b === bar ? '1' : '0.62'));
        if (bar) {
          const i = +bar.dataset.i;
          const t = chartTipAt(svg, el, e);
          showTip(tip, t.x, t.y - 32, `<b>${escapeHtml(String(labels[i]))}</b> · ${nums[i]} student${nums[i] == 1 ? '' : 's'}`);
        }
      });
      svg.addEventListener('mouseleave', () => { hideTip(tip); el.querySelectorAll('.chart-bar').forEach(b => b.setAttribute('opacity', '1')); });
    },
    multi(el, { labels = [], series = [] } = {}) {
      if (!el) return;
      labels = labels || []; series = series || [];
      const all = series.flatMap(s => s.values);
      if (!all.length) { el.innerHTML = '<p class="muted small">No data yet</p>'; return; }
      const max = Math.max(...all.map(Number), 1);
      const W = 480, H = 160, pad = { l: 30, r: 8, t: 10, b: 22 };
      const iw = W - pad.l - pad.r, ih = H - pad.t - pad.b;
      const step = labels.length > 1 ? iw / (labels.length - 1) : iw;
      const palette = ['var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)', 'var(--chart-6)'];
      const paths = series.map((s, si) => {
        const color = palette[si % palette.length];
        const isCssVar = /^var\(/.test(color);
        const stroke = isCssVar ? getComputedStyle(document.documentElement).getPropertyValue(color.slice(4, -1)).trim() || '#0d9488' : color;
        const pts = s.values.map((v, i) => [pad.l + i * step, pad.t + ih - (Number(v) || 0) / max * ih]);
        const path = pts.map((p, i) => (i ? 'L' : 'M') + p[0].toFixed(1) + ' ' + p[1].toFixed(1)).join(' ');
        return `<path d="${path}" fill="none" stroke="${stroke}" stroke-width="2.2" stroke-linecap="round"/>`;
      }).join('');
      el.innerHTML = `<svg viewBox="0 0 ${W} ${H}" style="width:100%;height:auto">${paths}</svg>`;
    },
    bar(el, { labels = [], values = [] } = {}, color) {
      if (!el) return;
      labels = labels || []; values = values || [];
      if (!values.length) { el.innerHTML = '<p class="muted small">No data yet</p>'; return; }
      const max = Math.max(...values.map(Number), 1);
      const W = 480, H = 160, pad = { l: 30, r: 8, t: 10, b: 26 };
      const iw = W - pad.l - pad.r, ih = H - pad.t - pad.b;
      const n = Math.max(labels.length, 1);
      const bw = Math.min(34, (iw / n) * 0.55);
      const step = iw / n;
      const isCssVar = /^var\(/.test(color || '');
      const stroke = isCssVar ? getComputedStyle(document.documentElement).getPropertyValue((color || '').slice(4, -1)).trim() || '#0d9488' : (color || '#0d9488');
      const bars = values.map((v, i) => {
        const h = Math.max(2, (Number(v) || 0) / max * ih);
        const x = pad.l + i * step + (step - bw) / 2;
        return `<rect x="${x.toFixed(1)}" y="${(pad.t + ih - h).toFixed(1)}" width="${bw.toFixed(1)}" height="${h.toFixed(1)}" rx="3" fill="${stroke}"><title>${escapeHtml(String(labels[i] || ''))}: ${v}</title></rect>`;
      }).join('');
      const ticks = labels.map((l, i) => {
        if (labels.length > 8 && i % Math.ceil(labels.length / 8)) return '';
        const x = pad.l + i * step + step / 2;
        return `<text x="${x.toFixed(1)}" y="${H - 8}" text-anchor="middle" font-size="8" fill="var(--text-muted)">${escapeHtml(String(l).slice(0, 10))}</text>`;
      }).join('');
      el.innerHTML = `<svg viewBox="0 0 ${W} ${H}" style="width:100%;height:auto">${bars}${ticks}</svg>`;
    },
  };

  /* ---------------- Auto-load charts ---------------- */
  document.addEventListener('DOMContentLoaded', () => {
    $$('[data-chart]').forEach(el => {
      const raw = el.dataset.chart;
      let data; try { data = JSON.parse(raw); } catch { return; }
      chart(el, data);
    });
    $$('[data-donut]').forEach(el => donut(el, parseFloat(el.dataset.donut)));
    $$('[data-theme-toggle]').forEach(el => { el.innerHTML = theme.current === 'dark' ? ico('sun') : ico('moon'); });
  });

  /* ---------------- Exam timer + autosave ---------------- */
  window.EdunexExam = {
    start(deadlineSec, attemptId, csrf) {
      const el = $('#exam-timer');
      if (!el) return;
      let remaining = deadlineSec;
      const tick = () => {
        if (remaining <= 0) { document.getElementById('exam-form')?.submit(); return; }
        remaining--;
        const h = String(Math.floor(remaining / 3600)).padStart(2, '0');
        const m = String(Math.floor((remaining % 3600) / 60)).padStart(2, '0');
        const s = String(remaining % 60).padStart(2, '0');
        el.textContent = h + ':' + m + ':' + s;
        if (remaining < 300) el.classList.add('warn');
      };
      tick();
      setInterval(tick, 1000);
      setInterval(() => this.autosave(attemptId, csrf), 15000);
    },
    autosave(attemptId, csrf) {
      const form = document.getElementById('exam-form');
      if (!form) return;
      const data = new FormData(form);
      data.set('autosave', '1');
      fetch(EDUNEX.API + '/api/exams/autosave', { method: 'POST', body: data, headers: { 'X-CSRF-Token': csrf } })
        .then(r => r.json()).then(d => { if (d.ok) { $('#autosave-status') && ($('#autosave-status').textContent = 'Saved ' + new Date().toLocaleTimeString()); } }).catch(() => {});
    },
    flag(questionId, btn, attemptId, csrf) {
      const form = document.getElementById('exam-form');
      const h = document.createElement('input'); h.type = 'hidden'; h.name = 'flag[]'; h.value = questionId;
      form.appendChild(h);
      btn.classList.toggle('flagged');
      const q = document.querySelector(`.qnav button[data-q="${questionId}"]`);
      if (q) q.classList.toggle('flagged');
      this.autosave(attemptId, csrf);
    }
  };

  /* ---------------- Global search ---------------- */
  let searchTimer = null;
  document.addEventListener('DOMContentLoaded', () => {
    const box = $('#global-search');
    if (!box) return;
    const panel = document.createElement('div');
    panel.className = 'dropdown-menu'; panel.style.cssText = 'position:absolute;left:0;right:0;top:calc(100%+8px);max-height:420px;overflow:auto;';
    box.parentElement.style.position = 'relative';
    box.parentElement.appendChild(panel);
    box.addEventListener('input', () => {
      clearTimeout(searchTimer);
      const q = box.value.trim();
      if (q.length < 2) { panel.style.visibility = 'hidden'; return; }
      searchTimer = setTimeout(async () => {
        try {
          const r = await fetch(EDUNEX.API + '/api/search?q=' + encodeURIComponent(q));
          const d = await r.json();
          panel.style.visibility = 'visible';
          panel.style.opacity = '1';
          panel.innerHTML = d.results.length
            ? d.results.slice(0, 12).map(it =>
                `<a class="dropdown-item" href="${EDUNEX.URL}/index.php?r=${it.route}"><span>${ico(it.icon)}</span><span><b>${escapeHtml(it.title)}</b><br><span class="faint small">${escapeHtml(it.type)}</span></span></a>`).join('')
            : '<div class="dropdown-head">No results</div>';
        } catch (err) {}
      }, 250);
    });
    document.addEventListener('click', e => { if (!box.parentElement.contains(e.target)) { panel.style.visibility = 'hidden'; } });
  });
})();

/* ================= Exam engine ================= */
(function () {
  let timerId = null, attemptId = 0, token = '', deadline = 0, autoSaveTimer = null;
  const saveStatus = () => {
    const el = document.getElementById('autosave-status');
    if (el) { el.textContent = '✓ Autosaved'; el.classList.add('badge-success'); }
  };
  const collect = () => {
    const data = { attempt_id: attemptId };
    document.querySelectorAll('#exam-form input[name], #exam-form textarea[name], #exam-form select[name]').forEach(f => {
      if (f.name === 'submit_exam' || f.name === '_token' || f.name.startsWith('m_left_')) return;
      if (f.type === 'radio') { if (f.checked) data[f.name] = f.value; return; }
      if (f.name.endsWith('[]')) {
        if (!data[f.name]) data[f.name] = [];
        if (f.value.trim() !== '') data[f.name].push(f.value);
        return;
      }
      data[f.name] = f.value;
    });
    return data;
  };
  const autosave = () => {
    fetch(EDUNEX.API + '/api/exams/autosave', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF': token },
      body: JSON.stringify(collect())
    }).then(r => r.json()).then(d => { if (d.ok) saveStatus(); }).catch(() => {});
  };
  window.EdunexExam = {
    start(secs, aid, tok) {
      attemptId = aid; token = tok; deadline = secs;
      const timer = document.getElementById('exam-timer');
      const tick = () => {
        if (deadline <= 0) { clearInterval(timerId); clearInterval(autoSaveTimer); timer.textContent = '00:00'; document.getElementById('exam-form').submit(); return; }
        const h = Math.floor(deadline / 3600), m = Math.floor((deadline % 3600) / 60), s = deadline % 60;
        timer.textContent = (h ? String(h).padStart(2, '0') + ':' : '') + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        if (deadline <= 300) timer.classList.add('timer-warning');
        deadline--;
      };
      tick(); timerId = setInterval(tick, 1000);
      autoSaveTimer = setInterval(autosave, 30000);
      window.addEventListener('beforeunload', e => { autosave(); });
    },
    flag(qid, btn, aid, tok) {
      fetch(EDUNEX.API + '/api/exams/flag', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF': tok },
        body: JSON.stringify({ attempt_id: aid, question_id: qid })
      }).then(r => r.json()).then(d => {
        btn.classList.toggle('flagged', d.flagged);
        const nav = document.querySelector(`.qnav button[data-q="${qid}"]`);
        if (nav) nav.classList.toggle('flagged', d.flagged);
      }).catch(() => {});
    }
  };

  /* ---------------- Notifications (navbar) ---------------- */
  window.EdunexNotif = {
    api(action, id) {
      return fetch(EDUNEX.API + '/api/notifications', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(action ? { action, id } : {})
      }).then(r => r.json()).catch(() => ({}));
    },
    _allCaughtUp() {
      const bell = document.getElementById('notif-bell');
      if (bell) { const dot = bell.querySelector('.dot'); if (dot) dot.style.display = 'none'; }
      const panel = document.querySelector('.notif-panel');
      if (!panel) return;
      panel.querySelectorAll('.notif-item').forEach(i => i.remove());
      const btn = panel.querySelector('.btn-ghost'); if (btn) btn.remove();
      if (panel.querySelector('.notif-empty-state')) return;
      const viewAll = panel.querySelector('.dropdown-item');
      const empty = document.createElement('div');
      empty.className = 'empty notif-empty-state';
      empty.style.cssText = 'padding:26px';
      empty.innerHTML = `<span class="empty-ico">${ico('bell-off')}</span>All caught up`;
      panel.insertBefore(empty, viewAll || null);
    },
    markRead(id, el) {
      this.api('mark', id);
      if (el) { el.classList.remove('unread'); el.style.opacity = .45; el.style.pointerEvents = 'none'; }
      if (!document.querySelector('.notif-item.unread')) this._allCaughtUp();
    },
    markAll() {
      this.api('markall').then(d => {
        if (d.ok) this._allCaughtUp();
      });
    }
  };
})();

/* ---------------- Users list (superadmin) ---------------- */
(function () {
  'use strict';

  const rootSel = '.list-root';
  const root = document.querySelector(rootSel);
  if (!root) return;

  const drawer = document.getElementById('item-drawer');
  const drawerBody = document.getElementById('drawer-body');
  const backdrop = document.getElementById('drawer-backdrop');

  const rows = () => [...document.querySelectorAll('.user-row')];
  const checked = () => rows().filter(r => {
    const c = r.querySelector('.row-chk');
    return c && !c.disabled && c.checked;
  });

  function refreshBulk() {
    const bar = document.getElementById('bulk-bar');
    const ids = document.getElementById('bulk-ids');
    const cnt = document.getElementById('bulk-count');
    const chkAll = document.getElementById('chk-all');
    if (!bar || !ids) return;
    const sel = checked();
    ids.value = sel.map(r => r.querySelector('.row-chk').value).join(',');
    cnt.textContent = sel.length + ' selected';
    bar.classList.toggle('show', sel.length > 0);
    const all = rows().filter(r => !r.querySelector('.row-chk')?.disabled);
    if (chkAll) {
      chkAll.checked = all.length > 0 && all.every(r => r.querySelector('.row-chk').checked);
      chkAll.indeterminate = all.length > 0 && sel.length > 0 && sel.length < all.length;
    }
  }

  function openDrawer(data) {
    const st = { active: ['badge-success', 'Active'], pending: ['badge-warning', 'Pending'], suspended: ['badge-danger', 'Suspended'], banned: ['badge-danger', 'Banned'] };
    const roleIco = { admin: ico('shield'), director: ico('graduation'), teacher: ico('graduation'), student: ico('users-card'), parent: ico('users'), guest: ico('user') };
    const [cls, lbl] = st[data.status] || ['badge-muted', data.status];
    const cap = (s) => s ? s[0].toUpperCase() + s.slice(1) : '—';
    const act = [];
    if (!data.protected) {
      if (data.status === 'active') {
        act.push(`<form method="post" class="inline">${window.EDUNEX_CSRF}<input type="hidden" name="set_status" value="${data.id}"><input type="hidden" name="new_status" value="suspended"><button class="drawer-action warn">${ico('pause')} Suspend</button></form>`);
      } else {
        act.push(`<form method="post" class="inline">${window.EDUNEX_CSRF}<input type="hidden" name="set_status" value="${data.id}"><input type="hidden" name="new_status" value="active"><button class="drawer-action ok">▶️ Activate</button></form>`);
      }
      act.push(`<form method="post" class="inline" data-confirm="Delete ${data.name}? All their data is removed.">${window.EDUNEX_CSRF}<input type="hidden" name="delete_user" value="${data.id}"><button class="drawer-action danger">${ico('trash')} Delete</button></form>`);
    }
    drawerBody.innerHTML = `
      <div class="drawer-profile">
        <div class="drawer-avatar">${data.initials || '?'}</div>
        <div class="min-0 flex-1">
          <b class="drawer-name">${data.name}</b>
          <p class="tiny faint ellipsis">${data.email}</p>
          <div class="flex gap-6" style="margin-top:7px">
            <span class="badge ${cls}">${lbl}</span>
            <span class="badge badge-muted">${roleIco[data.role] || ico('user')} ${cap(data.role)}</span>
          </div>
        </div>
      </div>
      <div class="drawer-stats">
        <div class="stat-box"><span class="tiny faint">${ico('bolt')} Level</span><b>${data.level}</b></div>
        <div class="stat-box"><span class="tiny faint">${ico('star')} XP</span><b>${data.xp}</b></div>
      </div>
      <div class="drawer-actions">
        <a class="drawer-action primary" href="${window.EDUNEX.URL}/index.php?r=admin/user&id=${data.id}">${ico('eye')} Full profile</a>
        ${act.join('')}
      </div>
      <div class="drawer-section">
        <h4>Account</h4>
        <div class="drawer-row"><span class="lbl">${ico('user')} Role</span><span class="val">${cap(data.role)}</span></div>
        <div class="drawer-row"><span class="lbl">🆔 Student ID</span><span class="val mono">${data.student_id || '—'}</span></div>
        <div class="drawer-row"><span class="lbl">${ico('calendar')} Joined</span><span class="val">${data.joined}</span></div>
      </div>
      <div class="drawer-section">
        <h4>School & class</h4>
        <div class="drawer-row"><span class="lbl">${ico('school')} School</span><span class="val">${data.school || '—'}</span></div>
        <div class="drawer-row"><span class="lbl">${ico('tag')} Class / Group</span><span class="val">${data.group || '—'}</span></div>
      </div>
      <div class="drawer-section">
        <h4>Contact</h4>
        <div class="drawer-row"><span class="lbl">${ico('mail')} Email</span><span class="val">${data.email}</span></div>
        <div class="drawer-row"><span class="lbl">${ico('phone')} Phone</span><span class="val">${data.phone || '—'}</span></div>
      </div>`;
    drawer.classList.add('open');
    backdrop.classList.add('show');
    drawerBody.querySelectorAll('[data-confirm]').forEach(f => f.addEventListener('submit', (e) => {
      if (!confirm(f.dataset.confirm)) e.preventDefault();
    }));
  }

  function closeDrawer() { drawer.classList.remove('open'); backdrop.classList.remove('show'); }

  /* --- AJAX swap: fetch partial and replace the dynamic region --- */
  async function swap(url, push = true) {
    const target = document.querySelector(rootSel);
    if (!target) return;
    target.classList.add('ajax-loading');
    try {
      const sep = url.includes('?') ? '&' : '?';
      const res = await fetch(url + sep + 'partial=1', { headers: { 'X-Requested-With': 'fetch' } });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const html = await res.text();
      target.innerHTML = html;
      closeDrawer();
      refreshBulk();
      if (push && location.href !== url) history.pushState({ users: true }, '', url);
      document.title = document.querySelector('#view-root h1') ? 'Users — Edunex' : document.title;
    } catch (err) {
      console.error('users swap failed', err);
      toast('Could not load users: ' + err.message, 'error');
    } finally {
      target.classList.remove('ajax-loading');
    }
  }

  /* Delegated events — survive partial swaps */
  document.addEventListener('click', (e) => {
    const link = e.target.closest('a.ajax-nav');
    if (link) {
      e.preventDefault();
      swap(link.getAttribute('href'));
      return;
    }
    const row = e.target.closest('.list-row');
    if (!row || e.target.closest('a, button, summary, label, input, form, details')) return;
    e.stopPropagation();
    if (row.dataset.drawerUrl) {
      drawerBody.innerHTML = '<div class="empty" style="padding:30px"><span class="empty-ico">' + ico('loader') + '</span>Loading…</div>';
      drawer.classList.add('open');
      backdrop.classList.add('show');
      fetch(row.dataset.drawerUrl, { headers: { 'X-Requested-With': 'fetch' } })
        .then(res => { if (!res.ok) throw new Error('HTTP ' + res.status); return res.text(); })
        .then(html => { drawerBody.innerHTML = html; })
        .catch(err => { drawerBody.innerHTML = '<div class="empty" style="padding:30px"><span class="empty-ico">' + ico('alert') + '</span>Could not load details<br><span class="tiny faint">' + escapeHtml(err.message) + '</span></div>'; });
    } else if (row.dataset.user) {
      openDrawer(JSON.parse(row.dataset.user));
    }
  });
  document.addEventListener('change', (e) => {
    if (e.target.id === 'chk-all') {
      rows().forEach(r => {
        const c = r.querySelector('.row-chk');
        if (c && !c.disabled) c.checked = e.target.checked;
      });
      refreshBulk();
    } else if (e.target.classList && e.target.classList.contains('row-chk')) {
      refreshBulk();
    }
  });
  document.addEventListener('submit', (e) => {
    const form = e.target;
    if (form.matches && form.matches('.ajax-nav')) {
      e.preventDefault();
      const fd = new FormData(form);
      const rv = fd.get('r');
      fd.delete('r');
      const qs = new URLSearchParams(fd).toString();
      const base = new URL(form.getAttribute('action') || location.href, location.href);
      let url = base.origin + base.pathname;
      const params = rv ? 'r=' + rv + (qs ? '&' + qs : '') : qs;
      url += params ? '?' + params : '';
      swap(url);
    }
  });
  window.addEventListener('popstate', () => swap(location.href, false));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeDrawer(); });

  /* Static bindings (elements outside the swapped region) */
  document.getElementById('drawer-close')?.addEventListener('click', closeDrawer);
  backdrop.addEventListener('click', closeDrawer);
  refreshBulk();
})();
