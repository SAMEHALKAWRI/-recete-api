<?php
$eczaneler = json_decode($_GET['data'], true);
$user_lat = floatval($_GET['lat'] ?? '37.8713');
$user_lng = floatval($_GET['lng'] ?? '32.4846');

// حساب المسافة
function mesafe($lat1, $lng1, $lat2, $lng2) {
    $R = 6371;
    $dLat = ($lat2 - $lat1) * M_PI / 180;
    $dLng = ($lng2 - $lng1) * M_PI / 180;
    $a = sin($dLat/2) * sin($dLat/2) +
         cos($lat1 * M_PI / 180) * cos($lat2 * M_PI / 180) *
         sin($dLng/2) * sin($dLng/2);
    return $R * 2 * atan2(sqrt($a), sqrt(1-$a));
}

// إضافة المسافة وترتيب
foreach ($eczaneler as &$e) {
    $lat = floatval($e['enlem'] ?? 37.8713);
    $lng = floatval($e['boylam'] ?? 32.4846);
    $e['mesafe'] = mesafe($user_lat, $user_lng, $lat, $lng);
}
usort($eczaneler, function($a, $b) {
    return $a['mesafe'] <=> $b['mesafe'];
});
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Eczane Haritası</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; }
    #map { height: 100vh; width: 100%; }
    #panel {
      position: fixed; bottom: 0; left: 0; right: 0;
      background: white; padding: 15px;
      box-shadow: 0 -3px 15px rgba(0,0,0,0.3);
      display: none; z-index: 1000;
      border-radius: 20px 20px 0 0;
    }
    #panel h3 { color: #6200ea; margin-bottom: 8px; font-size: 18px; }
    .info-row { display: flex; align-items: center; gap: 8px; margin: 5px 0; color: #555; }
    #dirBtn {
      background: linear-gradient(135deg, #6200ea, #3700b3);
      color: white; border: none;
      padding: 12px; border-radius: 10px;
      width: 100%; font-size: 16px;
      margin-top: 12px; cursor: pointer;
    }
    #closeBtn {
      position: absolute; top: 12px; right: 15px;
      background: #f0f0f0; border: none;
      width: 30px; height: 30px;
      border-radius: 50%; font-size: 16px; cursor: pointer;
    }
    #enYakinBtn {
      position: fixed; top: 15px; right: 15px;
      background: #e53935; color: white;
      border: none; padding: 10px 15px;
      border-radius: 10px; z-index: 1000;
      font-size: 14px; cursor: pointer;
      box-shadow: 0 3px 8px rgba(0,0,0,0.3);
    }
    .legend {
      position: fixed; top: 15px; left: 15px;
      background: white; padding: 10px;
      border-radius: 10px; z-index: 1000;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
      font-size: 13px;
    }
    .legend-item { display: flex; align-items: center; gap: 5px; margin: 3px 0; }
    .dot { width: 12px; height: 12px; border-radius: 50%; }
  </style>
</head>
<body>

<button id="enYakinBtn" onclick="enYakineGit()">📍 En Yakın</button>

<div class="legend">
  <div class="legend-item"><div class="dot" style="background:red"></div> En Yakın</div>
  <div class="legend-item"><div class="dot" style="background:green"></div> Tüm ilaçlar var</div>
  <div class="legend-item"><div class="dot" style="background:orange"></div> Kısmi stok</div>
  <div class="legend-item"><div class="dot" style="background:blue"></div> Konumunuz</div>
</div>

<div id="map"></div>

<div id="panel">
  <button id="closeBtn" onclick="kapat()">✕</button>
  <h3 id="panelAd"></h3>
  <div class="info-row">📍 <span id="panelAdres"></span></div>
  <div class="info-row">📞 <span id="panelTelefon"></span></div>
  <div class="info-row">🚗 <span id="panelMesafe"></span></div>
  <div class="info-row">💊 <span id="panelStok"></span></div>
  <button id="dirBtn" onclick="yolTarifi()">🗺️ Yol Tarifi Al</button>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var eczaneler = <?php echo json_encode($eczaneler); ?>;
