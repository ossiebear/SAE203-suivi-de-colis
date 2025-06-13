// GENERATE PATH BUTTON
// DATA FLOW: text boxes contain full array -> extract the IDs -> fetch CreatePath.php with start and finish IDs -> CreatePath.php returns the path data -> renderPathToMap and renderPathToText are called with the path data.

/**
 * Get the location type (office or shop) from an input field's data attribute
 * @param {string} inputId - The ID of the input field ('start' or 'destination')
 * @returns {string} - 'office' or 'shop'
 */
function getLocationTypeFromInput(inputId) {
    const input = document.getElementById(inputId);
    return input ? input.getAttribute('data-search-type') || 'office' : 'office';
}


let calculated_path = null; // Store the fetched journey data

// Add click event listener to the "simulate" button.
document.getElementById('generate-path-btn').addEventListener('click', function() {
   

    // Retrieve selected start and destination data.
    const start = selectedData['start'];
    const destination = selectedData['destination'];

    // Validate that both start and destination are selected.
    if (!start || !destination) {
        console.warn('Please select both start and destination.');
        return;
    }    // Extract IDs from the selected data.
    const startId = start[1];
    const finishId = destination[1];
    
    // Determine the types of start and destination locations
    const startType = getLocationTypeFromInput('start');
    const finishType = getLocationTypeFromInput('destination');

    // Fetch journey data from the server
    console.log("Asking CreatePath.php for path for start=", startId, "and finish=", finishId, "with types:", startType, finishType);
    fetch(`../../SRC/CreatePath.php?start=${encodeURIComponent(startId)}&finish=${encodeURIComponent(finishId)}&start_type=${encodeURIComponent(startType)}&finish_type=${encodeURIComponent(finishType)}`)
        .then(r => r.json())
        .then(serverReplyData => {
            console.log("CreatePath replied with path:", serverReplyData);
            if (!serverReplyData.success) {
                console.error(serverReplyData.error || 'Unknown error from CreatePath.php');
                return;
            }
            const journey = serverReplyData.results && serverReplyData.results.journey;
            if (!journey || !Array.isArray(journey) || journey.length === 0) {
                console.error('No journey data returned.');
                return;
            }            console.log('Sending path to map renderer', journey);
            renderPathToMap(journey); //==============================> sends the data out to map-render.js

            console.log('Sending path to text renderer', journey);
            renderPathToText(journey); //==============================> sends the data out to text-render.js            // Store the calculated path globally and update estimated delivery
            calculated_path = journey;
            if (typeof calculateEstimatedDelivery === 'function') {
                const estimatedDate = calculateEstimatedDelivery(journey.length);
                const dateInput = document.getElementById('estimated_delivery_date');
                if (dateInput && !dateInput.value) {
                    dateInput.value = estimatedDate;
                }
            }
            
            // Update the current office ID based on the start location
            if (typeof updateAutomaticFields === 'function') {
                updateAutomaticFields();
            }

        })
        .catch(err => {
            console.error('Fetch error:', err);
        });
});
//----------------------------------------------------------------------------------

