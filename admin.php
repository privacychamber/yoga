<?php
session_start();

// Define secure admin password (change this if you want a different password)
define('ADMIN_PASSWORD', 'himyog_admin_2026');

// Logout action
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Download CSV action
if (isset($_GET['action']) && $_GET['action'] == 'download' && isset($_SESSION['admin_logged_in'])) {
    $file = 'enquiries_backup_9418.csv';
    if (file_exists($file)) {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="himyog_leads_' . date('Y-m-d') . '.csv"');
        readfile($file);
        exit;
    } else {
        echo "No leads file found yet.";
        exit;
    }
}

// Login handling
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $pass = isset($_POST['password']) ? trim($_POST['password']) : '';
    if ($pass === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = 'Incorrect password. Please try again.';
    }
}

// Read CSV data if logged in
$leads = array();
if (isset($_SESSION['admin_logged_in'])) {
    $file = 'enquiries_backup_9418.csv';
    if (file_exists($file)) {
        if (($handle = fopen($file, "r")) !== FALSE) {
            // Read header
            $header = fgetcsv($handle, 1000, ",");
            
            // Read rows
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (count($data) >= 6) {
                    $leads[] = array(
                        'date'    => $data[0],
                        'name'    => $data[1],
                        'email'   => $data[2],
                        'phone'   => $data[3],
                        'program' => $data[4],
                        'message' => $data[5]
                    );
                }
            }
            fclose($handle);
            
            // Reverse so newest leads are at the top
            $leads = array_reverse($leads);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HimYog Leads Admin Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  
  <style>
    :root {
      --bg: #080A12;
      --bg-alt: #0D111A;
      --bg-accent: #151B28;
      --glass: rgba(21, 27, 40, 0.75);
      --glass-border: rgba(227, 165, 79, 0.2);
      --gold: #D4AF37;
      --gold-glow: #FFD700;
      --text-main: #F4F4F9;
      --text-dim: #A0AEC0;
      --text-mute: #718096;
      --error: #F56565;
      --success: #48BB78;
      --radius-md: 16px;
      --radius-sm: 8px;
    }
    
    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      background: var(--bg);
      color: var(--text-main);
      font-family: 'Jost', sans-serif;
      font-weight: 300;
      line-height: 1.6;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      padding: 40px 20px;
    }
    
    a {
      text-decoration: none;
      color: inherit;
    }
    
    h1, h2, h3, h4, .cinzel {
      font-family: 'Cinzel', serif;
    }
    
    /* ===== LOGIN PAGE ===== */
    .login-container {
      margin: auto;
      max-width: 420px;
      width: 100%;
      background: var(--bg-accent);
      border: 1px solid var(--glass-border);
      border-radius: var(--radius-md);
      padding: 3rem 2.5rem;
      box-shadow: 0 20px 45px rgba(0, 0, 0, 0.45);
      text-align: center;
    }
    
    .om-logo {
      font-size: 3rem;
      color: var(--gold);
      margin-bottom: 0.5rem;
      display: block;
      filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.3));
    }
    
    .login-container h2 {
      font-size: 1.8rem;
      color: var(--text-main);
      letter-spacing: 0.1em;
      margin-bottom: 0.5rem;
    }
    
    .login-sub {
      color: var(--text-dim);
      font-size: 0.95rem;
      margin-bottom: 2.5rem;
    }
    
    .fg {
      margin-bottom: 1.8rem;
      text-align: left;
    }
    
    .fg label {
      display: block;
      font-family: 'Cinzel', serif;
      font-size: 0.75rem;
      letter-spacing: 0.1em;
      color: var(--gold);
      margin-bottom: 0.5rem;
      text-transform: uppercase;
    }
    
    .fg input {
      width: 100%;
      background: var(--bg);
      border: 1px solid var(--glass-border);
      border-radius: var(--radius-sm);
      padding: 1rem 1.2rem;
      color: var(--text-main);
      font-family: 'Jost', sans-serif;
      font-size: 1rem;
      outline: none;
      transition: all 0.3s;
    }
    
    .fg input:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
    }
    
    .btn-login {
      display: inline-block;
      width: 100%;
      background: linear-gradient(135deg, var(--gold), #c8872a);
      color: #1a0800;
      padding: 1rem;
      border-radius: 50px;
      font-family: 'Jost', sans-serif;
      font-weight: 500;
      font-size: 0.95rem;
      letter-spacing: 0.05em;
      cursor: pointer;
      transition: all 0.3s;
      border: none;
    }
    
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(227, 165, 79, 0.35);
    }
    
    .err-msg {
      color: var(--error);
      font-size: 0.9rem;
      margin-bottom: 1.5rem;
      background: rgba(245, 101, 101, 0.1);
      border: 1px solid var(--error);
      padding: 0.75rem;
      border-radius: var(--radius-sm);
    }
    
    /* ===== DASHBOARD PAGE ===== */
    .dashboard-wrap {
      max-width: 1200px;
      width: 100%;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      gap: 30px;
    }
    
    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-bottom: 20px;
      border-bottom: 1px solid var(--glass-border);
      flex-wrap: wrap;
      gap: 15px;
    }
    
    .logo-area {
      display: flex;
      align-items: center;
      gap: 0.8rem;
    }
    
    .logo-area strong {
      font-size: 1.4rem;
      letter-spacing: 0.15em;
      color: var(--gold);
    }
    
    .actions-area {
      display: flex;
      gap: 12px;
    }
    
    .btn-action {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.6rem 1.4rem;
      border-radius: 50px;
      font-family: 'Jost', sans-serif;
      font-size: 0.85rem;
      font-weight: 500;
      letter-spacing: 0.05em;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .btn-action.download {
      background: rgba(212, 175, 55, 0.15);
      border: 1px solid var(--gold);
      color: var(--gold);
    }
    
    .btn-action.download:hover {
      background: var(--gold);
      color: #1a0800;
      transform: translateY(-2px);
    }
    
    .btn-action.logout {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.15);
      color: var(--text-dim);
    }
    
    .btn-action.logout:hover {
      background: var(--error);
      color: white;
      border-color: var(--error);
      transform: translateY(-2px);
    }
    
    /* Filter Bar */
    .filter-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 20px;
      flex-wrap: wrap;
    }
    
    .search-box {
      position: relative;
      max-width: 400px;
      width: 100%;
    }
    
    .search-box input {
      width: 100%;
      background: var(--bg-accent);
      border: 1px solid var(--glass-border);
      border-radius: 50px;
      padding: 0.8rem 1.5rem;
      color: var(--text-main);
      font-family: 'Jost', sans-serif;
      outline: none;
      transition: all 0.3s;
      font-size: 0.95rem;
    }
    
    .search-box input:focus {
      border-color: var(--gold);
      box-shadow: 0 0 10px rgba(212, 175, 55, 0.1);
    }
    
    .stats-count {
      color: var(--text-dim);
      font-size: 0.95rem;
    }
    
    /* Table styling */
    .table-container {
      background: var(--bg-alt);
      border: 1px solid var(--glass-border);
      border-radius: var(--radius-md);
      overflow: hidden;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
      font-size: 0.95rem;
    }
    
    th {
      background: var(--bg-accent);
      font-family: 'Cinzel', serif;
      font-size: 0.8rem;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: var(--gold);
      padding: 1.2rem 1.5rem;
      border-bottom: 1px solid var(--glass-border);
    }
    
    td {
      padding: 1.2rem 1.5rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      color: var(--text-dim);
      vertical-align: top;
    }
    
    tr:last-child td {
      border-bottom: none;
    }
    
    tr:hover td {
      background: rgba(255, 255, 255, 0.01);
      color: var(--text-main);
    }
    
    .td-date {
      white-space: nowrap;
      color: var(--text-mute);
      font-size: 0.85rem;
    }
    
    .td-name {
      font-weight: 500;
      color: var(--text-main);
    }
    
    .td-contact {
      font-size: 0.9rem;
    }
    
    .td-contact a {
      display: block;
      color: var(--text-dim);
    }
    
    .td-contact a:hover {
      color: var(--gold);
    }
    
    .td-prog {
      color: var(--gold);
      font-size: 0.9rem;
      font-weight: 500;
    }
    
    .td-msg {
      max-width: 320px;
      word-break: break-word;
    }
    
    .btn-msg-view {
      background: transparent;
      border: none;
      color: var(--gold);
      cursor: pointer;
      font-family: 'Jost', sans-serif;
      font-size: 0.85rem;
      text-decoration: underline;
      display: block;
      margin-top: 0.25rem;
    }
    
    .btn-msg-view:hover {
      color: var(--gold-glow);
    }
    
    .no-leads {
      text-align: center;
      padding: 4rem;
      color: var(--text-mute);
      font-size: 1.1rem;
    }
    
    /* ===== MODAL DETAIL POPUP ===== */
    .detail-modal {
      position: fixed;
      inset: 0;
      background: rgba(8, 10, 18, 0.8);
      backdrop-filter: blur(8px);
      z-index: 11000;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    }
    
    .detail-modal.active {
      opacity: 1;
      visibility: visible;
    }
    
    .detail-content {
      background: var(--bg-accent);
      border: 1px solid var(--gold);
      border-radius: var(--radius-md);
      padding: 2.5rem;
      max-width: 600px;
      width: 100%;
      position: relative;
      box-shadow: 0 20px 45px rgba(0, 0, 0, 0.5);
      transform: translateY(20px);
      transition: all 0.3s ease;
    }
    
    .detail-modal.active .detail-content {
      transform: translateY(0);
    }
    
    .detail-close {
      position: absolute;
      top: 1rem;
      right: 1.2rem;
      background: transparent;
      border: none;
      color: var(--text-mute);
      font-size: 2rem;
      cursor: pointer;
    }
    
    .detail-close:hover {
      color: var(--gold);
    }
    
    .detail-content h3 {
      font-size: 1.6rem;
      color: var(--gold);
      margin-bottom: 1.5rem;
      border-bottom: 1px solid var(--glass-border);
      padding-bottom: 0.5rem;
    }
    
    .detail-row {
      margin-bottom: 1rem;
      display: grid;
      grid-template-columns: 120px 1fr;
      font-size: 0.95rem;
    }
    
    .detail-label {
      font-family: 'Cinzel', serif;
      font-size: 0.75rem;
      letter-spacing: 0.08em;
      color: var(--text-mute);
      text-transform: uppercase;
      margin-top: 0.2rem;
    }
    
    .detail-val {
      color: var(--text-main);
      word-break: break-word;
    }
    
    .detail-val.msg {
      white-space: pre-wrap;
      line-height: 1.7;
    }
    
    @media (max-width: 768px) {
      body {
        padding: 20px 10px;
      }
      th, td {
        padding: 0.8rem 1rem;
      }
      th {
        font-size: 0.75rem;
      }
      td {
        font-size: 0.85rem;
      }
      .actions-area {
        width: 100%;
        justify-content: flex-end;
      }
      .detail-row {
        grid-template-columns: 100px 1fr;
      }
    }
  </style>
