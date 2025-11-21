// --- FORM ELEMENTS & TOGGLES ---
const loginForm = document.getElementById('loginForm');
const signupForm = document.getElementById('signupForm');
const switchToSignup = document.getElementById('switchToSignup');
const switchToLogin = document.getElementById('switchToLogin');

// --- LOGIN INPUTS & ERRORS ---
const loginEmailInput = document.getElementById('loginEmail');
const loginPasswordInput = document.getElementById('loginPassword');
const loginEmailError = document.getElementById('loginEmailError');
const loginEmailFormatError = document.getElementById('loginEmailFormatError');
const loginPasswordError = document.getElementById('loginPasswordError');

// --- SIGNUP INPUTS & ERRORS ---
const signupEmailInput = document.getElementById('signupEmail');
const signupPasswordInput = document.getElementById('signupPassword');
const signupConfirmInput = document.getElementById('signupConfirmPassword');
const signupEmailError = document.getElementById('signupEmailError');
const signupEmailFormatError = document.getElementById('signupEmailFormatError');
const signupPasswordError = document.getElementById('signupPasswordError');
const signupConfirmError = document.getElementById('signupConfirmError');
const signupMatchError = document.getElementById('signupMatchError');

// --- TOGGLE LOGIC ---
// Show Sign Up Form
switchToSignup.addEventListener('click', (e) => {
    e.preventDefault();
    loginForm.style.display = 'none';
    signupForm.style.display = 'block';
});

// Show Login Form
switchToLogin.addEventListener('click', (e) => {
    e.preventDefault();
    signupForm.style.display = 'none';
    loginForm.style.display = 'block';
});

// --- VALIDATION FUNCTIONS ---

// General Email Validation
const validateEmail = (emailInput, emptyError, formatError) => {
    emptyError.style.display = 'none';
    formatError.style.display = 'none';
    
    const emailValue = emailInput.value.trim();
    if (emailValue === '') {
        emptyError.style.display = 'block';
        return false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue)) {
        formatError.style.display = 'block';
        return false;
    }
    return true;
};

// General Password Validation (checks if empty)
const validatePassword = (passwordInput, errorElement) => {
    errorElement.style.display = 'none';
    if (passwordInput.value.trim() === '') {
        errorElement.style.display = 'block';
        return false;
    }
    return true;
};


// --- FORM SUBMISSION HANDLERS ---

// Login Form Validation
loginForm.addEventListener('submit', (e) => {
    let valid = true;
    
    if (!validateEmail(loginEmailInput, loginEmailError, loginEmailFormatError)) {
        valid = false;
    }
    
    if (!validatePassword(loginPasswordInput, loginPasswordError)) {
        valid = false;
    }
    
    if (!valid) {
        e.preventDefault(); // Stop form submission if invalid
    }
    // If valid, the form posts to login.php
});


// Sign Up Form Validation
signupForm.addEventListener('submit', (e) => {
    let valid = true;
    
    // 1. Validate Email
    if (!validateEmail(signupEmailInput, signupEmailError, signupEmailFormatError)) {
        valid = false;
    }

    // 2. Validate Password
    if (!validatePassword(signupPasswordInput, signupPasswordError)) {
        valid = false;
    }

    // 3. Validate Confirm Password (and matching)
    signupConfirmError.style.display = 'none';
    signupMatchError.style.display = 'none';
    
    const confirmValue = signupConfirmInput.value.trim();
    const passwordValue = signupPasswordInput.value.trim();

    if (confirmValue === '') {
        signupConfirmError.style.display = 'block';
        valid = false;
    } else if (passwordValue !== '' && confirmValue !== passwordValue) {
        // Only check for match if the main password field isn't empty
        signupMatchError.style.display = 'block';
        valid = false;
    }
    
    if (!valid) {
        e.preventDefault(); // Stop form submission if invalid
    }
    // If valid, the form posts to signup.php

    
});

document.addEventListener("DOMContentLoaded", () => {
    // Toggle between login and signup forms
    const loginForm = document.getElementById("loginForm");
    const signupForm = document.getElementById("signupForm");
    const toSignup = document.getElementById("switchToSignup");
    const toLogin = document.getElementById("switchToLogin");
  
    if (toSignup) {
      toSignup.addEventListener("click", (e) => {
        e.preventDefault();
        loginForm.style.display = "none";
        signupForm.style.display = "block";
      });
    }
  
    if (toLogin) {
      toLogin.addEventListener("click", (e) => {
        e.preventDefault();
        signupForm.style.display = "none";
        loginForm.style.display = "block";
      });
    }
  
    // Password visibility toggles (eye/eye-slash)
    document.querySelectorAll(".togglePassword").forEach((btn) => {
      btn.addEventListener("click", () => {
        const targetId = btn.getAttribute("data-target");
        const input = document.getElementById(targetId);
        if (!input) return;
  
        const isHidden = input.type === "password";
        input.type = isHidden ? "text" : "password";
  
        // Update icon
        btn.textContent = isHidden ? "︶" : "👁";
        btn.setAttribute("aria-label", isHidden ? "Hide password" : "Show password");
      });
    });
  });
  