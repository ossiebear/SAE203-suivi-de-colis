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
                    }

                    // Create a list item for each suggestion.
                    const li = document.createElement('li');
                    li.textContent = display; // Set the text content of the list item.
                    li.style.padding = '4px 12px'; // Add padding for better appearance.
                    li.style.cursor = 'pointer'; // Change cursor to pointer on hover.                    // Add an event listener to each list item to handle selection.
                    li.addEventListener('mousedown', function(e) {
                        input.value = display; // Set the input value to the selected suggestion.
                        selectedData[inputId] = item; // Store the full row of data in selectedData.
                        list.hidden = true; // Hide the list after selection.
                        e.preventDefault(); // Prevent the input from losing focus.
                        
                        // Console output for debugging
                        console.log(`Selected ${inputId}:`, item);
                        console.log(`Current selectedData:`, selectedData);
                        
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
    
    // Console output when clearing selection
    console.log(`Cleared ${inputId} selection - type changed to ${type}`);
    console.log(`Current selectedData:`, selectedData);
    
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

// Initialize autocomplete for the 'start' and 'destination' input fields.
setupAutocomplete('start');
setupAutocomplete('destination');

// Initialize default location types on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set default types to 'office'
    setLocationType('start', 'office');
    setLocationType('destination', 'office');
});

//----------------------------------------------------------------------------------