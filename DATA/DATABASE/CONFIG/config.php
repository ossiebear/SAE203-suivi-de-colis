<?php
// Remember to .gitignore this file
// Credentials and info for signing into srv-peda postgres server.
$lehost = '10.100.100.189'; //using IP instead of 'srv-peda-new' because i cant access school's DNS when working from home.
$leport = 5432;
$dbname = 'rta2';
$user = 'rta2';
$pass = 'Maurice';


/* Expected DB strucuture:

CREATE TABLE "post_offices" (
    identifiant_a character varying NOT NULL PRIMARY KEY,
    libelle_du_site character varying,
    caracteristique_du_site character varying,
    site_acores_de_rattachement character varying,
    adresse text,
    complement_d_adresse text,
    lieu_dit character varying,
    code_postal character varying,
    localite character varying,
    code_insee character varying,
    pays character varying,
    latitude double precision,
    longitude double precision,
    numero_de_telephone character varying
);

CREATE SEQUENCE "Packages_ID_seq";

CREATE TABLE "Packages" (
    "ID" integer NOT NULL DEFAULT nextval('"Packages_ID_seq"'::regclass) PRIMARY KEY,
    "trackingNumber" character varying(12) NOT NULL DEFAULT '1',
    "path" jsonb NOT NULL
);

*/