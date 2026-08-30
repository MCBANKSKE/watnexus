/* ============================================================
   Auth pages (login / register) — modern card design
   ============================================================ */

document.addEventListener('DOMContentLoaded', function(){
  // Password visibility toggle
  document.querySelectorAll('.pw-toggle').forEach(function(btn){
    btn.addEventListener('click', function(){
      const group = btn.closest('.input-group');
      const input = group && group.querySelector('input');
      if(!input) return;

      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      group.classList.toggle('is-visible', isPassword);
      btn.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
      btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
    });
  });

  // Loading state for auth forms
  document.querySelectorAll('.auth-form').forEach(function(form){
    form.addEventListener('submit', function(){
      const btn = form.querySelector('button[type="submit"]');
      if(btn){
        btn.dataset.originalText = btn.textContent;
        btn.textContent = 'Please wait…';
        btn.disabled = true;
      }
    });
  });
});