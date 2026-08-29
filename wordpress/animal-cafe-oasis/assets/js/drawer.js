/* ==========================================================================
   スマホメニュー（ドロワー）
   ハンバーガーボタンで開閉します。
   ========================================================================== */
(function () {
  'use strict';

  window.Oasis.ready(function () {
    var toggle = document.querySelector('.nav-toggle');
    var drawer = document.getElementById('drawer');
    if (!toggle || !drawer) return;

    var panel = drawer.querySelector('.drawer__panel');
    var scrim = drawer.querySelector('.drawer__scrim');
    var closeBtn = drawer.querySelector('.drawer__close');

    function open() {
      drawer.classList.add('is-open');
      toggle.setAttribute('aria-expanded', 'true');
      document.body.classList.add('is-locked');
      if (closeBtn) closeBtn.focus();
    }

    function close() {
      drawer.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
      document.body.classList.remove('is-locked');
      toggle.focus();
    }

    toggle.addEventListener('click', function () {
      if (drawer.classList.contains('is-open')) { close(); } else { open(); }
    });

    if (scrim) scrim.addEventListener('click', close);
    if (closeBtn) closeBtn.addEventListener('click', close);

    // メニュー内のリンクを押したら閉じる
    drawer.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', close);
    });

    // Esc キーで閉じる
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && drawer.classList.contains('is-open')) close();
    });

    // 開いている間はドロワーの外にフォーカスが出ないようにする
    if (panel) {
      document.addEventListener('focusin', function (e) {
        if (!drawer.classList.contains('is-open')) return;
        if (!panel.contains(e.target) && e.target !== toggle) {
          panel.querySelector('a, button').focus();
        }
      });
    }

    // PC 幅に戻ったら閉じておく
    window.addEventListener('resize', function () {
      if (window.innerWidth > 1024 && drawer.classList.contains('is-open')) close();
    });
  });
})();
