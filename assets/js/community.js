(function () {
  // Keeps the staff view of World Chat up to date.
  var log = document.getElementById('monitor-log');
  if (!log) return;
  var last = log.innerHTML;
  function atBottom() { return log.scrollHeight - log.scrollTop - log.clientHeight < 40; }
  function toBottom() { log.scrollTop = log.scrollHeight; }
  function poll() {
    if (document.hidden || log.offsetParent === null) return;   // tab hidden or World Chat tab not showing
    fetch(log.dataset.src, { credentials: 'same-origin' }).then(function (r) { return r.text(); }).then(function (html) {
      if (html === last) return;
      var stick = atBottom(); last = html; log.innerHTML = html; if (stick) toBottom();
    }).catch(function () {});
  }
  toBottom();
  document.querySelectorAll('[role=tab]').forEach(function (t) { t.addEventListener('click', function () { setTimeout(toBottom, 0); }); });
  setInterval(poll, 5000);
})();
