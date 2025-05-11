<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

// Function to get live air quality data (example using OpenAQ API)
function getAirQualityData() {
    $apiUrl = "https://api.openaq.org/v1/latest?city=&parameter=pm25";
    
    try {
        $response = file_get_contents($apiUrl);
        $data = json_decode($response, true);
        
        if (isset($data['results'][0]['measurements'][0]['value'])) {
            $pm25 = $data['results'][0]['measurements'][0]['value'];
            $aqi = calculateAQI($pm25); // You'll need to implement this conversion
            return [
                'pm25' => $pm25,
                'aqi' => $aqi,
                'timestamp' => $data['results'][0]['measurements'][0]['lastUpdated']
            ];
        }
    } catch (Exception $e) {
        error_log("Error fetching air quality data: " . $e->getMessage());
    }
    
    return null;
}

// Simple AQI calculation (simplified - use proper formula for your needs)
function calculateAQI($pm25) {
    // This is a simplified calculation - use the proper AQI formula
    return round($pm25 * 2); // Example conversion
}

// Get current air quality data
$airQuality = getAirQualityData();

$mail = new PHPMailer(true);

$csvFile = fopen("emails.csv", "r");
if (!$csvFile) {
    die("CSV file not found");
}

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'maithalipawar25@gmail.com';
    $mail->Password = 'juaf mkjx ckqc inlx';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Email subject & body with live data
    $mail->setFrom('maithalipawar25@gmail.com', 'Air Quality Alert');
    $mail->isHTML(true);
    $mail->Subject = 'URGENT: Poor Air Quality Alert!';
    
    // Dynamic email body with live readings
    $emailBody = '<h1>Air Quality Alert</h1>';
    $emailBody .= '<p>Air Quality has crossed safe levels. Please take precautions and stay indoors.</p>';
    
    if ($airQuality) {
        $emailBody .= '<h2>Current Air Quality Readings:</h2>';
        $emailBody .= '<ul>';
        $emailBody .= '<li><strong>PM2.5:</strong> ' . $airQuality['pm25'] . ' µg/m³</li>';
        $emailBody .= '<li><strong>AQI:</strong> ' . $airQuality['aqi'] . '</li>';
        $emailBody .= '<li><strong>Last Updated:</strong> ' . $airQuality['timestamp'] . '</li>';
        $emailBody .= '</ul>';
        
        // Add health recommendations based on AQI
        if ($airQuality['aqi'] > 150) {
            $emailBody .= '<p style="color: red;"><strong>Warning:</strong> Unhealthy air quality. Avoid outdoor activities.</p>';
        }
    } else {
        $emailBody .= '<p>Current air quality data is unavailable.</p>';
    }
    
    $mail->Body = $emailBody;

    // Send to all emails in CSV
    while (($line = fgetcsv($csvFile)) !== false) {
        $email = $line[0];
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mail->addAddress($email);
        }
    }

    $mail->send();
    echo 'Alert sent successfully with live air quality data';
} catch (Exception $e) {
    echo "Mailer Error: {$mail->ErrorInfo}";
}

fclose($csvFile);
?>  