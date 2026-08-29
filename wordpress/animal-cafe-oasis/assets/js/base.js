/* ==========================================================================
   あにまるカフェ Oasis — 共通の土台
   すべての機能ファイルの前に読み込みます。
   ・JS が動いていることを HTML に伝える
   ・「動きを減らす」設定を1か所で判定して共有する
   ・各機能の起動タイミング（画面ができたら）をまとめる
   ========================================================================== */
window.Oasis = window.Oasis || {};

(function () {
  'use strict';

  var Oasis = window.Oasis;

  // JS が動いていることを示す（CSS 側の保険を解除する）
  document.documentElement.classList.remove('no-js');

  // 端末の「視差効果を減らす」設定。動きを控えるかどうかの判断に使います。
  Oasis.reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  var queue = [];
  var started = false;

  /**
   * 画面ができたときに動かしたい処理を登録します。
   * 各機能ファイルは Oasis.ready(function () { ... }) と書くだけで済みます。
   * ひとつの機能で例外が出ても、ほかの機能は止まりません。
   */
  Oasis.ready = function (fn) {
    if (started) { run(fn); return; }
    queue.push(fn);
  };

  function run(fn) {
    try {
      fn();
    } catch (e) {
      if (window.console && window.console.error) { window.console.error(e); }
    }
  }

  function start() {
    started = true;
    queue.forEach(run);
    queue.length = 0;
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
