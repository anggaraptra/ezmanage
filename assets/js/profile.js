// Toggle menu dropdown profil
const btn = document.getElementById('profileDropdownBtn');
const menu = document.getElementById('profileDropdownMenu');
btn.addEventListener('click', function (e) {
  e.stopPropagation();
  menu.classList.toggle('hidden');
});
document.addEventListener('click', function () {
  menu.classList.add('hidden');
});

// Logika toggle mode gelap (dark mode)
const darkModeToggle = document.getElementById('darkModeToggle');
const html = document.documentElement;
// Cek awal preferensi tema
if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
  html.classList.add('dark');
} else {
  html.classList.remove('dark');
}
// Event untuk mengubah mode gelap/terang
darkModeToggle.addEventListener('click', () => {
  html.classList.toggle('dark');
  if (html.classList.contains('dark')) {
    localStorage.setItem('theme', 'dark');
  } else {
    localStorage.setItem('theme', 'light');
  }
});

// Preview gambar profil yang di-upload
document.getElementById('profilePicInput').addEventListener('change', function (e) {
  const [file] = e.target.files;
  if (file) {
    document.getElementById('profileImage').src = URL.createObjectURL(file);
  }
});

// Fungsi untuk menampilkan preview gambar profil
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

// Fungsi untuk membuka modal hapus foto profil
function openDeletePhotoModal() {
  document.getElementById('deletePhotoModal').classList.remove('hidden');
}
// Fungsi untuk menutup modal hapus foto profil
function closeDeletePhotoModal() {
  document.getElementById('deletePhotoModal').classList.add('hidden');
}

// Fungsi untuk membuka modal hapus akun
function openDeleteModal() {
  document.getElementById('deleteAccountModal').classList.remove('hidden');
}
// Fungsi untuk menutup modal hapus akun
function closeDeleteModal() {
  document.getElementById('deleteAccountModal').classList.add('hidden');
}
