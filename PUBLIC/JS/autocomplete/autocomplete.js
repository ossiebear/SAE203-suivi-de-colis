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
        }

        // Fetch autocomplete suggestions from the server-side endpoint.
        // The endpoint is assumed to be '../../SRC/autocomplete.php'.
        fetch('../../SRC/autocomplete.php?query=' + encodeURIComponent(val))
            .then(r => r.json()) // Parse the response as JSON.
            .then(data => {
                list.innerHTML = ''; // Clear any existing suggestions.

                // If no data is returned, hide the list and exit.
                if (data.length === 0) {
                    list.hidden = true;
                    return;
                }

                // Populate the dropdown list with suggestions from the data.
                data.forEach(item => {
                    // Create a display string from the item's data (city and state).
                    const display = item[1] + ', ' + item[7];

                    // Create a list item for each suggestion.
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
                    });

                    list.appendChild(li); // Append the list item to the dropdown list.
                });

                // Position the dropdown list below the input field.
                const rect = input.getBoundingClientRect();
                list.style.top = (input.offsetTop + input.offsetHeight) + 'px';
                list.style.left = input.offsetLeft + 'px';
                list.style.width = input.offsetWidth + 'px';
                list.hidden = false; // Show the list after populating it.
            });
    });

    // Hide the dropdown list when the input field loses focus.
    input.addEventListener('blur', function() {
        // Use setTimeout to delay hiding the list, allowing click events to fire.
        setTimeout(() => list.hidden = true, 100);
    });
}

// Initialize autocomplete for the 'start' and 'destination' input fields.
setupAutocomplete('start');
setupAutocomplete('destination');

//----------------------------------------------------------------------------------