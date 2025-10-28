<?php
require 'connect.php';

// Fetch logs with reservation details
$logs = $conn->query("
  SELECT t.LogID, r.InstructorName, r.SubjectCode, r.CourseSection,
         r.Campus, r.Building, r.Date, r.Time,
         t.PDFPath, t.LoggedAt
  FROM TransactionLogs t
  JOIN Reservations r ON t.ReservationID = r.ReservationID
  ORDER BY t.LoggedAt DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Transaction Logs</title>
  <link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="admin.css">
  <style>
    .log-card {
      background: var(--room-bg);
      border: 1px solid #cfcfcf;
      border-radius: 8px;
      padding: 14px 16px;
      margin-bottom: 12px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .log-meta strong {
      display: block;
      font-size: 13px;
      color: #111;
    }
    .log-meta small {
      display: block;
      color: #444;
      font-size: 12px;
      margin-top: 4px;
      line-height: 1.4;
    }
    .pdf-btn {
      background: var(--accent-yellow);
      border: none;
      padding: 8px 14px;
      border-radius: 6px;
      font-weight: 700;
      cursor: pointer;
      transition: transform 0.15s ease;
    }
    .pdf-btn:hover {
      transform: scale(1.05);
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

<div class="contentTransact">
  <div class="right-col">
    <div class="right-outer">
      <div class="right-inner">
        <h2>Transaction Logs</h2>
        <?php if ($logs->num_rows > 0): ?>
          <?php while($row = $logs->fetch_assoc()): ?>
            <div class="log-card">
              <div class="log-meta">
                <strong><?= htmlspecialchars($row['SubjectCode']) ?> — <?= htmlspecialchars($row['InstructorName']) ?></strong>
                <small>
                  <?= htmlspecialchars($row['CourseSection']) ?><br>
                  <?= htmlspecialchars($row['Campus']) ?> - <?= htmlspecialchars($row['Building']) ?><br>
                  <?= htmlspecialchars($row['Date']) ?> | <?= htmlspecialchars($row['Time']) ?><br>
                  Logged: <?= htmlspecialchars($row['LoggedAt']) ?>
                </small>
              </div>
              <?php if ($row['PDFPath']): ?>
                <a href="<?= htmlspecialchars($row['PDFPath']) ?>" target="_blank">
                  <button class="pdf-btn">Open PDF</button>
                </a>
              <?php else: ?>
                <button class="pdf-btn" disabled>N/A</button>
              <?php endif; ?>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="log-card">
            <div class="log-meta">
              <strong>No logs found</strong>
              <small>There are no transactions yet.</small>
            </div>
          </div>
        <?php endif; ?>
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
  <div class="back2Dash">
	  <a href="main.php">Back to Dashboard</a>
  </div>
  <hr>
  <button class="menu-item">Log-out Admin</button>
</div>

<script>
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
    window.location.href = 'index.html';
  }
});

document.querySelector('.Transactionbtn').addEventListener('click', () => {
  window.location.href = 'transaction_logs.php';
});
</script>
</body>
</html>
