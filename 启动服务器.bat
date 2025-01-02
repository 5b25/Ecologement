@echo off
echo Démarrage du serveur Uvicorn...
uvicorn serveur:app --host 0.0.0.0 --port 8000 --reload --reload-dir .
pause