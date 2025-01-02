import sqlite3

conn = sqlite3.connect('logement.db')
conn.row_factory = sqlite3.Row
c = conn.cursor()

logement = "Créteil"  # 或 "Place d'Italic"
query = "SELECT * FROM LOGEMENT WHERE NOM = ?"
c.execute(query, (logement,))
result = c.fetchall()

if result:
    print("Matched rows:", [dict(row) for row in result])
else:
    print("No matching rows found for logement:", logement)

conn.close()