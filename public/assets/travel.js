const quantity = document.getElementById('jumlah');
const invoice = document.querySelector('[data-price]');
if (quantity && invoice) {
  const update = () => {
    const count = Number(quantity.value) || 0;
    const subtotal = count * Number(invoice.dataset.price);
    const money = value => 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
    invoice.querySelector('[data-quantity]').textContent = count;
    invoice.querySelector('[data-subtotal]').textContent = money(subtotal);
    invoice.querySelector('[data-total]').textContent = money(subtotal + Number(invoice.dataset.admin));
  };
  quantity.addEventListener('input', update); update();
}
document.querySelectorAll('input[type="date"]').forEach(input => {
  const hidden = document.createElement('input');
  hidden.type = 'hidden'; hidden.name = input.name; hidden.value = input.value;
  input.removeAttribute('name'); input.type = 'text'; input.inputMode = 'numeric';
  input.placeholder = 'dd/mm/yyyy'; input.pattern = '[0-9]{2}/[0-9]{2}/[0-9]{4}';
  input.removeAttribute('min'); input.removeAttribute('max');
  const parts = hidden.value.split('-');
  if (parts.length === 3) input.value = parts.reverse().join('/');
  input.insertAdjacentElement('afterend', hidden);
  const sync = () => {
    const match = input.value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (!match) { hidden.value = ''; input.setCustomValidity(input.value ? 'Gunakan format dd/mm/yyyy.' : ''); return; }
    const [,day,month,year] = match;
    const date = new Date(Number(year), Number(month)-1, Number(day));
    const valid = date.getFullYear() === Number(year) && date.getMonth() === Number(month)-1 && date.getDate() === Number(day);
    input.setCustomValidity(valid ? '' : 'Tanggal tidak valid.');
    hidden.value = valid ? `${year}-${month}-${day}` : '';
  };
  input.addEventListener('input', sync); input.addEventListener('change', sync); sync();
});

document.querySelectorAll('.package-photo-panel input[type="file"]').forEach(input => {
  let previewUrl;
  input.addEventListener('change', () => {
    const file = input.files[0];
    if (!file || !['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) return;
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    const panel = input.closest('.package-photo-panel');
    let image = panel.querySelector('img');
    if (!image) { image = document.createElement('img'); image.alt = 'Pratinjau foto'; panel.prepend(image); }
    panel.querySelector('p')?.remove();
    previewUrl = URL.createObjectURL(file); image.src = previewUrl;
  });
});
