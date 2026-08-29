/* ==========================================================================
   どうぶつ個別ページの写真ギャラリー
   サムネイルを押すと、上のメイン写真が入れ替わります。
   JS が動かない環境でも、サムネイル自体が写真として見えるので情報は失われません。
   ========================================================================== */
(function () {
  'use strict';

  window.Oasis.ready(function () {
    var strips = document.querySelectorAll('[data-gallery]');

    Array.prototype.forEach.call(strips, function (strip) {
      var main = document.querySelector(strip.getAttribute('data-gallery-target'));
      if (!main) { return; }
      var buttons = strip.querySelectorAll('button[data-photo]');

      Array.prototype.forEach.call(buttons, function (btn) {
        btn.addEventListener('click', function () {
          var src = btn.getAttribute('data-photo');
          if (main.getAttribute('src') === src) { return; }

          // 最初の写真は srcset で画面幅ごとに出し分けている。
          // 差し替え後は単一の写真になるので、srcset を外してから src を入れる。
          main.removeAttribute('srcset');
          main.removeAttribute('sizes');
          main.setAttribute('src', src);
          main.setAttribute('alt', btn.getAttribute('data-alt') || '');

          Array.prototype.forEach.call(buttons, function (b) {
            b.setAttribute('aria-current', b === btn ? 'true' : 'false');
          });

          // 切り替わったことが伝わるように、ふわっと出し直す
          main.classList.remove('is-swapped');
          void main.offsetWidth;
          main.classList.add('is-swapped');
        });
      });
    });
  });
})();
