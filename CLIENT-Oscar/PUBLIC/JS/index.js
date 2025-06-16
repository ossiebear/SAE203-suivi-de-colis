/**
 * Landing Page JavaScript Functions
 * 
 * SAE203 Package Tracking System - Main Page Interactivity
 * Handles dark mode toggle, site selection modal, and user preferences
 * 
 * Features:
 * - Dark mode toggle with localStorage persistence
 * - Multi-site selection modal (Client/Shop/Delivery)
 * - User preference management
 * - Responsive interface interactions
 * 
 * Author: Oscar Collins, SAE203 Group A2
 * Date: 2025
 * 
 * AI usage: Written manually, AI comments and reorganisation
 */

// ================================
// DARK MODE FUNCTIONALITY
// ================================

/**
 * Toggles dark mode on/off based on checkbox state
 * Updates body data-theme attribute and saves preference to localStorage
 * 
 * @description Provides persistent dark mode functionality across page reloads
 */
function toggleDarkMode() {
    const body = document.body;
    const toggle = document.getElementById('darkModeToggle');
    
    if (toggle.checked) {
        // Enable dark mode
        body.setAttribute('data-theme', 'dark');
        localStorage.setItem('darkMode', 'enabled');
    } else {
        // Disable dark mode (revert to light theme)
        body.removeAttribute('data-theme');
        localStorage.setItem('darkMode', 'disabled');
    }
}

/**
 * Initializes dark mode based on saved user preference
 * Called on page load to restore previous dark mode setting
 * 
 * @description Ensures dark mode preference persists across browser sessions
 */
function initializeDarkMode() {
    const darkMode = localStorage.getItem('darkMode');
    const toggle = document.getElementById('darkModeToggle');
    
    // Apply saved dark mode preference
    if (darkMode === 'enabled') {
        document.body.setAttribute('data-theme', 'dark');
        toggle.checked = true;
    }
}

// ================================
// SITE SELECTION MODAL FUNCTIONALITY
// ================================

/**
 * Toggles the visibility of the site selection modal
 * Adds/removes 'show' class to display or hide modal
 * 
 * @description Provides smooth modal toggle for user type selection
 */
function toggleSiteModal() {
    const modal = document.getElementById('siteModal');
    modal.classList.toggle('show');
}

/**
 * Closes the site selection modal
 * Removes 'show' class to hide modal
 * 
 * @description Provides consistent modal closing behavior
 */
function closeSiteModal() {
    const modal = document.getElementById('siteModal');
    modal.classList.remove('show');
}

/**
 * Handles site selection when user clicks on a site option
 * Updates UI to show selected state and updates dropdown text
 * 
 * @param {string} siteType - The type of site selected (client, magasin, livreur)
 * @description Manages user type selection and visual feedback
 * 
 * Site Types:
 * - 'client': Regular customer interface
 * - 'magasin': Shop/merchant interface  
 * - 'livreur': Delivery personnel interface
 */
function selectSite(siteType) {
    // Remove selected state from all options
    const allOptions = document.querySelectorAll('.site-option');
    allOptions.forEach(option => {
        option.classList.remove('selected');
        // Remove existing checkmarks to prevent duplicates
        const existingCheckmark = option.querySelector('.checkmark');
        if (existingCheckmark) {
            existingCheckmark.remove();
        }
    });

    // Get the clicked option element
    const selectedOption = event.target.closest('.site-option');
    
    // Add selected state to clicked option
    selectedOption.classList.add('selected');
    
    // Add visual checkmark to selected option
    const checkmark = document.createElement('div');
    checkmark.className = 'checkmark';
    checkmark.textContent = '✓';
    selectedOption.appendChild(checkmark);

    // Update dropdown button text with selected option
    const selectedText = selectedOption.querySelector('h3').textContent;
    const dropdownButton = document.querySelector('.lp-dropdown span');
    dropdownButton.textContent = selectedText;

    // Close the modal after selection for better UX
    closeSiteModal();
    
    // TODO: Implement actual site redirection based on siteType
    // This could redirect to different modules (CLIENT-Oscar, FOURNISSEUR-Raph, LIVREUR-Kevin)
}

// ================================
// EVENT LISTENERS & INITIALIZATION
// ================================

/**
 * Initialize all functionality when DOM is fully loaded
 * Sets up event listeners and restores user preferences
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Redboot Landing Page initialized');
    
    // Initialize dark mode from saved preferences
    initializeDarkMode();
    
    // Set up dark mode toggle event listener
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', toggleDarkMode);
        console.log('🌙 Dark mode toggle initialized');
    }
    
    // Initialize quick tracking functionality (if tracking form exists)
    initializeQuickTracking();
    
    console.log('✅ Landing page initialization complete');
});

/**
 * Initializes quick package tracking functionality on the main page
 * Allows users to track packages without navigating to dedicated tracking page
 */
function initializeQuickTracking() {
    const trackingInput = document.querySelector('.lp-track-form input');
    const trackingButton = document.querySelector('.lp-track-form button');
    
    if (trackingInput && trackingButton) {
        // Add event listener for tracking button
        trackingButton.addEventListener('click', function() {
            const trackingNumber = trackingInput.value.trim();
            
            if (trackingNumber) {
                // Redirect to dedicated tracking page with tracking number
                window.location.href = `suivre_colis.html?tracking=${encodeURIComponent(trackingNumber)}`;
            } else {
                // Show error for empty input
                trackingInput.style.borderColor = '#ff4444';
                trackingInput.placeholder = 'Veuillez entrer un numéro de suivi';
                
                // Reset placeholder after 3 seconds
                setTimeout(() => {
                    trackingInput.style.borderColor = '';
                    trackingInput.placeholder = 'Renseignez votre ou vos numéros de suivi';
                }, 3000);
            }
        });
        
        // Allow Enter key to trigger tracking
        trackingInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                trackingButton.click();
            }
        });
        
        // Format tracking number input (uppercase, alphanumeric only)
        trackingInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
            
            // Limit to 20 characters (standard tracking number length)
            if (value.length > 20) {
                value = value.substring(0, 20);
            }
            
            e.target.value = value;
            
            // Reset border color when user starts typing
            e.target.style.borderColor = '';
        });
        
        console.log('📦 Quick tracking functionality initialized');
    }
}
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
        const modal = document.getElementById('siteModal');        // Close modal if clicking directly on the modal background (not modal content)
        if (event.target === modal) {
            closeSiteModal();
        }
    });
});
