#include <SoftwareSerial.h>
SoftwareSerial ArduinoUno(3,2);

const int relayPin = 8;

void setup(){
	Serial.begin(9600);
	ArduinoUno.begin(4800);
    pinMode(relayPin, OUTPUT);
    digitalWrite(relayPin, LOW); 
    }

void loop() {

    while(ArduinoUno.available()>0)
    {
        String val = ArduinoUno.readStringUntil('\n');
        if (val == "0") 
        {
            digitalWrite(relayPin, HIGH); 
            Serial.println("RELAY_OFF");
        }
        else if (val == "1")
        {
            digitalWrite(relayPin, LOW);
            Serial.println("RELAY_ON");
        }
        
    }
}