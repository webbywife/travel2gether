/* Travel2gether motion + interaction layer. No dependencies.
   Everything here is progressive: with JS off or motion reduced, content is
   visible and every control still works. */
(function () {
  var root = document.documentElement;
  var KEY = 't2g-motion';

  // --- Motion preference: OS setting, overridable by the footer toggle ---
  function stored() { try { return localStorage.getItem(KEY); } catch (e) { return null; } }
  function osReduce() { return window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches; }
  var pref = stored();
  if (pref === 'reduce' || pref === 'full') root.setAttribute('data-motion', pref);
  function reduced() {
    var m = root.getAttribute('data-motion');
    return m === 'reduce' || (m !== 'full' && osReduce());
  }

  function ready(fn) { document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', fn) : fn(); }

  ready(function () {
    // --- Footer toggle ---
    document.querySelectorAll('[data-motion-toggle]').forEach(function (btn) {
      function label() { btn.textContent = reduced() ? 'Motion: off' : 'Motion: on'; btn.setAttribute('aria-pressed', reduced() ? 'true' : 'false'); }
      label();
      btn.addEventListener('click', function () {
        var next = reduced() ? 'full' : 'reduce';
        root.setAttribute('data-motion', next);
        try { localStorage.setItem(KEY, next); } catch (e) {}
        label();
        if (next === 'reduce') showAll();
      });
    });

    // Inner pages: animate the page title too (skip whole-gradient titles; clipped text can't span split words).
    var h1 = document.querySelector('.page h1:not([data-split]):not(.gtext)');
    if (h1 && !h1.closest('.card')) h1.setAttribute('data-split', '');

    // --- Split headlines into words (text nodes only; inline elements move as one unit) ---
    var wi = 0;
    function wrap(node, parent) {
      var out = document.createDocumentFragment();
      if (node.nodeType === 3) {
        node.textContent.split(/(\s+)/).forEach(function (part) {
          if (!part) return;
          if (/^\s+$/.test(part)) { out.appendChild(document.createTextNode(' ')); return; }
          out.appendChild(unit(document.createTextNode(part)));
        });
      } else if (node.nodeType === 1) {
        out.appendChild(unit(node.cloneNode(true)));
      }
      return out;
    }
    function unit(content) {
      var w = document.createElement('span'), s = document.createElement('span');
      w.className = 'w'; s.style.setProperty('--i', wi++); s.appendChild(content); w.appendChild(s);
      return w;
    }
    document.querySelectorAll('[data-split]').forEach(function (el) {
      wi = 0;
      if (!el.getAttribute('aria-label')) el.setAttribute('aria-label', el.textContent.replace(/\s+/g, ' ').trim());
      var kids = Array.prototype.slice.call(el.childNodes), frag = document.createDocumentFragment();
      kids.forEach(function (k) { frag.appendChild(wrap(k)); });
      el.textContent = '';
      el.appendChild(frag);
      Array.prototype.forEach.call(el.children, function (c) { c.setAttribute('aria-hidden', 'true'); });
    });

    // --- Stagger indexes ---
    document.querySelectorAll('[data-stagger]').forEach(function (g) {
      Array.prototype.forEach.call(g.children, function (c, i) { c.style.setProperty('--i', i); });
    });

    // --- Reveal on scroll ---
    var sel = '.reveal, [data-split], [data-stagger], .unveil, [data-count]';
    var els = document.querySelectorAll(sel);
    function showAll() { els.forEach(function (e) { e.classList.add('is-visible'); if (e.hasAttribute('data-count')) countTo(e, true); }); }
    if (reduced() || !('IntersectionObserver' in window)) { showAll(); }
    else {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (!en.isIntersecting) return;
          en.target.classList.add('is-visible');
          if (en.target.hasAttribute('data-count')) countTo(en.target);
          io.unobserve(en.target);
        });
      }, { rootMargin: '0px 0px -10% 0px', threshold: 0.12 });
      els.forEach(function (e) { io.observe(e); });
      // Safety net: never leave content hidden.
      window.addEventListener('load', function () { setTimeout(showAll, 3000); });
    }

    // --- Count-up numbers: <span data-count="38" data-suffix="+"> ---
    function countTo(el, instant) {
      if (el.dataset.done) return; el.dataset.done = '1';
      var end = parseFloat(el.dataset.count) || 0, suf = el.dataset.suffix || '', pre = el.dataset.prefix || '';
      if (instant || reduced()) { el.textContent = pre + end.toLocaleString() + suf; return; }
      var t0 = null, dur = 1400;
      function step(t) {
        if (!t0) t0 = t;
        var p = Math.min(1, (t - t0) / dur), e = 1 - Math.pow(1 - p, 4);
        el.textContent = pre + Math.round(end * e).toLocaleString() + suf;
        if (p < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    }

    // --- Header: shadow once scrolled, hide on scroll down, show on scroll up ---
    var nav = document.querySelector('.navbar'), lastY = window.scrollY, ticking = false;
    var px = document.querySelectorAll('[data-parallax]');
    function onScroll() {
      var y = window.scrollY;
      if (nav) {
        nav.classList.toggle('is-scrolled', y > 8);
        var menuOpen = nav.querySelector('.site-nav.open');
        nav.classList.toggle('is-hidden', !menuOpen && y > 240 && y > lastY + 2);
        if (y < lastY - 2) nav.classList.remove('is-hidden');
      }
      lastY = y;
      if (!reduced()) px.forEach(function (el) {
        var r = el.getBoundingClientRect(), vh = window.innerHeight;
        if (r.bottom < -200 || r.top > vh + 200) return;
        var speed = parseFloat(el.dataset.parallax) || 0.15;
        var lim = r.height * 0.1, off = Math.max(-lim, Math.min(lim, (r.top + r.height / 2 - vh / 2) * -speed));
        var scale = el.dataset.scale ? 1 + Math.max(0, Math.min(1, 1 - r.top / vh)) * parseFloat(el.dataset.scale) : 1;
        el.style.transform = 'translate3d(0,' + off.toFixed(1) + 'px,0) scale(' + scale.toFixed(3) + ')';
      });
      ticking = false;
    }
    window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } }, { passive: true });
    onScroll();

    // --- Pointer spotlight on .spot cards ---
    if (window.matchMedia('(hover: hover)').matches) {
      document.addEventListener('pointermove', function (e) {
        var c = e.target.closest && e.target.closest('.spot');
        if (!c) return;
        var r = c.getBoundingClientRect();
        c.style.setProperty('--mx', (e.clientX - r.left) + 'px');
        c.style.setProperty('--my', (e.clientY - r.top) + 'px');
      }, { passive: true });
    }

    // --- Tabs with optional autoplay + progress bar ---
    // <div data-tabs data-autoplay="7000"> [role=tab][aria-controls] … [role=tabpanel] …
    document.querySelectorAll('[data-tabs]').forEach(function (box) {
      var tabs = Array.prototype.slice.call(box.querySelectorAll('[role="tab"]'));
      var ms = parseInt(box.dataset.autoplay, 10) || 0, timer = null, idx = 0, user = false;
      function select(i, focus) {
        idx = (i + tabs.length) % tabs.length;
        tabs.forEach(function (t, j) {
          var on = j === idx, p = document.getElementById(t.getAttribute('aria-controls'));
          t.setAttribute('aria-selected', on ? 'true' : 'false');
          t.tabIndex = on ? 0 : -1;
          t.classList.remove('run'); void t.offsetWidth;
          if (on && ms && !user && !reduced()) t.classList.add('run');
          if (p) { p.hidden = !on; p.classList.toggle('is-on', on); }
        });
        if (focus) tabs[idx].focus();
        schedule();
      }
      function schedule() {
        clearTimeout(timer);
        if (ms && !user && !reduced()) timer = setTimeout(function () { select(idx + 1); }, ms);
      }
      tabs.forEach(function (t, i) {
        t.style.setProperty('--dur', ms + 'ms');
        t.addEventListener('click', function () { user = true; select(i); });
        t.addEventListener('keydown', function (e) {
          if (e.key === 'ArrowRight' || e.key === 'ArrowDown') { e.preventDefault(); user = true; select(idx + 1, true); }
          if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') { e.preventDefault(); user = true; select(idx - 1, true); }
        });
      });
      // Pause autoplay while the pointer or focus is inside, or the panel is off-screen.
      box.addEventListener('mouseenter', function () { clearTimeout(timer); box.classList.add('is-paused'); });
      box.addEventListener('mouseleave', function () { box.classList.remove('is-paused'); schedule(); });
      var stop = box.querySelector('[data-tabs-pause]');
      if (stop) stop.addEventListener('click', function () { user = true; clearTimeout(timer); tabs.forEach(function (t) { t.classList.remove('run'); }); stop.hidden = true; });
      if ('IntersectionObserver' in window) new IntersectionObserver(function (en) {
        if (en[0].isIntersecting) schedule(); else clearTimeout(timer);
      }).observe(box);
      select(0);
    });

    // --- Marquee: duplicate the track once so it loops seamlessly ---
    document.querySelectorAll('.marquee').forEach(function (m) {
      var t = m.querySelector('.marquee-track');
      if (!t || m.dataset.cloned) return;
      var c = t.cloneNode(true); c.setAttribute('aria-hidden', 'true');
      c.querySelectorAll('a,button').forEach(function (a) { a.tabIndex = -1; });
      m.appendChild(c); m.dataset.cloned = '1';
      var pause = document.querySelector('[data-marquee-pause="' + m.id + '"]');
      if (pause) pause.addEventListener('click', function () {
        var p = m.classList.toggle('is-paused');
        pause.textContent = p ? 'Play' : 'Pause'; pause.setAttribute('aria-pressed', p ? 'true' : 'false');
      });
    });
  });
})();
