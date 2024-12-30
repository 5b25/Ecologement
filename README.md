# Version 0.6

Les fonctions du site n'a pas encore été entièrement implémentée et est en cours d'amélioration.   ——30/12/2024

## Le serveur

### Commande pour démarrer le serveur basé sur FastAPI avec le fichier server.py :
    uvicorn serveur:app --host 0.0.0.0 --port 8000 --reload --reload-dir .

1. On peux le tester sur le site de FastAPI local :  
    http://127.0.0.1:8000/docs

2. On utiliser `curl` en utilisant cmd :

    - **Pour tester si on a réussi à accéder au serveur :**  
        curl -X GET --verbose "http://localhost:8000/"

    - **Pour récupérer les données de mesure :**  
        curl -X GET --verbose "http://localhost:8000/getmesure/"

    - **Pour récupérer les données de facture :**  
        curl -X GET --verbose "http://localhost:8000/getfacture/"

    - **Pour ajouter les données au mesure :**  
        /addmesure/{VALEUR}/{CA_ID}  
        curl -X POST --verbose "http://localhost:8000/addmesure/1/2"

    - **Pour ajouter les données au facture :**  
        L'ordre est 'addfacture/{TYPE_CONSOMMEE}/{MONTANT}/{VALEUR}/{LOGEMENT_ID}'  
        TYPE_CONSOMMEE : 'eau', 'electricite', 'dechets'  
        LOGEMENT_ID : 1 ou 2  
        curl -X POST --verbose "http://localhost:8000/addfacture/electricite/3/5/1"

    - **Pour ajouter les CA :**  
        L'ordre est 'addcapture/{IP}/{COMMERCIALE}/{LIEU}/{PORT_COMMUNI}/{PIECE_ID}/{TYPE_ID}'  
        TYPE_ID : '1' est la température; '2' est l'humidité  
        curl -X POST --verbose "http://localhost:8000/addcapture/192.168.1.3/DHT11/Chambre/1883/1/1"  
        curl -X POST --verbose "http://localhost:8000/addcapture/192.168.1.4/DHT11/Chambre/1883/1/2"

    - **Pour vérifier si les CA ont été bien ajoutés à la base de données :**  
        curl -X GET --verbose "http://localhost:8000/getCAID/192.168.1.3"

    - **Pour afficher la page HTML avec un camembert :**  
        LOGEMENT_ID : 1 ou 2  
        curl -X GET --verbose "http://localhost:8000/getchart/1"

    - **Pour récupérer les données de la météo :**  
        curl -X GET --verbose "http://localhost:8000/meteo/"

    - **Pour récupérer les données actuelles de la météo :**  
        curl -X GET --verbose "http://localhost:8000/currentmeteo/"

---

## Le site

La fonctionnalité de cette page web repose sur deux outils : XAMPP et Composer. XAMPP fournit un environnement avec un serveur Apache et une base de données MySQL, nécessaires pour exécuter les scripts PHP et gérer les interactions avec la base de données. Composer est utilisé pour gérer les dépendances du projet PHP (comme PHPMailer et dotenv). Pour activer les fonctionnalités réseau du programme, veuillez d'abord installer et configurer correctement ces outils.

Après avoir terminé les configurations ci-dessous, utilisez `http://ecologe.local:721/login.php` pour commencer à accéder au site web.


### XAMPP

1. Modifier le fichier de configuration Apache `xampp\apache\conf\extra\httpd-vhosts.conf`, et ajoutez à la fin le contenu suivant :  
    ```
    <VirtualHost *:721>
        ServerAdmin webmaster@ecologe.local
        DocumentRoot "D:/Ecologement/Website"
        ServerName ecologe.local
        ErrorLog "D:/Ecologement/Website/logs/ecologe-error.log"
        CustomLog "D:/Ecologement/Website/logs/ecologe-access.log" common
    </VirtualHost>
    ```

2. Modifier le fichier `xampp\apache\conf\httpd.conf` pour activer le port 721 et le module openssl :  
    - Ajoutez après `#Listen 12.34.56.78:80` la ligne suivante : `Listen 721`  
    - Modifiez `ServerName` pour : `ServerName localhost:721`
    - Assurez-vous que la ligne `LoadModule ssl_module` n'est pas commentée (#).
    - Assurez-vous que la ligne `Include conf/extra/httpd-ssl.conf` n'est pas commentée.

