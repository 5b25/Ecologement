from fastapi import FastAPI, HTTPException, Request
from pydantic import BaseModel
import sqlite3
from interface import ui
from weather import meteo
import json

app = FastAPI()

def get(Nom_table: str):
    try:
        # Assurez-vous que le nom de la table est valide et ne contient pas de caractères malveillants
        if not Nom_table.isidentifier():
            raise HTTPException(status_code=400, detail="Nom de table non valide")
        
        conn = sqlite3.connect('logement.db')  # Connexion à la base de données locale
        conn.row_factory = sqlite3.Row
        c = conn.cursor()
        
        # Insérer le nom de la table de manière sécurisée
        query = f"SELECT * FROM {Nom_table}"
        c.execute(query)

        rows = c.fetchall()     
        conn.close()

        return rows
    
    except sqlite3.Error as e:
        raise HTTPException(status_code=500, detail=f"Erreur de la base de données: {e}")

def getfacture(ID: int):
    try:
        conn = sqlite3.connect('logement.db')  # Connexion à la base de données locale
        conn.row_factory = sqlite3.Row
        c = conn.cursor()

        # Requête pour calculer la somme de VALEUR pour les trois types
        query = f'''
        SELECT 
            TYPE_CONSOMMEE, 
            SUM(VALEUR) AS total_valeur 
        FROM FACTURE 
        WHERE LOGEMENT_ID = ? 
        AND TYPE_CONSOMMEE IN ('eau', 'electricite', 'dechets')
        GROUP BY TYPE_CONSOMMEE
        '''
        c.execute(query, (ID,))
        rows = c.fetchall()
        conn.close()

        # Convertir le résultat de la requête en un format adapté au graphique
        if not rows:
            raise HTTPException(status_code=404, detail="Aucune donnée trouvée pour cet ID")
        
        data = {row["TYPE_CONSOMMEE"]: row["total_valeur"] for row in rows}
        return data
    except sqlite3.Error as e:
        raise HTTPException(status_code=500, detail=f"Erreur de la base de données: {e}")
    
# Pour verifier est-ce que le CA a été déjà ajouté.
def getCAID(IP: str):
    try:
        # Utiliser le gestionnaire de contexte pour la connexion
        with sqlite3.connect('logement.db') as conn:
            conn.row_factory = sqlite3.Row
            c = conn.cursor()
            
            # Requête pour obtenir l'ID correspondant à l'adresse IP donnée
            query = '''
            SELECT 
                ID
            FROM CAPTUREACTIONNEUR 
            WHERE IP = ?
            '''
            c.execute(query, (IP,))
            row = c.fetchone()  # Utilisation de fetchone pour obtenir la première correspondance
            
        # Vérifier si le résultat est vide
        if row is None:
            raise HTTPException(status_code=471, detail=f"Le CA avec IP {IP} n'est pas ajouté dans la base de données")

        # Retourner l'ID dans un format JSON
        return row["ID"]

    except sqlite3.Error as e:
        raise HTTPException(status_code=500, detail=f"Erreur de la base de données: {e}")


def add(Nom_table: str, parametre):
    try:
        conn = sqlite3.connect('logement.db')
        conn.row_factory = sqlite3.Row
        c = conn.cursor()
        
        if Nom_table == "MESURE":
            c.execute("INSERT INTO MESURE(VALEUR, CA_ID) VALUES(?, ?)", parametre)
        
        elif Nom_table == "FACTURE":
            c.execute("INSERT INTO FACTURE(TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID) VALUES(?, ?, ?, ?)", parametre)
        
        elif Nom_table == "CA":
            c.execute("INSERT INTO CAPTUREACTIONNEUR(IP, COMMERCIALE, LIEU, PORT_COMMUNI, PIECE_ID, TYPE_ID) VALUES(?, ?, ?, ?, ?, ?)", parametre)

        conn.commit()
        conn.close()
        return {"message": f"{Nom_table} avec la valeur {parametre} est ajouté avec succès"}
    
    except sqlite3.Error as e:
        raise HTTPException(status_code=500, detail=f"Erreur de la base de données: {e}")

