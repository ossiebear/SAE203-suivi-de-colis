/**
 * Send Parcel Form JavaScript Functions
 * Handles shipping form functionality - now integrated with autocomplete
 */

// ================================
// SHIPPING FORM FUNCTIONALITY
// ================================

/**
 * Initializes the shipping form functionality
 * Sets up event listeners but defers to autocomplete.js for location type handling
 */
function initializeShippingForm() {
    console.log('Shipping form initialized - autocomplete handles location type toggles');
    
    // The autocomplete.js script handles the location type switching
    // through the setLocationType function called by button onclick events
    
    // You can add additional form validation or other functionality here
}

// ================================
// EVENT LISTENERS & INITIALIZATION
// ================================

/**
 * Initialize shipping form functionality when DOM is fully loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize shipping form functionality
    initializeShippingForm();
});
