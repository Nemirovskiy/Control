// Обязательное включение PSRAM при прошивке

#include "esp_camera.h"
#include <Arduino.h>
#include <WiFi.h>
#include <WiFiUdp.h>
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"
#include <ESP32_FTPClient.h>
#include <NTPClient.h>

const char* ssid = "*****";
const char* password = "*****";

char ftp_server[] = "*****";
char ftp_user[]   = "*****";
char ftp_pass[]   = "*****";

const int timerInterval = 5000;

unsigned long lastTime;

// Motion Sensor
bool motionDetected = false;

ESP32_FTPClient ftp (ftp_server,ftp_user,ftp_pass, 5000, 2);

WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "2.ru.pool.ntp.org");
//2.ru.pool.ntp.org

WiFiClient client;

// CAMERA_MODEL_AI_THINKER
#define PWDN_GPIO_NUM     32
#define RESET_GPIO_NUM    -1
#define XCLK_GPIO_NUM      0
#define SIOD_GPIO_NUM     26
#define SIOC_GPIO_NUM     27

#define Y9_GPIO_NUM       35
#define Y8_GPIO_NUM       34
#define Y7_GPIO_NUM       39
#define Y6_GPIO_NUM       36
#define Y5_GPIO_NUM       21
#define Y4_GPIO_NUM       19
#define Y3_GPIO_NUM       18
#define Y2_GPIO_NUM        5
#define VSYNC_GPIO_NUM    25
#define HREF_GPIO_NUM     23
#define PCLK_GPIO_NUM     22
// 4 for flash led or 33 for normal led
#define LED_GPIO_NUM       4


void setup() {
    Serial.begin(115200);
  WiFi.mode(WIFI_STA);
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);
  WiFi.begin(ssid, password);  
  while (WiFi.status() != WL_CONNECTED) {
    Serial.print(".");
    delay(500);
  }

  Serial.println("Connected!!!");
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
  /*
    UXGA(1600x1200)
    SXGA(1280x1024)
    HD(1280x720)
    XGA(1024x768)
    SVGA(800x600)
    VGA(640x480)
    HVGA(480x320)
    CIF(400x296)
    QVGA(320x240)
    HQVGA(240x176)
    QCIF(176x144)
    QQVGA(160x120)
    */
  // UXGA|SXGA|XGA|SVGA|VGA|CIF|QVGA|HQVGA|QQVGA
  config.frame_size = FRAMESIZE_VGA;
  config.pixel_format = PIXFORMAT_JPEG; // for streaming
  //config.pixel_format = PIXFORMAT_RGB565; // for face detection/recognition
  config.grab_mode = CAMERA_GRAB_WHEN_EMPTY;
  config.fb_location = CAMERA_FB_IN_PSRAM;
  config.jpeg_quality = 12;
  config.fb_count = 1;
  config.grab_mode = CAMERA_GRAB_LATEST; //add no 3 foto mode

 /// config.saturation = 1;
 //config.contrast = 2;
 // config.brightness = 2;
 //config.gainceiling = 3;

   delay(1000);
   esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK) {
    Serial.printf("Camera init failed with error 0x%x", err);
    delay(5000);
    ESP.restart();
  }
    sensor_t * s = esp_camera_sensor_get();
  s->set_brightness(s, 2);     // -2 to 2
  s->set_contrast(s, 0);       // -2 to 2
  s->set_saturation(s, 1);     // -2 to 2
  s->set_special_effect(s, 0); // 0 to 6 (0 - No Effect, 1 - Negative, 2 - Grayscale, 3 - Red Tint, 4 - Green Tint, 5 - Blue Tint, 6 - Sepia)
  s->set_whitebal(s, 1);       // 0 = disable , 1 = enable
  s->set_awb_gain(s, 1);       // 0 = disable , 1 = enable
  s->set_wb_mode(s, 0);        // 0 to 4 - if awb_gain enabled (0 - Auto, 1 - Sunny, 2 - Cloudy, 3 - Office, 4 - Home)
  s->set_exposure_ctrl(s, 1);  // 0 = disable , 1 = enable
  s->set_aec2(s, 0);           // 0 = disable , 1 = enable
  s->set_ae_level(s, 0);       // -2 to 2
  s->set_aec_value(s, 600);    // 300 0 to 1200
  s->set_gain_ctrl(s, 1);      // 0 = disable , 1 = enable
  s->set_agc_gain(s, 10);       // 0 to 30
  s->set_gainceiling(s, (gainceiling_t)0);  // 0 to 6
  s->set_bpc(s, 0);            // 0 = disable , 1 = enable
  s->set_wpc(s, 1);            // 0 = disable , 1 = enable
  s->set_raw_gma(s, 1);        // 0 = disable , 1 = enable
  s->set_lenc(s, 1);           // 0 = disable , 1 = enable
  s->set_hmirror(s, 1);        // 0 = disable , 1 = enable
  s->set_vflip(s, 1);          //0 0 = disable , 1 = enable
  s->set_dcw(s, 1);            // 0 = disable , 1 = enable
  s->set_colorbar(s, 0);       // 0 = disable , 1 = enable

  timeClient.begin();
  timeClient.setTimeOffset(10800);
  timeClient.update();

  ftp.OpenConnection();
}


int FTP_uploadImage(int64_t t, unsigned char *pdata, unsigned int size)
{
    // строка для геренерации рандома
    String alphabet = "abcdefghijklmnopqrstuvwxyz";
    char filename[32] = "";
    Serial.print("FTP_uploadImage=");
    Serial.println(size);
    Serial.println(timeClient.getFormattedTime());

    unsigned long epochTime = timeClient.getEpochTime();
 
    struct tm *ptm = gmtime ((time_t *)&epochTime);

    struct tm timeinfo;
    if (!ftp.isConnected()){
      ftp.OpenConnection();
    }
    
    int rand1 = random(1,alphabet.length()); // рандомное начало
    int rand2 = random(1,alphabet.length()); // рандомный конец
    
    sprintf(filename, "%04d%02d%02d_%02d%02d%02d_%1s%04d%1s.jpg",
            ptm->tm_year + 1900,
            ptm->tm_mon + 1,
            ptm->tm_mday,
            ptm->tm_hour,
            ptm->tm_min,
            ptm->tm_sec,
            alphabet.substring(rand1 - 1, rand1), // рандом начала
            random(1111,9999),                    // рандом середины
            alphabet.substring(rand2 - 1, rand2)  // рандом конца
            );
    // прейдем в директорию
    ftp.ChangeWorkDir("/img");
    ftp.InitFile("Type I");
    Serial.println(filename);
    // сохраним файл
    ftp.NewFile(filename);
    ftp.WriteData(pdata, size);
    ftp.CloseFile();

   // ftp.CloseConnection();
    timeClient.update();
    return 0;
}

int capture_ftpupload(void)
{
    camera_fb_t * fb = NULL;
    esp_err_t res = ESP_OK;
    int64_t fr_start = esp_timer_get_time();

    fb = esp_camera_fb_get();
    if (!fb) {
        Serial.println("Camera capture failed");

        return ESP_FAIL;
    }

    FTP_uploadImage(fr_start, fb->buf, fb->len);

  esp_camera_fb_return(fb);

    return res;
}


void loop() {
  if(digitalRead(13) > 0) {
    Serial.print("\nMotion! = ");
    Serial.println(digitalRead(13));
    
    capture_ftpupload();
    lastTime = millis();
  }

}