# valeur1 est VALEUR; valeur2 est ID de CAPTUREACTIONNEUR
@app.post("/addmesureESP/")
async def add_mesure_esp(request: Request):
    try:
        # Recevoir les données JSON de l'ESP8266
        json_data = await request.json()

        # Extraire VALEUR et CA_ID depuis les données JSON
        if "Humidity" in json_data:
            VALEUR = json_data["Humidity"]
            CA_ID = json_data["CA_ID"]
        elif "Temperature" in json_data:
            VALEUR = json_data["Temperature"]
            CA_ID = json_data["CA_ID"]
        else:
            raise HTTPException(status_code=400, detail="Les données JSON doivent contenir soit 'humidity' soit 'Temperature'.")

        # Ajouter les données à la table MESURE
        temp_parametre_mesure = (int(VALEUR), int(CA_ID))
        return add("MESURE", temp_parametre_mesure)

    except json.JSONDecodeError:
        raise HTTPException(status_code=400, detail="Erreur dans le format des données JSON.")

    except KeyError as e:
        raise HTTPException(status_code=400, detail=f"Clé manquante dans les données JSON: {str(e)}")

# valeur1 est VALEUR; valeur2 est ID de CAPTUREACTIONNEUR
@app.post("/addmesure/{VALEUR}/{CA_ID}")
async def add_mesure(VALEUR: int, CA_ID: int): 
    temp_parametre_mesure = (VALEUR, CA_ID)
    return add("MESURE", temp_parametre_mesure)


# TYPE_CONSOMMEE sont 'eau', 'electricite' , 'dechets'
@app.post("/addfacture/{TYPE_CONSOMMEE}/{MONTANT}/{VALEUR}/{LOGEMENT_ID}")
async def add_facture(TYPE_CONSOMMEE: str, MONTANT: int, VALEUR: int, LOGEMENT_ID: int):
    # Créer un tuple de paramètres et appeler la fonction `add`
    temp_parametre_facture = (TYPE_CONSOMMEE, MONTANT, VALEUR, LOGEMENT_ID)
    return add("FACTURE", temp_parametre_facture)


#INSERT INTO CAPTUREACTIONNEUR(IP, COMMERCIALE, LIEU, PORT_COMMUNI, PIECE_ID, TYPE_ID) 
#    VALUES('10.192.168.21', 'Marque_A', 'Sur tableu', '1883', '1', '1');
# valeur1 est VALEUR; valeur2 est ID de CAPTUREACTIONNEUR
@app.post("/addcapture/{IP}/{COMMERCIALE}/{LIEU}/{PORT_COMMUNI}/{PIECE_ID}/{TYPE_ID}")
async def add_capture(IP: str, COMMERCIALE: str, LIEU: str, PORT_COMMUNI: int, PIECE_ID: int, TYPE_ID: int): 
    CA = (IP, COMMERCIALE, LIEU, PORT_COMMUNI, PIECE_ID, TYPE_ID)
    return add("CA", CA)

@app.get("/getCAID/{CA_IP}")
async def get_CAID(CA_IP:str):
    return getCAID(CA_IP)

@app.get("/getmesure/")
async def get_mesure():
    return get("MESURE")

@app.get("/getfacture/")
async def get_facture():
    return get("FACTURE")

@app.get("/")
async def root():
    return {"message": "Accès réussi"}

@app.get("/getchart/{logement_id}")
async def generate_chart(logement_id: int):
    data = getfacture(logement_id)
    ui(data)
    return {"message": "La page HTML a été générée et ouverte dans le navigateur."}

@app.get("/meteo/")
async def get_weather_data():
    try:
        daily_dataframe, current_temperature = meteo()  # Utiliser la function du meteo
        # 转换 Pandas 数据框为 JSON 格式
        daily_data_json = daily_dataframe.to_dict(orient="records")
        current_temperature_json = {"current_temperature": current_temperature}
        return daily_data_json, current_temperature_json
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Erreur lors de l'appel météo: {e}")
    
    
@app.get("/currentmeteo/")
async def get_weather_data():
    try:
        _, current_temperature = meteo()  # Utiliser la function du meteo
        # 转换 Pandas 数据框为 JSON 格式
        return current_temperature
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Erreur lors de l'appel météo: {e}")