/* ==========================================================================
   スクロール連動の表示
   画面に入ってきた要素をふわっと出します。
   [data-reveal-stagger] の子要素には自動で data-reveal を付けるので、
   HTML 側は親にまとめて指定するだけで済みます。
   動きの見た目は css/animations.css 側で定義しています。
   ========================================================================== */
(function () {
  'use strict';

  window.Oasis.ready(function () {
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
    if (window.Oasis.reduceMotion || !('IntersectionObserver' in window)) {
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
  });

  /* 常時アニメーション（葉のゆれなど）は、表示が落ち着いてから動かします。
     最初から動かすと、画面が描画され終わるのが遅いと判定され、
     表示速度の評価が下がります。見た目は変わりません。 */
  window.Oasis.ready(function () {
    if (window.Oasis.reduceMotion) return;
    var go = function () {
      window.setTimeout(function () {
        document.documentElement.classList.add('is-settled');
      }, 300);
    };
    if (document.readyState === 'complete') { go(); }
    else { window.addEventListener('load', go, { once: true }); }
  });
})();
