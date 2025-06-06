// Oscar Collins 2025
// SAE203 groupe A2
// AI usage: Written manually
//           comments written by AI. GPT4.1-mini

// BUTTON FOR SAVE PATH TO DB------------------------------------------------------

// Add a click event listener to the button with ID 'save-path-btn'.
document.getElementById('save-path-btn').addEventListener('click', () => {
    // Retrieve the selected start and destination data from the global selectedData object.
    const start = selectedData['start'];
    const destination = selectedData['destination'];

    // Validate that both start and destination have been selected.
    if (!start || !destination) {
        console.log('Please select both start and destination.');

        // Add a 'shake' CSS class to the button to provide visual feedback for the error.
        const button = document.getElementById('save-path-btn');
        button.classList.add('shake');

        // Remove the 'shake' class once the animation ends to allow future shakes.
        button.addEventListener('animationend', () => {
            button.classList.remove('shake');
        }, { once: true });  // Ensures the event listener is removed after firing once.

        // Exit early since required selections are missing.
        return;
    }

    // Fetch the journey path from the server by calling CreatePath.php via GET with start and finish IDs.
    console.log("Asking CreatePath.php for path for start=", start[0], "and finish=", destination[0]);
    fetch(`../../SRC/CreatePath.php?start=${encodeURIComponent(start[0])}&finish=${encodeURIComponent(destination[0])}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    })
    .then(response => response.json()) // Parse the response as JSON.
    .then(data => {
        console.log('CreatePath.php replied with path:', data);
        // Extract the journey node IDs from the response and send them to savePathToDB.php via GET.
        console.log('Asking SavePathToDB.php to save calculated path to database'); 
        return fetch(`../../SRC/savePathToDB.php?pathData=${encodeURIComponent(JSON.stringify(data.results.journey.map(item => item.identifiant_a)))}`,
        {
            method: 'GET',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        });
    })
    .then(response => response.json()) // Read the response json from the savePathToDB.php call.
    .then(response => {
        console.log('Path saved to database successfully (assumed)');
        console.log('Server replied:', response);

        // Get the parent container where to add the tracking code element
        const pathArea = document.getElementById('path-area');

        // Check if tracking code element already exists
        let trackingCodeElement = document.getElementById('tracking-code');

        if (response.success) {
            // If it exists, update the content
            if (trackingCodeElement) {
                trackingCodeElement.innerText = "Tracking code: " + response.results;
            } else {
                // If it doesn't exist, create a new <p> element with id 'tracking-code'
                trackingCodeElement = document.createElement('p');
                trackingCodeElement.id = 'tracking-code';
                trackingCodeElement.innerText = "Tracking code: " + response.results;
                pathArea.appendChild(trackingCodeElement);
            }
        } else {
            // Show error message
            if (trackingCodeElement) {
                trackingCodeElement.innerText = "Error: " + response.error;
            } else {
                trackingCodeElement = document.createElement('p');
                trackingCodeElement.id = 'tracking-code';
                trackingCodeElement.innerText = "Error: " + response.error;
                pathArea.appendChild(trackingCodeElement);
            }
        }
    })
    .catch(err => {
        // Log any errors encountered during the fetch calls.
        console.error('Error: ' + err.message);
        // Optionally, provide user feedback for the error here.
    });
});