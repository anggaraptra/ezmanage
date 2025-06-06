// Dropdown toggle script
const btn = document.getElementById('profileDropdownBtn');
const menu = document.getElementById('profileDropdownMenu');
btn.addEventListener('click', function (e) {
  e.stopPropagation();
  menu.classList.toggle('hidden');
});
document.addEventListener('click', function () {
  menu.classList.add('hidden');
});

// Export history to CSV
function exportHistoryCSV() {
  const table = document.getElementById('calc-history-table');
  if (!table) return;
  let csv = [];
  const rows = table.querySelectorAll('tr');
  for (let row of rows) {
    let cols = row.querySelectorAll('th,td');
    let rowData = [];
    for (let col of cols) {
      // Remove line breaks and quotes
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

// Export history to PDF (simple, using window.print)
function exportHistoryPDF() {
  const table = document.getElementById('calc-history-table');
  if (!table) return;
  // Clone table for print
  let printWindow = window.open('', '', 'width=800,height=600');
  printWindow.document.write('<html><head><title>Riwayat Kalkulasi Anda</title>');
  printWindow.document.write('<style>body{font-family:sans-serif;}table{border-collapse:collapse;width:100%;}th,td{border:1px solid #ccc;padding:6px;}th{background:#f0f0f0;}</style>');
  printWindow.document.write('</head><body>');
  printWindow.document.write('<h2>Riwayat Kalkulasi Anda</h2>');
  printWindow.document.write(table.outerHTML);
  printWindow.document.write('</body></html>');
  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
  // Optionally auto-close after print
  printWindow.onafterprint = function () {
    printWindow.close();
  };
}

// Insert operator/function to input
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

// Reset result
function resetCalcResult() {
  document.getElementById('expression').value = '';
  document.getElementById('calc-result').style.display = 'none';
  document.getElementById('calc-error').style.display = 'none';
  document.getElementById('calc-result-value').textContent = '';
  document.getElementById('calc-error').textContent = '';
}

// Math expression evaluator (matches PHP logic)
function evalMathExpression(expr) {
  // Replace math functions to JS equivalents
  expr = expr
    .replace(/sin\s*\(/gi, 'Math.sin(')
    .replace(/cos\s*\(/gi, 'Math.cos(')
    .replace(/tan\s*\(/gi, 'Math.tan(')
    .replace(/log\s*\(/gi, 'Math.log10(')
    .replace(/sqrt\s*\(/gi, 'Math.sqrt(');
  // Replace percent (e.g. 10% to (10/100))
  expr = expr.replace(/(\d+(\.\d+)?)\s*%/g, '($1/100)');
  // Replace ^ with Math.pow
  expr = expr.replace(/(\d+(\.\d+)?)\s*\^\s*(\d+(\.\d+)?)/g, 'Math.pow($1,$3)');
  // Replace comma with dot
  expr = expr.replace(/,/g, '.');
  // Only allow safe characters, including ^ and %
  if (/[^0-9+\-*/().\s^%a-zA-Z]/.test(expr)) {
    throw new Error('Expression contains invalid characters.');
  }
  // Prevent empty expression
  if (!expr.trim()) {
    throw new Error('Expression is empty.');
  }
  // Replace ^ with Math.pow for all cases (including variables/expressions)
  expr = expr.replace(/([a-zA-Z0-9_.()]+)\s*\^\s*([a-zA-Z0-9_.()]+)/g, 'Math.pow($1,$2)');
  // Replace % with JavaScript modulo operator
  expr = expr.replace(/([a-zA-Z0-9_.()]+)\s*%\s*([a-zA-Z0-9_.()]+)/g, '($1%$2)');
  // Evaluate
  let res = Function('"use strict";return (' + expr + ')')();
  if (typeof res !== 'number' || isNaN(res) || !isFinite(res)) {
    throw new Error('Expression is not valid.');
  }
  return res;
}

// Handle form submit
document.getElementById('math-calc-form').addEventListener('submit', function (e) {
  e.preventDefault();
  document.getElementById('calc-result').style.display = 'none';
  document.getElementById('calc-error').style.display = 'none';
  const expr = document.getElementById('expression').value.trim();
  try {
    let result = evalMathExpression(expr);
    // Round to 4 decimals, remove trailing zeros
    let resultRounded = parseFloat(result.toFixed(4)).toString();
    document.getElementById('calc-result-value').textContent = resultRounded;
    document.getElementById('calc-result').style.display = '';
    // Send to server via AJAX to save history
    saveCalculation(expr, resultRounded, true);
  } catch (err) {
    document.getElementById('calc-error').textContent = err.message;
    document.getElementById('calc-error').style.display = '';
  }
});

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
        // Reload history via AJAX
        reloadHistory();
      }
    });
}

// Kalkulator Bunga Pinjaman Sederhana
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

// Kalkulator Amortisasi Pinjaman (Angsuran Tetap)
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

// Kalkulator Investasi (Future Value dengan tambahan tahunan)
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

// Reset fungsi untuk setiap kalkulator keuangan
function resetLoanInterest() {
  document.getElementById('loan-amount').value = '';
  document.getElementById('loan-rate').value = '';
  document.getElementById('loan-years').value = '';
  document.getElementById('loan-interest-result').textContent = '';
}

function resetAmortization() {
  document.getElementById('amort-loan').value = '';
  document.getElementById('amort-rate').value = '';
  document.getElementById('amort-years').value = '';
  document.getElementById('amortization-result').textContent = '';
}

function resetInvestment() {
  document.getElementById('invest-principal').value = '';
  document.getElementById('invest-rate').value = '';
  document.getElementById('invest-years').value = '';
  document.getElementById('invest-additional').value = '0';
  document.getElementById('investment-result').textContent = '';
}

// Konversi Panjang
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

// Konversi Berat
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

// Konversi Suhu
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
    // Convert to Celsius first
    if (from === 'c') tempC = value;
    else if (from === 'f') tempC = ((value - 32) * 5) / 9;
    else if (from === 'k') tempC = value - 273.15;
    // Convert from Celsius to target
    let final;
    if (to === 'c') final = tempC;
    else if (to === 'f') final = (tempC * 9) / 5 + 32;
    else if (to === 'k') final = tempC + 273.15;
    result = final + ' ' + to.toUpperCase();
  }
  document.getElementById('temp-result').textContent = result;
}

// Reset fungsi untuk setiap konverter
function resetLengthConverter() {
  document.getElementById('length-value').value = '';
  document.getElementById('length-from').selectedIndex = 0;
  document.getElementById('length-to').selectedIndex = 0;
  document.getElementById('length-result').textContent = '';
}

function resetWeightConverter() {
  document.getElementById('weight-value').value = '';
  document.getElementById('weight-from').selectedIndex = 0;
  document.getElementById('weight-to').selectedIndex = 0;
  document.getElementById('weight-result').textContent = '';
}

function resetTempConverter() {
  document.getElementById('temp-value').value = '';
  document.getElementById('temp-from').selectedIndex = 0;
  document.getElementById('temp-to').selectedIndex = 0;
  document.getElementById('temp-result').textContent = '';
}
