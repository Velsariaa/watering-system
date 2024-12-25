#include <LiquidCrystal_I2C.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>

const char* ssid = "Your_SSID"; // Replace with your WiFi SSID
const char* password = "Your_PASSWORD"; // Replace with your WiFi Password
const char* serverName = "http://<your-server-ip-or-domain>/log_water.php"; // Replace with your server's URL

const int moistureSensorPin = A0; // Analog pin connected to soil moisture sensor
const int relayPin = D1;          // Digital pin connected to relay module
int percentage = 0;

void setup() {
  Serial.begin(9600);
  pinMode(moistureSensorPin, INPUT);
  pinMode(relayPin, OUTPUT);
  digitalWrite(relayPin, HIGH); // Keep pump off initially

  // Connect to WiFi
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");
}

void loop() {
  int moistureLevel = analogRead(moistureSensorPin); // Read soil moisture level
  percentage = map(moistureLevel, 490, 1023, 100, 0); // Adjust these values as needed

  Serial.print("Moisture Level: ");
  Serial.print(moistureLevel);
  Serial.print(" -> Percentage: ");
  Serial.println(percentage);

  // Control the relay based on moisture percentage
  if (percentage < 10) { // If soil is too dry
    Serial.println("Watering... Turning ON pump");
    digitalWrite(relayPin, HIGH); // Turn on relay (start watering)

    // Log to the server
    if (WiFi.status() == WL_CONNECTED) {
      HTTPClient http;
      http.begin(serverName);
      http.addHeader("Content-Type", "application/x-www-form-urlencoded");

      String httpRequestData = "name=Plant1"; // Replace 'Plant1' with the plant name
      int httpResponseCode = http.POST(httpRequestData);

      if (httpResponseCode > 0) {
        String response = http.getString();
        Serial.println("Response: " + response);
      } else {
        Serial.println("Error logging data");
      }

      http.end();
    }

    delay(5000); // Watering duration (5 seconds)
    digitalWrite(relayPin, LOW); // Turn off relay
  } else if (percentage > 80) { // If soil is sufficiently moist
    Serial.println("Watering Done. Turning OFF pump");
    digitalWrite(relayPin, LOW); // Turn off relay
  }

  delay(1000); // Wait 1 second before the next check
}