</head>
<body>

<?php if (!isset($_SESSION['admin_logged_in'])): ?>
  <!-- LOGIN SECTION -->
  <div class="login-container">
    <span class="om-logo">ॐ</span>
    <h2>HIMYOG</h2>
    <p class="login-sub">LEADS MANAGEMENT DASHBOARD</p>
    
    <?php if ($error): ?>
      <div class="err-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="admin.php">
      <div class="fg">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Enter admin password" required autofocus>
      </div>
      <button type="submit" name="login" class="btn-login">Login to Dashboard →</button>
    </form>
  </div>

<?php else: ?>
  <!-- DASHBOARD SECTION -->
  <div class="dashboard-wrap">
    <header>
      <div class="logo-area">
        <span class="cinzel" style="font-size: 2rem; color: var(--gold); line-height: 1;">ॐ</span>
        <div>
          <strong class="cinzel">HIMYOG</strong>
          <span style="font-size:0.75rem; letter-spacing:0.1em; color:var(--text-mute); display:block; text-transform:uppercase;">Admin Leads Portal</span>
        </div>
      </div>
      
      <div class="actions-area">
        <a href="admin.php?action=download" class="btn-action download">📥 Export CSV</a>
        <a href="admin.php?action=logout" class="btn-action logout">🚪 Logout</a>
      </div>
    </header>
    
    <div class="filter-bar">
      <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search leads by name, email, phone..." onkeyup="filterLeads()">
      </div>
      <div class="stats-count">
        Total Enquiries: <strong style="color:var(--gold);" id="leadsCount"><?php echo count($leads); ?></strong>
      </div>
    </div>
    
    <div class="table-container">
      <table id="leadsTable">
        <thead>
          <tr>
            <th style="width: 15%">Date / Time</th>
            <th style="width: 18%">Name</th>
            <th style="width: 20%">Contact</th>
            <th style="width: 20%">Program</th>
            <th style="width: 27%">Message</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($leads)): ?>
            <tr id="noLeadsRow">
              <td colspan="5" class="no-leads">No enquiries have been submitted yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($leads as $index => $lead): ?>
              <tr class="lead-row">
                <td class="td-date"><?php echo htmlspecialchars($lead['date']); ?></td>
                <td class="td-name"><?php echo htmlspecialchars($lead['name']); ?></td>
                <td class="td-contact">
                  <?php if ($lead['email'] !== 'no-email-provided@himyog.com'): ?>
                    <a href="mailto:<?php echo htmlspecialchars($lead['email']); ?>" target="_blank">✉️ <?php echo htmlspecialchars($lead['email']); ?></a>
                  <?php endif; ?>
                  <?php if (!empty($lead['phone'])): ?>
                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $lead['phone']); ?>" target="_blank">💬 <?php echo htmlspecialchars($lead['phone']); ?></a>
                  <?php endif; ?>
                </td>
                <td class="td-prog"><?php echo htmlspecialchars($lead['program']); ?></td>
                <td class="td-msg">
                  <?php 
                  $msg = $lead['message'];
                  $trimmed = strlen($msg) > 60 ? substr($msg, 0, 60) . '...' : $msg;
                  echo htmlspecialchars($trimmed);
                  
                  // If message is longer or multiline, give a view modal trigger
                  if (strlen($msg) > 60 || strpos($msg, "\n") !== false):
                  ?>
                    <button class="btn-msg-view" onclick="showLeadDetails(<?php echo htmlspecialchars(json_encode($lead)); ?>)">View Details →</button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  
  <!-- DETAIL POPUP MODAL -->
  <div id="detailModal" class="detail-modal" onclick="closeModal(event)">
    <div class="detail-content">
      <button class="detail-close" onclick="closeModalDirect()">&times;</button>
      <h3>Lead Detail</h3>
      
      <div class="detail-row">
        <div class="detail-label">Date</div>
        <div class="detail-val" id="detDate"></div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Name</div>
        <div class="detail-val" id="detName" style="font-weight:600;"></div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Email</div>
        <div class="detail-val" id="detEmail"></div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Phone / WA</div>
        <div class="detail-val" id="detPhone"></div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Program</div>
        <div class="detail-val" id="detProg" style="color:var(--gold); font-weight:500;"></div>
      </div>
      <div class="detail-row" style="display:flex; flex-direction:column; border-top:1px solid var(--glass-border); padding-top:1rem; margin-top:1rem;">
        <div class="detail-label" style="margin-bottom:0.5rem;">Message Detail</div>
        <div class="detail-val msg" id="detMsg"></div>
      </div>
    </div>
  </div>
  
  <script>
    // Search filter function
    function filterLeads() {
      const searchVal = document.getElementById('searchInput').value.toLowerCase();
      const rows = document.querySelectorAll('.lead-row');
      let visibleCount = 0;
      
      rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        if (text.includes(searchVal)) {
          row.style.display = '';
          visibleCount++;
        } else {
          row.style.display = 'none';
        }
      });
      
      // Update count text
      document.getElementById('leadsCount').textContent = visibleCount;
      
      // Handle empty search result state
      const tableBody = document.querySelector('tbody');
      let noMatchRow = document.getElementById('noMatchRow');
      
      if (visibleCount === 0 && rows.length > 0) {
        if (!noMatchRow) {
          noMatchRow = document.createElement('tr');
          noMatchRow.id = 'noMatchRow';
          noMatchRow.innerHTML = '<td colspan="5" style="text-align:center; padding:3rem; color:var(--text-mute);">No leads found matching your search.</td>';
          tableBody.appendChild(noMatchRow);
        }
      } else if (noMatchRow) {
        noMatchRow.remove();
      }
    }
    
    // Details modal trigger
    function showLeadDetails(lead) {
      document.getElementById('detDate').textContent = lead.date;
      document.getElementById('detName').textContent = lead.name;
      
      const emailEl = document.getElementById('detEmail');
      if (lead.email === 'no-email-provided@himyog.com') {
        emailEl.innerHTML = '<span style="color:var(--text-mute); font-style:italic;">None Provided</span>';
      } else {
        emailEl.innerHTML = `<a href="mailto:${lead.email}" target="_blank" style="color:var(--gold); text-decoration:underline;">${lead.email}</a>`;
      }
      
      const phoneEl = document.getElementById('detPhone');
      if (lead.phone) {
        const cleanPhone = lead.phone.replace(/[^0-9]/g, '');
        phoneEl.innerHTML = `<a href="https://wa.me/${cleanPhone}" target="_blank" style="color:var(--gold); text-decoration:underline;">${lead.phone} 💬 (Open WhatsApp)</a>`;
      } else {
        phoneEl.innerHTML = '<span style="color:var(--text-mute); font-style:italic;">None Provided</span>';
      }
      
      document.getElementById('detProg').textContent = lead.program;
      document.getElementById('detMsg').textContent = lead.message;
      
      document.getElementById('detailModal').classList.add('active');
    }
    
    function closeModalDirect() {
      document.getElementById('detailModal').classList.remove('active');
    }
    
    function closeModal(e) {
      const modal = document.getElementById('detailModal');
      if (e.target === modal) {
        closeModalDirect();
      }
    }
  </script>
<?php endif; ?>

</body>
</html>
