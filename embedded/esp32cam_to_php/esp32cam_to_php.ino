#include "esp_camera.h"
#include <WiFi.h>
#include <HTTPClient.h>
#include <Base64.h>
#include <time.h>
#define CAMERA_MODEL_AI_THINKER
#include "camera_pins.h"

const char* ssid = "enter-wifi-name-here"; // MODIFY
const char* password = "enter-wifi-password-here"; // MODIFY
const char* serverName = "http://enter-ip-address-of-backend-here/api/upload"; // MODIFY

const char* ntpServer = "pool.ntp.org";
const long gmtOffset_sec = 28800;     // GMT+8 (Philippines timezone)
const int daylightOffset_sec = 0;
unsigned long lastCaptureTime = 0;
bool hasRunToday = false;

void initWiFi() {
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.println("WiFi connected");
}

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

  esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK) {
    Serial.printf("Camera init failed with error 0x%x", err);
    return;
  }
}

camera_fb_t* captureImage() {
  camera_fb_t* fb = esp_camera_fb_get();
  if (!fb) {
    Serial.println("Camera capture failed");
    return NULL;
  }
  return fb;
}

String createJsonPayload(camera_fb_t* fb) {
  String jsonData = "{\"image\":\"";
  String base64Image = base64::encode(fb->buf, fb->len);
  jsonData += base64Image;
  jsonData += "\"}";
  
  // Debug print
  Serial.println("JSON Length: " + String(jsonData.length()));
  Serial.println("First 100 chars of JSON: " + jsonData.substring(0, 100));
  Serial.println("Last 100 chars of JSON: " + jsonData.substring(jsonData.length() - 100));
  
  return jsonData;
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

bool sendImageToServer(String jsonData) {
  const int maxRetries = 3;
  int retryCount = 0;

  while (retryCount < maxRetries) {
    if(WiFi.status() != WL_CONNECTED) {
      Serial.println("WiFi not connected");
      if (!reconnectWiFi()) {
        return false;
      }
    }

    HTTPClient http;
    http.begin(serverName);
    http.addHeader("Content-Type", "application/json");
    
    // Debug print headers
    Serial.println("Content-Type: application/json");
    Serial.println("Sending request to: " + String(serverName));

    int httpResponseCode = http.POST(jsonData);
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

void initTime() {
  // Wait for WiFi connection first
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi not connected. Attempting to reconnect...");
    if (!reconnectWiFi()) {
      Serial.println("Failed to connect to WiFi. Cannot sync time.");
      return;
    }
  }

  Serial.println("Initializing NTP...");
  configTime(gmtOffset_sec, daylightOffset_sec, ntpServer, "time.nist.gov");
  
  // Wait for time to be set
  int retry = 0;
  const int maxRetries = 10;
  struct tm timeinfo;
  
  while(!getLocalTime(&timeinfo) && retry < maxRetries) {
    Serial.println("Waiting for time sync...");
    delay(1000);
    retry++;
  }

  if (retry >= maxRetries) {
    Serial.println("Failed to obtain time after multiple attempts");
    return;
  }

  Serial.println("Time synchronized successfully");
  Serial.printf("Current time: %02d:%02d:%02d\n", 
    timeinfo.tm_hour, 
    timeinfo.tm_min, 
    timeinfo.tm_sec
  );
}

bool isTimeToCapture() {
  struct tm timeinfo;
  if(!getLocalTime(&timeinfo)){
    Serial.println("Failed to obtain time");
    return false;
  }

  Serial.printf("Current time: %02d:%02d\n", timeinfo.tm_hour, timeinfo.tm_min);
  
  if(timeinfo.tm_hour == 10 && timeinfo.tm_min >= 0 && timeinfo.tm_min < 2 && !hasRunToday) {
    hasRunToday = true;
    return true;
  }
  
  if(timeinfo.tm_hour != 10) {
    hasRunToday = false;
  }
  
  return false;
}

void setup() {
  Serial.begin(115200);
  initWiFi();
  startCamera();
  initTime();
}

void loop() {
  if(isTimeToCapture()) {
    Serial.println("It's time to capture! Taking picture...");
    camera_fb_t* fb = captureImage();
    if (fb) {
      String jsonData = createJsonPayload(fb);
      sendImageToServer(jsonData);
      esp_camera_fb_return(fb);
    }
  }
  delay(15000); 
}

