#include <LiquidCrystal_I2C.h>

const int moistureSensorPin = A0; // Analog pin connected to soil moisture sensor
const int relayPin = 8;           // Digital pin connected to relay module
int percentage = 0;


void setup() {
  Serial.begin(9600);
  pinMode(moistureSensorPin, INPUT);
  pinMode(relayPin, OUTPUT);
  digitalWrite(relayPin, HIGH); // Keep pump off initially


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
  } else if (percentage > 80) { // If soil is sufficiently moist
    Serial.println("Watering Done. Turning OFF pump");
    digitalWrite(relayPin, LOW); // Turn off relay (A watering)
  }

  delay(1000); // Wait 1 second before next checkA
}
