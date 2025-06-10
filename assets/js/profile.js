// Toggle profile dropdown menu
const btn = document.getElementById('profileDropdownBtn');
const menu = document.getElementById('profileDropdownMenu');
btn.addEventListener('click', function (e) {
  e.stopPropagation();
  menu.classList.toggle('hidden');
});
document.addEventListener('click', function () {
  menu.classList.add('hidden');
});

// Preview uploaded profile picture
document.getElementById('profilePicInput').addEventListener('change', function (e) {
  const [file] = e.target.files;
  if (file) {
    document.getElementById('profileImage').src = URL.createObjectURL(file);
  }
});

// Function to handle profile image preview
function previewProfileImage(event) {
  const input = event.target;
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById('profileImage').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}
