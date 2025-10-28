const form = document.getElementById('reservationForm');
const inputs = form.querySelectorAll('input');
const submitBtn = document.getElementById('submitBtn');

function checkFormValidity() {
  let isValid = true;
  inputs.forEach(input => {
    if (!input.value.trim()) {
      isValid = false;
    }
  });
  submitBtn.disabled = !isValid;
}

inputs.forEach(input => {
  input.addEventListener('input', checkFormValidity);
});

form.addEventListener('submit', function (e) {
  e.preventDefault();
  alert("Reservation submitted successfully!");
  form.reset();
  checkFormValidity();
});
