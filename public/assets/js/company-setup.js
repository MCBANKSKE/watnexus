/* ============================================================
   Company Setup page
   Note: the original layout loaded jQuery + Select2 for the
   Country/Currency <select> elements, but the markup never
   applies select2 to them (no .select2 class or data attributes) —
   they're just plain <select> tags styled via CSS, so those two
   script tags were dead weight. Dropped, along with the CDN calls
   in the layout.
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('companySetupForm');
    if (!form) {
        return;
    }

    form.addEventListener('submit', function () {
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        }
    });
});