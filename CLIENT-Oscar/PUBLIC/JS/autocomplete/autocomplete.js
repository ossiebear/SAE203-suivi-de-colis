// Oscar Collins 2025
// SAE203 groupe A2
// AI usage: Written manually, AI help with drop-down menu positioning.
//           comments written by AI. GPT4.1-mini




// AUTOCOMPLETE -------------------------------------------------------------------

// Object to store the selected data for the start and destination inputs.
// This allows easy access to the full data row for each selection.
let selectedData = {
    start: null,
    destination: null
};

/**
 * Sets up autocomplete functionality for a given input field.
 *
 * @param {string} inputId The ID of the input element to attach autocomplete to.
 */
function setupAutocomplete(inputId) {
    // Get the input element by its ID.
    const input = document.getElementById(inputId);

    // Create a new unordered list element to hold the autocomplete suggestions.
    let list = document.createElement('ul');

    // Assign a class name to the list for styling purposes.
    list.className = 'autocomplete-list';

    // Set the width of the list to match the input field's width.
    list.style.width = input.offsetWidth + 'px';

    // Initially hide the list.
    list.hidden = true;

    // Append the list to the input's parent node.
    input.parentNode.appendChild(list);

    // Add an event listener to the input field that triggers on every input change.
    input.addEventListener('input', function() {
        // Get the current value of the input field.
        const val = input.value;

        // Only show suggestions if the input has at least 2 characters.
        if (val.length < 2) {
            list.hidden = true; // Hide the list if the input is too short.
            return;
        }        // Get the search type from the input's data attribute (office or shop)
        const searchType = input.getAttribute('data-search-type') || 'office';        // Fetch autocomplete suggestions from the server-side endpoint.
        // The endpoint is '../../SRC/autocomplete_office.php' which handles both offices and shops.
        fetch('../../SRC/autocomplete.php?query=' + encodeURIComponent(val) + '&type=' + encodeURIComponent(searchType))
            .then(r => {
                if (!r.ok) {
                    throw new Error('Network response was not ok');
                }
                return r.json();
            }) // Parse the response as JSON.
            .then(data => {
                list.innerHTML = ''; // Clear any existing suggestions.

                // If no data is returned, hide the list and exit.
                if (!Array.isArray(data) || data.length === 0) {
                    list.hidden = true;
                    return;
                }

                // Populate the dropdown list with suggestions from the data.
                data.forEach(item => {                    // Create a display string based on the search type
                    let display;
                    if (searchType === 'shop') {
                        // For shops: name (index 1), city (index 3), postal_code (index 4)
                        display = item[8] + ', ' + item[1] + ' ' + item[6];
                    } else {
                        // For offices: name (index 1), city (index 5), postal_code (index 4)
                        display = item[8] + ', ' + item[5] + ' ' + item[4];
                    }                    // Create a list item for each suggestion.
                    const li = document.createElement('li');
                    li.textContent = display; // Set the text content of the list item.
                    li.style.padding = '4px 12px'; // Add padding for better appearance.
                    li.style.cursor = 'pointer'; // Change cursor to pointer on hover.
                    
                    // Add an event listener to each list item to handle selection.
                    li.addEventListener('mousedown', function(e) {
                        input.value = display; // Set the input value to the selected suggestion.
                        selectedData[inputId] = item; // Store the full row of data in selectedData.
                        list.hidden = true; // Hide the list after selection.
                        e.preventDefault(); // Prevent the input from losing focus.
                        
                        // Enhanced console output for debugging
                        console.group(`🎯 Location Selected for ${inputId.toUpperCase()}`);
                        console.log(`📍 Display Text: "${display}"`);
                        console.log(`🏢 Type: ${searchType}`);
                        console.log(`📊 Raw Data:`, item);
                        
                        
                        
                        // Trigger automatic field updates if the function exists
                        setTimeout(() => {
                            if (typeof updateAutomaticFields === 'function') {
                                updateAutomaticFields();
                            }
                        }, 100); // Small delay to ensure selectedData is updated
                    });

                    list.appendChild(li); // Append the list item to the dropdown list.
                });

                // Position the dropdown list below the input field.
                const rect = input.getBoundingClientRect();
                list.style.top = (input.offsetTop + input.offsetHeight) + 'px';
                list.style.left = input.offsetLeft + 'px';                list.style.width = input.offsetWidth + 'px';
                list.hidden = false; // Show the list after populating it.
            })
            .catch(error => {
                console.error('Error fetching autocomplete data:', error);
                list.innerHTML = '';
                list.hidden = true;
            });
    });

    // Hide the dropdown list when the input field loses focus.
    input.addEventListener('blur', function() {
        // Use setTimeout to delay hiding the list, allowing click events to fire.
        setTimeout(() => list.hidden = true, 100);
    });
}

