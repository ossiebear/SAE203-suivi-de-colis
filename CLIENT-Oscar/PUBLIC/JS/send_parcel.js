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
    
    // Initialize form field handlers
    initializeDimensionCalculator();
    initializeFormValidation();
    initializeTrackingNumberGenerator();
    
    // The autocomplete.js script handles the location type switching
    // through the setLocationType function called by button onclick events
}

/**
 * Initializes automatic volume calculation based on dimensions
 */
function initializeDimensionCalculator() {
    const lengthInput = document.getElementById('length');
    const widthInput = document.getElementById('width');
    const heightInput = document.getElementById('height');
    const volumeInput = document.getElementById('volume');

    function calculateVolume() {
        const length = parseFloat(lengthInput.value) || 0;
        const width = parseFloat(widthInput.value) || 0;
        const height = parseFloat(heightInput.value) || 0;
        
        // Convert cm to m and calculate volume in m³
        const volumeM3 = (length * width * height) / 1000000;
        
        if (length > 0 && width > 0 && height > 0) {
            volumeInput.value = volumeM3.toFixed(6);
        } else {
            volumeInput.value = '';
        }
    }

    // Add event listeners to dimension inputs
    if (lengthInput && widthInput && heightInput && volumeInput) {
        lengthInput.addEventListener('input', calculateVolume);
        widthInput.addEventListener('input', calculateVolume);
        heightInput.addEventListener('input', calculateVolume);
    }
}

/**
 * Initializes form validation
 */
function initializeFormValidation() {
    // Add real-time validation for email-like inputs
    const inputs = document.querySelectorAll('input[type="text"], input[type="email"], textarea');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
    });
}

/**
 * Validates individual form fields
 */
function validateField(field) {
    const value = field.value.trim();
    
    // Remove any existing validation classes
    field.classList.remove('valid', 'invalid');
    
    if (field.hasAttribute('required') && !value) {
        field.classList.add('invalid');
        return false;
    }
    
    if (value) {
        field.classList.add('valid');
    }
    
    return true;
}

/**
 * Generates a tracking number if not provided
 */
function initializeTrackingNumberGenerator() {
    const trackingInput = document.getElementById('tracking-number');
    
    if (trackingInput && !trackingInput.value) {
        // Generate a tracking number when form is loaded
        const timestamp = Date.now().toString().slice(-8);
        const random = Math.random().toString(36).substr(2, 4).toUpperCase();
        trackingInput.placeholder = `RB${timestamp}${random}`;
    }
}

/**
 * Collects all form data for submission
 */
function collectFormData() {
    const formData = {
        // Location data
        start: document.getElementById('start')?.value,
        destination: document.getElementById('destination')?.value,
        
        // Package data
        tracking_number: document.getElementById('tracking-number')?.value || generateTrackingNumber(),
        onpackage_sender_name: document.getElementById('sender-name')?.value,
        onpackage_sender_address: document.getElementById('sender-address')?.value,
        sender_shop_id: document.getElementById('sender-shop-id')?.value || null,
        onpackage_recipient_name: document.getElementById('recipient-name')?.value,
        onpackage_destination_address: document.getElementById('destination-address')?.value,
        weight_kg: parseFloat(document.getElementById('weight')?.value) || null,
        dimensions_cm: {
            length: parseFloat(document.getElementById('length')?.value) || null,
            width: parseFloat(document.getElementById('width')?.value) || null,
            height: parseFloat(document.getElementById('height')?.value) || null
        },
        volume_m3: parseFloat(document.getElementById('volume')?.value) || null,
        is_priority: document.getElementById('is-priority')?.checked || false,
        is_fragile: document.getElementById('is-fragile')?.checked || false,
        declared_value: parseFloat(document.getElementById('declared-value')?.value) || null,
        current_status_id: parseInt(document.getElementById('current-status-id')?.value) || null,
        current_office_id: parseInt(document.getElementById('current-office-id')?.value) || null,
        estimated_delivery_date: document.getElementById('estimated-delivery')?.value || null,
        actual_delivery_date: document.getElementById('actual-delivery')?.value || null
    };
    
    return formData;
}

/**
 * Generates a unique tracking number
 */
function generateTrackingNumber() {
    const timestamp = Date.now().toString().slice(-8);
    const random = Math.random().toString(36).substr(2, 4).toUpperCase();
    return `RB${timestamp}${random}`;
}

/**
 * Validates the entire form before submission
 */
function validateForm() {
    const requiredFields = [
        'sender-name',
        'sender-address', 
        'recipient-name',
        'destination-address',
        'current-status-id'
    ];
    
    let isValid = true;
    const errors = [];
    
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field && !field.value.trim()) {
            isValid = false;
            errors.push(`Le champ "${field.previousElementSibling.textContent}" est requis`);
            field.classList.add('invalid');
        }
    });
    
    if (!isValid) {
        alert('Veuillez remplir tous les champs obligatoires:\n' + errors.join('\n'));
    }
    
    return isValid;
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
    
    // Set default values for status and office if needed
    setTimeout(() => {
        const statusField = document.getElementById('current-status-id');
        const officeField = document.getElementById('current-office-id');
        
        // Set default status to "1" (assuming 1 = "created" or initial status)
        if (statusField && !statusField.value) {
            statusField.value = '1';
        }
        
        // Auto-generate tracking number if empty
        const trackingField = document.getElementById('tracking-number');
        if (trackingField && !trackingField.value) {
            trackingField.value = generateTrackingNumber();
        }
    }, 100);
});
