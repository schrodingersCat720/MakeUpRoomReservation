<?php
session_start();
require 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT email, station, position, task FROM users WHERE user_id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Profile</title>
  <link rel="stylesheet" href="admin.css" />
</head>
<body>

<!-- Navigation -->
<nav>
  <div class="nav-left">
    <img src="plv_logo.png" alt="PLV Logo" />
    <div class="title-block">
      <h1>Pamantasan ng Lungsod ng Valenzuela</h1>
      <p>Make-Up Class Room Reservation</p>
    </div>
  </div>
  <div class="menu-icon" id="menuToggle">☰</div>
</nav>

<!-- Side Menu -->
<div class="overlay" id="overlay"></div>
<div class="side-menu" id="sideMenu">
  <div class="back-btn" id="backBtn">◀</div>
  <h3>MENU</h3>
  <button class="Transactionbtn">Transaction Logs</button>
  <div class="back2Dash"><a href="main.php">Back to Dashboard</a></div>
  <hr />
  <button class="menu-item">Log-out Admin</button>
</div>

<!-- Profile Section -->
<div class="profile-container">
  <h2 class="profile-title">Hello, <?php echo htmlspecialchars($user['email']); ?></h2>
  <div class="profile-card">
    <p><strong>Password:</strong> ********</p>
    <p><strong>Station:</strong> <?php echo htmlspecialchars($user['station']); ?></p>
    <p><strong>Position:</strong> <?php echo htmlspecialchars($user['position']); ?></p>
    <p><strong>Task:</strong> <?php echo htmlspecialchars($user['task']); ?></p>
    <button class="edit-btn" id="editBtn">Edit Profile</button>
  </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Edit Credentials</h3>
      <span class="close" id="closeModal">&times;</span>
    </div>

    <form id="editForm">
      <div class="field-group">
        <label>New Password:</label>
        <input type="password" id="newPassword" name="newPassword"/>
        <span class="toggle-pass" onclick="togglePassword('newPassword', this)" title="Show password">👁</span>
      </div>

      <div class="field-group">
        <label>Confirm Password:</label>
        <input type="password" id="confirmPassword"  />
        <span class="toggle-pass" onclick="togglePassword('confirmPassword', this)" title="Show password">👁</span>
      </div>

      <div class="field-group">
        <label>Station:</label>
        <input type="text" name="station" value="<?php echo htmlspecialchars($user['station']); ?>" required />
      </div>

      <div class="field-group">
        <label>Position:</label>
        <input type="text" name="position" value="<?php echo htmlspecialchars($user['position']); ?>" required />
      </div>

      <div class="field-group">
        <label>Task:</label>
        <input type="text" name="task" value="<?php echo htmlspecialchars($user['task']); ?>" required />
      </div>

      <div class="modal-actions">
        <button type="submit" class="save-btn">Save Changes</button>
        <button type="button" class="cancel-btn" id="cancelBtn">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Scripts -->
<script>
  // Menu toggle
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
  function closeMenu() {
    sideMenu.classList.remove('active');
    overlay.classList.remove('active');
  }

  // Logout
  document.querySelector('.menu-item').addEventListener('click', () => {
    if (confirm('Are you sure you want to log out?')) {
      window.location.href = 'index.php';
    }
  });

  // Transaction logs
  document.querySelector('.Transactionbtn').addEventListener('click', () => {
    window.location.href = 'transaction_logs.php';
  });

  // Modal logic
  const editBtn = document.getElementById('editBtn');
  const editModal = document.getElementById('editModal');
  const closeModal = document.getElementById('closeModal');
  const cancelBtn = document.getElementById('cancelBtn');

  editBtn.addEventListener('click', () => { editModal.style.display = 'block'; });
  closeModal.addEventListener('click', () => { editModal.style.display = 'none'; });
  cancelBtn.addEventListener('click', () => { editModal.style.display = 'none'; });
  window.addEventListener('click', (event) => {
    if (event.target === editModal) editModal.style.display = 'none';
  });

  // Toggle password visibility
  function togglePassword(fieldId, iconElement) {
    const input = document.getElementById(fieldId);
    const isHidden = input.type === "password";
    input.type = isHidden ? "text" : "password";
    iconElement.textContent = isHidden ? "︶" : "👁";
    iconElement.title = isHidden ? "Hide password" : "Show password";
  }

  // Submit form
  document.getElementById('editForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const newPass = document.getElementById('newPassword').value;
    const confirmPass = document.getElementById('confirmPassword').value;
    const station = document.querySelector('input[name="station"]').value;
    const position = document.querySelector('input[name="position"]').value;
    const task = document.querySelector('input[name="task"]').value;

    if (newPass !== confirmPass) {
      alert('Passwords do not match.');
      return;
    }

    fetch('update_credentials.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ newPassword: newPass, station, position, task })
    })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      if (data.status === "success") editModal.style.display = 'none';
    })
    .catch(err => {
      alert("Error updating credentials.");
      console.error(err);
    });
  });
</script>
</body>
</html>
