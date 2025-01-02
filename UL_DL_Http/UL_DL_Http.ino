#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include "DHT.h"

#ifndef STASSID
#define STASSID "yxc_k60"
#define STAPSK "Ciallour!"
#endif

#define MSG_BUFFER_SIZE	(50)
char msg[MSG_BUFFER_SIZE];
char Humidity[MSG_BUFFER_SIZE];
char Temperature[MSG_BUFFER_SIZE];
char H[MSG_BUFFER_SIZE];
char T[MSG_BUFFER_SIZE];
int value = 0;
int flag_ID = 0;

String ID_Humidite;
String ID_Temperature;

// DHT
#define DHTPIN D5     // Digital pin connected to the DHT sensor

#define DHTTYPE DHT11   // DHT 11
//#define DHTTYPE DHT22   // DHT 22  (AM2302), AM2321
//#define DHTTYPE DHT21   // DHT 21 (AM2301)

// Initialize DHT sensor
DHT dht(DHTPIN, DHTTYPE);

// Global client and HTTP client
WiFiClient client;
HTTPClient http;

void callback(char* topic, byte* payload, unsigned int length) {
  Serial.print("Message arrived [");
  Serial.print(topic);
  Serial.print("] ");
  for (int i = 0; i < length; i++) {
    Serial.print((char)payload[i]);
  }
  Serial.println();
  
  // Switch on the LED if an 1 was received as first character
  if ((char)payload[0] == '1') {
    digitalWrite(LED_BUILTIN, LOW);   // Turn the LED on (Note that LOW is the voltage level
    // but actually the LED is on; this is because it is active low on the ESP-01)
  } else {
    digitalWrite(LED_BUILTIN, HIGH);  // Turn the LED off by making the voltage HIGH
  }
}

void IDGetRequest(const char* serverUrl, int status) {
  // configure targeted server and URL
  http.begin(client, serverUrl);  // HTTP_GET pour récupérer l'ID de la température
  int httpCode_ID = http.GET(); 

  // Obtenir le contenu de la réponse
  if (httpCode_ID > 0) {
    if (httpCode_ID == HTTP_CODE_OK) {
      if (status == 1) {
        Serial.print("[HTTP] Température status verification begin...\n");
        ID_Temperature = http.getString();  
        Serial.println("HTTP GET Response: << Le CA de la température est déjà ajouté à la base de données. >>\n");
        Serial.println("ID de la température: " + ID_Temperature);
        flag_ID = 0;
      } else {
        Serial.print("[HTTP] Humidité status verification begin...\n");
        ID_Humidite = http.getString();  
        Serial.println("HTTP GET Response: << Le CA de l'humidité est déjà ajouté à la base de données. >>\n");
        Serial.println("ID de l'humidité: " + ID_Humidite);
        flag_ID = 0;
      }
    } 
     else {
      Serial.printf("HTTP GET request returned status code: %d\n", httpCode_ID);
    }
  } else {
    Serial.printf("HTTP GET request failed, error: %s\n", http.errorToString(httpCode_ID).c_str());
  }
  http.end();  // Important: release resources after each request

  if (httpCode_ID == 471) {
      if (status == 1) {
        http.begin(client, "http://192.168.154.65:8000/addcapture/10.20.20.21/Température/Chambre/1883/5/1");
        http.end();
      } else {
        http.begin(client, "http://192.168.154.65:8000/addcapture/10.20.20.22/Humidité/Chambre/1883/5/2");
        http.end();
      }
  }
}

void sendPOSTRequest(const char* serverUrl, String jsonData) {
  HTTPClient http;  // 使用局部 HTTPClient 以避免内存泄漏
  Serial.print("[HTTP] POST Begin...\n");
  // Configure targeted server and URL
  http.begin(client, serverUrl);
  http.addHeader("Content-Type", "application/json");

  Serial.print("[HTTP] POST...\n");
  // Start connection and send HTTP message
  int httpCode = http.POST(jsonData);

  // HTTP code will be negative on error
  if (httpCode > 0) {
    // HTTP header has been sent and Server response header has been handled
    Serial.printf("[HTTP] POST... code: %d\n", httpCode);

    // Check if the response code indicates success
    if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_CREATED) {
      String payload = http.getString();
      Serial.println("Received payload: <<" + payload + ">>\n");
    } else {
      Serial.printf("Unexpected HTTP response code: %d\n", httpCode);
    }
  } else {
    Serial.printf("[HTTP] POST... failed, error: %s\n", http.errorToString(httpCode).c_str());
  }
  http.end();  // Important: release resources after each request
}

