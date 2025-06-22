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

// Fungsi untuk ekspor riwayat kalkulasi ke CSV
function exportHistoryCSV() {
  const table = document.getElementById('calc-history-table');
  if (!table) return;
  let csv = [];
  const rows = table.querySelectorAll('tr');
  for (let row of rows) {
    let cols = row.querySelectorAll('th,td');
    let rowData = [];
    for (let col of cols) {
      // Hilangkan line break dan tanda kutip
      let text = col.innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/"/g, '""');
      rowData.push('"' + text + '"');
    }
    csv.push(rowData.join(','));
  }
  let csvContent = csv.join('\n');
  let blob = new Blob([csvContent], {
    type: 'text/csv;charset=utf-8;',
  });
  let link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = 'calculation_history.csv';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

// Fungsi untuk ekspor riwayat kalkulasi ke PDF (menggunakan window.print)
function exportHistoryPDF() {
  const table = document.getElementById('calc-history-table');
  if (!table) return;
  // Clone tabel untuk dicetak
  let printWindow = window.open('', '', 'width=800,height=600');
  printWindow.document.write('<html><head><title>Riwayat Kalkulasi</title>');
  printWindow.document.write('<style>body{font-family:sans-serif;}table{border-collapse:collapse;width:100%;}th,td{border:1px solid #ccc;padding:6px;}th{background:#f0f0f0;}</style>');
  printWindow.document.write('</head><body>');
  printWindow.document.write('<h2>Riwayat Kalkulasi</h2>');
  printWindow.document.write(table.outerHTML);
  printWindow.document.write('</body></html>');
  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
  // Tutup otomatis setelah print
  printWindow.onafterprint = function () {
    printWindow.close();
  };
}

// Fungsi untuk menampilkan hasil kalkulasi dari riwayat
function showHistoryCalc(expr, result) {
  // Set nilai input ekspresi
  document.getElementById('expression').value = expr;
  // Tampilkan hasil
  document.getElementById('calc-result-value').textContent = result;
  document.getElementById('calc-result').style.display = '';
  // Sembunyikan error jika ada
  let err = document.getElementById('calc-error');
  if (err) err.style.display = 'none';
}

// Fungsi untuk memasukkan operator/fungsi ke input kalkulator
function insertOp(op) {
  const input = document.getElementById('expression');
  const val = input.value;
  if (['sin', 'cos', 'tan', 'log', 'sqrt'].includes(op)) {
    input.value = val + op + '(';
  } else {
    input.value = val + op;
  }
  input.focus();
}

// Fungsi untuk mereset hasil kalkulasi
function resetCalcResult() {
  document.getElementById('expression').value = '';
  document.getElementById('calc-result').style.display = 'none';
  document.getElementById('calc-error').style.display = 'none';
  document.getElementById('calc-result-value').textContent = '';
  document.getElementById('calc-error').textContent = '';
}

// Fungsi evaluator ekspresi matematika (cocok dengan logika PHP)
function evalMathExpression(expr) {
  // Ganti fungsi matematika ke bentuk JavaScript
  expr = expr
    .replace(/sin\s*\(/gi, 'Math.sin(')
    .replace(/cos\s*\(/gi, 'Math.cos(')
    .replace(/tan\s*\(/gi, 'Math.tan(')
    .replace(/log\s*\(/gi, 'Math.log10(')
    .replace(/sqrt\s*\(/gi, 'Math.sqrt(');
  // Ganti persen (misal: 10% menjadi (10/100))
  expr = expr.replace(/(\d+(\.\d+)?)\s*%/g, '($1/100)');
  // Ganti ^ dengan Math.pow
  expr = expr.replace(/(\d+(\.\d+)?)\s*\^\s*(\d+(\.\d+)?)/g, 'Math.pow($1,$3)');
  // Ganti koma dengan titik
  expr = expr.replace(/,/g, '.');
  // Hanya izinkan karakter yang aman, termasuk ^ dan %
  if (/[^0-9+\-*/().\s^%a-zA-Z]/.test(expr)) {
    throw new Error('Ekspresi mengandung karakter tidak valid.');
  }
  // Cegah ekspresi kosong
  if (!expr.trim()) {
    throw new Error('Ekspresi kosong.');
  }
  // Ganti ^ dengan Math.pow untuk semua kasus (termasuk variabel/ekspresi)
  expr = expr.replace(/([a-zA-Z0-9_.()]+)\s*\^\s*([a-zA-Z0-9_.()]+)/g, 'Math.pow($1,$2)');
  // Ganti % dengan operator modulo JavaScript
  expr = expr.replace(/([a-zA-Z0-9_.()]+)\s*%\s*([a-zA-Z0-9_.()]+)/g, '($1%$2)');
  // Evaluasi ekspresi
  let res = Function('"use strict";return (' + expr + ')')();
  if (typeof res !== 'number' || isNaN(res) || !isFinite(res)) {
    throw new Error('Ekspresi tidak valid.');
  }
  return res;
}

// Event handler submit form kalkulator matematika
document.getElementById('math-calc-form').addEventListener('submit', function (e) {
  e.preventDefault();
  document.getElementById('calc-result').style.display = 'none';
  document.getElementById('calc-error').style.display = 'none';
  const expr = document.getElementById('expression').value.trim();
  try {
    let result = evalMathExpression(expr);
    // Pembulatan ke 4 desimal, hapus nol di belakang
    let resultRounded = parseFloat(result.toFixed(4)).toString();
    document.getElementById('calc-result-value').textContent = resultRounded;
    document.getElementById('calc-result').style.display = '';
    // Kirim ke server via AJAX untuk simpan riwayat
    saveCalculation(expr, resultRounded, true);
  } catch (err) {
    document.getElementById('calc-error').textContent = err.message;
    document.getElementById('calc-error').style.display = '';
  }
});

// Fungsi untuk menyimpan kalkulasi ke server dan reload riwayat via AJAX
function saveCalculation(expression, result) {
  fetch(window.location.pathname, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'expression=' + encodeURIComponent(expression) + '&result=' + encodeURIComponent(result),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data && data.status === 'ok') {
        // Reload riwayat via AJAX
        reloadHistory();
      }
    });
}

// Fungsi kalkulator bunga pinjaman sederhana
function calcLoanInterest() {
  const amount = parseFloat(document.getElementById('loan-amount').value);
  const rate = parseFloat(document.getElementById('loan-rate').value);
  const years = parseFloat(document.getElementById('loan-years').value);
  let result = '';
  if (isNaN(amount) || isNaN(rate) || isNaN(years)) {
    result = 'Masukkan semua nilai dengan benar.';
  } else {
    const interest = amount * (rate / 100) * years;
    result = 'Rp ' + interest.toLocaleString('id-ID');
  }
  document.getElementById('loan-interest-result').textContent = result;
}

// Fungsi kalkulator amortisasi pinjaman (angsuran tetap)
function calcAmortization() {
  const P = parseFloat(document.getElementById('amort-loan').value);
  const r = parseFloat(document.getElementById('amort-rate').value) / 100 / 12;
  const n = parseFloat(document.getElementById('amort-years').value) * 12;
  let result = '';
  if (isNaN(P) || isNaN(r) || isNaN(n) || r === 0) {
    result = 'Masukkan semua nilai dengan benar.';
  } else {
    const payment = (P * r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
    result =
      'Rp ' +
      payment.toLocaleString('id-ID', {
        maximumFractionDigits: 2,
      });
  }
  document.getElementById('amortization-result').textContent = result;
}

// Fungsi kalkulator investasi (future value dengan tambahan tahunan)
function calcInvestment() {
  const principal = parseFloat(document.getElementById('invest-principal').value);
  const rate = parseFloat(document.getElementById('invest-rate').value) / 100;
  const years = parseFloat(document.getElementById('invest-years').value);
  const additional = parseFloat(document.getElementById('invest-additional').value) || 0;
  let result = '';
  if (isNaN(principal) || isNaN(rate) || isNaN(years)) {
    result = 'Masukkan semua nilai dengan benar.';
  } else {
    // FV = P*(1+r)^n + PMT*(((1+r)^n - 1)/r)
    const fvPrincipal = principal * Math.pow(1 + rate, years);
    const fvAdditional = additional > 0 && rate > 0 ? (additional * (Math.pow(1 + rate, years) - 1)) / rate : additional * years;
    const fv = fvPrincipal + fvAdditional;
    result =
      'Rp ' +
      fv.toLocaleString('id-ID', {
        maximumFractionDigits: 2,
      });
  }
  document.getElementById('investment-result').textContent = result;
}

// Fungsi reset untuk kalkulator bunga pinjaman sederhana
function resetLoanInterest() {
  document.getElementById('loan-amount').value = '';
  document.getElementById('loan-rate').value = '';
  document.getElementById('loan-years').value = '';
  document.getElementById('loan-interest-result').textContent = '';
}

// Fungsi reset untuk kalkulator amortisasi pinjaman
function resetAmortization() {
  document.getElementById('amort-loan').value = '';
  document.getElementById('amort-rate').value = '';
  document.getElementById('amort-years').value = '';
  document.getElementById('amortization-result').textContent = '';
}

// Fungsi reset untuk kalkulator investasi
function resetInvestment() {
  document.getElementById('invest-principal').value = '';
  document.getElementById('invest-rate').value = '';
  document.getElementById('invest-years').value = '';
  document.getElementById('invest-additional').value = '0';
  document.getElementById('investment-result').textContent = '';
}

// Fungsi konversi panjang
function convertLength() {
  const value = parseFloat(document.getElementById('length-value').value);
  const from = document.getElementById('length-from').value;
  const to = document.getElementById('length-to').value;
  const units = {
    m: 1,
    cm: 0.01,
    mm: 0.001,
    km: 1000,
    in: 0.0254,
    ft: 0.3048,
    yd: 0.9144,
    mi: 1609.344,
  };
  let result = '';
  if (isNaN(value)) {
    result = 'Masukkan nilai yang valid.';
  } else if (!units[from] || !units[to]) {
    result = 'Satuan tidak didukung.';
  } else {
    const meterValue = value * units[from];
    const converted = meterValue / units[to];
    result = converted + ' ' + to;
  }
  document.getElementById('length-result').textContent = result;
}

// Fungsi konversi berat
function convertWeight() {
  const value = parseFloat(document.getElementById('weight-value').value);
  const from = document.getElementById('weight-from').value;
  const to = document.getElementById('weight-to').value;
  const units = {
    mg: 0.001,
    g: 1,
    kg: 1000,
    oz: 28.3495,
    lb: 453.592,
    ton: 1000000,
  };
  let result = '';
  if (isNaN(value)) {
    result = 'Masukkan nilai yang valid.';
  } else if (!units[from] || !units[to]) {
    result = 'Satuan tidak didukung.';
  } else {
    const gramValue = value * units[from];
    const converted = gramValue / units[to];
    result = converted + ' ' + to;
  }
  document.getElementById('weight-result').textContent = result;
}

// Fungsi konversi suhu
function convertTemp() {
  const value = parseFloat(document.getElementById('temp-value').value);
  const from = document.getElementById('temp-from').value;
  const to = document.getElementById('temp-to').value;
  let result = '';
  if (isNaN(value)) {
    result = 'Masukkan nilai yang valid.';
  } else if (from === to) {
    result = value + ' ' + to.toUpperCase();
  } else {
    let tempC;
    // Konversi ke Celsius terlebih dahulu
    if (from === 'c') tempC = value;
    else if (from === 'f') tempC = ((value - 32) * 5) / 9;
    else if (from === 'k') tempC = value - 273.15;
    // Konversi dari Celsius ke satuan target
    let final;
    if (to === 'c') final = tempC;
    else if (to === 'f') final = (tempC * 9) / 5 + 32;
    else if (to === 'k') final = tempC + 273.15;
    result = final + ' ' + to.toUpperCase();
  }
  document.getElementById('temp-result').textContent = result;
}

// Fungsi reset untuk konverter panjang
function resetLengthConverter() {
  document.getElementById('length-value').value = '';
  document.getElementById('length-from').selectedIndex = 0;
  document.getElementById('length-to').selectedIndex = 0;
  document.getElementById('length-result').textContent = '';
}

// Fungsi reset untuk konverter berat
function resetWeightConverter() {
  document.getElementById('weight-value').value = '';
  document.getElementById('weight-from').selectedIndex = 0;
  document.getElementById('weight-to').selectedIndex = 0;
  document.getElementById('weight-result').textContent = '';
}

// Fungsi reset untuk konverter suhu
function resetTempConverter() {
  document.getElementById('temp-value').value = '';
  document.getElementById('temp-from').selectedIndex = 0;
  document.getElementById('temp-to').selectedIndex = 0;
  document.getElementById('temp-result').textContent = '';
}
