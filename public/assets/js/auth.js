/* ============================================================
   Auth pages (login / register) — sign-in/sign-up panel toggle
   ============================================================ */

function toggleAuth(target){
  const container = document.getElementById('authContainer');
  if(!container) return;
  container.classList.toggle('is-signup', target === 'signup');

  // Move focus into the newly-active form's first field — matters most on
  // mobile, where the inactive panel is display:none rather than just faded.
  const activePanel = container.querySelector(
    target === 'signup' ? '.panel-signup' : '.panel-signin'
  );
  const firstField = activePanel && activePanel.querySelector('input');
  if(firstField) firstField.focus({preventScroll:true});
}

document.addEventListener('DOMContentLoaded', function(){
  // Loading state for whichever auth form gets submitted.
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