void sendHumidityAndTemperature(float h, String ID_Humidite, float t, String ID_Temperature) {
    // 将浮点数 h 转换为整数
    int int_h = (int)h;
    int int_t = (int)t;
    int int_HumiditeID = 7;
    int int_TemperatureID = 6;

    // 创建 JSON 数据
    String jsonHumidityData = "{\"Humidity\": " + String(int_h) + ", \"CA_ID\": " + String(int_HumiditeID) + "}";
    sendPOSTRequest("http://192.168.154.65:8000/addmesureESP/", jsonHumidityData);
    delay(1000);

    String jsonTemperatureData = "{\"Temperature\": " + String(int_t) + ", \"CA_ID\": " + String(int_TemperatureID) + "}";
    sendPOSTRequest("http://192.168.154.65:8000/addmesureESP/", jsonTemperatureData);
    delay(1000);
}

void LED_Control(const char* serverUrl, float h) {
  // Configure targeted server and URL
  http.begin(client, serverUrl);  // HTTP_GET pour récupérer l'ID de la température
  int httpCode_ID = http.GET(); 

  String Temperature_Actuale;  // 将 Temperature_Actuale 提升到函数的外部以便于后续使用
  if (httpCode_ID > 0) {
    if (httpCode_ID == HTTP_CODE_OK) {
      Serial.print("[HTTP] Température status verification begin...\n");
      Temperature_Actuale = http.getString();  // 获取服务器返回的温度值
      Serial.println("HTTP GET Response: << La temperature actuelle a été déjà récupérée. >>\n");
      Serial.println("La température actuelle: " + Temperature_Actuale);
    } else {
      Serial.printf("HTTP GET request returned status code: %d\n", httpCode_ID);
    }
  } else {
    Serial.printf("HTTP GET request failed, error: %s\n", http.errorToString(httpCode_ID).c_str());
  }
  http.end();  // Important: release resources after each request

  // 转换字符串温度为整数
  int int_Temperature = Temperature_Actuale.toInt();
  int int_h = (int)h;  // 将湿度值转换为整数

  // 判断是否成功转换温度值，避免误判
  if (Temperature_Actuale.length() == 0 || int_Temperature == 0) {
    Serial.println("Erreur de conversion: La valeur de température est invalide.");
    return; // 若温度转换失败，直接退出函数
  }

  // 控制 LED
  if (int_h > int_Temperature) {
    digitalWrite(LED_BUILTIN, LOW);  // Turn the LED on (Note that LOW is the voltage level
    // but actually the LED is on; this is because it is active low on the ESP-01)
    Serial.println("LED ON: Humidité est supérieure à la température actuelle.");
  } else {
    digitalWrite(LED_BUILTIN, HIGH);  // Turn the LED off by making the voltage HIGH
    Serial.println("LED OFF: Humidité est inférieure ou égale à la température actuelle.");
  }
}


void setup() {

  Serial.begin(115200);

  Serial.println();
  Serial.println();
  Serial.println();

  WiFi.begin(STASSID, STAPSK);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.print("Connected! IP address: ");
  Serial.println(WiFi.localIP());

  
  // Initialize the LED_BUILTIN pin as an output
  pinMode(LED_BUILTIN, OUTPUT);

  // DHT
  Serial.println(F("DHT11 test!"));
  dht.begin();
}

void loop() {
  // wait for WiFi connection
  if ((WiFi.status() == WL_CONNECTED)) {
  
    Serial.print("Connected! IP address: ");
    Serial.println(WiFi.localIP());

    // Reading temperature or humidity takes about 250 milliseconds!
    // Sensor readings may also be up to 2 seconds 'old' (its a very slow sensor)
    float h = dht.readHumidity();
    // Read temperature as Celsius (the default)
    float t = dht.readTemperature();

    // Check if any reads failed and exit early (to try again).
    if (isnan(h) || isnan(t)) {
      Serial.println(F("Failed to read from DHT sensor!"));
      return;
    }

    //snprintf (Temperature, MSG_BUFFER_SIZE, "Temperature = %.2f℃", t);
    //snprintf (Humidity, MSG_BUFFER_SIZE, "Humidity = %.2f%%\n", h);

    //snprintf (T, MSG_BUFFER_SIZE, "%.2f", t);
    //snprintf (H, MSG_BUFFER_SIZE, "%.2f", h);

  // Faire une requête "GET" pour vérifier la température
  IDGetRequest("http://192.168.154.65:8000/getCAID/10.20.20.21", 1);  // HTTP_GET pour récupérer l'ID de la température
  IDGetRequest("http://192.168.154.65:8000/getCAID/10.20.20.22", 2);  // HTTP_GET pour récupérer l'ID de l'humidité

  sendHumidityAndTemperature(h, ID_Humidite, t, ID_Temperature);

  //LED_Control("192.168.154.65:8000/currentmeteo/", h);

  delay(10000); 
  }

}

/*

        // Switch on the LED if an 2 was received as first character
        if (payload_LED[0] == '2') {
          digitalWrite(LED_BUILTIN, LOW);  // Turn the LED on (Note that LOW is the voltage level
           // but actually the LED is on; this is because it is active low on the ESP-01)
        } else {
          digitalWrite(LED_BUILTIN, HIGH);  // Turn the LED off by making the voltage HIGH
        }
*/
