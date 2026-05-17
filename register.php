<?php
// Start session for authentication
require_once 'config.php';

// Handle form submission
$error = '';
$success = '';
$activeRole = isset($_POST['role']) ? $_POST['role'] : 'student';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'student';
    
    // Student-specific fields
    $department = '';
    $year = '';
    $rollNumber = '';
    
    // Organizer-specific field
    $organization = '';
    
    if ($role === 'student') {
        $department = trim($_POST['department'] ?? '');
        $year = trim($_POST['year'] ?? '');
        $rollNumber = trim($_POST['roll_number'] ?? '');
    } else {
        $organization = trim($_POST['organization'] ?? '');
    }
    
    // Validation
    $errors = [];
    
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validations for all users
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters';
    } elseif (strlen($username) > 50) {
        $errors[] = 'Username must be less than 50 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores';
    }
    
    // Email validation
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    // Password validation
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    // Role-specific validations
      // Student-specific validations
    if ($role === 'student') {
        
        if (empty($department)) {
            $errors[] = 'Department is required';
        } elseif (strlen($department) > 50) {
            $errors[] = 'Department must be less than 50 characters';
        }
        
        if (empty($year)) {
            $errors[] = 'Academic year is required';
        } elseif (strlen($year) > 20) {
            $errors[] = 'Year must be less than 20 characters';
        }
    }

     
    // Check if username or email already exists
    if (empty($errors)) {
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = 'Username or email already exists';
        }
        
        // Check if student ID already exists (for student role)
        if ($role === 'student' && !empty($rollNumber)) {
            $check_student_sql = "SELECT id FROM users WHERE roll_number = ?";
            $check_student_stmt = $conn->prepare($check_student_sql);
            $check_student_stmt->bind_param("s", $rollNumber);
            $check_student_stmt->execute();
            $check_student_result = $check_student_stmt->get_result();
            
            if ($check_student_result->num_rows > 0) {
                $errors[] = 'Roll Number already registered';
            }
        }
    }


    // Organizer-specific validations
    if ($role === 'organizer' && empty($organization)) {
        $errors[] = 'Organization is required for organizers';
    }
    
    // If no errors, proceed to register user 
    if (empty($errors)) {
       
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));
        
        // Insert user with student details if applicable
        $status = ($role === 'student') ? 'active' : 'pending';
        
        if ($role === 'student') {
            $sql = "INSERT INTO users (username, email, password, role, verification_token, 
                 phone, department, year, roll_number, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssss", $username, $email, $hashed_password, $role, $verification_token,
                             $phone, $department, $year, $rollNumber, $status);
        } else {
            $sql = "INSERT INTO users (username, email, password, role, verification_token, phone, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $username, $email, $hashed_password, $role, $verification_token, $phone, $status);
        }
        if (!$stmt->execute()) {
            $errors[] = 'Database error: ' . $stmt->error;
        }     
        
        // Redirect based on role
        if ($role === 'student') {
            // Auto-login student
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;
            $_SESSION['username'] = $username;
            $_SESSION['logged_in'] = true;
            $_SESSION['department'] = $department;
            $_SESSION['year'] = $year;
            $_SESSION['rollNumber'] = $rollNumber;
            
            $success = 'Registration successful! Redirecting to student dashboard...';
            header("refresh:1;url=user/student.php");
        } else {
            // Organizer needs approval
            $success = 'Registration successful! Your organizer account is pending admin approval.';
            header("refresh:2;url=login.php");
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
}
// Departments for dropdown
$departments = [
    'Tamil',
    'English',
    'Commerce',
    'Mathematics',
    'Computer Science'
];