/**
 * Sets the location type (office or shop) for a given input field
 * and updates the autocomplete behavior accordingly.
 *
 * @param {string} inputId - The ID of the input field ('start' or 'destination')
 * @param {string} type - The type to set ('office' or 'shop')
 */
function setLocationType(inputId, type) {
    const input = document.getElementById(inputId);
    const officeBtn = document.getElementById(inputId === 'start' ? 'start-office-btn' : 'dest-office-btn');
    const shopBtn = document.getElementById(inputId === 'start' ? 'start-shop-btn' : 'dest-shop-btn');
    
    // Update the data attribute to control autocomplete search type
    input.setAttribute('data-search-type', type);
    
    // Update button states
    if (type === 'office') {
        officeBtn.classList.add('active');
        shopBtn.classList.remove('active');
        input.placeholder = inputId === 'start' ? 'Enter post office location' : 'Enter destination office';
    } else {
        shopBtn.classList.add('active');
        officeBtn.classList.remove('active');
        input.placeholder = inputId === 'start' ? 'Enter shop location' : 'Enter destination shop';
    }    // Clear the input value and selected data when type changes
    input.value = '';
    selectedData[inputId] = null;
    
    // Enhanced console output when clearing selection
    console.group(`🔄 Location Type Changed for ${inputId.toUpperCase()}`);
    console.log(`🏢 New Type: ${type}`);
    console.log(`🧹 Cleared input field: "${inputId}"`);
    console.log(`📋 Updated selectedData:`, selectedData);
    console.log(`🎯 New placeholder: "${input.placeholder}"`);
    console.groupEnd();
    
    // Hide any open autocomplete list
    const list = input.parentNode.querySelector('.autocomplete-list');
    if (list) {
        list.hidden = true;
    }
    
    // Update the location type in savePathToDB.js if the function exists
    if (typeof window.updateLocationType === 'function') {
        window.updateLocationType(inputId, type);
    }
}

// Make setLocationType available globally
window.setLocationType = setLocationType;

/**
 * Global function to output current selected data to console
 * Can be called from browser console: outputSelectedData()
 */
function outputSelectedData() {
    console.group('📊 Current Selected Location Data');
    console.log('🗃️ Selected Data Object:', selectedData);
    
    if (selectedData.start) {
        const startType = document.getElementById('start').getAttribute('data-search-type');
        console.group(`🚀 START LOCATION (${startType.toUpperCase()})`);
        console.log('📍 Display:', document.getElementById('start').value);
        console.log('📊 Raw Data:', selectedData.start);
        
        if (startType === 'shop') {
            console.log('🏪 Shop Details:');
            console.log(`   - Name: ${selectedData.start[8] || 'N/A'}`);
            console.log(`   - City: ${selectedData.start[1] || 'N/A'}`);
            console.log(`   - Postal Code: ${selectedData.start[6] || 'N/A'}`);
        } else {
            console.log('🏢 Office Details:');
            console.log(`   - Name: ${selectedData.start[8] || 'N/A'}`);
            console.log(`   - City: ${selectedData.start[5] || 'N/A'}`);
            console.log(`   - Postal Code: ${selectedData.start[4] || 'N/A'}`);
        }
        console.groupEnd();
    } else {
        console.log('🚀 START LOCATION: Not selected');
    }
    
    if (selectedData.destination) {
        const destType = document.getElementById('destination').getAttribute('data-search-type');
        console.group(`🎯 DESTINATION LOCATION (${destType.toUpperCase()})`);
        console.log('📍 Display:', document.getElementById('destination').value);
        console.log('📊 Raw Data:', selectedData.destination);
        
        if (destType === 'shop') {
            console.log('🏪 Shop Details:');
            console.log(`   - Name: ${selectedData.destination[8] || 'N/A'}`);
            console.log(`   - City: ${selectedData.destination[1] || 'N/A'}`);
            console.log(`   - Postal Code: ${selectedData.destination[6] || 'N/A'}`);
        } else {
            console.log('🏢 Office Details:');
            console.log(`   - Name: ${selectedData.destination[8] || 'N/A'}`);
            console.log(`   - City: ${selectedData.destination[5] || 'N/A'}`);
            console.log(`   - Postal Code: ${selectedData.destination[4] || 'N/A'}`);
        }
        console.groupEnd();
    } else {
        console.log('🎯 DESTINATION LOCATION: Not selected');
    }
    
    console.groupEnd();
}

