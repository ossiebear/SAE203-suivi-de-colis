/**
 * Render the textual journey information in the UI.
 * Displays each step with labels for start, main hub, and destination.
 *
 * @param {Array} journey Array of journey nodes (rows).
 */
function renderPathInfo(journey) {
    console.log('Writing Step list')
    const pathArea = document.getElementById('path-area');
    const savePathBtn = document.getElementById('save-path-btn');

    // Create a container div for the journey info.
    const journeyInfo = document.createElement('div');
    journeyInfo.style.marginTop = '20px'; // Add spacing above.

    if (!journey || journey.length === 0) {
        // Show error message if no journey data is available.
        journeyInfo.innerHTML = '<div style="color:red;">No journey data available.</div>';
    } else {
        // Helper function to format each step's display.
        function formatSite(row, stepNum, isStart, isCommonRoot, isDestination) {
            let label = `Step ${stepNum}:`;
            if (isStart) label += ' <span style="color:blue;">(Start)</span>';
            if (isCommonRoot) label += ' <span style="color:violet;">(Main Hub)</span>';
            if (isDestination) label += ' <span style="color:green;">(Destination)</span>';
            return `
            <div class="timeline-step">
                <span class="timeline-circle"></span>
                <div class="timeline-content">
                    <b>${label}</b> ${row['libelle_du_site']}, <span style="color:#9c9c9c;">${row['caracteristique_du_site']}</span><br>
                    <span class="timeline-subtitle">${row['code_postal']}, ${row['adresse']} ${row['localite']}</span> 
                </div>
            </div>
            `;
        }

        // Calculate the index of the common root node (middle of the journey).
        const commonRootIndex = Math.floor(journey.length / 2);

        // Build the HTML for the journey timeline.
        let html = '<h3>Parcel Journey</h3>';
        html += '<div class="timeline">';
        journey.forEach((row, i) => {
            const isStart = i === 0;
            const isCommonRoot = i === commonRootIndex;
            const isDestination = i === journey.length - 1;
            html += formatSite(row, i + 1, isStart, isCommonRoot, isDestination);
        });
        html += '</div>';

        journeyInfo.innerHTML = html;
    }

    // Clear existing content and append the journey info and save button.
    pathArea.innerHTML = '';
    pathArea.appendChild(journeyInfo);
    pathArea.appendChild(savePathBtn);
    console.log('done');
}