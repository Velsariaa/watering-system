#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <SoftwareSerial.h>

SoftwareSerial NodeMCU(D2,D3);

const char* ssid = "enter-wifi-name-here"; 
const char* password = "enter-wifi-password-here";

const int moistureSensorPin = A0;
const int DRY_THRESHOLD = 800;
const int WET_THRESHOLD = 300;

void initWiFi() {
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nWiFi connected");
}

int getMoistureRawValue() {
    int rawValue = analogRead(moistureSensorPin);
    Serial.print("Raw Value: ");
    Serial.println(rawValue);
    return rawValue;
}

void setup() {
    Serial.begin(9600);
    NodeMCU.begin(4800);
    pinMode(D2,INPUT);
    pinMode(D3,OUTPUT);
    initWiFi();
}

void loop() {
  int rawValue = getMoistureRawValue();
    
    if (rawValue > DRY_THRESHOLD) {
        NodeMCU.print(0);
        NodeMCU.println("\n");
    } else if (rawValue < WET_THRESHOLD) {
        NodeMCU.print(1);
        NodeMCU.println("\n");
    }
  delay(5000);
}
