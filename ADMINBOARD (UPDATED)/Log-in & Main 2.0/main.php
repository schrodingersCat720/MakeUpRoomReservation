<?php 
require_once __DIR__ . '/auth_check.php';

include 'connect.php';
include 'weekly_reset.php';

/* -----------------------------
   AJAX HANDLERS
------------------------------*/

// Get buildings for selected campus
if (isset($_POST['ajax']) && $_POST['ajax'] === 'getBuildings') {
    $campusID = $_POST['campusID'] ?? null;
    if (!$campusID || !is_numeric($campusID)) {
        echo "<option value=''>Select a valid campus</option>";
        exit;
    }

    $stmt = $conn->prepare("SELECT BuildingName FROM Buildings WHERE CampusID = ?");
    $stmt->bind_param("i", $campusID);
    $stmt->execute();
    $result = $stmt->get_result();

    $options = "<option value=''>All buildings</option>";
    while ($row = $result->fetch_assoc()) {
        $building = htmlspecialchars($row['BuildingName']);
        $options .= "<option value='$building'>$building</option>";
    }
    echo $options;
    exit;
}

// Get available time slots
if (isset($_POST['ajax']) && $_POST['ajax'] === 'getTimes') {
    $buildingName = $_POST['buildingName'] ?? '';
    $selectedDate = $_POST['selectedDate'] ?? '';
    $dayOfWeek = $selectedDate ? date('l', strtotime($selectedDate)) : '';
    $likeDay = "%$dayOfWeek%";

    $stmt = $conn->prepare("
        SELECT DISTINCT r.TimeAvailable 
        FROM Rooms r
        JOIN Buildings b ON r.BuildingID = b.BuildingID
        WHERE b.BuildingName = ? AND r.DaysAvailable LIKE ?
    ");
    $stmt->bind_param("ss", $buildingName, $likeDay);
    $stmt->execute();
    $result = $stmt->get_result();

    $options = "<option value=''>All times</option>";
    while ($row = $result->fetch_assoc()) {
        $time = htmlspecialchars($row['TimeAvailable']);
        $options .= "<option value='$time'>$time</option>";
    }
    echo $options;
    exit;
}

/* -----------------------------
   FILTER HANDLER
------------------------------*/
$rooms = [];
$selectedCampus = $_POST['campus'] ?? '';
$selectedBuilding = $_POST['building'] ?? '';
$selectedTime = $_POST['prefTime'] ?? '';
$selectedDate = $_POST['date'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax'])) {
    $dayOfWeek = $selectedDate ? date('l', strtotime($selectedDate)) : '';

    $query = "SELECT r.RoomID, r.RoomName, b.BuildingName, c.CampusName, r.TimeAvailable 
              FROM Rooms r
              JOIN Buildings b ON r.BuildingID = b.BuildingID
              JOIN Campus c ON b.CampusID = c.CampusID
              WHERE 1=1";

    $params = ["types" => "", "values" => []];

    if (!empty($selectedCampus)) {
        $query .= " AND c.CampusID = ?";
        $params["types"] .= "i";
        $params["values"][] = $selectedCampus;
    }
    if (!empty($selectedBuilding)) {
        $query .= " AND b.BuildingName = ?";
        $params["types"] .= "s";
        $params["values"][] = $selectedBuilding;
    }
    if (!empty($selectedTime)) {
        $query .= " AND r.TimeAvailable = ?";
        $params["types"] .= "s";
        $params["values"][] = $selectedTime;
    }
    if (!empty($selectedDate)) {
        // Only show rooms available on that weekday
        $query .= " AND FIND_IN_SET(?, r.DaysAvailable) > 0";
        $params["types"] .= "s";
        $params["values"][] = $dayOfWeek;

        // Exclude rooms already reserved for that exact date
        $query .= " AND r.RoomID NOT IN (
                      SELECT RoomID FROM Reservations 
                      WHERE Date = ? AND Status = 'active'
                    )";
        $params["types"] .= "s";
        $params["values"][] = $selectedDate;
    }

    $stmt = $conn->prepare($query);
    if ($params["types"]) {
        $stmt->bind_param($params["types"], ...$params["values"]);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Room Reservation</title>
    <link rel="stylesheet" href="admin.css" />
    <style>
        .room-card {
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 6px;
        }
        .room-card.no-rooms {
            background-color: #f9f9f9;
            border: 1px dashed #aaa;
            text-align: center;
            color: #555;
        }
    </style>
</head>
<body>
<nav>
    <div class="nav-left">
      <img src="plv_logo.png" alt="PLV Logo">
      <div class="title-block">
        <h1>Pamantasan ng Lungsod ng Valenzuela</h1>
        <p>Make-Up Class Room Reservation</p>
      </div>
    </div>
    <div class="menu-icon" id="menuToggle">☰</div>
</nav>

<div class="content">
    <div class="left-col">
        <form method="POST" class="filter-card" id="filterForm">
            <!-- Campus -->
            <label for="campus">Select a Campus:</label>
            <select name="campus" id="campus">
                <option value="">All campuses</option>
                <?php
                $campusQuery = $conn->query("SELECT CampusID, CampusName FROM Campus");
                while ($row = $campusQuery->fetch_assoc()) {
                    $selected = ($selectedCampus == $row['CampusID']) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($row['CampusID']) . "' $selected>" . htmlspecialchars($row['CampusName']) . "</option>";
                }
                ?>
            </select>

            <!-- Building -->
            <label for="building">Select a Building:</label>
            <select name="building" id="building">
                <?php
                if ($selectedCampus) {
                    $stmt = $conn->prepare("SELECT BuildingName FROM Buildings WHERE CampusID = ?");
                    $stmt->bind_param("i", $selectedCampus);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    echo "<option value=''>All buildings</option>";
                    while ($row = $result->fetch_assoc()) {
                        $building = htmlspecialchars($row['BuildingName']);
                        $selected = ($selectedBuilding === $building) ? 'selected' : '';
                        echo "<option value='$building' $selected>$building</option>";
                    }
                } else {
                    echo "<option value=''>Select a campus first</option>";
                }
                ?>
            </select>

            <!-- Date -->
            <label for="date">Select Date:</label>
            <input type="date" name="date" id="date" required 
                   value="<?= htmlspecialchars($selectedDate) ?>">

            <!-- Time -->
            <label for="prefTime">Preferred Time:</label>
            <select name="prefTime" id="prefTime">
                <option value="">All times</option>
            </select>

            <button type="submit" class="find-btn">Find</button>
        </form>
    </div>

    <div class="divider"></div>

    <!-- Results -->
    <div class="right-col">
        <div class="right-outer">
            <div class="right-inner">
                <h2>Available Rooms:</h2>
                <div id="roomList">
                    <?php if (!empty($rooms)): ?>
                        <?php foreach ($rooms as $room): ?>
                            <div class="room-card">
                                <div class="room-meta">
                                    <strong><?= htmlspecialchars($room["RoomName"]) ?></strong>
                                    <small>
                                        <?= htmlspecialchars($room["CampusName"]) ?> - <?= htmlspecialchars($room["BuildingName"]) ?><br>
                                        <?= htmlspecialchars(date('l', strtotime($selectedDate))) ?><br>
                                        <?= htmlspecialchars($room["TimeAvailable"]) ?>
                                    </small>
                                </div>
                                <button 
                                  class="select-btn" 
                                  onclick="openReservationForm(
                                    '<?= $room['RoomID'] ?>',
                                    '<?= htmlspecialchars($room['CampusName']) ?>',
                                                                        '<?= htmlspecialchars($room['BuildingName']) ?>',
                                    '<?= htmlspecialchars($selectedDate) ?>',
                                    '<?= htmlspecialchars($room['TimeAvailable']) ?>'
                                  )"
                                >
                                  Select
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="room-card no-rooms">
                            <div class="room-meta">
                                <strong>No rooms available</strong>
                                <small>for the selected filters</small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- OVERLAY AND SIDE MENU -->
<div class="overlay" id="overlay"></div>

<div class="side-menu" id="sideMenu">
  <div class="back-btn" id="backBtn">◀</div>
  <h3>MENU</h3>
  <button class="Transactionbtn">Transaction Logs</button>
  <hr>
  <button class="menu-item">Log-out Admin</button>
</div>

<script>
const campusSelect = document.getElementById('campus');
const buildingSelect = document.getElementById('building');
const timeSelect = document.getElementById('prefTime');
const dateInput = document.getElementById('date');

// Load buildings when campus changes
campusSelect.addEventListener('change', () => {
    const campusID = campusSelect.value;
    buildingSelect.innerHTML = "<option>Loading...</option>";
    timeSelect.innerHTML = "<option value=''>All times</option>";

    fetch('main.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'ajax=getBuildings&campusID=' + encodeURIComponent(campusID)
    })
    .then(res => res.text())
    .then(data => {
        buildingSelect.innerHTML = data;
    });
});

