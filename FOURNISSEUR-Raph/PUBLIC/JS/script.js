// Auto-submit du formulaire quand on change la sélection
document.addEventListener('DOMContentLoaded', function() {
    const select = document.querySelector('select[name="client_id"]');
    if (select) {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    }
});