/* ==========================================================================
   ヘッダー：少しスクロールしたら引き締める
   ========================================================================== */
(function () {
  'use strict';

  window.Oasis.ready(function () {
    var header = document.querySelector('.site-header');
    if (!header) return;

    var ticking = false;

    function update() {
      header.classList.toggle('is-scrolled', window.scrollY > 24);
      ticking = false;
    }

    window.addEventListener('scroll', function () {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(update);
    }, { passive: true });

    update();
  });
})();
