#include "esp_camera.h"
#include <WiFi.h>
#include <HTTPClient.h>
#include <Base64.h> 
#define CAMERA_MODEL_AI_THINKER
#include "camera_pins.h"


// Replace with your network credentials
const char* ssid = "PLDTHOMEFIBRLtYs4";
const char* password = "aia3562@2015";

// Replace with the URL of your PHP script
const char* serverName = "http://192.168.1.12/api/upload";

void startCamera() {
  camera_config_t config;
  config.ledc_channel = LEDC_CHANNEL_0;
  config.ledc_timer = LEDC_TIMER_0;
  config.pin_d0 = Y2_GPIO_NUM;
  config.pin_d1 = Y3_GPIO_NUM;
  config.pin_d2 = Y4_GPIO_NUM;
  config.pin_d3 = Y5_GPIO_NUM;
  config.pin_d4 = Y6_GPIO_NUM;
  config.pin_d5 = Y7_GPIO_NUM;
  config.pin_d6 = Y8_GPIO_NUM;
  config.pin_d7 = Y9_GPIO_NUM;
  config.pin_xclk = XCLK_GPIO_NUM;
  config.pin_pclk = PCLK_GPIO_NUM;
  config.pin_vsync = VSYNC_GPIO_NUM;
  config.pin_href = HREF_GPIO_NUM;
  config.pin_sccb_sda = SIOD_GPIO_NUM;
  config.pin_sccb_scl = SIOC_GPIO_NUM;
  config.pin_pwdn = PWDN_GPIO_NUM;
  config.pin_reset = RESET_GPIO_NUM;
  config.xclk_freq_hz = 20000000;
  config.frame_size = FRAMESIZE_UXGA;
  config.pixel_format = PIXFORMAT_JPEG;
  config.grab_mode = CAMERA_GRAB_WHEN_EMPTY;
  config.fb_location = CAMERA_FB_IN_PSRAM;
  config.jpeg_quality = 12;
  config.fb_count = 1;

  if(psramFound()){
    config.frame_size = FRAMESIZE_UXGA;
    config.jpeg_quality = 10;
    config.fb_count = 2;
  } else {
    config.frame_size = FRAMESIZE_SVGA;
    config.jpeg_quality = 12;
    config.fb_count = 1;
  }

  // Camera init
  esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK) {
    Serial.printf("Camera init failed with error 0x%x", err);
    return;
  }
}

void setup() {
  Serial.begin(115200);

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.println("WiFi connected");

  startCamera();
}

void loop() {
  camera_fb_t * fb = esp_camera_fb_get();
  if (!fb) {
    Serial.println("Camera capture failed");
    return;
  }

  if(WiFi.status() == WL_CONNECTED){
    HTTPClient http;

    http.begin(serverName);

    http.addHeader("Content-Type", "application/json");  // Change to application/json

    // Create JSON object
    String jsonData = "{\"image\":\"";
    jsonData += base64::encode(fb->buf, fb->len);  // Encode image to base64
    jsonData += "\"}";

    int httpResponseCode = http.POST(jsonData);  // Send JSON data
    if (httpResponseCode > 0) {
        Serial.printf("HTTP Response Code: %d\n", httpResponseCode);
        String payload = http.getString();
        Serial.printf("Server Response: %s\n", payload.c_str());
    } else {
        Serial.printf("HTTP Error: %d (%s)\n", httpResponseCode, http.errorToString(httpResponseCode).c_str());
    }

    http.end();
  }

  esp_camera_fb_return(fb);
  
  delay(10000);
}

