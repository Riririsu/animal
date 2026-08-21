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
          // data-cat は空白区切りで複数持てる（例：data-cat="reptile bird"）
          var cat = (item.getAttribute('data-cat') || '').split(/\s+/);
          var show = (value === 'all' || cat.indexOf(value) !== -1);

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
     5. 営業状況の自動表示
     曜日と時刻から「営業中／定休日／営業時間外」を判定して書き換えます。
     data-open-status を付けた要素がすべて対象です。

     ▼ 営業時間を変えるとき・臨時休業を入れるときは、この設定だけ直してください。
     ------------------------------------------------------------------------ */
  /* WordPress 版では、管理画面の「Oasis サイト設定 → 営業時間」で設定した値が
     OASIS_HOURS として渡ってきます。渡ってこない場合は下の既定値を使います。 */
  var HOURS = (typeof OASIS_HOURS !== 'undefined' && OASIS_HOURS) ? {
    open:           OASIS_HOURS.open           || '11:00',
    close:          OASIS_HOURS.close          || '19:00',
    closedDays:     OASIS_HOURS.closedDays     || [],
    closedDayLabel: OASIS_HOURS.closedDayLabel || '',
    holidays:       OASIS_HOURS.holidays       || []
  } : {
    open: '11:00',          // 開店時刻
    close: '19:00',         // 閉店時刻
    closedDays: [2],        // 定休日（0=日 1=月 2=火 3=水 4=木 5=金 6=土）
    closedDayLabel: '火曜',
    // 臨時休業日。'2026-08-20' の形式で追加してください（複数可）
    holidays: []
  };

  function setupOpenStatus() {
    var targets = document.querySelectorAll('[data-open-status]');
    if (!targets.length) return;

    function toMinutes(hhmm) {
      var p = hhmm.split(':');
      return parseInt(p[0], 10) * 60 + parseInt(p[1], 10);
    }

    function currentStatus() {
      var now = new Date();
      var ymd = now.getFullYear() + '-' +
                String(now.getMonth() + 1).padStart(2, '0') + '-' +
                String(now.getDate()).padStart(2, '0');

      if (HOURS.holidays.indexOf(ymd) !== -1) {
        return { open: false, text: '本日 臨時休業' };
      }
      if (HOURS.closedDays.indexOf(now.getDay()) !== -1) {
        return { open: false, text: '本日 定休日（' + HOURS.closedDayLabel + '）' };
      }

      var mins = now.getHours() * 60 + now.getMinutes();
      if (mins < toMinutes(HOURS.open)) {
        return { open: false, text: '本日 ' + HOURS.open + ' 開店' };
      }
      if (mins >= toMinutes(HOURS.close)) {
        return { open: false, text: '本日の営業は終了しました' };
      }
      return { open: true, text: '本日 営業中 ' + HOURS.open + ' – ' + HOURS.close };
    }

    var s = currentStatus();
    targets.forEach(function (el) {
      el.textContent = s.text;
      // ヒーローのピル表示
      el.classList.toggle('hero__fact--closed', el.classList.contains('hero__fact') && !s.open);
      // 来店の流れセクションの表示
      el.classList.toggle('is-closed', !s.open);
    });
  }

  /* ------------------------------------------------------------------------
     6. 常時アニメーションは、表示が落ち着いてから動かす
     葉のゆれなどを最初から動かすと、画面が描画され終わるのが遅いと
     判定され、表示速度の評価が下がります。見た目は変わりません。
     ------------------------------------------------------------------------ */
  function startAmbientMotion() {
    if (reduceMotion) return;
    var go = function () {
      window.setTimeout(function () {
        document.documentElement.classList.add('is-settled');
      }, 300);
    };
    if (document.readyState === 'complete') { go(); }
    else { window.addEventListener('load', go, { once: true }); }
  }

  /* ------------------------------------------------------------------------
     どうぶつ個別ページの写真ギャラリー
     サムネイルを押すと、上のメイン写真が入れ替わります。
     JS が動かない環境でも、サムネイル自体が写真として見えるので情報は失われません。
     ------------------------------------------------------------------------ */
  function setupGallery() {
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
  }

  /* ------------------------------------------------------------------------
     起動
     ------------------------------------------------------------------------ */
  function init() {
    setupReveal();
    setupHeader();
    setupDrawer();
    setupFilters();
    setupOpenStatus();
    setupGallery();
    startAmbientMotion();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