var userLat = <?php echo $user_lat; ?>;
var userLng = <?php echo $user_lng; ?>;
var selectedLat, selectedLng;

// تهيئة الخريطة
var map = L.map('map').setView([userLat, userLng], 14);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap'
}).addTo(map);

// موقع المستخدم
L.circleMarker([userLat, userLng], {
  radius: 10, color: 'blue', fillColor: 'blue',
  fillOpacity: 0.8, weight: 3
}).addTo(map).bindPopup('<b>📍 Konumunuz</b>').openPopup();

// دالة إنشاء أيقونة ملونة
function createIcon(color, size) {
  return L.divIcon({
    html: '<div style="background:' + color + ';width:' + size + 'px;height:' + size + 'px;border-radius:50%;border:3px solid white;box-shadow:0 2px 5px rgba(0,0,0,0.4);"></div>',
    iconSize: [size, size],
    iconAnchor: [size/2, size/2],
    className: ''
  });
}

// إضافة الصيدليات
eczaneler.forEach(function(e, i) {
  var lat = parseFloat(e.enlem || 37.8713);
  var lng = parseFloat(e.boylam || 32.4846);

  var color, size;
  if (i === 0) { color = 'red'; size = 28; }
  else if (e.tip === 'tam') { color = 'green'; size = 22; }
  else { color = 'orange'; size = 22; }

  var marker = L.marker([lat, lng], { icon: createIcon(color, size) }).addTo(map);

  // نافذة منبثقة صغيرة
  var mesafeTxt = e.mesafe < 1
    ? Math.round(e.mesafe * 1000) + ' m'
    : e.mesafe.toFixed(1) + ' km';

  marker.bindPopup(
    '<b>' + (i === 0 ? '⭐ ' : '') + e.ad + '</b><br>' +
    '📍 ' + (e.adres || '') + '<br>' +
    '🚗 ' + mesafeTxt + '<br>' +
    '💊 ' + e.bulunan_ilac + '/' + e.toplam_ilac + ' ilaç<br>' +
    '<button onclick="selectedLat=' + lat + ';selectedLng=' + lng + ';yolTarifi()" ' +
    'style="background:#6200ea;color:white;border:none;padding:5px 10px;border-radius:5px;margin-top:5px;cursor:pointer">' +
    '🗺️ Yol Tarifi Al</button>'
  );

  marker.on('click', function() {
    selectedLat = lat;
    selectedLng = lng;
    document.getElementById('panelAd').textContent = (i === 0 ? '⭐ En Yakın: ' : '') + e.ad;
    document.getElementById('panelAdres').textContent = e.adres || '';
    document.getElementById('panelTelefon').textContent = e.telefon || '';
    document.getElementById('panelMesafe').textContent = e.mesafe < 1
      ? Math.round(e.mesafe * 1000) + ' metre uzakta'
      : e.mesafe.toFixed(1) + ' km uzakta';
    document.getElementById('panelStok').textContent =
      e.bulunan_ilac + '/' + e.toplam_ilac + ' ilaç mevcut';
    document.getElementById('panel').style.display = 'block';
  });
});

// التكبير على الأقرب
if (eczaneler.length > 0) {
  var enYakin = eczaneler[0];
  map.setView([
    parseFloat(enYakin.enlem || 37.8713),
    parseFloat(enYakin.boylam || 32.4846)
  ], 15);
}

function enYakineGit() {
  if (eczaneler.length > 0) {
    var e = eczaneler[0];
    map.setView([parseFloat(e.enlem || 37.8713), parseFloat(e.boylam || 32.4846)], 16);
  }
}

function yolTarifi() {
  var url = "https://www.google.com/maps/dir/" +
    userLat + "," + userLng + "/" +
    selectedLat + "," + selectedLng;
  window.open(url, '_blank');
}

function kapat() {
  document.getElementById('panel').style.display = 'none';
}
</script>
</body>
</html>
