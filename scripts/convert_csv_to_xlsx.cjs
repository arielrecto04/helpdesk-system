const fs = require('fs');
const path = require('path');
const XLSX = require('xlsx');

const args = process.argv.slice(2);
const csvFile = args[0] ? path.resolve(args[0]) : path.resolve(__dirname, '../ticket-analysis-test.csv');
const xlsxFile = args[1] ? path.resolve(args[1]) : path.resolve(__dirname, '../ticket-analysis-test.xlsx');

if (!fs.existsSync(csvFile)) {
  console.error('CSV file not found:', csvFile);
  process.exit(1);
}

const csv = fs.readFileSync(csvFile, 'utf8');
const wb = XLSX.read(csv, { type: 'string', raw: false });

// Ensure 'Created At' column displays as date/time in Excel
try {
  const sheetName = wb.SheetNames[0];
  const ws = wb.Sheets[sheetName];

  // find header row keys and apply date format to date-like columns
  const range = XLSX.utils.decode_range(ws['!ref']);
  const dateHeaders = ['created at', 'deadline'];
  const dateCols = [];

  // scan first row for headers we want to treat as dates
  for (let C = range.s.c; C <= range.e.c; ++C) {
    const cellAddress = { c: C, r: range.s.r };
    const cellRef = XLSX.utils.encode_cell(cellAddress);
    const cell = ws[cellRef];
    if (cell && typeof cell.v === 'string') {
      const header = String(cell.v).trim().toLowerCase();
      if (dateHeaders.includes(header)) {
        dateCols.push({ col: C, header });
      }
    }
  }

  if (dateCols.length > 0) {
    // apply formatting to each matching column for every data row
    for (const dc of dateCols) {
      const colLetter = XLSX.utils.encode_col(dc.col);
      for (let R = range.s.r + 1; R <= range.e.r; ++R) {
        const dataRef = colLetter + (R + 1);
        const dataCell = ws[dataRef];
        if (!dataCell) continue;

        // If the cell was parsed as a number (Excel serial), set a readable date format
        if (typeof dataCell.v === 'number') {
          dataCell.z = 'yyyy-mm-dd hh:mm';
        } else {
          // Otherwise ensure it remains a string to avoid unexpected conversions
          dataCell.t = 's';
        }
      }
    }
  }
} catch (e) {
  // non-fatal: log and continue
  console.error('Warning: failed to apply Created At formatting:', e.message || e);
}

XLSX.writeFile(wb, xlsxFile);
console.log('Wrote XLSX to:', xlsxFile);
