/* ==========================================================================
   絞り込みチップ（どうぶつ一覧・お知らせ）
   チップの data-filter と、カード側の data-cat を突き合わせます。
   ========================================================================== */
(function () {
  'use strict';

  window.Oasis.ready(function () {
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
          if (show && !window.Oasis.reduceMotion) {
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
  });
})();
