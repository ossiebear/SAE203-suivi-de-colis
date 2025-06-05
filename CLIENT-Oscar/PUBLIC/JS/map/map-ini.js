// Oscar Collins 2025
// SAE203 groupe A2
// AI usage: Written with much Ai help, some functions copied from old project (SAE105), renderPathInfo() function fully AI
//           comments written by AI. GPT4.1-mini

// MAP -----------------------------------------------------------------------------

// Initialize the Leaflet map centered on France with zoom level 6.
var map = L.map('map').setView([46.603354, 1.888334], 6);

// Add OpenStreetMap tile layer with attribution and max zoom level.
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap'
}).addTo(map);
//----------------------------------------------------------------------------------


