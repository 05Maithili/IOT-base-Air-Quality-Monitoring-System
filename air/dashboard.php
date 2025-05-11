<?php
$mysqli = new mysqli("localhost", "root", "", "air_monitor");
$result = $mysqli->query("SELECT * FROM esp_data ORDER BY id DESC LIMIT 1");
$data = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Air Quality Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <meta http-equiv="refresh" content="5">
  <style>
    body { background: #f4f6f9; padding: 20px; font-family: 'Segoe UI'; }
    .card { border-radius: 15px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { color: #333; }
  </style>
</head>
<body>

<div class="container">
  <h2 class="text-center mb-4">Live Air Quality Monitoring</h2>

  <div class="row">
    <div class="col-md-4 mb-4">
      <div class="card p-3 text-center">
        <h5>Temperature</h5>
        <h3 class="text-danger"><?= $data['temperature'] ?> °C</h3>
      </div>
    </div>
    <div class="col-md-4 mb-4">
      <div class="card p-3 text-center">
        <h5>Humidity</h5>
        <h3 class="text-primary"><?= $data['humidity'] ?> %</h3>
      </div>
    </div>
    <div class="col-md-4 mb-4">
      <div class="card p-3 text-center">
        <h5>Air Quality</h5>
        <h3 class="text-success"><?= $data['air_quality'] ?></h3>
      </div>
    </div>
  </div>

  <canvas id="aqChart" height="100"></canvas>
</div>

<script>
const ctx = document.getElementById('aqChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Temp (°C)', 'Humidity (%)', 'Air Quality'],
        datasets: [{
            label: 'Latest Sensor Data',
            data: [<?= $data['temperature'] ?>, <?= $data['humidity'] ?>, <?= $data['air_quality'] ?>],
            backgroundColor: ['#dc3545', '#0d6efd', '#198754']
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});
</script>

</body>
</html>
