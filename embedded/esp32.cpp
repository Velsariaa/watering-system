#include "esp_camera.h"
#include <WiFi.h>
#include <HTTPClient.h>

// WiFi credentials
const char *ssid = "Totskie";
const char *password = "12345678";

// Server URL
const char *serverUrl = "http://<your-server-ip>/upload.php";

// Timer setup for auto-capture
unsigned long lastCapture = 0;

void setup() {
    Serial.begin(115200);
    Serial.println();

    // Initialize WiFi
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nWiFi connected!");

    // Initialize camera
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
    config.frame_size = FRAMESIZE_QVGA;
    config.pixel_format = PIXFORMAT_JPEG;
    config.jpeg_quality = 12;
    config.fb_count = 1;

    if (esp_camera_init(&config) != ESP_OK) {
        Serial.println("Camera init failed");
        return;
    }
}

void loop() {
    // Check if it's 10:00 AM
    time_t now = time(nullptr);
    struct tm *currentTime = localtime(&now);
    if (currentTime->tm_hour == 10 && currentTime->tm_min == 0 && millis() - lastCapture > 60000) {
        captureAndUploadImage();
        lastCapture = millis();
    }

    delay(1000);
}

void captureAndUploadImage() {
    camera_fb_t *fb = esp_camera_fb_get();
    if (!fb) {
        Serial.println("Camera capture failed");
        return;
    }

    HTTPClient http;
    http.begin(serverUrl);
    http.addHeader("Content-Type", "application/octet-stream");
    int responseCode = http.POST(fb->buf, fb->len);

    if (responseCode > 0) {
        Serial.printf("Image uploaded successfully, response code: %d\n", responseCode);
    } else {
        Serial.printf("Upload failed: %s\n", http.errorToString(responseCode).c_str());
    }

    http.end();
    esp_camera_fb_return(fb);
}
