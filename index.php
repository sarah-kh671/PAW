<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Attendance Management System — Final (All features)</title>

<!--
  Final single-file implementation for the Attendance system.
  Implements Tutorial 2 requirements (table structure, counts, highlighting, add student,
  search, sort, highlight best, reset, report with bar chart for present & participation,
  navigation pages: Home, Attendance, Add Student, Report, Logout).
  Requirements reference: Tutorial 2 AWP V1. :contentReference[oaicite:0]{index=0}
-->

<!-- Libraries -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
  /* ================= THEME & LAYOUT (one theme for all pages) ================= */
  :root{
    --bg:#f4f7fb;
    --card:#ffffff;
    --primary:#2563eb;    /* blue */
    --accent:#06b6d4;     /* cyan-accent */
    --muted:#6b7280;
    --good:#c8e6c9;
    --warn:#fff9c4;
    --bad:#ffcdd2;
    --highlight:#9c27b0;  /* purple for best student */
  }
  body{
    font-family: Inter, Arial, sans-serif;
    background: linear-gradient(180deg, var(--bg), #eef6ff 120%);
    color:#111827;
    margin:0;
    padding:18px;
  }
  .container{max-width:1200px;margin:0 auto;}
  header{margin-bottom:12px;}
  nav{
    background:var(--primary);
    padding:10px;
    border-radius:10px;
    display:flex;
    gap:10px;
    flex-wrap:wrap;
  }
  .nav-link{
    color:#fff;
    padding:8px 12px;
    cursor:pointer;
    border-radius:8px;
    background:transparent;
    text-decoration:none;
  }
  .nav-link:hover{opacity:0.9; filter:brightness(.95);}
  h1,h2{margin:8px 0 12px 0;}
  section{display:none;background:var(--card);padding:14px;border-radius:10px;margin-top:12px;box-shadow:0 6px 18px rgba(15,23,42,0.06);}
  section.active{display:block;}
  .controls{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:10px;}
  .search-input{padding:8px;border-radius:8px;border:1px solid #d1d5db;min-width:260px;}
  button{background:var(--primary);color:#fff;border:none;padding:8px 12px;border-radius:8px;cursor:pointer;}
  button.ghost{background:transparent;color:#fff;border:1px solid rgba(255,255,255,0.12);}
  button.flat{background:#e6eefc;color:var(--primary);}
  .small{padding:6px 8px;font-size:0.95rem;}
  /* Table */
  .table-wrap{overflow:auto;}
  table{border-collapse:collapse;width:100%;min-width:900px;background:var(--card);border-radius:6px;overflow:hidden;}
  thead th{background:var(--primary);color:#fff;padding:8px;border:1px solid rgba(0,0,0,0.06);font-weight:600;}
  th,td{border:1px solid #e6eefc;padding:8px;text-align:center;font-size:14px;}
  td input[type="checkbox"]{width:18px;height:18px;cursor:pointer;}
  .green{background:var(--good);}
  .yellow{background:var(--warn);}
  .red{background:var(--bad);}
  .highlight{background:var(--highlight)!important;color:#fff !important;}
  .sort-area{display:flex;flex-direction:column;gap:8px;max-width:260px;margin-top:12px;}
  #sortMessage{margin-top:8px;color:var(--muted);font-weight:600;}
  /* Report */
  .chart-box{width:100%;max-width:900px;height:360px;margin-top:8px;}
  .chart-box canvas{width:100% !important;height:100% !important;}
  /* Responsive */
  @media (min-width:900px){
    nav{justify-content:flex-start;}
    .controls{justify-content:flex-start;}
  }
  @media (max-width:740px){
    table{min-width:700px;}
    .chart-box{height:300px;}
  }
  .muted{color:var(--muted);font-size:0.95rem;}
  label{display:block;margin-bottom:6px;font-weight:600;}
  input[type="text"], input[type="email"]{padding:8px;border-radius:6px;border:1px solid #d1d5db;width:100%;box-sizing:border-box;}
  form .row{margin-bottom:10px;}
  .msg{color:green;font-weight:600;margin-top:8px;}
  .error{color:#b91c1c;font-weight:600;font-size:0.95rem;}
</style>
</head>
<body>
<div class="container">
  <!-- ================= NAVBAR (Title: navigation for all pages) ================= -->
  <nav>
    <div class="nav-link" data-target="homeView">Home</div>
    <div class="nav-link" data-target="attendanceView">Attendance</div>
    <div class="nav-link" data-target="addView">Add Student</div>
    <div class="nav-link" data-target="reportView">Report</div>
    <div class="nav-link" data-target="logoutView">Logout</div>
  </nav>

  <!-- ================= HOME (Title: welcome page with button takes to attendance) ================= -->
  <section id="homeView" class="active">
    <h1>Welcome to the Attendance Management System</h1>
    <p class="muted">Track student attendance across sessions, record participation, add new students, and view session reports.</p>
    <p style="margin-top:12px;">
      <button id="gotoAttendance">Go to Attendance System</button>
    </p>
    <!-- comment: Home section ends -->
  </section>

  <!-- ================= ATTENDANCE (Title: table + search + sort + highlight + reset) ================= -->
  <section id="attendanceView">
    <h2>Attendance</h2>

    <!-- Search bar (Title: Search by Name - alone above the table) -->
    <div class="controls">
      <div><label for="searchInput">Search by Name</label><input id="searchInput" class="search-input" placeholder="Type last or first name..."></div>
      <div class="muted">Type partial or full name to filter rows (Last or First).</div>
    </div>

    <!-- Table (Title: Attendance table with 6 sessions and participation columns) -->
    <div class="table-wrap">
      <table id="attendanceTable">
        <thead>
          <tr>
            <th rowspan="2">Last Name</th>
            <th rowspan="2">First Name</th>
            <th colspan="2">S1</th><th colspan="2">S2</th><th colspan="2">S3</th>
            <th colspan="2">S4</th><th colspan="2">S5</th><th colspan="2">S6</th>
            <th rowspan="2">Absences</th>
            <th rowspan="2">Participation</th>
            <th rowspan="2">Message</th>
          </tr>
          <tr>
            <th>P</th><th>Pa</th><th>P</th><th>Pa</th><th>P</th><th>Pa</th>
            <th>P</th><th>Pa</th><th>P</th><th>Pa</th><th>P</th><th>Pa</th>
          </tr>
        </thead>
        <tbody>
          <!-- Sample rows (3 students minimum as required by tutorial) -->
          <!-- NOTE: each pair is Present (.present) then Participate (.participate) -->
          <tr>
            <td>Ahmed</td><td>Sara</td>
            <td><input class="present" type="checkbox" checked></td><td><input class="participate" type="checkbox" checked></td>
            <td><input class="present" type="checkbox" checked></td><td><input class="participate" type="checkbox"></td>
            <td><input class="present" type="checkbox"></td><td><input class="participate" type="checkbox"></td>
            <td><input class="present" type="checkbox"></td><td><input class="participate" type="checkbox"></td>
            <td><input class="present" type="checkbox"></td><td><input class="participate" type="checkbox"></td>
            <td><input class="present" type="checkbox"></td><td><input class="participate" type="checkbox"></td>
            <td></td><td></td><td></td>
          </tr>

          <tr>
            <td>Yacine</td><td>Ali</td>
            <td><input class="present" type="checkbox" checked></td><td><input class="participate" type="checkbox"></td>
            <td><input class="present" type="checkbox"></td><td><input class="participate" type="checkbox" checked></td>
            <td><input class="present" type="checkbox" checked></td><td><input class="participate" type="checkbox"></td>
            <td><input class="present" type="checkbox" checked></td><td><input class="participate" type="checkbox" checked></td>
            <td><input class="present" type="checkbox" checked></td><td><input class="participate" type="checkbox"></td>
            <td><input class="present" type="checkbox" checked></td><td><input class="participate" type="checkbox"></td>
            <td></td><td></td><td></td>
          </tr>

          <tr>
            <td>Houcine</td><td>Rania</td>
            <td><input class="present" type="checkbox" checked></td><td><input class="participate" type="checkbox"></td>
            <td><input class="present" type="checkbox" checked></td><td><input class="participate" type="checkbox"></td>
            <td><input class="present" type="checkbox"></td><td><input class="participate" type="checkbox" checked></td>
            <td><input class="present" type="checkbox" checked></td><td><input class="participate" type="checkbox" checked></td>
            <td><input class="present" type="checkbox"></td><td><input class="participate" type="checkbox"></td>
            <td><input class="present" type="checkbox"></td><td><input class="participate" type="checkbox"></td>
            <td></td><td></td><td></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Controls below the table (Title: Sort & Highlight & Reset) -->
    <div class="sort-area">
      <button id="sortAbs">Sort by Absences (Ascending)</button>
      <button id="sortPar">Sort by Participation (Descending)</button>
      <button id="highlightBest">Highlight Best Student</button>
      <button id="resetColors">Reset Colors</button>
    </div>

    <div id="sortMessage" aria-live="polite"></div>
    <!-- comment: Attendance section ends -->
  </section>

  <!-- ================= ADD STUDENT (Title: form with validation + submit) ================= -->
  <section id="addView">
    <h2>Add Student</h2>
    <form id="addStudentForm" novalidate>
      <div class="row"><label>Student ID</label><input type="text" id="studentId"><div class="error" id="idError"></div></div>
      <div class="row"><label>Last Name</label><input type="text" id="lastName"><div class="error" id="lastError"></div></div>
      <div class="row"><label>First Name</label><input type="text" id="firstName"><div class="error" id="firstError"></div></div>
      <div class="row"><label>Email</label><input type="email" id="email"><div class="error" id="emailError"></div></div>
      <div style="margin-top:8px;"><button type="submit">Submit</button></div>
    </form>
    <div id="message" class="msg" aria-live="polite"></div>
    <!-- comment: Add Student section ends -->
  </section>

  <!-- ================= REPORT (Title: Show Report then bar chart with 2 colors) ================= -->
  <section id="reportView">
    <h2>Report</h2>
    <p class="muted">Click "Show Report" to generate Present vs Participation per session.</p>
    <button id="showReport">Show Report</button>

    <div id="report" class="hidden">
      <div class="chart-box">
        <canvas id="absenceChart"></canvas>
      </div>
      <div id="report-stats" style="margin-top:8px;"></div>
    </div>
    <!-- comment: Report ends -->
  </section>

  <!-- ================= LOGOUT (Title: simple logout page with Return Home) ================= -->
  <section id="logoutView">
    <h2>Logged out</h2>
    <p class="muted">You have been logged out safely (UI-only).</p>
    <button id="returnHome">Return Home</button>
  </section>
</div>

<!-- ================= SCRIPT: functionality with comments for each part ================= -->
<script>
/* ================= CONSTANTS & REFS ================= */
const SESSIONS = 6;
const $table = $('#attendanceTable');
let chartInstance = null;

/* ================= Helper: footerIndexes(row) =================
   Returns indexes for Absences / Participation / Message columns for a row
*/
function footerIndexes(row){
  const len = row.children.length;
  return { absIndex: len - 3, partIndex: len - 2, msgIndex: len - 1 };
}

/* ================= Helper: parse number from cell "N Abs" or "N Par" ================= */
function parseNumberCellText(text){
  const n = parseInt(text, 10);
  return isNaN(n) ? 0 : n;
}

/* ================= updateRowCounts(row) =================
   - Count absences (present unchecked) and participation (participate checked)
   - Apply color classes: green (<3), yellow (3-4), red (>=5)
   - Set message cell text
   - Store original base class in data-originalClass for Reset
*/
function updateRowCounts(row){
  const $r = $(row);
  const boxes = $r.find('input[type="checkbox"]');
  let abs = 0, par = 0;
  for(let i=0;i<SESSIONS;i++){
    const present = boxes.eq(i*2);       // present checkbox
    const participate = boxes.eq(i*2 + 1); // participation checkbox
    if(!present.prop('checked')) abs++;
    if(participate.prop('checked')) par++;
  }

  const idx = footerIndexes(row);
  row.children[idx.absIndex].textContent = `${abs} Abs`;
  row.children[idx.partIndex].textContent = `${par} Par`;

  // determine base class
  let baseClass = '';
  if(abs < 3) baseClass = 'green';
  else if(abs <= 4) baseClass = 'yellow';
  else baseClass = 'red';

  // preserve highlight if applied: if highlighted, keep highlight and save base in dataset
  if(!$r.hasClass('highlight')){
    $r.removeClass().addClass(baseClass);
  } else {
    // keep highlight, but remember base
    row.dataset._baseClass = baseClass;
  }

  // Save original class if not present
  if(!$r.data('originalClass')) $r.data('originalClass', $r.attr('class') || baseClass);

  // message content
  row.children[idx.msgIndex].textContent = (abs < 3) ? 'Good attendance – Excellent participation'
    : (abs <= 4) ? 'Warning – attendance low – participate more'
    : 'Excluded – too many absences';
}

/* ================= updateTable() =================
   Run updateRowCounts for all rows (used on load, after sorts, after add)
*/
function updateTable(){
  $table.find('tbody tr').each(function(){ updateRowCounts(this); });
}

/* initial compute on load */
updateTable();

/* ================= NAVIGATION handlers =================
   Simple SPA-like show/hide of sections
*/
$('.nav-link').on('click', function(){
  const target = $(this).data('target');
  $('section').removeClass('active');
  $('#' + target).addClass('active');
  // hide report area until Show Report clicked
  if(target === 'reportView') $('#report').addClass('hidden');
});
$('#gotoAttendance').on('click', ()=>{ $('section').removeClass('active'); $('#attendanceView').addClass('active'); });
$('#returnHome').on('click', ()=>{ $('section').removeClass('active'); $('#homeView').addClass('active'); });

/* ================= Real-time checkbox handling =================
   When any present/participate checkbox changes -> update that row counts
   If the report is visible, redraw chart to reflect change immediately
*/
$table.on('change', 'input[type="checkbox"]', function(){
  const row = $(this).closest('tr')[0];
  updateRowCounts(row);
  if(!$('#report').hasClass('hidden')) drawBarChart();
});

/* ================= Add Student form =================
   - Validates fields
   - Adds a new row to table with present & participate checkboxes
   - Updates counts and stores original class
*/
$('#addStudentForm').on('submit', function(e){
  e.preventDefault();
  // clear errors
  $('#idError,#lastError,#firstError,#emailError').text('');
  $('#message').text('');

  const id = $('#studentId').val().trim();
  const ln = $('#lastName').val().trim();
  const fn = $('#firstName').val().trim();
  const em = $('#email').val().trim();

  let valid = true;
  if(!/^[0-9]+$/.test(id)){ $('#idError').text('Numbers only'); valid = false; }
  if(!/^[A-Za-z]+$/.test(ln)){ $('#lastError').text('Letters only'); valid = false; }
  if(!/^[A-Za-z]+$/.test(fn)){ $('#firstError').text('Letters only'); valid = false; }
  if(!/^[^@]+@[^@]+\.[a-z]+$/.test(em)){ $('#emailError').text('Invalid email'); valid = false; }
  if(!valid) return;

  // build row HTML
  let row = `<tr><td>${ln}</td><td>${fn}</td>`;
  for(let i=0;i<SESSIONS;i++){
    row += `<td><input class="present" type="checkbox"></td><td><input class="participate" type="checkbox"></td>`;
  }
  row += `<td></td><td></td><td></td></tr>`;

  $('#attendanceTable tbody').append(row);
  const $new = $('#attendanceTable tbody tr').last();
  updateRowCounts($new[0]);
  $new.data('originalClass', $new.attr('class') || '');
  this.reset();
  $('#message').text('Student added successfully!');
  setTimeout(()=>$('#message').text(''), 2000);
});

/* ================= Search (filter) =================
   Filters rows live by Last or First name (partial or full)
*/
$('#searchInput').on('keyup', function(){
  const val = $(this).val().toLowerCase();
  $('#attendanceTable tbody tr').filter(function(){
    const last = $(this).children('td').eq(0).text().toLowerCase();
    const first = $(this).children('td').eq(1).text().toLowerCase();
    $(this).toggle(last.includes(val) || first.includes(val));
  });
});

/* ================= Sorting =================
   - Sort by Absences (ascending) -> fewest absences first
   - Sort by Participation (descending) -> highest participation first
   - After re-append call updateTable() so classes / data-originalClass remain consistent
*/
$('#sortAbs').on('click', function(){
  const rows = $('#attendanceTable tbody tr').get();
  rows.sort(function(a,b){
    const aVal = parseNumberCellText($(a).children().eq(-3).text());
    const bVal = parseNumberCellText($(b).children().eq(-3).text());
    return aVal - bVal; // ascending
  });
  $('#attendanceTable tbody').append(rows);
  updateTable();
  $('#sortMessage').text('Currently sorted by absences (ascending)');
});

$('#sortPar').on('click', function(){
  const rows = $('#attendanceTable tbody tr').get();
  rows.sort(function(a,b){
    const aVal = parseNumberCellText($(a).children().eq(-2).text());
    const bVal = parseNumberCellText($(b).children().eq(-2).text());
    return bVal - aVal; // descending
  });
  $('#attendanceTable tbody').append(rows);
  updateTable();
  $('#sortMessage').text('Currently sorted by participation (descending)');
});

/* ================= Highlight Excellent Students (all with <3 absences) =================
   - Finds all students having fewer than 3 absences.
   - Animates their rows by changing color to purple gradually.
*/
$('#highlightBest').on('click', function(){
  // Reset all rows to original colors first
  $('#attendanceTable tbody tr').each(function(){
    const orig = $(this).data('originalClass') || '';
    $(this).removeClass().addClass(orig);
  });

  // Highlight all excellent students (<3 abs)
  $('#attendanceTable tbody tr:visible').each(function(){
    const abs = parseNumberCellText($(this).children().eq(-3).text());
    if(abs < 3){
      $(this)
        .animate({opacity:0.6},150)
        .animate({opacity:1},150)
        .addClass('highlight');
    }
  });
});


/* ================= Reset Colors =================
   - Restores original base classes (green/yellow/red) from data-originalClass
*/
$('#resetColors').on('click', function(){
  $('#attendanceTable tbody tr').each(function(){
    const orig = $(this).data('originalClass') || '';
    $(this).removeClass().addClass(orig);
    updateRowCounts(this);
  });
  $('#sortMessage').text('');
});

/* ================= Row hover & click behavior (Exercise 5) =================
   - Hover: outline
   - Click: alert with full name and absences
*/
$('#attendanceTable tbody')
  .on('mouseenter','tr', function(){ $(this).css('outline','2px solid rgba(37,99,235,0.9)'); })
  .on('mouseleave','tr', function(){ $(this).css('outline','none'); })
  .on('click','tr', function(){
    const last = $(this).children('td').eq(0).text();
    const first = $(this).children('td').eq(1).text();
    const abs = $(this).children('td').eq(-3).text();
    alert(`${last} ${first} has ${abs}`);
  });

/* ================= Report: drawBarChart() =================
   - Two datasets: Present (blue) and Participation (green)
   - Counts across all rows (change to :visible if you want filter behavior)
*/
function drawBarChart(){
  const presentPerSession = new Array(SESSIONS).fill(0);
  const participatePerSession = new Array(SESSIONS).fill(0);

  // iterate all rows (you can change to 'tr:visible' to respect search filter)
  $('#attendanceTable tbody tr').each(function(){
    const presentBoxes = $(this).find('input.present');
    const participateBoxes = $(this).find('input.participate');
    for(let i=0;i<SESSIONS;i++){
      if(presentBoxes.eq(i).prop('checked')) presentPerSession[i]++;
      if(participateBoxes.eq(i).prop('checked')) participatePerSession[i]++;
    }
  });

  const ctx = document.getElementById('absenceChart').getContext('2d');
  if(chartInstance) chartInstance.destroy();
  chartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: Array.from({length:SESSIONS}, (_,i) => 'S' + (i+1)),
      datasets: [
        { label: 'Present', data: presentPerSession, backgroundColor: '#1976d2' },
        { label: 'Participation', data: participatePerSession, backgroundColor: '#16a34a' }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { title: { display: true, text: 'Present & Participation per Session' } },
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } }
      }
    }
  });

  $('#report-stats').html(`<strong>Present per session:</strong> ${presentPerSession.join(', ')}<br>
                          <strong>Participation per session:</strong> ${participatePerSession.join(', ')}<br>
                          <strong>Total present:</strong> ${presentPerSession.reduce((a,b)=>a+b,0)} &nbsp;|&nbsp;
                          <strong>Total participation:</strong> ${participatePerSession.reduce((a,b)=>a+b,0)}`);
}

/* Show report only when user clicks button */
$('#showReport').on('click', function(){
  drawBarChart();
  $('#report').removeClass('hidden');
});

/* ================= Ensure initial originalClass saved ================= */
$('#attendanceTable tbody tr').each(function(){
  updateRowCounts(this);
  $(this).data('originalClass', $(this).attr('class') || '');
});
</script>
</body>
</html>