// Load time slots when building + date are selected
function loadTimeSlots() {
    const buildingName = buildingSelect.value;
    const selectedDate = dateInput.value;

    if (!buildingName || !selectedDate) {
        timeSelect.innerHTML = "<option value=''>All times</option>";
        return;
    }

    timeSelect.innerHTML = "<option>Loading...</option>";

    fetch('main.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'ajax=getTimes&buildingName=' + encodeURIComponent(buildingName) +
              '&selectedDate=' + encodeURIComponent(selectedDate)
    })
    .then(res => res.text())
    .then(data => {
        timeSelect.innerHTML = data;
    });
}

buildingSelect.addEventListener('change', loadTimeSlots);
dateInput.addEventListener('change', loadTimeSlots);

// Side menu
const menuToggle = document.getElementById('menuToggle');
const sideMenu = document.getElementById('sideMenu');
const overlay = document.getElementById('overlay');
const backBtn = document.getElementById('backBtn');

menuToggle.addEventListener('click', () => {
  sideMenu.classList.add('active');
  overlay.classList.add('active');
});

backBtn.addEventListener('click', closeMenu);
overlay.addEventListener('click', closeMenu);

function closeMenu(){
  sideMenu.classList.remove('active');
  overlay.classList.remove('active');
}

const logoutAdminBtn = document.querySelectorAll('.menu-item')[0];
logoutAdminBtn.addEventListener('click', () => {
  const confirmLogout = confirm('Are you sure you want to log out?');
  if (confirmLogout) {
    window.location.href = 'index.php';
  }
});

// Transaction logs button
document.querySelector('.Transactionbtn').addEventListener('click', () => {
  window.location.href = 'transaction_logs.php';
});

// Reservation form redirect
function openReservationForm(roomID, campus, building, date, time) {
  const params = new URLSearchParams({
    roomID: roomID,
    campus: campus,
    building: building,
    date: date,   // <-- this is already YYYY-MM-DD
    time: time
  });
  window.location.href = `Reservation Form/form.html?${params.toString()}`;
}

</script>
</body>
</html>
