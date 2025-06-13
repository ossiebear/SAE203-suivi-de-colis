// Fonction pour initialiser le mode sombre
function initDarkMode() {
    // Récupérer la préférence sauvegardée
    const savedTheme = localStorage.getItem('theme') || 'light';
    const toggle = document.getElementById('darkModeToggle');
    
    // Appliquer le thème sauvegardé
    document.documentElement.setAttribute('data-theme', savedTheme);
    if (toggle) {
        toggle.checked = savedTheme === 'dark';
    }
    
    // Écouter les changements du toggle
    if (toggle) {
        toggle.addEventListener('change', function() {
            const theme = this.checked ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
        });
    }
}

// Initialiser quand le DOM est chargé
document.addEventListener('DOMContentLoaded', initDarkMode);