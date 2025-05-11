#include <ESP8266WiFi.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <DHT.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>

// WiFi credentials
const char* ssid = "MaithiliPawar";
const char* password = "m@!thu2413";

// LCD settings
LiquidCrystal_I2C lcd(0x27, 16, 2);  // Use 0x3F if 0x27 doesn't work

// DHT11 settings
#define BUZZER_PIN D8

#define DHTPIN D4
#define DHTTYPE DHT11
DHT dht(DHTPIN, DHTTYPE);

// MQ135 analog pin
#define MQ135_PIN A0

// LED pins
#define GREEN_LED D5
#define YELLOW_LED D6
#define RED_LED D7

void sendToServer(float temp, float hum, int aqi) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    WiFiClient client;

    http.begin(client, "http://192.168.43.146/air/update.php");
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String postData = "temperature=" + String(temp) + "&humidity=" + String(hum) + "&air_quality=" + String(aqi);
    int httpResponseCode = http.POST(postData);

    Serial.print("Server response: ");
    Serial.println(httpResponseCode);

    http.end();
  }
}
void sendToThingSpeak(float temp, float hum, int aqi) {
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;

    String apiKey = "ROKB38JU9BM0ALW5";  // Replace with your ThingSpeak Write API Key
    String url = "http://api.thingspeak.com/update?api_key=" + apiKey +
                 "&field1=" + String(temp) +
                 "&field2=" + String(hum) +
                 "&field3=" + String(aqi);

    http.begin(client, url);
    int httpCode = http.GET();
    Serial.print("ThingSpeak response: ");
    Serial.println(httpCode);
    http.end();
  }
}

void setup() {
  Serial.begin(9600);

  // Setup LEDs
  pinMode(GREEN_LED, OUTPUT);
  pinMode(YELLOW_LED, OUTPUT);
  pinMode(RED_LED, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT); // Set buzzer as output

  // Initialize LCD
  lcd.init();
  lcd.backlight();

  // Initialize DHT
  dht.begin();

  lcd.setCursor(0, 0);
  lcd.print("Connecting WiFi");

  // Connect to WiFi
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("WiFi Connected");
  lcd.setCursor(0, 1);
  lcd.print(WiFi.localIP());  // Show IP on LCD
  delay(3000); // Show IP for a while

  lcd.clear();
}

void loop() {
  float temperature = dht.readTemperature();
  float humidity = dht.readHumidity();
  int airValue = analogRead(MQ135_PIN);
  sendToServer(temperature, humidity, airValue);
  sendToThingSpeak(temperature, humidity, airValue);

  if (isnan(temperature) || isnan(humidity)) {
    Serial.println("DHT sensor error!");
    lcd.setCursor(0, 0);
    lcd.print("Sensor Error");
    delay(2000);
    return;
  }

  // Display sensor values
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Temp:");
  lcd.print(temperature);
  lcd.print("C");

  lcd.setCursor(0, 1);
  lcd.print("AQ:");
  lcd.print(airValue);

  // LED logic
  if (airValue < 100) {
    digitalWrite(GREEN_LED, HIGH);
    digitalWrite(YELLOW_LED, LOW);
    digitalWrite(BUZZER_PIN, LOW);
    digitalWrite(RED_LED, LOW);
  } else if (airValue <= 300) {
    digitalWrite(GREEN_LED, LOW);
    digitalWrite(YELLOW_LED, HIGH);
    digitalWrite(BUZZER_PIN, LOW);
    digitalWrite(RED_LED, LOW);
  } else {
    digitalWrite(GREEN_LED, LOW);
    digitalWrite(YELLOW_LED, LOW);
    digitalWrite(BUZZER_PIN, HIGH);
    digitalWrite(RED_LED, HIGH);
  }

  // Serial log
  Serial.print("Temp: ");
  Serial.print(temperature);
  Serial.print(" °C, Hum: ");
  Serial.print(humidity);
  Serial.print(" %, Air: ");
  Serial.println(airValue);

  //delay(3600000);
  delay(20000);
}
