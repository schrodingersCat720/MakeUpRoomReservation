<?php
session_start();

if (isset($_SESSION['signup_success'])) {
    $message = $_SESSION['signup_success'];
    unset($_SESSION['signup_success']);
    echo "<script>alert('{$message}');</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Account Access</title>
  <link rel="stylesheet" href="admin.css" />
</head>
<body class="log-in">
  <div class="login-container">
    <img src="plv_logo.png" alt="plv_logo" class="loginLogo">
    <h2 class="admin">Admin</h2>

    <!-- LOGIN FORM -->
    <form id="loginForm" action="login.php" method="POST" novalidate>
      <div class="form-group">
        <input class="loginInput"
          type="email"
          name="email"
          id="loginEmail"
          placeholder="Enter your email"
          required
          
        />

        <div class="error" id="loginEmailError">Please enter your email.</div>
        <div class="error" id="loginEmailFormatError">Please enter a valid email address.</div>
      </div>

      <div class="form-group password-group">
        <input class="loginInput"
          type="password"
          name="password"
          id="loginPassword"
          placeholder="Enter your password"
          required
        />
        <button type="button" class="togglePassword" aria-label="Show password" data-target="loginPassword">👁</button>
        <div class="error" id="loginPasswordError">Please enter your password.</div>
      </div>

      <button class="loginButton" type="submit">Log In</button>
      <p class="toggle-text">
        Don't have an account? <a href="#" id="switchToSignup">Sign Up</a>
      </p>
    </form>

    <!-- SIGNUP FORM -->
    <form id="signupForm" action="signup.php" method="POST" novalidate style="display: none;">
        
      <div class="form-group email-input-group">
        <div class="email-wrapper">
            <input class="loginInput email-prefix-input"
                type="text"
                name="email_prefix"
                id="signupEmailPrefix"
                placeholder="Enter your email"
                required
            />
            <span class="email-suffix">@gmail.com</span>
        </div>

        <input type="hidden" name="email" id="signupEmailHidden">

        <div class="error" id="signupEmailError">Please enter your username.</div>
        <div class="error" id="signupEmailFormatError">Please enter a valid username.</div>
      </div>

      <div class="form-group password-group">
        <input class="loginInput"
          type="password"
          name="password"
          id="signupPassword"
          placeholder="Choose a password"
          required
        />
        <button type="button" class="togglePassword" aria-label="Show password" data-target="signupPassword">👁</button>
        <div class="error" id="signupPasswordError">Please choose a password.</div>
      </div>

      <div class="form-group password-group">
        <input class="loginInput"
          type="password"
          name="confirm_password"
          id="signupConfirmPassword"
          placeholder="Confirm your password"
          required
        />
        <button type="button" class="togglePassword" aria-label="Show password" data-target="signupConfirmPassword">👁</button>
        <div class="error" id="signupConfirmError">Please confirm your password.</div>
        <div class="error" id="signupMatchError">Passwords do not match.</div>
      </div>

      <button class="loginButton" type="submit">Sign Up</button>
      <p class="toggle-text">
        Already have an account? <a href="#" id="switchToLogin">Log In</a>
      </p>
    </form>
  </div>

  <script src="script.js"></script>
</body>
</html>
