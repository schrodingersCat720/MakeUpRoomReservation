<?php 
require_once __DIR__ . '/auth_check.php';
include 'connect.php';
include 'weekly_reset.php';

/* -----------------------------
   AJAX HANDLERS
------------------------------*/

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
$selectedCampus = $_POST['campus'] ?? ''; // campus now comes from tab
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
        $query .= " AND FIND_IN_SET(?, r.DaysAvailable) > 0";
        $params["types"] .= "s";
        $params["values"][] = $dayOfWeek;

        $query .= " AND r.RoomID NOT IN (
                      SELECT RoomID FROM Reservations 
                      WHERE Date = ? AND Status = 'active'
                    )";
        $params["types"] .= "s";
        $params["values"][] = $selectedDate;
    }

    $query .= " ORDER BY STR_TO_DATE(r.TimeAvailable, '%h:%i %p') ASC";

    $stmt = $conn->prepare($query);
    if ($params["types"]) {
        $bind_names[] = $params["types"];
        for ($i=0; $i<count($params["values"]); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $params["values"][$i];
            $bind_names[] = &$$bind_name;
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
} else {
    $allRoomsQuery = "SELECT r.RoomID, r.RoomName, b.BuildingName, c.CampusName, r.TimeAvailable 
                      FROM Rooms r
                      JOIN Buildings b ON r.BuildingID = b.BuildingID
                      JOIN Campus c ON b.CampusID = c.CampusID
                      ORDER BY STR_TO_DATE(r.TimeAvailable, '%h:%i %p') ASC";
    $result = $conn->query($allRoomsQuery);
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
        .room-card { background:#fff; border:1px solid #ccc; padding:15px; margin-bottom:10px; border-radius:6px; }
        .room-card.no-rooms { background:#f9f9f9; border:1px dashed #aaa; text-align:center; color:#555; }

        .popup-overlay { position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); display:none; justify-content:center; align-items:center; z-index:2000; }
        .popup-overlay.active { display:flex; }
        .popup-box { background:#fff; padding:20px; border-radius:8px; width:360px; max-width:90%; text-align:center; box-shadow:0 6px 18px rgba(0,0,0,0.2); }
        .popup-box input[type="file"], .popup-box select { width:100%; margin-bottom:10px; }
        .update-msg { margin-top:10px; }
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
    <div class="greetings">
        <div><h4>Hello, PLV Admin</h4></div>
        <div><a href="admin_profile.html"><img src="image/profIcon.png" alt="profileIcon" class="profIcon"></a></div>
    </div>
    <div class="menu-icon" id="menuToggle">☰</div>
</nav>

<div class="content">
    <div class="left-col">
        <form method="POST" class="filter-card" id="filterForm">
            <label for="building">Select a Building:</label>
            <select name="building" id="building">
                <option value="">All buildings</option>
                <?php
                if ($selectedCampus) {
                    $stmt = $conn->prepare("SELECT BuildingName FROM Buildings WHERE CampusID = ?");
                    $stmt->bind_param("i", $selectedCampus);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        $building = htmlspecialchars($row['BuildingName']);
                        $selected = ($selectedBuilding === $building) ? 'selected' : '';
                        echo "<option value='$building' $selected>$building</option>";
                    }
                }
                ?>
            </select>

            <label for="date">Select Date:</label>
            <input type="date" name="date" id="date" required value="<?= htmlspecialchars($selectedDate) ?>">

            <label for="prefTime">Preferred Time:</label>
            <select name="prefTime" id="prefTime">
                <option value="">All times</option>
            </select>

            <button type="submit" class="find-btn">Find</button>
        </form>

        <button class="find-btn" id="updateBtn">Update Database</button>
    </div>

    <div class="divider"></div>

    <div class="right-col">
        <div class="dashCampus">
            <button class="campusButton" id="lButton">MAIN</button>
            <button class="campusButton">ANNEX</button>
            <button class="campusButton" id="rButton">CPAG</button>
        </div>
        <div class="right-outer">
            <div class="right-inner">
                <h2>Available Rooms for <?= $selectedDate ? htmlspecialchars($selectedDate) : '[Date]' ?> at <?= $selectedTime ? htmlspecialchars($selectedTime) : '[Time]' ?>:</h2>
                <div id="roomList">
                    <?php if (!empty($rooms)): ?>
                        <?php foreach ($rooms as $room): ?>
                            <div class="room-card">
                                <div class="room-meta">
                                    <strong><?= htmlspecialchars($room["RoomName"]) ?></strong>
                                    <small>
                                        <?= htmlspecialchars($room["CampusName"]) ?> - <?= htmlspecialchars($room["BuildingName"]) ?><br>
                                        <?= htmlspecialchars($selectedDate ? date('l', strtotime($selectedDate)) : '') ?><br>
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
                                >Select</button>
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

<!-- UPDATE DATABASE POPUP -->
<div id="updatePopup" class="popup-overlay" aria-hidden="true">
  <div class="popup-box" role="dialog" aria-modal="true" aria-labelledby="updateTitle">
      <h3 id="updateTitle">Update Database</h3>
      <p>Upload an Excel (.xlsx) file to update the database. Choose the table first:</p>

      <form id="uploadForm" enctype="multipart/form-data" method="post" novalidate>
          <label for="tableSelect">Select Table:</label>
          <select name="table" id="tableSelect" required>
              <option value="">--Select Table--</option>
              <option value="rooms">Rooms</option>
              <option value="teachers">Teachers</option>
              <option value="subjects">Subjects</option>
          </select>

          <input type="file" name="file" accept=".xlsx" required>
          <button type="submit" class="find-btn">Upload & Update</button>
      </form>

      <button onclick="closePopup()" class="close-btn">Close</button>
      <div id="updateMessage" class="update-msg" aria-live="polite"></div>
  </div>
</div>

<div class="overlay" id="overlay"></div>

<script>
const buildingSelect = document.getElementById('building');
const timeSelect = document.getElementById('prefTime');
const dateInput = document.getElementById('date');
const filterForm = document.getElementById('filterForm');

// Load time slots when building + date are selected
function loadTimeSlots() {
    const buildingName = buildingSelect.value;
    const selectedDate = dateInput.value;
    if (!buildingName || !selectedDate) { timeSelect.innerHTML = "<option value=''>All times</option>"; return; }
    timeSelect.innerHTML = "<option>Loading...</option>";
    fetch('main.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'ajax=getTimes&buildingName=' + encodeURIComponent(buildingName) +
              '&selectedDate=' + encodeURIComponent(selectedDate)
    })
    .then(res => res.text())
    .then(data => { timeSelect.innerHTML = data; });
}
buildingSelect.addEventListener('change', loadTimeSlots);
dateInput.addEventListener('change', loadTimeSlots);

// Campus tabs (preserve design)
document.querySelectorAll('.campusButton').forEach(btn => {
    btn.addEventListener('click', () => {
        let campusId = 0;
        const text = btn.textContent.trim();
        if(text === 'MAIN') campusId = 1;
        else if(text === 'ANNEX') campusId = 2;
        else if(text === 'CPAG') campusId = 3;

        // Create or update hidden input for campus
        let campusInput = document.querySelector('input[name="campus"]');
        if(!campusInput){
            campusInput = document.createElement('input');
            campusInput.type = 'hidden';
            campusInput.name = 'campus';
            filterForm.appendChild(campusInput);
        }
        campusInput.value = campusId;
        filterForm.submit();
    });
});

// Update Database popup
const updateBtn = document.getElementById('updateBtn');
const updatePopup = document.getElementById('updatePopup');
const uploadForm = document.getElementById('uploadForm');
const updateMessage = document.getElementById('updateMessage');

updateBtn.addEventListener('click', () => {
    updatePopup.classList.add('active');
    updatePopup.setAttribute('aria-hidden', 'false');
    updateMessage.innerHTML = '';
});

function closePopup() {
    updatePopup.classList.remove('active');
    updatePopup.setAttribute('aria-hidden', 'true');
}

uploadForm.addEventListener('submit', function (e) {
    e.preventDefault();
    updateMessage.innerHTML = 'Uploading...';

    const formData = new FormData(this);
    const selectedTable = document.getElementById('tableSelect').value;
    if (!selectedTable) { updateMessage.innerHTML = "<p style='color:red;'>Please select a table.</p>"; return; }
    formData.append('table', selectedTable);

    fetch("update_rooms.php", { method: "POST", body: formData })
    .then(res => res.text())
    .then(data => { updateMessage.innerHTML = data; })
    .catch(err => { updateMessage.innerHTML = "<p style='color:red;'>Upload failed.</p>"; });
});
</script>
</body>
</html>
