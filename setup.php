<?php
// ============================================================
//  setup.php — One-time Admin Account Creator
//  Ugay's Elegant Stitches
//
//  IMPORTANT: DELETE THIS FILE immediately after running it!
// ============================================================

require_once 'config/database.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = 'admin';
    $full_name = 'Administrator';
    $role      = 'admin';
    $password  = password_hash('admin123', PASSWORD_BCRYPT);

    // Check if admin already exists
    $check = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
    $check->execute([$username]);

    if ($check->rowCount() > 0) {
        $message = 'Admin account already exists. No changes were made.';
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$username, $password, $full_name, $role]);
        $success = true;
        $message = 'Admin account created successfully! You can now log in with username <strong>admin</strong> and password <strong>admin123</strong>.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Setup — Ugay's Elegant Stitches</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'DM Sans', sans-serif;
    background: #f7f8fb;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
  }
  .setup-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 32px rgba(0,0,0,0.10);
    padding: 48px 40px;
    max-width: 480px;
    width: 100%;
    text-align: center;
  }
  .setup-logo {
    font-family: 'Playfair Display', serif;
    font-size: 26px;
    color: #111827;
    margin-bottom: 6px;
  }
  .setup-logo span { color: #e83e8c; }
  .setup-sub {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 32px;
  }
  h2 {
    font-size: 20px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 10px;
  }
  p.desc {
    font-size: 14px;
    color: #374151;
    line-height: 1.6;
    margin-bottom: 28px;
  }
  .alert {
    border-radius: 8px;
    padding: 14px 18px;
    font-size: 14px;
    text-align: left;
    margin-bottom: 24px;
    border-left: 4px solid;
  }
  .alert-success {
    background: #f0fdf4;
    border-color: #22c55e;
    color: #166534;
  }
  .alert-error {
    background: #fff1f2;
    border-color: #ef4444;
    color: #991b1b;
  }
  .warning-box {
    background: #fffbeb;
    border: 1.5px solid #f59e0b;
    border-radius: 8px;
    padding: 14px 18px;
    font-size: 13px;
    color: #92400e;
    text-align: left;
    margin-top: 20px;
    margin-bottom: 24px;
    line-height: 1.6;
  }
  .warning-box strong { color: #b45309; }
  .btn {
    display: inline-block;
    padding: 12px 32px;
    border-radius: 8px;
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: opacity 0.2s;
    text-decoration: none;
  }
  .btn:hover { opacity: 0.88; }
  .btn-primary {
    background: linear-gradient(135deg, #ee4b91, #db317d);
    color: #fff;
  }
  .btn-secondary {
    background: #fff;
    color: #374151;
    border: 1.5px solid #e5e7eb;
    margin-left: 10px;
  }
  .info-list {
    list-style: none;
    text-align: left;
    margin-bottom: 28px;
    font-size: 14px;
    color: #374151;
  }
  .info-list li { padding: 4px 0; }
  .info-list li::before { content: '→ '; color: #e83e8c; font-weight: 700; }
</style>
</head>
<body>
<div class="setup-card">
  <div class="setup-logo">Ugay's <span>Elegant Stitches</span></div>
  <div class="setup-sub">Admin Setup Utility</div>

  <h2>Create Admin Account</h2>
  <p class="desc">This one-time script creates the default administrator account in your database. It should only be run once after importing <code>database.sql</code>.</p>

  <?php if ($message): ?>
    <div class="alert <?= $success ? 'alert-success' : 'alert-error' ?>">
      <?= $message ?>
    </div>
  <?php endif; ?>

  <?php if (!$success): ?>
    <ul class="info-list">
      <li>Username: <strong>admin</strong></li>
      <li>Password: <strong>admin123</strong></li>
      <li>Role: <strong>Administrator</strong></li>
    </ul>

    <form method="POST">
      <button type="submit" class="btn btn-primary">Create Admin Account</button>
      <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
  <?php else: ?>
    <a href="index.php" class="btn btn-primary">Go to Login</a>
  <?php endif; ?>

  <div class="warning-box">
    <strong>⚠ Security Warning:</strong><br>
    Delete <code>setup.php</code> from your server <strong>immediately</strong> after the admin account has been created. Leaving this file accessible is a security risk.
  </div>
</div>
</body>
</html>