// Years for dropdown
$years = [
    'First Year',
    'Second Year', 
    'Third Year'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Connect - Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Home
        </a>
        
        <div class="logo-container">
            <img src="assets/clg-logo.png" 
                 alt="CampusPulse Logo" 
                 class="logo">
            <h1 class="logo-text">Campus Connect</h1>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Create Account</h2>
            </div>
            
            <div class="card-content">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Role Tabs -->
                <div class="tabs">
                    <div class="tabs-list">
                        <button type="button" 
                                class="tab-trigger <?php echo $activeRole === 'student' ? 'active' : ''; ?>" 
                                onclick="setActiveRole('student')"
                                data-role="student">
                            Student
                        </button>
                        <button type="button" 
                                class="tab-trigger <?php echo $activeRole === 'organizer' ? 'active' : ''; ?>" 
                                onclick="setActiveRole('organizer')"
                                data-role="organizer">
                            Faculty/Organizer
                        </button>
                        
                    </div>
                </div>

                <!-- Registration Form -->
                <form method="POST" action="register.php" class="register-form" id="registerForm">
                    <!-- Hidden field for role -->
                    <input type="hidden" name="role" id="roleInput" value="<?php echo $activeRole; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   placeholder="Username" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                   class="form-input" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   placeholder="username@gmail.com" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   class="form-input" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   placeholder="••••••••" 
                                   class="form-input" 
                                   required
                                   minlength="6">
                            <small class="form-hint">Minimum 6 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone (Optional)</label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   placeholder="+91 23456 83900" 
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                   class="form-input">
                        </div>
                    </div>
                    
                    <!-- Student Fields -->
                    <div id="studentFields" class="<?php echo $activeRole === 'student' ? '' : 'hidden'; ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="department" class="form-label">Department *</label>
                                <select id="department" 
                                        name="department" 
                                        class="form-input" 
                                        required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept; ?>" 
                                            <?php echo (isset($_POST['department']) && $_POST['department'] === $dept) ? 'selected' : ''; ?>>
                                            <?php echo $dept; ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="other" <?php echo (isset($_POST['department']) && $_POST['department'] === 'other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="year" class="form-label">Year</label>
                                <select id="year" 
                                        name="year" 
                                        class="form-input">
                                    <option value="">Select Year</option>
                                    <?php foreach ($years as $yearOption): ?>
                                        <option value="<?php echo $yearOption; ?>" 
                                            <?php echo (isset($_POST['year']) && $_POST['year'] === $yearOption) ? 'selected' : ''; ?>>
                                            <?php echo $yearOption; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="roll_number" class="form-label">Roll Number</label>
                            <input type="text" 
                                   id="roll_number" 
                                   name="roll_number" 
                                   placeholder="e.g., CS2021001" 
                                   value="<?php echo isset($_POST['roll_number']) ? htmlspecialchars($_POST['roll_number']) : ''; ?>"
                                   class="form-input">
                        </div>
                    </div>
                    
                    <!-- Organizer Fields -->
                    <div id="organizerFields" class="<?php echo $activeRole === 'organizer' ? '' : 'hidden'; ?>">
                        <div class="form-group">
                            <label for="organization" class="form-label">Organization *</label>
                            <input type="text" 
                                   id="organization" 
                                   name="organization" 
                                   placeholder="Student Council" 
                                   value="<?php echo isset($_POST['organization']) ? htmlspecialchars($_POST['organization']) : ''; ?>"
                                   class="form-input" 
                                   required>
                        </div>
                    </div>
                    
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span id="submitText">Create Account</span>
                    </button>
                </form>

                <div class="register-link">
                    <span class="register-text">Already have an account? </span>
                    <a href="login.php" class="register-btn">Sign in here</a>
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
            
            // Show/hide role-specific fields
            if (role === 'student') {
                document.getElementById('studentFields').classList.remove('hidden');
                document.getElementById('organizerFields').classList.add('hidden');
                
                // Make student department required
                document.getElementById('department').required = true;
                document.getElementById('organization').required = false;
            } else {
                document.getElementById('studentFields').classList.add('hidden');
                document.getElementById('organizerFields').classList.remove('hidden');
                
                // Make organizer organization required
                document.getElementById('department').required = false;
                document.getElementById('organization').required = true;
            }
        }
        
        // Handle "Other" department selection
        document.getElementById('department').addEventListener('change', function() {
            const otherDeptField = document.getElementById('otherDeptField');
            if (this.value === 'other') {
                otherDeptField.classList.remove('hidden');
                document.getElementById('other_department').required = true;
            } else {
                otherDeptField.classList.add('hidden');
                document.getElementById('other_department').required = false;
            }
        });
        
        // Add loading state on form submission
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            
            // If "Other" department is selected, transfer value
            const departmentSelect = document.getElementById('department');
            if (departmentSelect.value === 'other') {
                const otherDeptInput = document.getElementById('other_department');
                if (otherDeptInput.value.trim()) {
                    // Create a hidden input with the correct name
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'department';
                    hiddenInput.value = otherDeptInput.value;
                    this.appendChild(hiddenInput);
                    
                    // Disable the original select
                    departmentSelect.disabled = true;
                }
            }
            
            // Show loading state
            submitBtn.disabled = true;
            submitText.textContent = 'Creating Account...';
            submitBtn.style.opacity = '0.7';
            
            // Form will submit normally
        });
        
        // Initialize with correct active role
        document.addEventListener('DOMContentLoaded', function() {
            const role = '<?php echo $activeRole; ?>';
            setActiveRole(role);
            
            // Initialize "Other" department field if needed
            const departmentSelect = document.getElementById('department');
            if (departmentSelect.value === 'other') {
                document.getElementById('otherDeptField').classList.remove('hidden');
            }
        });

         // Username availability check
    let usernameTimeout;
    usernameInput.addEventListener('input', function() {
        clearTimeout(usernameTimeout);
        usernameTimeout = setTimeout(() => {
            checkUsernameAvailability(this.value);
        }, 500);
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
    grid-template-columns: repeat(2, 1fr);
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

    /* Form Rows for 2-column layout */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .form-row .form-group {
        margin-bottom: 0;
    }
    
    @media (max-width: 640px) {
        .form-row {
            grid-template-columns: 1fr;
        }
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


.register-form {
    margin-top: 1rem;
}

.form-hint {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.75rem;
    color: var(--slate-600);
}

select.form-input {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%230f172a' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.875rem center;
    background-size: 1rem;
    padding-right: 2.5rem;
}

.hidden {
    display: none !important;
}

/* Role-specific fields animation */
#studentFields, #organizerFields {
    transition: all 0.3s ease;
}

/* Form validation styles */
.form-input:invalid {
    border-color: var(--error-border);
}

.form-input:valid {
    border-color: var(--success-border);
}

/* Responsive adjustments for registration form */
@media (max-width: 480px) {
    .tabs-list {
        grid-template-columns: 1fr;
        gap: 0.25rem;
    }
    
    .tab-trigger {
        padding: 0.75rem;
    }
}

</style>
