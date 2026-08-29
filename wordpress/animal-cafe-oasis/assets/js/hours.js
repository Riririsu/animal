/* ==========================================================================
   営業状況の自動表示
   曜日と時刻から「営業中／定休日／営業時間外」を判定して書き換えます。
   data-open-status を付けた要素がすべて対象です。

   ▼ 営業時間を変えるとき・臨時休業を入れるときは、下の HOURS だけ直してください。
     （WordPress 版では管理画面「Oasis サイト設定」の値が優先されます）
   ========================================================================== */
(function () {
  'use strict';

  var DEFAULTS = {
    open: '11:00',          // 開店時刻
    close: '19:00',         // 閉店時刻
    closedDays: [2],        // 定休日（0=日 1=月 2=火 3=水 4=木 5=金 6=土）
    closedDayLabel: '火曜',
    // 臨時休業日。'2026-08-20' の形式で追加してください（複数可）
    holidays: []
  };

  // WordPress では管理画面の設定が OASIS_HOURS として渡ってきます。
  // 渡ってこない場合（静的サイト）は上の既定値を使います。
  var src = (typeof window.OASIS_HOURS !== 'undefined' && window.OASIS_HOURS) ? window.OASIS_HOURS : {};
  var HOURS = {
    open:           src.open           || DEFAULTS.open,
    close:          src.close          || DEFAULTS.close,
    closedDays:     src.closedDays     || DEFAULTS.closedDays,
    closedDayLabel: src.closedDayLabel || DEFAULTS.closedDayLabel,
    holidays:       src.holidays       || DEFAULTS.holidays
  };

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

  window.Oasis.ready(function () {
    var targets = document.querySelectorAll('[data-open-status]');
    if (!targets.length) return;

    var s = currentStatus();
    targets.forEach(function (el) {
      el.textContent = s.text;
      // ヒーローのピル表示
      el.classList.toggle('hero__fact--closed', el.classList.contains('hero__fact') && !s.open);
      // 来店の流れセクションの表示
      el.classList.toggle('is-closed', !s.open);
    });
  });
})();
