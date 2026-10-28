(function () {
  const KEY = 'padelio_theme';
  const root = document.documentElement;
  const stored = localStorage.getItem(KEY);
  if (stored === 'light' || stored === 'dark') {
    root.setAttribute('data-theme', stored);
  } else if (window.matchMedia('(prefers-color-scheme: light)').matches) {
    root.setAttribute('data-theme', 'light');
  }

  document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', next);
      localStorage.setItem(KEY, next);
      btn.setAttribute('aria-label', next === 'dark' ? 'Aktifkan tema terang' : 'Aktifkan tema gelap');
    });
  });

  document.addEventListener('click', function (event) {
    document.querySelectorAll('details.dd[open]').forEach(function (dd) {
      if (!dd.contains(event.target)) dd.removeAttribute('open');
    });
  });

  document.querySelectorAll('input[data-auto-submit]').forEach(function (input) {
    input.addEventListener('change', function () {
      if (input.files && input.files.length > 0) input.form.requestSubmit();
    });
  });

  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
  });

  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function () {
      const button = form.querySelector('button[type="submit"]');
      if (button) {
        setTimeout(function () { button.disabled = true; }, 0);
      }
    });
  });

  document.querySelectorAll('.toast').forEach(function (toast, index) {
    setTimeout(function () {
      toast.classList.add('out');
      setTimeout(function () { toast.remove(); }, 250);
    }, 2600 + index * 300);
  });
})();
