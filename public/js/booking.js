(function () {
  const form = document.getElementById('booking-form');
  const totalEl = document.getElementById('booking-total');
  if (!form || !totalEl) return;

  const base = Number(totalEl.dataset.base || 0);

  function format(value) {
    return 'Rp' + new Intl.NumberFormat('id-ID').format(Math.round(value));
  }

  function update() {
    const addons = Array.from(form.querySelectorAll('input[name="addons[]"]:checked'))
      .reduce(function (sum, input) { return sum + Number(input.dataset.price || 0); }, 0);
    totalEl.textContent = format(base + addons);
  }

  form.querySelectorAll('input[name="addons[]"]').forEach(function (input) {
    input.addEventListener('change', update);
  });

  update();
})();
