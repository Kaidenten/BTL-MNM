// Format số tiền khi nhập
document.querySelectorAll('.money-input').forEach(input => {
  input.addEventListener('input', function () {
    let raw = this.value.replace(/\D/g, '');
    this.dataset.raw = raw;
    this.value = raw ? Number(raw).toLocaleString('vi-VN') : '';
  });
});
 
// Trước khi submit form chứa money-input, ghi lại giá trị thô
document.querySelectorAll('form').forEach(form => {
  form.addEventListener('submit', function () {
    this.querySelectorAll('.money-input').forEach(input => {
      input.value = input.dataset.raw || input.value.replace(/\D/g, '');
    });
  });
});
 
// Auto-dismiss alert sau 4 giây
document.querySelectorAll('.alert-auto-dismiss').forEach(el => {
  setTimeout(() => {
    el.classList.add('fade');
    setTimeout(() => el.remove(), 300);
  }, 4000);
});
 
// Hiện/ẩn mật khẩu
document.querySelectorAll('.toggle-password').forEach(btn => {
  btn.addEventListener('click', function () {
    const input = document.querySelector(this.dataset.target);
    if (!input) return;
    const isPass = input.type === 'password';
    input.type = isPass ? 'text' : 'password';
    this.querySelector('i').className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
  });
});
