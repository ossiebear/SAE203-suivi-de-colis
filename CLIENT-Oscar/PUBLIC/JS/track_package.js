// track_package.js - Frontend JavaScript for package tracking
// SAE203 Package Tracking System

document.addEventListener('DOMContentLoaded', function() {
    const trackingForm = document.getElementById('trackingForm');
    const trackingNumberInput = document.getElementById('trackingNumber');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const errorMessage = document.getElementById('errorMessage');
    const trackingResults = document.getElementById('trackingResults');

    // Handle form submission
    trackingForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const trackingNumber = trackingNumberInput.value.trim();
        
        if (!trackingNumber) {
            showError('Veuillez entrer un numéro de suivi');
            return;
        }
        
        trackPackage(trackingNumber);
    });

    // Auto-format tracking number input (optional)
    trackingNumberInput.addEventListener('input', function(e) {
        // Remove any non-alphanumeric characters and convert to uppercase
        let value = e.target.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        
        // Limit to 20 characters
        if (value.length > 20) {
            value = value.substring(0, 20);
        }
        
        e.target.value = value;
    });

    /**
     * Track a package by its tracking number
     * @param {string} trackingNumber - The tracking number to search for
     */
    async function trackPackage(trackingNumber) {
        showLoading();
        hideError();
        hideResults();        try {
            const response = await fetch(`../../SRC/track_package.php?tracking_number=${encodeURIComponent(trackingNumber)}`);
            
            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Le serveur a retourné une réponse invalide');
            }
            
            const data = await response.json();
            
            hideLoading();
            
            if (data.success) {
                displayPackageInfo(data.package);
            } else {
                showError(data.error || 'Erreur lors de la recherche du colis');
            }
        } catch (error) {
            hideLoading();
            console.error('Error tracking package:', error);
            showError('Erreur de connexion. Veuillez réessayer plus tard.');
        }
    }    /**
     * Display package information in the results section
     * @param {Object} packageData - The package data from the API
     */
    function displayPackageInfo(packageData) {
        console.log('Package data received:', packageData); // Debug log
        
        // Update tracking number display
        document.getElementById('displayTrackingNumber').textContent = packageData.tracking_number || 'Non spécifié';
          // Update package details
        const weight = packageData.details.weight_kg || 'Non spécifié';
        document.getElementById('packageWeight').textContent = weight !== 'Non spécifié' ? `${weight} kg` : weight;
        
        // Format dimensions
        if (packageData.details.dimensions_cm) {
            const dims = packageData.details.dimensions_cm;
            const dimensionsText = `${dims.length || '?'} × ${dims.width || '?'} × ${dims.height || '?'} cm`;
            document.getElementById('packageDimensions').textContent = dimensionsText;
        } else {
            document.getElementById('packageDimensions').textContent = 'Non spécifié';
        }
        
        const volume = packageData.details.volume_m3 || 'Non calculé';
        document.getElementById('packageVolume').textContent = volume !== 'Non calculé' ? `${volume} m³` : volume;
        
        const value = packageData.details.declared_value || 'Non déclarée';
        document.getElementById('packageValue').textContent = value !== 'Non déclarée' ? `${value} €` : value;
        document.getElementById('packagePriority').textContent = packageData.details.is_priority ? 'Oui' : 'Non';
        document.getElementById('packageFragile').textContent = packageData.details.is_fragile ? 'Oui' : 'Non';
        
        // Update sender information
        document.getElementById('senderName').textContent = packageData.sender.name || 'Non spécifié';
        document.getElementById('senderAddress').textContent = packageData.sender.address || 'Non spécifiée';
        
        // Update recipient information
        document.getElementById('recipientName').textContent = packageData.recipient.name || 'Non spécifié';
        document.getElementById('recipientAddress').textContent = packageData.recipient.address || 'Non spécifiée';
          // Update status information with enhanced progress bar
        const statusName = packageData.status.name || 'Inconnu';
        updateProgressBar(statusName, packageData.dates.updated_at);
        
        // Update current status display
        document.getElementById('currentStatusName').textContent = getStatusDisplayName(statusName);
        document.getElementById('currentStatusDescription').textContent = packageData.status.description || 'Aucune description disponible';
        document.getElementById('currentStatusIcon').textContent = getStatusIcon(statusName);
        document.getElementById('statusUpdateTime').textContent = formatDate(packageData.dates.updated_at) || 'Non spécifiée';
        
        document.getElementById('currentOffice').textContent = 
            packageData.current_office.name ? 
            `${packageData.current_office.name}${packageData.current_office.city ? ' - ' + packageData.current_office.city : ''}` : 
            'Non spécifié';
        
        // Update dates
        document.getElementById('createdAt').textContent = formatDate(packageData.dates.created_at) || 'Non spécifiée';
        document.getElementById('updatedAt').textContent = formatDate(packageData.dates.updated_at) || 'Non spécifiée';
        document.getElementById('estimatedDelivery').textContent = 
            formatDate(packageData.dates.estimated_delivery) || 'Non estimée';
        document.getElementById('actualDelivery').textContent = 
            formatDate(packageData.dates.actual_delivery) || 'Pas encore livré';
        
        // Display tracking history if available
        if (packageData.history && packageData.history.length > 0) {
            displayTrackingHistory(packageData.history);
            document.getElementById('trackingHistory').style.display = 'block';
        } else {
            document.getElementById('trackingHistory').style.display = 'none';
        }
        
        showResults();
    }

    /**
     * Get CSS class for status badge based on status name
     * @param {string} statusName - The status name
     * @returns {string} CSS class name
     */
    function getStatusClass(statusName) {
        const status = statusName.toLowerCase();
        
        if (status.includes('livré') || status.includes('delivered')) {
            return 'status-livre';
        } else if (status.includes('transit') || status.includes('en route')) {
            return 'status-en-transit';
        } else if (status.includes('expédié') || status.includes('shipped')) {
            return 'status-expedie';
        } else if (status.includes('attente') || status.includes('pending')) {
            return 'status-en-attente';
        } else if (status.includes('retour') || status.includes('return')) {
            return 'status-retour';
        }
        
        return 'status-en-attente'; // default
    }

    /**
     * Format a date string for display
     * @param {string} dateString - ISO date string
     * @returns {string} Formatted date or empty string
     */
    function formatDate(dateString) {
        if (!dateString) return '';
        
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (error) {
            return dateString; // Return original if formatting fails
        }
    }

    /**
     * Show loading indicator
     */
    function showLoading() {
        loadingIndicator.style.display = 'block';
    }

    /**
     * Hide loading indicator
     */
    function hideLoading() {
        loadingIndicator.style.display = 'none';
    }

    /**
     * Show error message
     * @param {string} message - Error message to display
     */
    function showError(message) {
        document.getElementById('errorText').textContent = message;
        errorMessage.style.display = 'block';
    }

    /**
     * Hide error message
     */
    function hideError() {
        errorMessage.style.display = 'none';
    }

    /**
     * Show tracking results
     */
    function showResults() {
        trackingResults.style.display = 'block';
    }

    /**
     * Hide tracking results
     */
    function hideResults() {
        trackingResults.style.display = 'none';
    }

    /**
     * Display tracking history timeline
     * @param {Array} history - Array of tracking events
     */
    function displayTrackingHistory(history) {
        const timeline = document.getElementById('historyTimeline');
        timeline.innerHTML = '';
        
        history.forEach(event => {
            const historyItem = document.createElement('div');
            historyItem.className = 'history-item';
            
            historyItem.innerHTML = `
                <div class="history-timestamp">${formatDate(event.timestamp)}</div>
                <div class="history-status">${event.status_name || 'Événement'}</div>
                <div class="history-description">${event.description || 'Aucune description disponible'}</div>
                ${event.office_name ? `<div class="history-location">📍 ${event.office_name}${event.office_city ? ', ' + event.office_city : ''}</div>` : ''}
                ${event.notes ? `<div class="history-notes"><small>Note: ${event.notes}</small></div>` : ''}
            `;
            
            timeline.appendChild(historyItem);
        });
    }

    // Allow Enter key to submit form when input is focused
    trackingNumberInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            trackingForm.dispatchEvent(new Event('submit'));
        }
    });

    // Check for tracking number in URL parameters on page load
    function checkURLForTrackingNumber() {
        const urlParams = new URLSearchParams(window.location.search);
        const trackingNumber = urlParams.get('tracking');
        
        if (trackingNumber) {
            trackingNumberInput.value = trackingNumber;
            trackPackage(trackingNumber);
        }
    }
    
    // Initialize page
    checkURLForTrackingNumber();
    
    /**
     * Update the progress bar based on current status
     * @param {string} currentStatus - The current package status
     * @param {string} lastUpdate - Last update timestamp
     */
    function updateProgressBar(currentStatus, lastUpdate) {
        // Define the normal progression order
        const normalProgress = [
            'CREATED', 'PICKED_UP', 'IN_TRANSIT', 'ARRIVED_HUB', 'OUT_DELIVERY', 'DELIVERED'
        ];
        
        // Special statuses that can occur at any point
        const specialStatuses = ['DELAYED', 'ON_HOLD', 'FAILED', 'RETURNED'];
        
        // Map status codes to their display names
        const statusMap = {
            'CREATED': 'Created',
            'PICKED_UP': 'Picked Up', 
            'IN_TRANSIT': 'In Transit',
            'ARRIVED_HUB': 'Arrived at Hub',
            'OUT_DELIVERY': 'Out for Delivery',
            'DELIVERED': 'Delivered',
            'DELAYED': 'Delayed',
            'ON_HOLD': 'On Hold',
            'FAILED': 'Delivery Failed',
            'RETURNED': 'Returned to Sender'
        };
        
        // Find current status in normal progression or handle special cases
        let currentIndex = normalProgress.findIndex(status => 
            statusMap[status] === currentStatus || status === currentStatus
        );
        
        // Handle special statuses
        const isSpecialStatus = specialStatuses.some(status => 
            statusMap[status] === currentStatus || status === currentStatus
        );
        
        // Reset all steps
        normalProgress.forEach(status => {
            const circle = document.getElementById(`step-${status}`);
            const timestamp = document.getElementById(`time-${status}`);
            if (circle) {
                circle.className = 'progress-circle pending';
            }
            if (timestamp) {
                timestamp.textContent = '';
            }
        });
        
        // Handle special status display
        if (isSpecialStatus) {
            // Show special status indicators
            document.getElementById('specialStatusIndicators').style.display = 'block';
            
            // Highlight the special status
            specialStatuses.forEach(status => {
                if (statusMap[status] === currentStatus || status === currentStatus) {
                    const circle = document.getElementById(`step-${status}`);
                    if (circle) {
                        circle.className = 'progress-circle current';
                    }
                }
            });
            
            // For special statuses, assume we've at least created and picked up
            currentIndex = Math.max(currentIndex, 1);
        } else {
            // Hide special status indicators for normal flow
            document.getElementById('specialStatusIndicators').style.display = 'none';
        }
        
        // Update progress for normal statuses
        if (currentIndex >= 0) {
            // Mark completed steps
            for (let i = 0; i <= currentIndex; i++) {
                const circle = document.getElementById(`step-${normalProgress[i]}`);
                const timestamp = document.getElementById(`time-${normalProgress[i]}`);
                if (circle) {
                    circle.className = i === currentIndex ? 'progress-circle current' : 'progress-circle completed';
                }
                if (timestamp && i === currentIndex) {
                    timestamp.textContent = formatTimeShort(lastUpdate);
                }
            }
            
            // Update progress line
            const progressLine = document.getElementById('progressLineActive');
            if (progressLine) {
                const percentage = (currentIndex / (normalProgress.length - 1)) * 100;
                progressLine.style.width = `${percentage}%`;
            }
        }
        
        // Update active labels
        document.querySelectorAll('.progress-label').forEach(label => {
            label.classList.remove('active');
        });
        
        const currentStep = document.querySelector(`[data-status="${currentStatus}"]`);
        if (currentStep) {
            const label = currentStep.querySelector('.progress-label');
            if (label) {
                label.classList.add('active');
            }
        }
    }
    
    /**
     * Get display name for status
     * @param {string} statusName - The status name from database
     * @returns {string} Display name
     */
    function getStatusDisplayName(statusName) {
        const displayNames = {
            'Created': 'Colis créé',
            'Picked Up': 'Colis récupéré',
            'In Transit': 'En transit',
            'Arrived at Hub': 'Arrivé au centre de tri',
            'Out for Delivery': 'En cours de livraison',
            'Delivered': 'Livré',
            'Delayed': 'Retardé',
            'On Hold': 'En attente',
            'Delivery Failed': 'Échec de livraison',
            'Returned to Sender': 'Retourné à l\'expéditeur'
        };
        
        return displayNames[statusName] || statusName;
    }
    
    /**
     * Get icon for status
     * @param {string} statusName - The status name
     * @returns {string} Emoji icon
     */
    function getStatusIcon(statusName) {
        const icons = {
            'Created': '📝',
            'Picked Up': '📤',
            'In Transit': '🚛',
            'Arrived at Hub': '🏢',
            'Out for Delivery': '🚚',
            'Delivered': '✅',
            'Delayed': '⏰',
            'On Hold': '⏸️',
            'Delivery Failed': '❌',
            'Returned to Sender': '↩️'
        };
        
        return icons[statusName] || '📦';
    }
    
    /**
     * Format time in short format
     * @param {string} dateString - ISO date string
     * @returns {string} Short formatted time
     */
    function formatTimeShort(dateString) {
        if (!dateString) return '';
        
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR', {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (error) {
            return '';
        }
    }
});
