/* ==========================================================================
   管理画面「Oasis メニュー」の行の追加・削除・並べ替え
   ========================================================================== */
(function () {
  'use strict';

  var form = document.getElementById('oasis-menu-form');
  if (!form) { return; }

  var groupsBox = document.getElementById('oasis-menu-groups');
  var groupTpl  = document.getElementById('oasis-menu-group-tpl');
  var rowTpl    = document.getElementById('oasis-menu-row-tpl');

  /** name="oasis_menu[groups][0][items][2][name]" の番号を振り直す。 */
  function renumber() {
    var groups = groupsBox.querySelectorAll('.oasis-menu-group');
    Array.prototype.forEach.call(groups, function (g, gi) {
      g.setAttribute('data-gi', gi);
      var rows = g.querySelectorAll('.oasis-menu-row');
      Array.prototype.forEach.call(g.querySelectorAll('input[name]'), function (input) {
        input.name = input.name.replace(/\[groups\]\[[^\]]*\]/, '[groups][' + gi + ']');
      });
      Array.prototype.forEach.call(rows, function (row, ri) {
        Array.prototype.forEach.call(row.querySelectorAll('input[name]'), function (input) {
          input.name = input.name.replace(/\[items\]\[[^\]]*\]/, '[items][' + ri + ']');
        });
      });
    });
  }

  function html(tpl) {
    var wrap = document.createElement('div');
    wrap.innerHTML = tpl.innerHTML.trim();
    return wrap.firstElementChild;
  }

  // 区分を追加
  document.getElementById('oasis-menu-add-group').addEventListener('click', function () {
    var g = html(groupTpl);
    groupsBox.appendChild(g);
    renumber();
    var first = g.querySelector('input');
    if (first) { first.focus(); }
  });

  groupsBox.addEventListener('click', function (e) {
    var btn = e.target.closest('button');
    if (!btn) { return; }
    var group = btn.closest('.oasis-menu-group');

    // 品目を追加
    if (btn.classList.contains('oasis-menu-add-row')) {
      var wrap = document.createElement('table');
      wrap.innerHTML = '<tbody>' + rowTpl.innerHTML.trim() + '</tbody>';
      var row = wrap.querySelector('tr');
      group.querySelector('.oasis-menu-rows tbody').appendChild(row);
      renumber();
      row.querySelector('input').focus();
      return;
    }

    // 品目を削除
    if (btn.classList.contains('oasis-menu-del-row')) {
      btn.closest('.oasis-menu-row').remove();
      renumber();
      return;
    }

    // 品目を上下に動かす
    if (btn.classList.contains('oasis-menu-move-row')) {
      var r = btn.closest('.oasis-menu-row');
      var to = btn.getAttribute('data-dir') === 'up' ? r.previousElementSibling : r.nextElementSibling;
      if (to) {
        if (btn.getAttribute('data-dir') === 'up') { r.parentNode.insertBefore(r, to); }
        else { r.parentNode.insertBefore(to, r); }
        renumber();
      }
      return;
    }

    // 区分を削除
    if (btn.classList.contains('oasis-menu-del-group')) {
      var name = (group.querySelector('input') || {}).value || 'この区分';
      if (window.confirm('「' + name + '」を、中の品目ごと削除します。よろしいですか？')) {
        group.remove();
        renumber();
      }
      return;
    }

    // 区分を上下に動かす
    if (btn.classList.contains('oasis-menu-move')) {
      var t = btn.getAttribute('data-dir') === 'up' ? group.previousElementSibling : group.nextElementSibling;
      if (t) {
        if (btn.getAttribute('data-dir') === 'up') { group.parentNode.insertBefore(group, t); }
        else { group.parentNode.insertBefore(t, group); }
        renumber();
      }
    }
  });
})();
