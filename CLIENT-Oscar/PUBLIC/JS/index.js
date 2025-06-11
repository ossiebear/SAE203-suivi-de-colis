/**
 * Landing Page JavaScript Functions
 * Handles dark mode toggle, site selection modal, and user preferences
 */

// ================================
// DARK MODE FUNCTIONALITY
// ================================

/**
 * Toggles dark mode on/off based on checkbox state
 * Updates body data-theme attribute and saves preference to localStorage
 */
function toggleDarkMode() {
    const body = document.body;
    const toggle = document.getElementById('darkModeToggle');
    
    if (toggle.checked) {
        body.setAttribute('data-theme', 'dark');
        localStorage.setItem('darkMode', 'enabled');
    } else {
        body.removeAttribute('data-theme');
        localStorage.setItem('darkMode', 'disabled');
    }
}

/**
 * Initializes dark mode based on saved user preference
 * Called on page load to restore previous dark mode setting
 */
function initializeDarkMode() {
    const darkMode = localStorage.getItem('darkMode');
    const toggle = document.getElementById('darkModeToggle');
    
    if (darkMode === 'enabled') {
        document.body.setAttribute('data-theme', 'dark');
        toggle.checked = true;
    }
}

// ================================
// SITE SELECTION MODAL
// ================================

/**
 * Toggles the visibility of the site selection modal
 * Adds/removes 'show' class to display or hide modal
 */
function toggleSiteModal() {
    const modal = document.getElementById('siteModal');
    modal.classList.toggle('show');
}

/**
 * Closes the site selection modal
 * Removes 'show' class to hide modal
 */
function closeSiteModal() {
    const modal = document.getElementById('siteModal');
    modal.classList.remove('show');
}

/**
 * Handles site selection when user clicks on a site option
 * Updates UI to show selected state and updates dropdown text
 * @param {string} siteType - The type of site selected (client, magasin, livreur)
 */
function selectSite(siteType) {
    // Remove selected state from all options
    const allOptions = document.querySelectorAll('.site-option');
    allOptions.forEach(option => {
        option.classList.remove('selected');
        // Remove existing checkmarks
        const existingCheckmark = option.querySelector('.checkmark');
        if (existingCheckmark) {
            existingCheckmark.remove();
        }
    });

    // Get the clicked option element
    const selectedOption = event.target.closest('.site-option');
    
    // Add selected state to clicked option
    selectedOption.classList.add('selected');
    
    // Add checkmark to selected option
    const checkmark = document.createElement('div');
    checkmark.className = 'checkmark';
    checkmark.textContent = '✓';
    selectedOption.appendChild(checkmark);

    // Update dropdown button text with selected option
    const selectedText = selectedOption.querySelector('h3').textContent;
    const dropdownButton = document.querySelector('.lp-dropdown span');
    dropdownButton.textContent = selectedText;

    // Close the modal after selection
    closeSiteModal();
}

// ================================
// EVENT LISTENERS & INITIALIZATION
// ================================

/**
 * Initialize all functionality when DOM is fully loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dark mode based on saved preference
    initializeDarkMode();
    
    // Add event listener for dark mode toggle
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', toggleDarkMode);
    }
    
    // Add event listener to close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('siteModal');
        // Close modal if clicking directly on the modal background (not modal content)
        if (event.target === modal) {
            closeSiteModal();
        }
    });
});
