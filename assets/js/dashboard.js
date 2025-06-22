// Jam Digital (hanya waktu)
function updateClock() {
  const now = new Date();
  const pad = (n) => n.toString().padStart(2, '0');
  document.getElementById('digitalClock').textContent = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
}
setInterval(updateClock, 1000); // Memperbarui jam setiap detik
updateClock(); // Menampilkan jam saat halaman dimuat

// Logika toggle mode gelap (dark mode)
const darkModeToggle = document.getElementById('darkModeToggle');
const html = document.documentElement;
// Cek awal preferensi tema dari localStorage atau preferensi sistem
if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
  html.classList.add('dark');
} else {
  html.classList.remove('dark');
}
// Event ketika tombol dark mode diklik
darkModeToggle.addEventListener('click', () => {
  html.classList.toggle('dark');
  if (html.classList.contains('dark')) {
    localStorage.setItem('theme', 'dark');
  } else {
    localStorage.setItem('theme', 'light');
  }
});

// Fungsi untuk menampilkan kalender mini yang ringkas
function renderMiniCalendar(elemId, date = new Date()) {
  const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  let year = date.getFullYear();
  let month = date.getMonth();
  let today = new Date().toDateString();

  let firstDay = new Date(year, month, 1);
  let lastDay = new Date(year, month + 1, 0);
  let startDay = firstDay.getDay();
  let totalDays = lastDay.getDate();

  // Membuat struktur HTML kalender mini
  let html = `
                                <div class="flex justify-between items-center mb-1">
                                    <button id="prevMonth" class="px-1 py-0.5 rounded hover:bg-blue-600 text-xs">&lt;</button>
                                    <span class="font-semibold text-sm">${months[month]} ${year}</span>
                                    <button id="nextMonth" class="px-1 py-0.5 rounded hover:bg-blue-600 text-xs">&gt;</button>
                                </div>
                                <div class="grid grid-cols-7 gap-0.5 text-xs mb-0.5">
                                    ${days.map((d) => `<div class="text-center font-semibold">${d}</div>`).join('')}
                                </div>
                                <div class="grid grid-cols-7 gap-0.5 text-xs">
                            `;

  // Mengisi hari kosong sebelum tanggal 1
  for (let i = 0; i < startDay; i++) {
    html += `<div></div>`;
  }
  // Mengisi tanggal-tanggal dalam bulan
  for (let d = 1; d <= totalDays; d++) {
    let thisDate = new Date(year, month, d);
    let isToday = thisDate.toDateString() === today;
    html += `<div class="text-center px-1 py-0.5 rounded ${isToday ? 'bg-white text-blue-600 font-bold shadow' : 'hover:bg-blue-600 hover:text-white'}">${d}</div>`;
  }
  html += '</div>';
  document.getElementById(elemId).innerHTML = html;

  // Event untuk tombol bulan sebelumnya dan berikutnya
  document.getElementById('prevMonth').onclick = () => renderMiniCalendar(elemId, new Date(year, month - 1, 1));
  document.getElementById('nextMonth').onclick = () => renderMiniCalendar(elemId, new Date(year, month + 1, 1));
}
renderMiniCalendar('miniCalendar'); // Menampilkan kalender mini saat halaman dimuat

// Script toggle dropdown profil
const btn = document.getElementById('profileDropdownBtn');
const menu = document.getElementById('profileDropdownMenu');
// Event ketika tombol dropdown diklik
btn.addEventListener('click', function (e) {
  e.stopPropagation();
  menu.classList.toggle('hidden');
});
// Menutup dropdown jika klik di luar menu
document.addEventListener('click', function () {
  menu.classList.add('hidden');
});
