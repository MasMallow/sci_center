// Function to open modal
function openModal() {
  document.querySelector('.logoutMODAL').style.display = 'flex';
}

// Function to close modal
function closeModal() {
  document.querySelector('.logoutMODAL').style.display = 'none';
}

// Event listener for the button to open the modal
document.getElementById('userInfoBtn').addEventListener('click', openModal);

// Event listener for the close button inside the modal
document.querySelectorAll('.cancel_del').forEach(button => {
  button.addEventListener('click', closeModal);
});

// Event listener to close modal if clicked outside of it
document.addEventListener('click', (event) => {
  if (event.target.classList.contains('logoutMODAL')) {
      closeModal();
  }
});
