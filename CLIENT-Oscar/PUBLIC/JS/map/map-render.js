// Arrays to store markers and polylines so they can be removed before new simulation.
let markers = [];
let polylines = [];

function renderPathToMap(journey) {
    // Remove existing markers and polylines from the map before rendering new ones
    if (Array.isArray(markers)) {
        markers.forEach(m => map.removeLayer(m));
        markers = [];
    }
    if (Array.isArray(polylines)) {
        polylines.forEach(l => map.removeLayer(l));
        polylines = [];
    }

    // Helper function to extract latitude and longitude from a data row.
    function extractLatLong(row) {
        return [parseFloat(row['latitude']), parseFloat(row['longitude'])];
    }

    console.log('Creating polyline for journey');
    const journeyLatLngs = journey.map(extractLatLong);
    const journeyLine = L.polyline(journeyLatLngs, {color: 'blue', weight: 4}).addTo(map);
    polylines.push(journeyLine);

    // Add markers for each node in the journey with color coding.
    journey.forEach((row, i) => {
        // Default marker color is red.
        let markerColor = 'red';

        // Start node marker is blue.
        if (i === 0) markerColor = 'blue';

        // Finish node marker is green.
        else if (i === journey.length - 1) markerColor = 'green';

        // Middle node (common root) marker is violet.
        if (i === Math.floor(journey.length / 2)) markerColor = 'violet';

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
    console.log('Path rendered to map successfully');
}

