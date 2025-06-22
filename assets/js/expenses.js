// Script toggle dropdown profil
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
if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
  html.classList.add('dark');
} else {
  html.classList.remove('dark');
}
darkModeToggle.addEventListener('click', () => {
  html.classList.toggle('dark');
  if (html.classList.contains('dark')) {
    localStorage.setItem('theme', 'dark');
  } else {
    localStorage.setItem('theme', 'light');
  }
});

// Fungsi untuk membuka modal edit kategori
function openEditCategoryModal(id, name) {
  document.getElementById('edit_category_id').value = id;
  document.getElementById('edit_category_name').value = name;
  document.getElementById('editCategoryModal').classList.remove('hidden');
}

// Fungsi untuk menutup modal edit kategori
function closeEditCategoryModal() {
  document.getElementById('editCategoryModal').classList.add('hidden');
}

// Fungsi untuk membuka modal hapus kategori
function openDeleteCategoryModal(id, name) {
  document.getElementById('delete_category_id').value = id;
  document.getElementById('delete_category_name').textContent = name;
  document.getElementById('deleteCategoryModal').classList.remove('hidden');
}

// Fungsi untuk menutup modal hapus kategori
function closeDeleteCategoryModal() {
  document.getElementById('deleteCategoryModal').classList.add('hidden');
}
