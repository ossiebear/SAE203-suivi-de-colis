// GENERATE PATH BUTTON
// DATA FLOW: text boxes contain full array -> extract the IDs -> fetch TrackingCodeToJourney.php with TrackingNumber and finish IDs -> TrackingCodeToJourney.php returns the path data -> renderPathToMap and renderPathToText are called with the path data.


let calculated_path = null; // Store the fetched journey data

// Add click event listener to the "simulate" button.
document.getElementById('search-button').addEventListener('click', function() {
   
    // Get the tracking number from the input field
    const trackingNumber = document.getElementById('tracking-code-input-box').value.trim();    

    // Fetch journey data from the server
    console.log("Asking TrackingCodeToJourney.php for path for TrackingNumber=", trackingNumber);
    fetch(`../../SRC/TrackingCodeToJourney.php?trackingNumber=${encodeURIComponent(trackingNumber)}`)
        .then(r => r.json())
        .then(serverReplyData => {
            console.log("TrackingCodeToJourney replied with path:", serverReplyData);
            if (!serverReplyData.success) {
                console.error(serverReplyData.error || 'Unknown error from TrackingCodeToJourney.php');
                return;
            }
            const journey = serverReplyData.results; // <-- Fix: results is the array
            if (!journey || !Array.isArray(journey) || journey.length === 0) {
                console.error('No journey data returned.');
                return;
            }

            console.log('Sending path to map renderer', journey);
            renderPathToMap(journey); //==============================> sends the data out to map-render.js


            console.log('Sending path to text renderer', journey);
            renderPathToText(journey); //==============================> sends the data out to text-render.js

        })
        .catch(err => {
            console.error('Fetch error:', err);
        });
});
//----------------------------------------------------------------------------------

