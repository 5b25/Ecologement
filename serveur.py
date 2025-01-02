from fastapi import FastAPI, HTTPException, Query, Request
from pydantic import BaseModel
import sqlite3
from interface import ui
from weather import meteo
import json
from typing import Optional
from urllib.parse import unquote
from fastapi.responses import JSONResponse
import pandas as pd


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
        WHERE LOGEMENT_ID IN (
            SELECT ID FROM LOGEMENT WHERE NOM = ?
        )
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
    
def getfactureDetail(type: str = None, logement: str = None, start_date: str = None, end_date: str = None):
    try:
        conn = sqlite3.connect('logement.db')
        conn.row_factory = sqlite3.Row
        c = conn.cursor()

        # 构造 SQL 查询
        query = '''
        SELECT 
            TYPE_CONSOMMEE, 
            SUM(VALEUR) AS total_valeur 
        FROM FACTURE 
        WHERE 1=1
        '''
        params = []
        if logement:
            query += " AND LOGEMENT_ID IN (SELECT ID FROM LOGEMENT WHERE NOM = ?)"
            params.append(logement)
        if type:
            query += " AND TYPE_CONSOMMEE = ?"
            params.append(type)
        if start_date:
            query += " AND DATE_CREATION >= ?"
            params.append(start_date)
        if end_date:
            query += " AND DATE_CREATION <= ?"
            params.append(end_date)
        query += " GROUP BY TYPE_CONSOMMEE"

        print("Executing query:", query)  # 调试日志
        print("Parameters:", params)  # 调试日志

        c.execute(query, params)
        rows = c.fetchall()
        conn.close()

        # 转换结果为 JSON 格式
        if not rows:
            return {}

        data = {row["TYPE_CONSOMMEE"]: row["total_valeur"] for row in rows}
        return data

    except sqlite3.Error as e:
        raise HTTPException(status_code=500, detail=f"Erreur de la base de données: {e}")
    
@app.get("/getmesureDetail/")
async def get_mesureDetail(logement: Optional[str] = Query(None)):
    return getmesureDetail(logement=logement)

def getmesureDetail(logement: Optional[str] = None):
    try:
        conn = sqlite3.connect('logement.db')
        conn.row_factory = sqlite3.Row
        c = conn.cursor()

        query = '''
        SELECT m.ID AS MESURE_ID, 
               m.VALEUR, 
               m.DATE_CREATION, 
               c.ID AS CA_ID, 
               c.IP AS CA_IP, 
               COALESCE(c.LIEU, '未知位置') AS LIEU, 
               p.ID AS PIECE_ID, 
               COALESCE(p.IP, '未知房间') AS PIECE_NAME, 
               l.ID AS LOGEMENT_ID, 
               l.NOM AS LOGEMENT_NAME,
               COALESCE(t.NOM, '未知类型') AS DATA_TYPE
        FROM MESURE m
        JOIN CAPTUREACTIONNEUR c ON m.CA_ID = c.ID
        JOIN PIECE p ON c.PIECE_ID = p.ID
        JOIN LOGEMENT l ON p.LOGEMENT_ID = l.ID
        JOIN TYPE_CA t ON c.TYPE_ID = t.ID
        '''

        params = []
        if logement:
            query += " WHERE l.NOM = ?"
            params.append(logement)

        c.execute(query, params)
        rows = c.fetchall()
        conn.close()

        data = [dict(row) for row in rows] if rows else []
        return data
    except sqlite3.Error as e:
        raise HTTPException(status_code=500, detail=f"Erreur de la base de données: {e}")


