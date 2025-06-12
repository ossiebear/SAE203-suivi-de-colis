<?php
require_once 'fonctionsConnexion.php';
require_once 'fonctionsBDD.php';
require_once '../../../DATA/DATABASE/CONFIG/config.php';

$conn=connexionBDD('../../../DATA/DATABASE/CONFIG/config.php');

$clientName = $_POST["shopName"];
$clientFirstname = $_POST["shopFirstname"];
$emailAddressClient = $_POST["emailAddress"];
$phoneNumberClient = $_POST["phoneNumber"];
$passwordEncryptClient = $_POST["password"];
$destinationAddress = $_POST["destinationAddress"];
?>