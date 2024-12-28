#include <LiquidCrystal_I2C.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>

const char* ssid = "Your_SSID"; 
const char* password = "Your_PASSWORD"; 
const char* serverName = "http://<your-server-ip-or-domain>/log_water.php"; 

const int moistureSensorPin = A0; 
const int relayPin = D1;          
int percentage = 0;

void setup() {
  Serial.begin(9600);
  pinMode(moistureSensorPin, INPUT);
  pinMode(relayPin, OUTPUT);
  digitalWrite(relayPin, HIGH); 

  // Connect to WiFi
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");
}

void loop() {
  int moistureLevel = analogRead(moistureSensorPin); 
  percentage = map(moistureLevel, 490, 1023, 100, 0); 

  Serial.print("Moisture Level: ");
  Serial.print(moistureLevel);
  Serial.print(" -> Percentage: ");
  Serial.println(percentage);

  // Control the relay based on moisture percentage
  if (percentage < 10) { 
    Serial.println("Watering... Turning ON pump");
    digitalWrite(relayPin, HIGH); 

    // Log to the server
    if (WiFi.status() == WL_CONNECTED) {
      HTTPClient http;
      http.begin(serverName);
      http.addHeader("Content-Type", "application/x-www-form-urlencoded");

      String httpRequestData = "name=Plant1"; 
      int httpResponseCode = http.POST(httpRequestData);

      if (httpResponseCode > 0) {
        String response = http.getString();
        Serial.println("Response: " + response);
      } else {
        Serial.println("Error logging data");
      }

      http.end();
    }

    delay(5000); 
    digitalWrite(relayPin, LOW); 
  } else if (percentage > 80) { 
    Serial.println("Watering Done. Turning OFF pump");
    digitalWrite(relayPin, LOW); 
  }

  delay(1000); 
}