// Make outputSelectedData available globally
window.outputSelectedData = outputSelectedData;

// Initialize autocomplete for the 'start' and 'destination' input fields.
setupAutocomplete('start');
setupAutocomplete('destination');

// Monitor input changes for additional debugging (optional)
function monitorInputs() {
    const startInput = document.getElementById('start');
    const destInput = document.getElementById('destination');
    
    startInput.addEventListener('input', function() {
        if (this.value.length >= 2) {
            console.log(`🔍 Searching for START locations: "${this.value}" (type: ${this.getAttribute('data-search-type')})`);
        }
    });
    
    destInput.addEventListener('input', function() {
        if (this.value.length >= 2) {
            console.log(`🔍 Searching for DESTINATION locations: "${this.value}" (type: ${this.getAttribute('data-search-type')})`);
        }
    });
}

// Initialize monitoring when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    monitorInputs();
});

/**
 * Submits the selected path data to generate_path.php
 * Sends startID, startType, endID, endType parameters
 */
function submitPath() {
    console.group('🚀 Submitting Path Data');
    
    // Validate that both locations are selected
    if (!selectedData.start || !selectedData.destination) {
        console.error('❌ Both start and destination locations must be selected');
        alert('Veuillez sélectionner un point de départ et une destination.');
        console.groupEnd();
        return;
    }
    
    // Get the types from the input attributes
    const startType = document.getElementById('start').getAttribute('data-search-type');
    const destType = document.getElementById('destination').getAttribute('data-search-type');
    
    // Extract IDs from selected data (assuming ID is at index 0)
    const startID = selectedData.start[0];
    const endID = selectedData.destination[0];
    
    // Log the data being sent
    console.log('📦 Sending data to generate_path.php:');
    console.log(`   - Start ID: ${startID}`);
    console.log(`   - Start Type: ${startType}`);
    console.log(`   - End ID: ${endID}`);
    console.log(`   - End Type: ${destType}`);
    
    // Prepare the request parameters
    const params = new URLSearchParams({
        startID: startID,
        startType: startType,
        endID: endID,
        endType: destType
    });
      // Send the request
    fetch(`../../SRC/generate_path.php?${params.toString()}`)
        .then(response => {
            console.log(`📡 Response status: ${response.status}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json(); // Parse as JSON instead of text
        })
        .then(data => {
            console.log('✅ Response received from generate_path.php:');
            console.log(data);            // Check if path generation was successful
            if ((data.status === 'success' || data.status === 'valid') && data.path && Array.isArray(data.path)) {
                console.log('🗺️ Rendering path to map...');
                
                // Show the map section
                const mapSection = document.getElementById('map-section');
                if (mapSection) {
                    mapSection.style.display = 'block';
                    
                    // Initialize map if not already done
                    if (typeof initializeMap === 'function') {
                        initializeMap();
                    }
                    
                    // Ensure map size is correct after making container visible
                    if (typeof ensureMapSize === 'function') {
                        ensureMapSize();
                    }
                }
                
                // Convert path data to format expected by renderPathToMap
                const pathForMap = data.path.map(node => {
                    const nodeData = node.data;
                    return {
                        latitude: nodeData.latitude,
                        longitude: nodeData.longitude,
                        libelle_du_site: nodeData.name || nodeData.libelle_du_site || 'Unknown Location'
                    };
                });
                
                // Render the path on the map (with a small delay to ensure map is ready)
                setTimeout(() => {
                    if (typeof renderPathToMap === 'function') {
                        renderPathToMap(pathForMap);
                        console.log(`✅ Path with ${pathForMap.length} nodes rendered on map`);
                    } else {
                        console.error('❌ renderPathToMap function not available');
                    }
                }, 200);
            } else {
                console.error('❌ Path generation failed:', data.message || 'Unknown error');
                alert('Erreur lors de la génération du chemin: ' + (data.message || 'Erreur inconnue'));
            }
            
            console.groupEnd();
        })
        .catch(error => {
            console.error('❌ Error submitting path:', error);
            console.groupEnd();
        });
}

// Make submitPath available globally
window.submitPath = submitPath;