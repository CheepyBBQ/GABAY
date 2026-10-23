(function () {
  var log = document.getElementById('chat-log'), form = document.getElementById('chat-form');
  if (!log || !form) return;
  var input = document.getElementById('chat-input'), err = document.getElementById('chat-error'),
      api = form.dataset.api, sig = '', timer = null;

  function show(msg) { err.textContent = msg; err.hidden = !msg; }

  function render(list) {
    var s = list.map(function (m) { return m.id; }).join(',');
    if (s === sig) return;
    sig = s;
    var atBottom = log.scrollHeight - log.scrollTop - log.clientHeight < 40;
    log.textContent = '';
    if (!list.length) {
      var p = document.createElement('p'); p.className = 'empty'; p.textContent = 'No messages yet. Say hello.'; log.appendChild(p);
    }
    list.forEach(function (m) {
      var row = document.createElement('div'); row.className = 'chat-msg' + (m.mine ? ' mine' : '');
      var head = document.createElement('div'); head.className = 'chat-head';
      var who = document.createElement('b'); who.textContent = m.alias + (m.mine ? ' (you)' : '');
      var t = document.createElement('span'); t.className = 'muted small'; t.textContent = m.time;
      head.appendChild(who); head.appendChild(t);
      if (!m.mine) {
        var r = document.createElement('button'); r.type = 'button'; r.className = 'chat-report';
        r.textContent = 'Report'; r.setAttribute('aria-label', 'Report this message from ' + m.alias);
        r.addEventListener('click', function () { report(m.id, r); });
        head.appendChild(r);
      }
      var body = document.createElement('div'); body.className = 'chat-body'; body.textContent = m.body;
      row.appendChild(head); row.appendChild(body); log.appendChild(row);
    });
    if (atBottom || sig === s) log.scrollTop = log.scrollHeight;
  }

  function load() {
    fetch(api, { credentials: 'same-origin' }).then(function (r) { return r.json(); })
      .then(function (d) { if (d.messages) { show(''); render(d.messages); } })
      .catch(function () { show('Could not refresh the chat. Check your connection.'); });
  }
  function post(data) {
    return fetch(api, { method: 'POST', credentials: 'same-origin', body: new URLSearchParams(data) })
      .then(function (r) { return r.json().then(function (d) { d._ok = r.ok; return d; }); });
  }
  function report(id, btn) {
    post({ action: 'report', post_id: id }).then(function (d) {
      if (d.ok) { btn.textContent = 'Reported'; btn.disabled = true; show(''); }
      else show(d.error || 'Could not report that message.');
    });
  }
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var text = input.value.trim(); if (!text) return;
    post({ action: 'send', body: text }).then(function (d) {
      if (d.ok) {
        input.value = ''; show('');
        if (d.support && window.gabayCrisis) window.gabayCrisis.open();   // same hotline pop-up as the Peer Circle
        sig = ''; load();
      } else show(d.error || 'Your message was not sent. Try again.');
    }).catch(function () { show('Your message was not sent. Check your connection.'); });
  });

  function start() { load(); timer = setInterval(load, 4000); }
  document.addEventListener('visibilitychange', function () {
    clearInterval(timer);
    if (!document.hidden) start();
  });
  start();
})();
