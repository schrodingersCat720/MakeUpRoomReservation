 const form = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const emailError = document.getElementById('emailError');
    const emailFormatError = document.getElementById('emailFormatError');
    const passwordError = document.getElementById('passwordError');

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      let valid = true;

      // Hide all error messages
      emailError.style.display = 'none';
      emailFormatError.style.display = 'none';
      passwordError.style.display = 'none';

      const emailValue = emailInput.value.trim();
      const passwordValue = passwordInput.value.trim();

      // Validate email
      if (emailValue === '') {
        emailError.style.display = 'block';
        valid = false;
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue)) {
        emailFormatError.style.display = 'block';
        valid = false;
      }

      // Validate password
      if (passwordValue === '') {
        passwordError.style.display = 'block';
        valid = false;
      }
	  
	  //Check credentials after input
	  if (valid) {
		const validEmail = 'admin@plv.edu.ph';
		const validPassword = 'admin123';

		if (emailValue === validEmail && passwordValue === validPassword) {
		  window.location.href = 'main.php'; //redirect
		} else {
		  alert('Invalid email or password!');
		}
	  }

      if (valid) { //placeholder for successful login, replace with redirectory once home page is available
         window.location.href = 'main.php'; 
      }
    });