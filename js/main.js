/* ==========================================================================
   あにまるカフェ Oasis — 画面の動き
   ・スクロール連動の表示
   ・ヘッダーの縮小
   ・スマホメニュー（ドロワー）
   ・絞り込みチップ
   動きの見た目は css/animations.css 側で定義しています。
   ========================================================================== */
(function () {
  'use strict';

  // JS が動いていることを示す（CSS 側の保険を解除する）
  document.documentElement.classList.remove('no-js');

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ------------------------------------------------------------------------
     1. スクロール連動の表示
     [data-reveal-stagger] の子要素には自動で data-reveal を付けるので、
     HTML 側は親にまとめて指定するだけで済みます。
     ------------------------------------------------------------------------ */
  function setupReveal() {
    // ずらし表示のグループの子に data-reveal を配る
    document.querySelectorAll('[data-reveal-stagger]').forEach(function (group) {
      var type = group.getAttribute('data-reveal-stagger') || 'up';
      Array.prototype.forEach.call(group.children, function (child) {
        if (!child.hasAttribute('data-reveal')) {
          child.setAttribute('data-reveal', type);
        }
      });
    });

    var targets = document.querySelectorAll('[data-reveal]');
    if (!targets.length) return;

    // 動きを減らす設定・非対応ブラウザでは、そのまま表示する
    if (reduceMotion || !('IntersectionObserver' in window)) {
      targets.forEach(function (el) { el.classList.add('is-visible'); });
      return;
    }

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target); // 一度出したら監視をやめる
      });
    }, {
      // 画面の下から少し入ったところで発火させる
      rootMargin: '0px 0px -12% 0px',
      threshold: 0.08
    });

    targets.forEach(function (el) { observer.observe(el); });
  }

  /* ------------------------------------------------------------------------
     2. ヘッダー：少しスクロールしたら引き締める
     ------------------------------------------------------------------------ */
  function setupHeader() {
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
  }

  /* ------------------------------------------------------------------------
     3. スマホメニュー（ドロワー）
     ------------------------------------------------------------------------ */
  function setupDrawer() {
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
  }

  /* ------------------------------------------------------------------------
     4. 絞り込みチップ（どうぶつ一覧・お知らせ）
     チップの data-filter と、カード側の data-cat を突き合わせます。
     ------------------------------------------------------------------------ */
  function setupFilters() {
    document.querySelectorAll('[data-filter-group]').forEach(function (group) {
      var targetSel = group.getAttribute('data-filter-target');
      var list = targetSel ? document.querySelector(targetSel) : null;
      if (!list) return;

      var chips = group.querySelectorAll('[data-filter]');
      var items = list.children;

      group.addEventListener('click', function (e) {
        var chip = e.target.closest('[data-filter]');
        if (!chip || !group.contains(chip)) return;

        var value = chip.getAttribute('data-filter');

        chips.forEach(function (c) {
          c.setAttribute('aria-pressed', String(c === chip));
        });

        Array.prototype.forEach.call(items, function (item, i) {
          var cat = item.getAttribute('data-cat');
          var show = (value === 'all' || cat === value);

          item.hidden = !show;

          // 表示されるものだけ、順番にもう一度出し直す
          if (show && !reduceMotion) {
            item.classList.remove('is-visible');
            item.style.setProperty('--reveal-delay', (i * 0.05) + 's');
            // 一度描画を挟んでから付け直すことでアニメーションを再生させる
            void item.offsetWidth;
            item.classList.add('is-visible');
          } else if (show) {
            item.classList.add('is-visible');
          }
        });
      });
    });
  }

  /* ------------------------------------------------------------------------
     起動
     ------------------------------------------------------------------------ */
  function init() {
    setupReveal();
    setupHeader();
    setupDrawer();
    setupFilters();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
