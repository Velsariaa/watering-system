#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <SoftwareSerial.h>

SoftwareSerial NodeMCU(D2,D3);

const char* ssid = "enter-wifi-name-here"; // MODIFY
const char* password = "enter-wifi-password-here"; // MODIFY
const char* serverName = "http://enter-ip-address-of-backend-here/api/log_water"; // MODIFY

const int moistureSensorPin = A0;
const int DRY_THRESHOLD = 800;
const int WET_THRESHOLD = 300;

bool myswitch = false;

void initWiFi() {
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nWiFi connected");
}

bool reconnectWiFi() {
  int attempts = 0;
  const int maxAttempts = 10;
  
  while (WiFi.status() != WL_CONNECTED && attempts < maxAttempts) {
    Serial.println("Reconnecting to WiFi...");
    WiFi.disconnect();
    WiFi.begin(ssid, password);
    attempts++;
    
    // Wait up to 5 seconds for connection
    int timeout = 0;
    while (WiFi.status() != WL_CONNECTED && timeout < 10) {
      delay(500);
      Serial.print(".");
      timeout++;
    }
    
    if (WiFi.status() == WL_CONNECTED) {
      Serial.println("\nWiFi reconnected");
      return true;
    }
  }
  
  if (attempts >= maxAttempts) {
    Serial.println("\nFailed to reconnect to WiFi after maximum attempts");
    return false;
  }
  
  return false;
}

bool triggerLogToServer() {
    const int maxRetries = 3;
    int retryCount = 0;

    while (retryCount < maxRetries) {
        if(WiFi.status() != WL_CONNECTED) {
            Serial.println("WiFi not connected");
            if (!reconnectWiFi()) {
                return false;
            }
        }

        WiFiClient client;
        HTTPClient http;
        http.begin(client, serverName);
        http.addHeader("Content-Type", "application/json");
        Serial.println("Sending request to: " + String(serverName));

        int httpResponseCode = http.POST("");
        bool success = false;

        if (httpResponseCode > 0) {
            Serial.printf("HTTP Response Code: %d\n", httpResponseCode);
            String payload = http.getString();
            Serial.printf("Server Response: %s\n", payload.c_str());

            if (payload.indexOf("\"error\"") == -1) {
                success = true;
                http.end();
                return success;
            } else {
                Serial.printf("Attempt %d failed with error response, retrying...\n", retryCount + 1);
            }
        } else {
            Serial.printf("HTTP Error: %d (%s)\n", httpResponseCode, http.errorToString(httpResponseCode).c_str());
        }

        http.end();
        retryCount++;
        if (retryCount < maxRetries) {
            delay(1000);
        }
    }

    Serial.println("Max retries reached, giving up");
    return false;
}


int getMoistureRawValue() {
    int rawValue = analogRead(moistureSensorPin);
    Serial.print("Raw Value: ");
    Serial.println(rawValue);
    return rawValue;
}

void setup() {
    Serial.begin(115200);
    NodeMCU.begin(4800);
    pinMode(D2,INPUT);
    pinMode(D3,OUTPUT);
    initWiFi();
}

void loop() {

    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi not connected");
        reconnectWiFi();
    } else {
        Serial.print("ESP IP Address: ");
        Serial.println(WiFi.localIP());
    }
    
    int rawValue = getMoistureRawValue();

    if (rawValue > DRY_THRESHOLD) {
        NodeMCU.print(1);
        NodeMCU.println("\n");
        triggerLogToServer();
        Serial.println("Water pump ON");
    } else if (rawValue < WET_THRESHOLD) {
        NodeMCU.print(0);
        NodeMCU.println("\n");
        Serial.println("Water pump OFF");
    }

    delay(5000);
}
