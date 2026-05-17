<?php
require_once 'config.php';

// Initialize error variable
$error = '';

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';

    // Validate input
    if (empty($email) || empty($password)) {
        $error = "Please fill in both email and password.";
    } else {
        // Prepare and execute SQL statement
        $stmt = $conn->prepare("SELECT id, username, email, password, role, status FROM users WHERE email = ? AND role = ?");
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if ($user['status'] === 'pending') {
                $error = "Your account is pending approval. Please contact the administrator.";
            } else {
                $loginSuccess = false;

                // 1. Check using password_verify (for new/migrated users)
                if (password_verify($password, $user['password'])) {
                    $loginSuccess = true;
                }
                // 2. Check using legacy SHA-256 (for old users)
                elseif ($user['password'] === hash('sha256', $password)) {
                    // Match found! Migrate to new secure hash
                    $newHash = password_hash($password, PASSWORD_DEFAULT);

                    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $updateStmt->bind_param("si", $newHash, $user['id']);
                    $updateStmt->execute();
                    $updateStmt->close();

                    $loginSuccess = true;
                }

                if ($loginSuccess) {
                    // Password is correct, set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    // ✅ ROLE BASED REDIRECT
                    if ($user['role'] === 'admin') {
                        header("Location: admin/admin.php");
                        exit();
                    } elseif ($user['role'] === 'organizer') {
                        header("Location: user/organizer/organizer.php");
                        exit();
                    } else {
                        header("Location: user/student/student.php");
                        exit();
                    }
                } else {
                    // Invalid password
                    $error = "Invalid email or password.";
                }
            }
        } else {
            // User not found
            $error = "Invalid email or password.";
        }

        // Close statement
        $stmt->close();
    }

}


// Set default active role if not set
$activeRole = $_POST['role'] ?? 'student';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Connect - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="container">
        <a href="index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Home
        </a>

        <div class="logo-container">
            <img src="assets/clg-logo.png" alt="CampusPulse Logo" class="logo">
            <h1 class="logo-text">Campus Connect</h1>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Welcome Back</h2>
                <p class="card-description">Sign in to your account</p>
            </div>

            <!-- Error Message Display -->
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card-content">
                <!-- Role Tabs -->
                <div class="tabs">
                    <div class="tabs-list">
                        <button type="button"
                            class="tab-trigger <?php echo $activeRole === 'student' ? 'active' : ''; ?>"
                            onclick="setActiveRole('student')" data-role="student">
                            Student
                        </button>
                        <button type="button"
                            class="tab-trigger <?php echo $activeRole === 'organizer' ? 'active' : ''; ?>"
                            onclick="setActiveRole('organizer')" data-role="organizer">
                            Organizer/Faculty
                        </button>
                        <button type="button" class="tab-trigger <?php echo $activeRole === 'admin' ? 'active' : ''; ?>"
                            onclick="setActiveRole('admin')" data-role="admin">
                            Admin
                        </button>
                    </div>
                </div>

                <!-- Login Form -->
                <form method="POST" action="login.php" class="login-form" id="loginForm">
                    <!-- Hidden field for role -->
                    <input type="hidden" name="role" id="roleInput" value="<?php echo $activeRole; ?>">

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" placeholder="user@gmail.com"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" class="form-input" required>
                    </div>

                    <div class="form-group password-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="password" name="password" placeholder="••••••••"
                                value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>"
                                class="form-input password-input" required>
                            <button type="button" class="toggle-password" id="togglePassword"
                                aria-label="Show password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="forgot-password">
                        <a href="forgot_password.php" class="forgot-link">
                            <i class="fas fa-key"></i>
                            Forgot Password?
                        </a>
                    </div>

                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span id="submitText">Sign In</span>
                    </button>
                </form>

                <div class="register-link">
                    <span class="register-text">Don't have an account? </span>
                    <a href="register.php" class="register-btn">Register here</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Function to set active role
        function setActiveRole(role) {
            document.getElementById('roleInput').value = role;

            // Update active tab visually
            document.querySelectorAll('.tab-trigger').forEach(tab => {
                if (tab.dataset.role === role) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });
        }

        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.setAttribute('aria-label', 'Hide password');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.setAttribute('aria-label', 'Show password');
            }
        });

        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function (event) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');

            let isValid = true;

            // Email validation
            if (!email) {
                isValid = false;
            }

            // Password validation
            if (!password || password.length < 6) {
                isValid = false;
            }

            if (isValid) {
                // Show loading state
                submitBtn.disabled = true;
                submitText.textContent = 'Signing in...';
                submitBtn.style.opacity = '0.7';
            }
            // If invalid, let PHP handle the error display
        });

        // Initialize with correct active role
        document.addEventListener('DOMContentLoaded', function () {
            setActiveRole('<?php echo $activeRole; ?>');
        });
    </script>
