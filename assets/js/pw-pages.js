/* preworks SEOページ用スクリプト */

/* LINEボタンのクリックを GTM（dataLayer）へ送る：イベント名 line_click
   GA4側で「line_click」をキーイベントに設定する。パラメータ：page_path / cta_position */
(function () {
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href*="sub.preworks.jp/line"]');
    if (!a) return;
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      event: 'line_click',
      page_path: location.pathname,
      cta_position: a.getAttribute('data-pw-cta') || (a.closest('.hdr') ? 'header' : a.closest('.finalcta') ? 'final' : 'body')
    });
  }, true);
})();

/* 向いている人診断（/check/）。設問・判定はページ側の data 属性で定義 */
(function () {
  var form = document.getElementById('pw-quiz');
  if (!form) return;
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var qs = form.querySelectorAll('.pw-quiz-q');
    var score = 0, answered = 0;
    qs.forEach(function (q) {
      var c = q.querySelector('input:checked');
      if (c) { answered++; score += parseInt(c.value, 10) || 0; }
    });
    var msg = form.querySelector('.pw-quiz-msg');
    if (answered < qs.length) {
      if (msg) msg.textContent = 'まだ回答していない質問があります（' + answered + '／' + qs.length + '問）';
      return;
    }
    if (msg) msg.textContent = '';
    var results = document.querySelectorAll('.pw-quiz-result');
    var shown = null;
    results.forEach(function (r) {
      var min = parseInt(r.getAttribute('data-min'), 10), max = parseInt(r.getAttribute('data-max'), 10);
      var hit = score >= min && score <= max;
      r.hidden = !hit;
      if (hit) shown = r;
    });
    var sc = document.getElementById('pw-quiz-score');
    if (sc) sc.textContent = score;
    document.querySelectorAll('.pw-quiz-score').forEach(function (el) { el.textContent = score; });
    if (shown) {
      shown.scrollIntoView({ behavior: 'smooth', block: 'start' });
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({ event: 'check_complete', check_score: score, check_result: shown.getAttribute('data-type') || '' });
    }
  });
})();

/* 表がはみ出しているときだけ「横にスクロール」を表示 */
(function () {
  var t = document.querySelectorAll('.pw-table');
  if (!t.length) return;
  function sync() {
    t.forEach(function (w) {
      var i = w.querySelector('.pw-table-in'), h = w.querySelector('.pw-table-hint');
      if (i && h) h.hidden = i.scrollWidth <= i.clientWidth + 16;
    });
  }
  sync(); window.addEventListener('load', sync); window.addEventListener('resize', sync);
})();
