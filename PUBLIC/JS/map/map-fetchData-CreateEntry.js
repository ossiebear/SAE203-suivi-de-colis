// GENERATE PATH BUTTON
// We grad data from the input boxes, send that to createpath.php, the retuned data is forwareded to the renderer
//

// Arrays to store markers and polylines so they can be removed before new simulation.
let markers = [];
let polylines = [];

// Add click event listener to the "simulate" button.
document.getElementById('generate-path-btn').addEventListener('click', function() {
    // Remove existing markers and polylines from the map.
    markers.forEach(m => map.removeLayer(m));
    markers = [];
    polylines.forEach(l => map.removeLayer(l));
    polylines = [];

    // Retrieve selected start and destination data.
    const start = selectedData['start'];
    const destination = selectedData['destination'];

    // Validate that both start and destination are selected.
    if (!start || !destination) {
        console.log('Please select both start and destination.');
        return;
    }

    const startId = start[0];
    const finishId = destination[0];




    // Give CreatePath.php the start and finish IDs. The result is returned and rendered
    console.log("Asking CreatePath.php for path for start=", startId, "and finish=", finishId);
    console.time('fetchCreatePath');
    fetch(`../../SRC/CreatePath.php?start=${encodeURIComponent(startId)}&finish=${encodeURIComponent(finishId)}`)
        .then(r => r.json())
        .then(data => {
            console.timeEnd('fetchCreatePath');
            console.log("CreatePath replied with journey: ", data);
            // Validate the returned journey data.
            if (!data.journey || !Array.isArray(data.journey) || data.journey.length === 0) {
                alert('No journey data returned.');
                return;
            }

            // Helper function to extract latitude and longitude from a data row.
            function extractLatLong(row) {
                return [parseFloat(row['latitude']), parseFloat(row['longitude'])];
            }

            // Create a polyline representing the full journey and add it to the map.
            console.log('Rendering data on map');
            const journeyLatLngs = data.journey.map(extractLatLong);
            const journeyLine = L.polyline(journeyLatLngs, {color: 'blue', weight: 4}).addTo(map);
            polylines.push(journeyLine);

            // Add markers for each node in the journey with color coding.
            data.journey.forEach((row, i) => {
                // Default marker color is red.
                let markerColor = 'red';

                // Start node marker is blue.
                if (i === 0) markerColor = 'blue';

                // Finish node marker is green.
                else if (i === data.journey.length - 1) markerColor = 'green';

                // Middle node (common root) marker is violet.
                if (i === Math.floor(data.journey.length / 2)) markerColor = 'violet';

                // Create a Leaflet marker with a colored icon.
                const marker = L.marker(extractLatLong(row), {
                    icon: L.icon({
                        iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-${markerColor}.png`,
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                }).addTo(map)
                .bindPopup(`<b>Step ${i + 1}</b><br>${row['libelle_du_site']}`);

                // Store the marker for later removal.
                markers.push(marker);
            });

            // Adjust the map view to fit all journey points.
            map.fitBounds(L.latLngBounds(journeyLatLngs));

            // Render textual journey information below the map.
            renderPathInfo(data.journey);
        });
});
//----------------------------------------------------------------------------------

