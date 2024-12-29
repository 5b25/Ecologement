-- Détruire toutes les tables éventuellement présentes dans la base chaque fois
DROP TABLE IF EXISTS LOGEMENT;
DROP TABLE IF EXISTS FACTURE;
DROP TABLE IF EXISTS PIECE;
DROP TABLE IF EXISTS CAPTUREACTIONNEUR;
DROP TABLE IF EXISTS TYPE_CA;
DROP TABLE IF EXISTS MESURE;

-- Créer un tableau pour le logement
CREATE TABLE LOGEMENT(
    ID              INTEGER     PRIMARY KEY     AUTOINCREMENT,
    NOM             TEXT                        NOT NULL,
    IP              TEXT                        NOT NULL,
    TELEPHONE       TEXT                        NOT NULL,
    DATE_CREATION   TIMESTAMP   DEFAULT         CURRENT_TIMESTAMP
);

-- Créer un tableau pour le Facteur d'Alimentent
CREATE TABLE FACTURE(
    ID              INTEGER         PRIMARY KEY     AUTOINCREMENT,
    TYPE_CONSOMMEE  TEXT                            NOT NULL,
    MONTANT         INTEGER                         NOT NULL,
    VALEUR          INTEGER                         NOT NULL,
    DATE_CREATION   TIMESTAMP       DEFAULT         CURRENT_TIMESTAMP,
    LOGEMENT_ID     INTEGER,
    FOREIGN KEY (LOGEMENT_ID)       REFERENCES      LOGEMENT(ID)
);

-- Créer un tableau pour les pièces
CREATE TABLE PIECE(
    ID              INTEGER         PRIMARY KEY     AUTOINCREMENT,
    IP              TEXT                            NOT NULL,
    ADRESSE_X       INTEGER                         NOT NULL,
    ADRESSE_Y       INTEGER                         NOT NULL,
    ADRESSE_Z       INTEGER                         NOT NULL,
    LOGEMENT_ID     INTEGER                         NOT NULL,
    LOGEMENT_NOM    TEXT                            NOT NULL,
    FOREIGN KEY (LOGEMENT_ID)       REFERENCES      LOGEMENT(ID),
    FOREIGN KEY (LOGEMENT_NOM)      REFERENCES      LOGEMENT(NOM)
);

-- Créer un tableau pour le capture ou actionneur
CREATE TABLE CAPTUREACTIONNEUR(
    ID              INTEGER         PRIMARY KEY     AUTOINCREMENT,
    IP              TEXT                            NOT NULL,
    COMMERCIALE     TEXT,
    LIEU            TEXT,
    PORT_COMMUNI    INTEGER,
    DATE_CREATION   TIMESTAMP       DEFAULT         CURRENT_TIMESTAMP,
    PIECE_ID        INTEGER,
    TYPE_ID         INTEGER,
    FOREIGN KEY (PIECE_ID)          REFERENCES      PIECE(ID),
    FOREIGN KEY (TYPE_ID)           REFERENCES      TYPE_CA(ID)
);

-- Créer un tableau pour le typr de CA
CREATE TABLE TYPE_CA(
    ID              INTEGER         PRIMARY KEY     AUTOINCREMENT,
    NOM             TEXT,
    UNITE           TEXT,
    PLAGE           TEXT
);

-- Créer un tableau pour les données du musure
CREATE TABLE MESURE(
    ID              INTEGER         PRIMARY KEY     AUTOINCREMENT,
    VALEUR          INTEGER                         NOT NULL,
    DATE_CREATION   TIMESTAMP       DEFAULT         CURRENT_TIMESTAMP,
    CA_ID           INTEGER                         NOT NULL,
    FOREIGN KEY (CA_ID)             REFERENCES      CAPTUREACTIONNEUR(ID)
);

