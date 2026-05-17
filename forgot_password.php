<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $newPassword = $_POST['new_password'] ?? '';
  $confirmPassword = $_POST['confirm_password'] ?? '';

  if (empty($email) || empty($phone) || empty($newPassword) || empty($confirmPassword)) {
    $error = "Please fill in all fields.";
  } elseif ($newPassword !== $confirmPassword) {
    $error = "Passwords do not match.";
  } elseif (strlen($newPassword) < 6) {
    $error = "Password must be at least 6 characters.";
  } else {
    // Verify user exists with this email and phone
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND phone = ?");
    $stmt->bind_param("ss", $email, $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
      $user = $result->fetch_assoc();
      $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

      $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
      $updateStmt->bind_param("si", $hashedPassword, $user['id']);

      if ($updateStmt->execute()) {
        $success = "Password successfully reset! You can now log in.";
      } else {
        $error = "Error updating password. Please try again.";
      }
      $updateStmt->close();
    } else {
      $error = "No account found with that email and phone number.";
    }
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Campus Connect - Forgot Password</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --indigo-50: #f8fafc;
      --indigo-100: #f1f5f9;
      --indigo-200: #e2e8f0;
      --indigo-300: #cbd5e1;
      --indigo-700: #334155;
      --indigo-800: #1e293b;
      --indigo-900: #0f172a;
      --slate-200: #e2e8f0;
      --slate-600: #475569;
      --white: #ffffff;
      --radius: 0.5rem;
      --success-bg: #d1fae5;
      --success-text: #065f46;
      --success-border: #a7f3d0;
      --error-bg: #fee2e2;
      --error-text: #991b1b;
      --error-border: #fecaca;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    }

    body {
      background-color: var(--indigo-50);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }

    .container {
      width: 100%;
      max-width: 28rem;
    }

    .back-btn {
      display: inline-flex;
      align-items: center;
      background: none;
      border: none;
      color: var(--indigo-900);
      font-size: 0.875rem;
      font-weight: 500;
      cursor: pointer;
      margin-bottom: 1.5rem;
      padding: 0.5rem 0;
      text-decoration: none;
    }

    .back-btn:hover {
      color: var(--indigo-700);
    }

    .back-btn i {
      width: 1rem;
      height: 1rem;
      margin-right: 0.5rem;
    }

    .logo-container {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 2rem;
    }

    .logo {
      height: 3rem;
      width: 3rem;
      border-radius: var(--radius);
      object-fit: cover;
      margin-right: 0.75rem;
    }

    .logo-text {
      font-size: 1.875rem;
      font-weight: bold;
      color: var(--indigo-900);
    }

    .card {
      background-color: var(--white);
      border: 1px solid var(--slate-200);
      border-radius: var(--radius);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .card-header {
      padding: 1.5rem 1.5rem 0;
      text-align: center;
    }

    .card-title {
      font-size: 1.5rem;
      font-weight: bold;
      color: var(--indigo-900);
      margin-bottom: 0.25rem;
    }

    .card-description {
      color: var(--slate-600);
      font-size: 0.875rem;
    }

    .card-content {
      padding: 1.5rem;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .form-label {
      display: block;
      font-size: 0.875rem;
      font-weight: 500;
      margin-bottom: 0.25rem;
      color: var(--indigo-900);
    }

    .form-input {
      width: 100%;
      padding: 0.625rem 0.875rem;
      border: 1px solid var(--slate-200);
      border-radius: var(--radius);
      font-size: 0.875rem;
      transition: all 0.2s ease;
      background-color: var(--white);
    }

    .form-input:focus {
      outline: none;
      border-color: var(--indigo-700);
      box-shadow: 0 0 0 3px rgba(51, 65, 85, 0.1);
    }

    .submit-btn {
      width: 100%;
      background-color: var(--indigo-900);
      color: var(--white);
      border: none;
      border-radius: 9999px;
      padding: 1rem 0;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.2s ease;
      margin-top: 1rem;
    }

    .submit-btn:hover {
      background-color: var(--indigo-800);
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
    }

    .login-link {
      margin-top: 1.5rem;
      text-align: center;
      font-size: 0.875rem;
      padding-top: 1rem;
      border-top: 1px solid var(--indigo-100);
    }

    .link-text {
      color: var(--slate-600);
    }

    .link-btn {
      color: var(--indigo-900);
      font-weight: 600;
      text-decoration: none;
      transition: color 0.2s;
    }

    .link-btn:hover {
      color: var(--indigo-700);
      text-decoration: underline;
    }

    .alert {
      padding: 0.75rem 1rem;
      border-radius: var(--radius);
      margin: 0 1.5rem 1.5rem;
      font-size: 0.875rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .alert-success {
      background-color: var(--success-bg);
      color: var(--success-text);
      border: 1px solid var(--success-border);
    }

    .alert-error {
      background-color: var(--error-bg);
      color: var(--error-text);
      border: 1px solid var(--error-border);
    }
  </style>
</head>

<body>
  <div class="container">
    <a href="login.php" class="back-btn">
      <i class="fas fa-arrow-left"></i>
      Back to Login
    </a>

    <div class="logo-container">
      <img src="assets/clg-logo.png" alt="CampusPulse Logo" class="logo">
      <h1 class="logo-text">Campus Connect</h1>
    </div>

    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Reset Password</h2>
        <p class="card-description">Enter your details to reset your password</p>
      </div>

      <div class="card-content">
        <?php if ($error): ?>
          <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success); ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="forgot_password.php">
          <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" id="email" name="email" placeholder="user@gmail.com" class="form-input" required>
          </div>

          <div class="form-group">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="text" id="phone" name="phone" placeholder="Enter your registered phone" class="form-input"
              required>
          </div>

          <div class="form-group">
            <label for="new_password" class="form-label">New Password</label>
            <input type="password" id="new_password" name="new_password" placeholder="••••••••" class="form-input"
              required>
          </div>

          <div class="form-group">
            <label for="confirm_password" class="form-label">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••"
              class="form-input" required>
          </div>

          <button type="submit" class="submit-btn">Reset Password</button>
        </form>

        <div class="login-link">
          <span class="link-text">Remember your password? </span>
          <a href="login.php" class="link-btn">Login here</a>
        </div>
      </div>
    </div>
  </div>
</body>

</html>