<?php

function ListerClients($connex) {
    /*--------------------------------
    récupère les clients à partir de la base de données
    paramètres d'entrées :
        $connex : connecteur de la base de données
    retourne la liste des clients
    -----------------------------*/
      $sql="SELECT * FROM CLIENTS";       // déclaration de la variable appelee $sql.
      $res=$connex->query($sql); 				// execution de la requête. Le resultat est dans la variable $res.
      return $res;								// retourne a l'appelant le resultat.
    }


    function EnregistreClient($leNom, $connex) {
        $sql="INSERT INTO COMMANDES (refclient) VALUES ('".$leNom."') RETURNING idcommande";
        print($sql);
        $res=$connex->query($sql);
        $lire = $res->fetchColumn();
        return $lire;
      }




?>