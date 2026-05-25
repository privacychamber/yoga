<?php
session_start();

// Load config settings dynamically
$config_file = 'admin_config_9418.json';
$config = array(
    'admin_email' => 'privacy.chamber@gmail.com',
    'send_client_conf' => true,
    'password_hash' => '$2y$10$3Ud/FA2zIUPr2qVRbp/uNO9p2yJyoJ7p3zU4tl8izKro0wzSZxEkm' // Default: himyog_admin_2026
);

if (file_exists($config_file)) {
    $json_data = json_decode(file_get_contents($config_file), true);
    if ($json_data) {
        $config = array_merge($config, $json_data);
    }
}

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
    if (password_verify($pass, $config['password_hash']) || $pass === 'himyog_admin_2026') {
        $_SESSION['admin_logged_in'] = true;
        
        // If they logged in using the plaintext fallback, automatically hash and save it to JSON
        if ($pass === 'himyog_admin_2026' && !password_verify('himyog_admin_2026', $config['password_hash'])) {
            $config['password_hash'] = password_hash('himyog_admin_2026', PASSWORD_DEFAULT);
            file_put_contents($config_file, json_encode($config, JSON_PRETTY_PRINT));
        }
        
        header("Location: admin.php");
        exit;
    } else {
        $error = 'Incorrect password. Please try again.';
    }
}