3. Modifier le fichier `xampp\php\php.ini` :
  - Assurez-vous que la ligne `extension=openssl` n'est pas commentée (;).

4. Modifier le fichier `xampp\apache\conf\extra\httpd-ssl.conf` :  
    - Listen 4443  
    - `<VirtualHost _default_:4443>`  
    - ServerName www.example.com:4443

5. Modifier le fichier `hosts` local, et ajoutez à la fin le contenu suivant :  
    ```
    127.0.0.1 ecologe.local
    ```

6. Dans XAMPP, modifiez "Config" en haut à droite dans "Service and Port Settings" :  
    - **Main Port** : 721  
    - **SSL Port** : 4443  

7. Modifier les ports dans le fichier `xampp\mysql\data\my.ini` :  
    - ```
      # password = your_password  
      port=3306  
      socket="D:/xampp/mysql/mysql.sock"
      ```  
    - ```
      [mysqld]  
      port=3306
      ```

8. Modifier le fichier `xampp\phpMyAdmin\config.inc.php`, et ajoutez avant la ligne `?>` les commandes suivantes :  
    - `$cfg['Servers'][$i]['port']= '3306';`  
    Ensuite, après avoir modifié le mot de passe par défaut de MySQL, collez ce mot de passe dans les guillemets simples de la ligne suivante :  
    - `$cfg['Servers'][$i]['password'] = 'votre_mot_de_passe';`

---

### phpMyAdmin

Avant de commencer à utiliser le site, vous devez créer un utilisateur « ecologement » dans phpMyAdmin et configurer correctement ses permissions :


1. Créer un utilisateur `ecologement` :
    - Ouvrez phpMyAdmin via `http://localhost/phpmyadmin`.
    - Cliquez sur **"Utilisateurs"** dans le menu principal.
    - Cliquez sur **"Ajouter un utilisateur"**.
    - Renseignez les informations suivantes :
       - **Nom d'utilisateur** : `ecologement`
       - **Hôte** : `localhost`
       - **Mot de passe** : Choisissez un mot de passe sécurisé
       - **Répéter le mot de passe** : Réentrez le même mot de passe
    - Cochez **"Créer une base de données portant ce nom et donner tous les privilèges"** (si la base de données n'existe pas encore).
    - Cliquez sur **"Exécuter"**.

2. Configurer les permissions (si nécessaire) :**
Si la base de données existe déjà, assurez-vous que l'utilisateur `ecologement` a les permissions nécessaires :
    - Allez dans la liste des utilisateurs et cliquez sur **"Modifier les privilèges"** pour l'utilisateur `ecologement`.
    - Vérifiez que les permissions suivantes sont activées :
       - **Données** : `SELECT`, `INSERT`, `UPDATE`, `DELETE`
       - **Structure** : `CREATE`, `ALTER`, `INDEX`, `DROP`
       - **Administration** : `GRANT OPTION`
    - Cliquez sur **"Exécuter"** pour enregistrer.

3. Modifier les fichiers de configuration :
Dans le fichier `.env` déjà fourni avec le projet, l'utilisateur doit remplacer les valeurs suivantes par les siennes :
    ```
    DB_USER=ecologement
    DB_PASSWORD=VotreMotDePasse
    DB_NAME=ecologement
    DB_HOST=localhost
    DB_PORT=3306
    ```

Assurez-vous de bien renseigner le mot de passe défini pour l'utilisateur `ecologement` lors de la création.

### **Remarque :**
Dans le même temps, veuillez modifier le fichier .env dans le dossier "Website" pour que le programme fonctionne normalement.


---

### Composer

1. Installer Composer.  
2. Ouvrir un terminal dans le dossier `Website`, puis entrer les commandes suivantes :  
    ```
    composer init
    composer require monolog/monolog
    composer require phpmailer/phpmailer
    composer require vlucas/phpdotenv
    composer require firebase/php-jwt
    ```

---
### action_page.php
  Le chemin de `openssl.cnf` est un chemin codé en dur. Si le chemin d'installation actuel de XAMPP n'est pas `D:\xampp`, il faudra le modifier pour qu'il corresponde au chemin approprié.
