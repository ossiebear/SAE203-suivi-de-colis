document.getElementById('searchForm').addEventListener('submit', function(event) {
    event.preventDefault();
    var nomClient = document.getElementById('name').value;

    fetch('recherche.php?name=' + encodeURIComponent(nomClient))
        .then(response => response.text())
        .then(data => {
            document.getElementById('searchResults').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('searchResults').innerHTML = '<div class="error">Erreur : ' + error + '</div>';
        });
});