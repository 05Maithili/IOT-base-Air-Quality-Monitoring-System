<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "air_monitor";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $temp = $_POST['temperature'];
    $hum = $_POST['humidity'];
    $aq = $_POST['air_quality'];

    $sql = "INSERT INTO esp_data (temperature, humidity, air_quality)
            VALUES ('$temp', '$hum', '$aq')";

            if ($conn->query($sql) === TRUE) {
                echo "Data inserted";
            
                if ($aq > 400) {
                    include("alert.php"); // Triggers alert email
                }
            } else {
                echo "Error: " . $conn->error;
            }
}

$conn->close();
?>
