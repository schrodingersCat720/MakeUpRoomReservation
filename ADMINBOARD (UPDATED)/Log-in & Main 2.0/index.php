<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Form</title>
  <link rel="stylesheet" href="admin.css" />
</head>
<body class="log-in">
  <div class="login-container">
    <img src="plv_logo.png" alt="plv_logo" class="loginLogo">
    <h2 class="admin">Admin</h2>
    <form id="loginForm" action="login.php" method="POST" novalidate>
      <div class="form-group">
        <input class="loginInput"
          type="email"
          name="email"
          id="email"
          placeholder="Enter your email"
          required
        />
        <div class="error" id="emailError">Please enter your email.</div>
        <div class="error" id="emailFormatError">Please enter a valid email address.</div>
      </div>

      <div class="form-group">
        <input class="loginInput"
          type="password"
          name="password"
          id="password"
          placeholder="Enter your password"
          required
        />
        <div class="error" id="passwordError">Please enter your password.</div>
      </div>

      <button class="loginButton" type="submit">Log In</button>
    </form>
  </div>

  <script src="script.js"></script>
</body>
</html>