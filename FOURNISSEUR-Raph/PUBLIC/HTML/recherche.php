<?php
    require_once '../../SRC/fonctionsConnexion.php';
    require_once '../../SRC/fonctionsBDD.php';
    require_once '../../SRC/fonctionSys.php';

    $conn1 = connexionBDD('../../DATA/config.php');

    if (isset($_GET["name"])) {
        $nomClient = filtreChaine($_GET["name"]);
        $resultats = [];

        if (!empty($nomClient)) {
            $resultats = rechercheCommandesParNom($conn1, $nomClient);
        } else {
            $resultats = toutesLesCommandes($conn1);
        }

        if (!empty($resultats)) {
            echo '<table class="results-table">';
            echo '<thead>';
            echo '<tr><th>Numéro Commande</th><th>Client</th><th>Date</th></tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach ($resultats as $commande) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($commande["num_commande"], ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . htmlspecialchars($commande["nom_client"], ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td>' . htmlspecialchars($commande["date_commande"], ENT_QUOTES, 'UTF-8') . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            echo '<p><strong>' . count($resultats) . '</strong> commande(s) trouvée(s)</p>';
        } else {
            echo '<div class="no-results">Aucune commande trouvée pour "' . htmlspecialchars($nomClient, ENT_QUOTES, 'UTF-8') . '"</div>';
        }
    }

    deconnexionBDD($conn1);
?>