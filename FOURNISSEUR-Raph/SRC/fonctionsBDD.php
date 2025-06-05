<?php

function enregistreClient($nom,$connex) {
/* ------------------------------------------------
permet d'enregister le client dans  la bdd (insert)
	paramètres d'entrée
		- 1er paramètre $nom : contient le nom du client
		- 2ème paramètre $connex : contient le connecteur de la bdd
	retourne l'identifiant qui a été choisi par le sgbd lors de l'insertion
*/
  $sql="INSERT INTO CLIENTS (NomClient) VALUES ('".$nom."') RETURNING idclient";    // déclaration de la variable appelée $sql.
  $res=$connex->query($sql);				// demande d'execution de la requête sur la base via le connecteur $connex. Le resultat est dans la variable $res au format structuré PDO
  $lire = $res->fetchColumn(); 		// récupération de la valeur l'élément RETURNING contenu dans $res
  return $lire;							// retourne l'identifiant choisi par le sgbd
}

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

function EnregistreNouvelArticle($LeNomArticle, $lePrix, $connex) {
  $sql="INSERT INTO ARTICLES (designation, prixvente) VALUES ('".$LeNomArticle."','".$lePrix."') RETURNING idarticle";
  $res=$connex->query($sql);
  $lire = $res->fetchColumn();
  return $lire;
}

function ListerArticles($connex) {
  $sql = "SELECT * FROM ARTICLES";
  $res = $connex->query($sql);
  return $res;
}

function RechercheArticle($tarif, $connex) {
  $sql = "SELECT designation, infosuppl, prixvente FROM ARTICLES WHERE prixvente >".$tarif;
  $resultat = $connex->query($sql);
  $res = $resultat->fetchAll();
  return $res;
}

function EnregistreCommande($LaDate, $leNom, $connex) {
  $sql="INSERT INTO COMMANDES (datec, refclient) VALUES ('".$LaDate."','".$leNom."') RETURNING idcommande";
  print($sql);
  $res=$connex->query($sql);
  $lire = $res->fetchColumn();
  return $lire;
}

function ListerIDCommande($connex) {
  $sql = "SELECT IdCommande FROM COMMANDES";
  $res = $connex->query($sql);
  return $res;
}

function ListerContenir($connex) {
    $sql = "SELECT * FROM CONTENIR";
    print($sql);
    $res = $connex->query($sql);
    return $res;
}

function EnregistreContenu($commande, $article, $quantite, $connex) {
  $sql="INSERT INTO CONTENIR (idrefcommande, idrefarticle, qtecommandee) VALUES (".$commande.",".$article.",".$quantite.") RETURNING idrefcommande";
  print $sql;
  $res=$connex->query($sql);
  $lire = $res->fetchColumn();
  return $lire;
}



?>
