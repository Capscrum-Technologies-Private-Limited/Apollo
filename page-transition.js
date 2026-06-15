/* Apollo page transitions — smooth cream wipe between pages.
   The word "Apollo" is drawn by a single multicolour gradient line, then fills in.
   Loaded in each page's <helmet>. Self-registers once. */
(function () {
  if (window.__apolloPT) return;
  window.__apolloPT = true;

  var SVG_NS = 'http://www.w3.org/2000/svg';

  function buildOverlay() {
    var ov = document.createElement('div');
    ov.id = 'apollo-pt';
    ov.style.cssText = 'position:fixed;inset:0;z-index:1900;background:#fffaf0;opacity:1;pointer-events:none;transition:opacity .55s cubic-bezier(.16,1,.3,1);display:flex;align-items:center;justify-content:center;';
    ov.innerHTML =
      '<svg width="380" height="120" viewBox="0 0 460 140" fill="none" style="max-width:72vw;">' +
        '<defs><linearGradient id="apolloPtGrad" x1="30" y1="0" x2="430" y2="0" gradientUnits="userSpaceOnUse">' +
          '<stop offset="0" stop-color="#ff4d8b"></stop>' +
          '<stop offset="0.22" stop-color="#ff6b5a"></stop>' +
          '<stop offset="0.44" stop-color="#e8b94a"></stop>' +
          '<stop offset="0.64" stop-color="#a4d4c5"></stop>' +
          '<stop offset="0.82" stop-color="#1a3a3a"></stop>' +
          '<stop offset="1" stop-color="#b8a4ed"></stop>' +
        '</linearGradient></defs>' +
        '<text x="230" y="74" text-anchor="middle" dominant-baseline="central" ' +
          'font-family="Fredoka,-apple-system,sans-serif" font-weight="500" font-size="88" letter-spacing="2" ' +
          'fill="url(#apolloPtGrad)" fill-opacity="0" stroke="url(#apolloPtGrad)" stroke-width="1.4" ' +
          'stroke-dasharray="1700" stroke-dashoffset="1700">Apollo</text>' +
      '</svg>';
    ov._txt = ov.querySelector('text');
    return ov;
  }

  // Draw the line, then fill — pure requestAnimationFrame so it works without GSAP.
  function play(txt, drawMs, onDone) {
    if (!txt) { if (onDone) onDone(); return; }
    txt.setAttribute('stroke-dashoffset', '1700');
    txt.setAttribute('fill-opacity', '0');
    var start = performance.now();
    function frame(now) {
      var p = Math.min((now - start) / drawMs, 1);
      var e = 1 - Math.pow(1 - p, 2);                 // ease-out
      txt.setAttribute('stroke-dashoffset', String(1700 * (1 - e)));
      if (p > 0.7) txt.setAttribute('fill-opacity', String(Math.min((p - 0.7) / 0.3, 1)));
      if (p < 1) requestAnimationFrame(frame); else if (onDone) onDone();
    }
    requestAnimationFrame(frame);
  }

  function start() {
    var body = document.body || document.documentElement;
    var ov = buildOverlay();
    body.appendChild(ov);

    // Reveal: draw the word, then fade the cover out on load.
    play(ov._txt, 900);
    requestAnimationFrame(function () {
      requestAnimationFrame(function () { ov.style.opacity = '0'; });
    });
    setTimeout(function () { ov.style.display = 'none'; }, 700);

    // Intercept clicks on internal page links: wipe in, draw, then navigate.
    document.addEventListener('click', function (e) {
      if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      var a = e.target.closest ? e.target.closest('a') : null;
      if (!a) return;
      var href = a.getAttribute('href');
      if (!href || a.target === '_blank') return;
      if (!/\.dc\.html(\?|#|$)/.test(href)) return;           // only internal page links
      e.preventDefault();
      ov.style.display = 'flex';
      ov.offsetHeight; // reflow
      ov.style.pointerEvents = 'auto';
      ov.style.opacity = '1';
      play(ov._txt, 620);
      setTimeout(function () { window.location.href = href; }, 700);
    }, true);

    // Hide the cover if the user returns via the back/forward cache.
    window.addEventListener('pageshow', function (ev) {
      if (ev.persisted) { ov.style.display = 'none'; ov.style.opacity = '0'; ov.style.pointerEvents = 'none'; }
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