</body>

</html>
<style>
    /* styles.css */
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

    .tabs {
        margin-bottom: 1.5rem;
    }

    .tabs-list {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        background-color: var(--indigo-100);
        border-radius: var(--radius);
        padding: 0.25rem;
    }

    .tab-trigger {
        background: none;
        border: none;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: calc(var(--radius) - 0.25rem);
        cursor: pointer;
        transition: all 0.2s ease;
        color: var(--indigo-800);
    }

    .tab-trigger.active {
        background-color: var(--white);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        color: var(--indigo-900);
        font-weight: 600;
    }

    .tab-trigger:hover:not(.active) {
        background-color: var(--indigo-200);
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

    .login-form {
        margin-top: 1rem;
    }

    .submit-btn {
        width: 100%;
        background-color: var(--indigo-900);
        color: var(--white);
        border: none;
        border-radius: 9999px;
        padding: 1.125rem 0;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-top: 0.5rem;
    }

    .submit-btn:hover {
        background-color: var(--indigo-800);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
    }

    .submit-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .register-link {
        margin-top: 1.5rem;
        text-align: center;
        font-size: 0.875rem;
        padding-top: 1rem;
        border-top: 1px solid var(--indigo-100);
    }

    .register-text {
        color: var(--slate-600);
    }

    .register-btn {
        background: none;
        border: none;
        color: var(--indigo-900);
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: color 0.2s;
    }

    .register-btn:hover {
        color: var(--indigo-700);
        text-decoration: underline;
    }

    .alert {
        padding: 0.75rem 1rem;
        border-radius: var(--radius);
        margin-bottom: 1rem;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
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

    .alert i {
        margin-right: 0.5rem;
    }

    /* Responsive design */
    @media (max-width: 640px) {
        .container {
            max-width: 100%;
            padding: 0 0.5rem;
        }

        .logo-text {
            font-size: 1.5rem;
        }

        .card-title {
            font-size: 1.25rem;
        }

        .tab-trigger {
            padding: 0.5rem 0.5rem;
            font-size: 0.75rem;
        }
    }

    @media (max-width: 480px) {
        .tabs-list {
            grid-template-columns: 1fr;
            gap: 0.25rem;
        }

        .tab-trigger {
            padding: 0.75rem;
        }

        .logo-container {
            flex-direction: column;
            text-align: center;
        }

        .logo {
            margin-right: 0;
            margin-bottom: 0.5rem;
        }
    }

    /* Forgot Password Link */
    .forgot-password {
        text-align: right;
        margin-bottom: 20px;
    }

    .forgot-link {
        color: #141414;
        text-decoration: none;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .forgot-link:hover {
        text-decoration: underline;
        color: #3730a3;
    }

    /* Password Input Wrapper */
    .password-input-wrapper {
        position: relative;
    }

    .password-input {
        padding-right: 40px;
    }

    .toggle-password {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #6b7280;
        padding: 5px;
    }

    .toggle-password:hover {
        color: #4f46e5;
    }

    /* Error Message */
    .error-message {
        background-color: #fee2e2;
        border: 1px solid #fecaca;
        color: #dc2626;
        padding: 12px;
        border-radius: 8px;
        margin: 0 20px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .error-message i {
        font-size: 18px;
    }
</style>