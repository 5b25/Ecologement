import sqlite3, random

## ouverture/initialisation de la base de donnee 
conn = sqlite3.connect('logement.db')
conn.row_factory = sqlite3.Row
c = conn.cursor()

## Exécution de la requête INSERT
# logement  (Pour prévenir les injections SQL et gérer automatiquement les caractères spéciaux)
c.execute("INSERT INTO LOGEMENT (NOM, IP, TELEPHONE) VALUES (?, ?, ?)", ("Place d'Italic", '10.20.20.10', '0780001111'))
# temperature
c.execute("INSERT INTO MESURE(VALEUR, CA_ID) VALUES(?, ?)", ('20', '1'))
c.execute("INSERT INTO MESURE(VALEUR, CA_ID) VALUES(?, ?)", ('26', '2'))
c.execute("INSERT INTO MESURE(VALEUR, CA_ID) VALUES(?, ?)", ('29', '1'))
c.execute("INSERT INTO MESURE(VALEUR, CA_ID) VALUES(?, ?)", ('32', '2'))
# humidity
c.execute("INSERT INTO MESURE(VALEUR, CA_ID) VALUES(?, ?)", ('45', '1'))
c.execute("INSERT INTO MESURE(VALEUR, CA_ID) VALUES(?, ?)", ('51', '2'))
c.execute("INSERT INTO MESURE(VALEUR, CA_ID) VALUES(?, ?)", ('60', '1'))
c.execute("INSERT INTO MESURE(VALEUR, CA_ID) VALUES(?, ?)", ('72', '2'))
# bruit
c.execute("INSERT INTO MESURE(VALEUR, CA_ID) VALUES(?, ?)", ('43', '1'))
c.execute("INSERT INTO MESURE(VALEUR, CA_ID) VALUES(?, ?)", ('10', '2'))
c.execute("INSERT INTO MESURE(VALEUR, CA_ID) VALUES(?, ?)", ('20', '1'))
c.execute("INSERT INTO MESURE(VALEUR, CA_ID) VALUES(?, ?)", ('27', '2'))
# rideaux
c.execute("INSERT INTO MESURE(VALEUR, CA_ID) VALUES(?, ?)", ('100',   '1'))
c.execute("INSERT INTO MESURE(VALEUR, CA_ID) VALUES(?, ?)", ('0',     '2'))
c.execute("INSERT INTO MESURE(VALEUR, CA_ID) VALUES(?, ?)", ('50',    '1'))
c.execute("INSERT INTO MESURE(VALEUR, CA_ID) VALUES(?, ?)", ('25',    '2'))
# eau
c.execute("INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES(?, ?, ?, ?)", ('eau', '3', '6', '1'))
c.execute("INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES(?, ?, ?, ?)", ('eau', '1', '2', '2'))
c.execute("INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES(?, ?, ?, ?)", ('eau', '4', '7', '1'))
c.execute("INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES(?, ?, ?, ?)", ('eau', '2', '4', '2'))
# électricité
c.execute("INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES(?, ?, ?, ?)", ('electricite', '2', '4', '1'))
c.execute("INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES(?, ?, ?, ?)", ('electricite', '3', '4', '2'))
c.execute("INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES(?, ?, ?, ?)", ('electricite', '2', '5', '2'))
# déchets
c.execute("INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES(?, ?, ?, ?)", ('dechets', '2', '3', '1'))
c.execute("INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES(?, ?, ?, ?)", ('dechets', '3', '5', '1'))
c.execute("INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES(?, ?, ?, ?)", ('dechets', '4', '6', '2'))
c.execute("INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES(?, ?, ?, ?)", ('dechets', '5', '8', '2'))

## fermeture
conn.commit()
conn.close()