# Pour verifier est-ce que le CA a été déjà ajouté.
def getCAID(IP: str):
    try:
        print(f"Debug: Searching for IP: {IP}")
        with sqlite3.connect('logement.db') as conn:
            conn.row_factory = sqlite3.Row
            c = conn.cursor()
            query = '''
            SELECT 
                ID
            FROM CAPTUREACTIONNEUR 
            WHERE IP = ?
            '''
            print(f"Debug: Executing query: {query} with IP: {IP}")
            c.execute(query, (IP,))
            row = c.fetchone()

            if row is None:
                print(f"Debug: No record found for IP: {IP}")
                raise HTTPException(status_code=471, detail=f"Le CA avec IP {IP} n'est pas ajouté dans la base de données")
            
            print(f"Debug: Query result: {row['ID']}")
            return {"CA_ID": row["ID"]}

    except sqlite3.Error as e:
        print(f"Debug: Database error: {str(e)}")
        raise HTTPException(status_code=500, detail=f"Erreur de la base de données: {str(e)}")


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
async def get_CAID(CA_IP: str):
        return getCAID(CA_IP)


@app.get("/getmesure/")
async def get_mesure():
    return get("MESURE")

@app.get("/getmesureDetail/")
async def get_mesureDetail(logement: Optional[str] = Query(None)):
    return getmesureDetail(logement=logement)

@app.get("/getfacture/")
async def get_facture():
    return get("FACTURE")

@app.get("/getfactureDetail/")
async def get_factureDetail(type: str = None, logement: str = None, start_date: str = None, end_date: str = None):
    return getfactureDetail(type=type, logement=logement, start_date=start_date, end_date=end_date)

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
        hourly_dataframe, current_dataframe, daily_dataframe = meteo()

        # 转换日期列为字符串
        if "date" in hourly_dataframe.columns:
            hourly_dataframe["date"] = hourly_dataframe["date"].astype(str)
        if "date" in current_dataframe.columns:
            current_dataframe["date"] = current_dataframe["date"].astype(str)
        if "date" in daily_dataframe.columns:
            daily_dataframe["date"] = daily_dataframe["date"].astype(str)

        # 转换 Pandas 数据框为 JSON 格式
        daily_data_json = daily_dataframe.to_dict(orient="records")
        hourly_data_json = hourly_dataframe.to_dict(orient="records")
        current_data_json = current_dataframe.to_dict(orient="records")
        return JSONResponse(
            content={
                "daily_data": daily_data_json,
                "hourly_data": hourly_data_json,
                "current_temperature": current_data_json,
            },
            status_code=200,
        )
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Erreur lors de l'appel météo: {e}")
    
@app.get("/currentmeteo/")
async def get_current_weather():
    try:
        _, current_dataframe, _ = meteo()
        
        if not isinstance(current_dataframe, pd.DataFrame):
            raise ValueError("Current temperature data is not in DataFrame format.")

        # 将 DataFrame 转换为 JSON 序列化格式
        current_weather_json = current_dataframe.to_dict(orient="records")[0]  # 获取第一条记录
        
        print("Current weather JSON:", current_weather_json)
        return JSONResponse(
            content={"current_temperature": current_weather_json["temperature_2m"]},
            status_code=200,
        )
    except Exception as e:
        print("Error in /currentmeteo/:", e)  # 打印错误日志
        raise HTTPException(status_code=500, detail=f"Erreur lors de l'appel météo: {e}")



@app.get("/hourlymeteo/")
async def get_hourly_weather():
    try:
        hourly_dataframe, _, _ = meteo() 
        hourly_data_json = hourly_dataframe.to_dict(orient="records")
        return JSONResponse(
            content={"hourly_data": hourly_data_json},
            status_code=200,
        )
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Erreur lors de l'appel météo: {e}")


@app.get("/dailymeteo/")
async def get_daily_weather():
    try:
        _, _, daily_dataframe = meteo()
        daily_data_json = daily_dataframe.to_dict(orient="records")
        return JSONResponse(
            content={"daily_data": daily_data_json},
            status_code=200,
        )
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Erreur lors de l'appel météo: {e}")
    
@app.get("/getlogements/")
async def get_logements():
    try:
        conn = sqlite3.connect('logement.db')
        conn.row_factory = sqlite3.Row
        c = conn.cursor()
        c.execute("SELECT NOM FROM LOGEMENT")
        rows = c.fetchall()
        conn.close()
        return [dict(row) for row in rows]
    except sqlite3.Error as e:
        raise HTTPException(status_code=500, detail=f"Erreur de la base de données: {e}")