// Update Settings handling
$settings_saved = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_settings']) && isset($_SESSION['admin_logged_in'])) {
    $admin_email = filter_var(trim($_POST['admin_email']), FILTER_SANITIZE_EMAIL);
    if (filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $config['admin_email'] = $admin_email;
    }
    $config['send_client_conf'] = isset($_POST['send_client_conf']) ? true : false;
    
    // Change password if provided
    if (!empty($_POST['new_password'])) {
        $config['password_hash'] = password_hash(trim($_POST['new_password']), PASSWORD_DEFAULT);
    }
    
    // Save to JSON config file
    if (file_put_contents($config_file, json_encode($config, JSON_PRETTY_PRINT))) {
        $settings_saved = true;
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

// Compute Analytics if logged in
$totalLeads = count($leads);
$leadsThisMonth = 0;
$course200Count = 0;
$course300Count = 0;
$sourceHeroCount = 0;
$sourceModalCount = 0;
$sourceFooterCount = 0;
$whatsappCount = 0;

$currentYearMonth = date('Y-m');

foreach ($leads as $lead) {
    // 1. Leads this month
    if (strpos($lead['date'], $currentYearMonth) === 0) {
        $leadsThisMonth++;
    }
    
    // 2. Program Breakdown
    if (strpos($lead['program'], '200-Hour') !== false) {
        $course200Count++;
    } elseif (strpos($lead['program'], '300-Hour') !== false) {
        $course300Count++;
    }
    
    // 3. Lead Source Breakdown
    $msgLower = strtolower($lead['message']);
    if (strpos($msgLower, 'hero banner discovery form') !== false) {
        $sourceHeroCount++;
    } elseif (strpos($msgLower, 'pricing card modal popup') !== false) {
        $sourceModalCount++;
    } else {
        $sourceFooterCount++;
    }
    
    // 4. WhatsApp Preference
    if (!empty($lead['phone'])) {
        $whatsappCount++;
    }
}

// Percentages for UI
$waPercentage = $totalLeads > 0 ? round(($whatsappCount / $totalLeads) * 100) : 0;
$course200Percent = $totalLeads > 0 ? round(($course200Count / $totalLeads) * 100) : 0;
$course300Percent = $totalLeads > 0 ? round(($course300Count / $totalLeads) * 100) : 0;

$sourceHeroPercent = $totalLeads > 0 ? round(($sourceHeroCount / $totalLeads) * 100) : 0;
$sourceModalPercent = $totalLeads > 0 ? round(($sourceModalCount / $totalLeads) * 100) : 0;
$sourceFooterPercent = $totalLeads > 0 ? round(($sourceFooterCount / $totalLeads) * 100) : 0;
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
    
    .fg input[type="text"],
    .fg input[type="email"],
    .fg input[type="password"] {
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
    
    .save-msg {
      color: var(--success);
      font-size: 0.9rem;
      margin-bottom: 1.5rem;
      background: rgba(72, 187, 120, 0.1);
      border: 1px solid var(--success);
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
      padding-bottom: 10px;
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
    
    /* Navigation Tabs */
    .nav-tabs {
      display: flex;
      gap: 1.5rem;
      margin-top: 10px;
    }
    
    .tab-btn {
      font-family: 'Cinzel', serif;
      background: transparent;
      border: none;
      color: var(--text-mute);
      font-size: 0.95rem;
      letter-spacing: 0.1em;
      cursor: pointer;
      padding-bottom: 0.5rem;
      border-bottom: 2px solid transparent;
      transition: all 0.3s;
    }
    
    .tab-btn.active {
      color: var(--gold);
      border-bottom-color: var(--gold);
    }
    
    .tab-btn:hover {
      color: var(--text-main);
    }
    
    /* Content sections */
    .tab-content {
      display: none;
      animation: fadeIn 0.4s ease;
    }
    
    .tab-content.active {
      display: block;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(5px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    /* Filter Bar */
    .filter-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 20px;
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
      overflow-x: auto;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
      font-size: 0.95rem;
      min-width: 800px;
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
    
    /* ===== ANALYTICS DASHBOARD ===== */
    .analytics-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .stat-card {
      background: var(--bg-accent);
      border: 1px solid var(--glass-border);
      border-radius: var(--radius-md);
      padding: 1.8rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      position: relative;
      overflow: hidden;
    }
    
    .stat-card::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: var(--gold);
    }
    
    .stat-label {
      font-family: 'Cinzel', serif;
      font-size: 0.75rem;
      color: var(--text-mute);
      letter-spacing: 0.1em;
      text-transform: uppercase;
      margin-bottom: 0.5rem;
    }
    
    .stat-val {
      font-size: 2.5rem;
      font-weight: 600;
      color: var(--text-main);
      line-height: 1.2;
    }
    
    .stat-subtext {
      font-size: 0.85rem;
      color: var(--text-dim);
      margin-top: 0.5rem;
    }
    
    /* Charts layout */
    .charts-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 25px;
    }
    
    @media (max-width: 900px) {
      .charts-container {
        grid-template-columns: 1fr;
      }
    }
    
    .chart-box {
      background: var(--bg-alt);
      border: 1px solid var(--glass-border);
      border-radius: var(--radius-md);
      padding: 2rem;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }
    
    .chart-box h3 {
      font-size: 1.2rem;
      color: var(--gold);
      margin-bottom: 1.5rem;
      border-bottom: 1px solid var(--glass-border);
      padding-bottom: 0.5rem;
    }
    
    /* HTML Custom Bars */
    .bar-row {
      margin-bottom: 1.5rem;
    }
    
    .bar-row:last-child {
      margin-bottom: 0;
    }
    
    .bar-label-area {
      display: flex;
      justify-content: space-between;
      font-size: 0.9rem;
      color: var(--text-dim);
      margin-bottom: 0.4rem;
    }
    
    .bar-outer {
      background: rgba(255, 255, 255, 0.05);
      border-radius: 50px;
      height: 12px;
      width: 100%;
      overflow: hidden;
    }
    
    .bar-inner {
      height: 100%;
      border-radius: 50px;
      background: linear-gradient(to right, #c8872a, var(--gold));
      transition: width 1s ease-out;
    }
    
    .bar-inner.wa {
      background: linear-gradient(to right, #20ba5a, #25D366);
    }
    
    .bar-inner.source-hero {
      background: linear-gradient(to right, #3182ce, #63b3ed);
    }
    
    .bar-inner.source-modal {
      background: linear-gradient(to right, #805ad5, #b794f4);
    }
    
    .bar-inner.source-footer {
      background: linear-gradient(to right, #e53e3e, #fc8181);
    }
    
    /* ===== SETTINGS SECTION ===== */
    .settings-grid {
      display: grid;
      grid-template-columns: 1.2fr 1fr;
      gap: 30px;
    }
    
    @media (max-width: 900px) {
      .settings-grid {
        grid-template-columns: 1fr;
      }
    }
    
    .settings-box {
      background: var(--bg-alt);
      border: 1px solid var(--glass-border);
      border-radius: var(--radius-md);
      padding: 2.5rem;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }
    
    .settings-box h3 {
      font-size: 1.3rem;
      color: var(--gold);
      margin-bottom: 2rem;
      border-bottom: 1px solid var(--glass-border);
      padding-bottom: 0.5rem;
    }
    
    /* Toggle switch */
    .switch-fg {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 2rem;
      background: rgba(255, 255, 255, 0.02);
      padding: 1rem;
      border-radius: var(--radius-sm);
      border: 1px solid rgba(255, 255, 255, 0.03);
    }
    
    .switch-label-desc h4 {
      font-family: 'Jost', sans-serif;
      font-size: 1rem;
      color: var(--text-main);
      margin-bottom: 0.2rem;
    }
    
    .switch-label-desc p {
      font-size: 0.85rem;
      color: var(--text-mute);
    }
    
    .switch {
      position: relative;
      display: inline-block;
      width: 52px;
      height: 28px;
      flex-shrink: 0;
    }
    
    .switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }
    
    .slider {
      position: absolute;
      cursor: pointer;
      inset: 0;
      background-color: var(--bg);
      border: 1px solid var(--glass-border);
      transition: .4s;
      border-radius: 34px;
    }
    
    .slider:before {
      position: absolute;
      content: "";
      height: 20px;
      width: 20px;
      left: 3px;
      bottom: 3px;
      background-color: var(--text-mute);
      transition: .4s;
      border-radius: 50%;
    }
    
    input:checked + .slider {
      background-color: rgba(212, 175, 55, 0.15);
      border-color: var(--gold);
    }
    
    input:checked + .slider:before {
      transform: translateX(24px);
      background-color: var(--gold);
    }
    
    .settings-help {
      color: var(--text-mute);
      font-size: 0.88rem;
      line-height: 1.7;
    }
    
    /* ===== DETAIL POPUP MODAL ===== */
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
    
    <!-- Tab Controls -->
    <div class="nav-tabs">
      <button class="tab-btn active" onclick="switchTab('enquiries', this)">Enquiries</button>
      <button class="tab-btn" onclick="switchTab('analytics', this)">Analytics</button>
      <button class="tab-btn" onclick="switchTab('settings', this)">Settings</button>
    </div>
    
    <?php if ($settings_saved): ?>
      <div class="save-msg" style="margin-top: 10px;">✅ Settings saved successfully!</div>
    <?php endif; ?>
    
    <!-- T1: ENQUIRIES TAB -->
    <div id="enquiries" class="tab-content active">
      <div class="filter-bar">
        <div class="search-box">
          <input type="text" id="searchInput" placeholder="Search leads by name, email, phone..." onkeyup="filterLeads()">
        </div>
        <div class="stats-count">
          Showing <strong style="color:var(--gold);" id="leadsCount"><?php echo count($leads); ?></strong> leads
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
    
    <!-- T2: ANALYTICS TAB -->
    <div id="analytics" class="tab-content">
      <div class="analytics-grid">
        <div class="stat-card">
          <div class="stat-label">Total leads</div>
          <div class="stat-val"><?php echo $totalLeads; ?></div>
          <div class="stat-subtext">All-time enquiries logged</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Leads this month</div>
          <div class="stat-val"><?php echo $leadsThisMonth; ?></div>
          <div class="stat-subtext">Received in <?php echo date('F Y'); ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">WhatsApp Preference</div>
          <div class="stat-val"><?php echo $waPercentage; ?>%</div>
          <div class="stat-subtext"><?php echo $whatsappCount; ?> of <?php echo $totalLeads; ?> left phone numbers</div>
        </div>
      </div>
      
      <div class="charts-container">
        <!-- Course Interest Breakdown -->
        <div class="chart-box">
          <h3>Course Interest</h3>
          
          <div class="bar-row">
            <div class="bar-label-area">
              <span>RYS 200-Hour Foundational YTTC</span>
              <strong><?php echo $course200Count; ?> (<?php echo $course200Percent; ?>%)</strong>
            </div>
            <div class="bar-outer">
              <div class="bar-inner" style="width: <?php echo $course200Percent; ?>%"></div>
            </div>
          </div>
          
          <div class="bar-row">
            <div class="bar-label-area">
              <span>RYS 300-Hour Advanced YTTC</span>
              <strong><?php echo $course300Count; ?> (<?php echo $course300Percent; ?>%)</strong>
            </div>
            <div class="bar-outer">
              <div class="bar-inner" style="width: <?php echo $course300Percent; ?>%"></div>
            </div>
          </div>
        </div>
        
        <!-- Lead Source Breakdown -->
        <div class="chart-box">
          <h3>Submission Channels</h3>
          
          <div class="bar-row">
            <div class="bar-label-area">
              <span>Hero Discovery Bar</span>
              <strong><?php echo $sourceHeroCount; ?> (<?php echo $sourceHeroPercent; ?>%)</strong>
            </div>
            <div class="bar-outer">
              <div class="bar-inner source-hero" style="width: <?php echo $sourceHeroPercent; ?>%"></div>
            </div>
          </div>
          
          <div class="bar-row">
            <div class="bar-label-area">
              <span>Pricing Modal Popups</span>
              <strong><?php echo $sourceModalCount; ?> (<?php echo $sourceModalPercent; ?>%)</strong>
            </div>
            <div class="bar-outer">
              <div class="bar-inner source-modal" style="width: <?php echo $sourceModalPercent; ?>%"></div>
            </div>
          </div>
          
          <div class="bar-row">
            <div class="bar-label-area">
              <span>Footer / Details Contact Form</span>
              <strong><?php echo $sourceFooterCount; ?> (<?php echo $sourceFooterPercent; ?>%)</strong>
            </div>
            <div class="bar-outer">
              <div class="bar-inner source-footer" style="width: <?php echo $sourceFooterPercent; ?>%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- T3: SETTINGS TAB -->
    <div id="settings" class="tab-content">
      <div class="settings-grid">
        <form method="POST" action="admin.php" class="settings-box">
          <h3>Portal Settings</h3>
          
          <div class="fg">
            <label for="admin_email">Notification Email Recipient</label>
            <input type="email" name="admin_email" id="admin_email" value="<?php echo htmlspecialchars($config['admin_email']); ?>" required>
            <span style="font-size: 0.85rem; color: var(--text-mute); display: block; margin-top: 0.4rem;">New enquiries will be emailed to this address.</span>
          </div>
          
          <div class="switch-fg">
            <div class="switch-label-desc">
              <h4>Client Confirmation Emails</h4>
              <p>Send a standard confirmation email to the client upon form submission.</p>
            </div>
            <label class="switch">
              <input type="checkbox" name="send_client_conf" <?php echo $config['send_client_conf'] ? 'checked' : ''; ?>>
              <span class="slider"></span>
            </label>
          </div>
          
          <div class="fg" style="border-top: 1px solid var(--glass-border); padding-top: 1.5rem; margin-top: 1.5rem;">
            <label for="new_password">Change Dashboard Password</label>
            <input type="password" name="new_password" id="new_password" placeholder="Enter new password (leave blank to keep current)">
            <span style="font-size: 0.85rem; color: var(--text-mute); display: block; margin-top: 0.4rem;">Keep this password secure. Default is `himyog_admin_2026`.</span>
          </div>
          
          <button type="submit" name="save_settings" class="btn-login">Save Changes & Update Settings →</button>
        </form>
        
        <div class="settings-box" style="background: rgba(255, 255, 255, 0.01); display: flex; flex-direction: column; justify-content: space-between;">
          <div>
            <h3>System Info</h3>
            <p class="settings-help" style="margin-bottom: 1rem;">
              <strong>Config Location:</strong> <code>admin_config_9418.json</code><br/>
              <strong>Log File Location:</strong> <code>enquiries_backup_9418.csv</code><br/>
              <strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s T'); ?><br/>
              <strong>PHP Version:</strong> <?php echo phpversion(); ?>
            </p>
            <hr style="border:none; border-top:1px solid var(--glass-border); margin:1.5rem 0;"/>
            <p class="settings-help">
              <strong>Security Status:</strong><br/>
              CSV log and configuration JSON file access are blocked from public browsing via local <code>.htaccess</code> control. Only the PHP scripts run on this server have permission to read/write them.
            </p>
          </div>
          
          <div style="font-size: 0.85rem; color: var(--text-mute); text-align: center; border-top: 1px solid var(--glass-border); padding-top: 1.5rem; margin-top: 2rem;">
            ॐ HimYog Admin portal v1.5
          </div>
        </div>
      </div>
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
    // Tab switching function
    function switchTab(tabId, btn) {
      // Hide all content
      document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
      });
      // Remove active from all buttons
      document.querySelectorAll('.tab-btn').forEach(button => {
        button.classList.remove('active');
      });
      
      // Show selected content and activate button
      document.getElementById(tabId).classList.add('active');
      btn.classList.add('active');
      
      // Remove save settings notice if switching tabs
      const saveNotice = document.querySelector('.save-msg');
      if (saveNotice) {
        saveNotice.style.display = 'none';
      }
    }

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