-- Créer un donnée pour un logement avec 4 pièces
INSERT INTO LOGEMENT(NOM, IP, TELEPHONE) VALUES('Créteil', '10.192.168.10', '0780001100');
    -- La premier pièce
    INSERT INTO PIECE(IP, ADRESSE_X, ADRESSE_Y, ADRESSE_Z, LOGEMENT_ID, LOGEMENT_NOM) 
            --VALUES('10.192.168.23', '0', '0', '1', 
            --(SELECT ID FROM LOGEMENT WHERE NOM = "Créteil") as LOGEMENT_ID)
            SELECT
                ('10.192.168.11') as IP,
                ('0') as ADRESSE_X, 
                ('0') as ADRESSE_Y, 
                ('1') as ADRESSE_Z,
                (SELECT ID FROM LOGEMENT WHERE NOM = "Créteil") as LOGEMENT_ID,
                (SELECT NOM FROM LOGEMENT WHERE NOM = "Créteil") as LOGEMENT_NOM;

    -- La deuxième pièce
    INSERT INTO PIECE(IP, ADRESSE_X, ADRESSE_Y, ADRESSE_Z, LOGEMENT_ID, LOGEMENT_NOM) 
            SELECT
                ('10.192.168.12') as IP,
                ('0') as ADRESSE_X, 
                ('1') as ADRESSE_Y, 
                ('0') as ADRESSE_Z,
                (SELECT ID FROM LOGEMENT WHERE NOM = "Créteil") as LOGEMENT_ID,
                (SELECT NOM FROM LOGEMENT WHERE NOM = "Créteil") as LOGEMENT_NOM;

    -- La troisième pièce
    INSERT INTO PIECE(IP, ADRESSE_X, ADRESSE_Y, ADRESSE_Z, LOGEMENT_ID, LOGEMENT_NOM) 
            SELECT
                ('10.192.168.13') as IP,
                ('0') as ADRESSE_X, 
                ('1') as ADRESSE_Y, 
                ('1') as ADRESSE_Z,
                (SELECT ID FROM LOGEMENT WHERE NOM = "Créteil") as LOGEMENT_ID,
                (SELECT NOM FROM LOGEMENT WHERE NOM = "Créteil") as LOGEMENT_NOM;

    -- La quartième pièce
    INSERT INTO PIECE(IP, ADRESSE_X, ADRESSE_Y, ADRESSE_Z, LOGEMENT_ID, LOGEMENT_NOM) 
            SELECT
                ('10.192.168.14') as IP,
                ('1') as ADRESSE_X, 
                ('0') as ADRESSE_Y, 
                ('0') as ADRESSE_Z,
                (SELECT ID FROM LOGEMENT WHERE NOM = "Créteil") as LOGEMENT_ID,
                (SELECT NOM FROM LOGEMENT WHERE NOM = "Créteil") as LOGEMENT_NOM;

-- Créer les types de capteurs/actionneurs
INSERT INTO TYPE_CA(NOM, UNITE, PLAGE) VALUES('Temprerature', '℃', '-20~45');
INSERT INTO TYPE_CA(NOM, UNITE, PLAGE) VALUES('Humidite', '%', '0~100');
INSERT INTO TYPE_CA(NOM, UNITE, PLAGE) VALUES('Bruit', 'dB', '0~100');
INSERT INTO TYPE_CA(NOM, UNITE, PLAGE) VALUES('Rideaux', '%', '0~100');

-- Créer les capteurs/actionneurs
INSERT INTO CAPTUREACTIONNEUR(IP, COMMERCIALE, LIEU, PORT_COMMUNI, PIECE_ID, TYPE_ID) 
    VALUES('10.192.168.21', 'Marque_A', 'Sur tableu', '1883', '1', '1');
INSERT INTO CAPTUREACTIONNEUR(IP, COMMERCIALE, LIEU, PORT_COMMUNI, PIECE_ID, TYPE_ID) 
    VALUES('10.192.168.22', 'Marque_A', 'Sur tableu', '1883', '1', '2');

-- Créer les mesures par capteur/actionneur
INSERT INTO MESURE(VALEUR, CA_ID) VALUES('23', '1');
INSERT INTO MESURE(VALEUR, CA_ID) VALUES('45', '2');

-- Créer les données pour les factures
INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES('eau', '2', '4', '1');
INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES('eau', '1', '2', '1');
INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES('electricite', '1', '2', '1');
INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES('dechets', '4', '12', '1');

INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES('eau', '1', '2', '2');
INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES('eau', '2', '4', '2');
INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES('electricite', '2', '3', '2');
INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES('dechets', '4', '8', '2');