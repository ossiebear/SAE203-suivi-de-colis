// Oscar Collins 2025
// SAE203 groupe A2
// AI usage: Written with much Ai help, some functions copied from old project (SAE105), renderPathInfo() function fully AI
//           comments written by AI. GPT4.1-mini

// MAP -----------------------------------------------------------------------------

// Initialize map variable
var map;

// Function to initialize the map when needed
function initializeMap() {
    if (!map) {
        // Initialize the Leaflet map centered on France with zoom level 6.
        map = L.map('map').setView([46.603354, 1.888334], 6);

        // Add OpenStreetMap tile layer with attribution and max zoom level.
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);
        
        console.log('🗺️ Map initialized with center: [46.603354, 1.888334], zoom: 6');
    }
    return map;
}

// Function to ensure map is properly sized when container becomes visible
function ensureMapSize() {
    if (map) {
        // Force Leaflet to recalculate map size
        setTimeout(() => {
            map.invalidateSize();
            // Reset to France view
            map.setView([46.603354, 1.888334], 6);
            console.log('🔄 Map size refreshed and reset to France view');
        }, 100);
    }
}
//----------------------------------------------------------------------------------


