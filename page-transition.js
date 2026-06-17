/* Apollo page transitions — smooth cursor-ripple clip-path wipe between pages.
   The page transition morphs from a circle at the cursor click position.
   Loaded in each page's <helmet>. Self-registers once. */
(function () {
  if (window.__apolloPT) return;
  window.__apolloPT = true;

  var SVG_NS = 'http://www.w3.org/2000/svg';

  function buildOverlay() {
    var ov = document.createElement('div');
    ov.id = 'apollo-pt';
    // Use transition for clip-path with a custom bezier curve for high premium feel
    ov.style.cssText = 'position:fixed;inset:0;z-index:1900;background:#fffaf0;pointer-events:none;' +
                       'transition:clip-path .75s cubic-bezier(.86,0,.07,1);display:flex;align-items:center;justify-content:center;';
    ov.innerHTML =
      '<svg width="380" height="120" viewBox="0 0 460 140" fill="none" style="max-width:72vw;z-index:2;position:relative;">' +
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

    // Retrieve previous click position or default to center
    var clickX = sessionStorage.getItem('apollo-click-x') || (window.innerWidth / 2);
    var clickY = sessionStorage.getItem('apollo-click-y') || (window.innerHeight / 2);

    // Initial state: cover the screen, then reveal the new page by shrinking the circle to the click position
    ov.style.clipPath = 'circle(150% at ' + clickX + 'px ' + clickY + 'px)';
    
    // Draw the word as it reveals, then wipe cover out
    play(ov._txt, 800);
    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        ov.style.clipPath = 'circle(0% at ' + clickX + 'px ' + clickY + 'px)';
      });
    });

    // Clean up coordinates after page reveal
    sessionStorage.removeItem('apollo-click-x');
    sessionStorage.removeItem('apollo-click-y');

    // Intercept clicks on internal page links: ripple wipe in, draw, then navigate.
    document.addEventListener('click', function (e) {
      if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      var a = e.target.closest ? e.target.closest('a') : null;
      if (!a) return;
      var href = a.getAttribute('href');
      if (!href || a.target === '_blank') return;
      
      // Determine if it is a local internal HTML link (index.html, about.html, career.html, etc.)
      var isInternal = href && !/^https?:\/\//i.test(href) && !href.startsWith('#') && !href.startsWith('tel:') && !href.startsWith('mailto:');
      if (!isInternal) return;

      e.preventDefault();

      // Store click coordinates for the next page load
      var x = e.clientX;
      var y = e.clientY;
      sessionStorage.setItem('apollo-click-x', String(x));
      sessionStorage.setItem('apollo-click-y', String(y));

      // Reset overlay to circle(0% at click position)
      ov.style.clipPath = 'circle(0% at ' + x + 'px ' + y + 'px)';
      ov.style.pointerEvents = 'auto';

      // Reflow and trigger transition to circle(150% at click position)
      ov.offsetHeight;
      ov.style.clipPath = 'circle(150% at ' + x + 'px ' + y + 'px)';

      play(ov._txt, 620);
      setTimeout(function () { window.location.href = href; }, 750);
    }, true);

    // Hide the cover if the user returns via the back/forward cache.
    window.addEventListener('pageshow', function (ev) {
      if (ev.persisted) {
        ov.style.clipPath = 'circle(0% at ' + (window.innerWidth / 2) + 'px ' + (window.innerHeight / 2) + 'px)';
        ov.style.pointerEvents = 'none';
      }
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
