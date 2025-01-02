INSERT INTO LOGEMENT(NOM, IP, TELEPHONE) VALUES("Place d'Italic", '10.20.20.10', '0780001111');
    -- La premier pièce
    INSERT INTO PIECE(IP, ADRESSE_X, ADRESSE_Y, ADRESSE_Z, LOGEMENT_ID, LOGEMENT_NOM) 
            --VALUES('10.192.168.23', '0', '0', '1', 
            --(SELECT ID FROM LOGEMENT WHERE NOM = "Place d'Italic") as LOGEMENT_ID)
            SELECT
                ('10.20.20.11') as IP,
                ('0') as ADRESSE_X, 
                ('0') as ADRESSE_Y, 
                ('1') as ADRESSE_Z,
                (SELECT ID FROM LOGEMENT WHERE NOM = "Place d'Italic") as LOGEMENT_ID,
                (SELECT NOM FROM LOGEMENT WHERE NOM = "Place d'Italic") as LOGEMENT_NOM;

    -- La deuxième pièce
    INSERT INTO PIECE(IP, ADRESSE_X, ADRESSE_Y, ADRESSE_Z, LOGEMENT_ID, LOGEMENT_NOM) 
            SELECT
                ('10.20.20.12') as IP,
                ('0') as ADRESSE_X, 
                ('1') as ADRESSE_Y, 
                ('0') as ADRESSE_Z,
                (SELECT ID FROM LOGEMENT WHERE NOM = "Place d'Italic") as LOGEMENT_ID,
                (SELECT NOM FROM LOGEMENT WHERE NOM = "Place d'Italic") as LOGEMENT_NOM;

    -- La troisième pièce
    INSERT INTO PIECE(IP, ADRESSE_X, ADRESSE_Y, ADRESSE_Z, LOGEMENT_ID, LOGEMENT_NOM) 
            SELECT
                ('10.20.20.13') as IP,
                ('0') as ADRESSE_X, 
                ('1') as ADRESSE_Y, 
                ('1') as ADRESSE_Z,
                (SELECT ID FROM LOGEMENT WHERE NOM = "Place d'Italic") as LOGEMENT_ID,
                (SELECT NOM FROM LOGEMENT WHERE NOM = "Place d'Italic") as LOGEMENT_NOM;

    -- La quartième pièce
    INSERT INTO PIECE(IP, ADRESSE_X, ADRESSE_Y, ADRESSE_Z, LOGEMENT_ID, LOGEMENT_NOM) 
            SELECT
                ('10.20.20.14') as IP,
                ('1') as ADRESSE_X, 
                ('0') as ADRESSE_Y, 
                ('0') as ADRESSE_Z,
                (SELECT ID FROM LOGEMENT WHERE NOM = "Place d'Italic") as LOGEMENT_ID,
                (SELECT NOM FROM LOGEMENT WHERE NOM = "Place d'Italic") as LOGEMENT_NOM;


-- Créer les capteurs/actionneurs
INSERT INTO CAPTUREACTIONNEUR(IP, COMMERCIALE, LIEU, PORT_COMMUNI, PIECE_ID, TYPE_ID) 
    VALUES('10.20.20.21', 'Température', 'Sur tableu', '1883', '1', '1');
INSERT INTO CAPTUREACTIONNEUR(IP, COMMERCIALE, LIEU, PORT_COMMUNI, PIECE_ID, TYPE_ID) 
    VALUES('10.20.20.22', 'Humidité', 'Sur tableu', '1883', '1', '2');