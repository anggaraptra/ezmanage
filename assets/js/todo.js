// Membuka modal untuk menambah, mengedit, dan menghapus todo
function openModal(type, data = null) {
  // Tampilkan modal
  document.getElementById('modal-todo').classList.remove('hidden');
  // Tampilkan form todo
  document.getElementById('todo-form').style.display = 'block';
  // Sembunyikan grup hapus
  document.getElementById('delete-group').classList.add('hidden');
  // Sembunyikan grup status
  document.getElementById('status-group').style.display = 'none';
  // Reset form todo
  document.getElementById('todo-form').reset();
  // Kosongkan nilai input
  document.getElementById('todo-id').value = '';
  document.getElementById('todo-title').value = '';
  document.getElementById('todo-desc').value = '';
  document.getElementById('todo-due').value = '';
  document.getElementById('todo-priority').value = ''; // Reset prioritas
  // Set teks tombol submit dan judul modal untuk tambah todo
  document.getElementById('modal-submit').innerText = 'Simpan';
  document.getElementById('modal-title').innerText = 'Tambah Todo';

  // Jika mode edit, isi form dengan data todo yang dipilih
  if (type === 'edit' && data) {
    document.getElementById('modal-title').innerText = 'Edit Todo';
    document.getElementById('todo-id').value = data.id;
    document.getElementById('todo-title').value = data.title;
    document.getElementById('todo-desc').value = data.description;
    document.getElementById('todo-due').value = data.due_date;
    document.getElementById('todo-priority').value = data.priority || ''; // Set prioritas
    document.getElementById('status-group').style.display = 'block';
    document.getElementById('todo-status').value = data.status;
    document.getElementById('modal-submit').innerText = 'Update';
  }

  // Jika mode hapus, tampilkan konfirmasi hapus
  if (type === 'delete') {
    document.getElementById('todo-form').style.display = 'none';
    document.getElementById('delete-group').classList.remove('hidden');
    document.getElementById('delete-id').value = data;
  }
}

// Fungsi untuk menutup modal
function closeModal() {
  document.getElementById('modal-todo').classList.add('hidden');
}

// Script untuk toggle dropdown profil
const btn = document.getElementById('profileDropdownBtn');
const menu = document.getElementById('profileDropdownMenu');
btn.addEventListener('click', function (e) {
  e.stopPropagation();
  menu.classList.toggle('hidden');
});
document.addEventListener('click', function () {
  menu.classList.add('hidden');
});

// Logika toggle dark mode
const darkModeToggle = document.getElementById('darkModeToggle');
const html = document.documentElement;
// Cek preferensi tema dari localStorage atau sistem
if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
  html.classList.add('dark');
} else {
  html.classList.remove('dark');
}
// Toggle dark mode saat tombol diklik
darkModeToggle.addEventListener('click', () => {
  html.classList.toggle('dark');
  if (html.classList.contains('dark')) {
    localStorage.setItem('theme', 'dark');
  } else {
    localStorage.setItem('theme', 'light');
  }
});
