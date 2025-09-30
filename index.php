<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/db_connection.php';

$response = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $selected_role = trim($_POST['role'] ?? '');

    if ($username === '' || $password === '' || $selected_role === '') {
        $response = "error|All fields are required";
    } else {
        $stmt = $conn->prepare("SELECT UserID, Username, Password, Role, RefID FROM users WHERE Username = ? AND Role = ? LIMIT 1");
        $stmt->bind_param("ss", $username, $selected_role);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $hash = $row['Password'];
            $ok = password_verify($password, $hash) || ($password === $hash && strlen($hash) < 60);

            if ($ok) {
                // Auto-upgrade legacy plaintext to bcrypt
                if ($password === $hash || !preg_match('/^\$2y\$|^\$argon2/i', $hash)) {
                    $newHash = password_hash($password, PASSWORD_BCRYPT);
                    if ($newHash) {
                        $up = $conn->prepare("UPDATE users SET Password = ? WHERE UserID = ?");
                        $up->bind_param("si", $newHash, $row['UserID']);
                        $up->execute();
                    }
                }

                $_SESSION['user_id'] = (int)$row['UserID'];
                $_SESSION['username'] = $row['Username'];
                $_SESSION['role'] = $row['Role'];
                $_SESSION['ref_id'] = $row['RefID'];

                $redirect = "";
                switch ($row['Role']) {
                    case 'admin': $redirect = "admin/dashboard.php"; break;
                    case 'student': $redirect = "student/dashboard.php"; break;
                    case 'teacher': $redirect = "Teacher/dashboard.php"; break;
                    case 'coordinator': $redirect = "Coordinator/dashboard.php"; break;
                    default: $response = "error|Unknown role assigned!"; break;
                }
                if ($redirect) $response = "success|$redirect";
            } else {
                $response = "error|Invalid password!";
            }
        } else {
            $response = "error|No user found for selected role!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Unified Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    body {
        background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);
        color: #fff;
        font-family: 'Segoe UI', Tahoma, sans-serif;
    }
    .login-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.4);
        width: 100%;
        max-width: 400px;
    }
    .login-card h3 {
        font-weight: bold;
        color: #fff;
    }
    .form-control, .form-select {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
        border: none;
    }
    .form-control:focus, .form-select:focus {
        background: rgba(255, 255, 255, 0.3);
        color: #fff;
        box-shadow: none;
        border: 1px solid #00d4ff;
    }
    .btn-custom {
        background: #00d4ff;
        border: none;
        color: #000;
        font-weight: bold;
        transition: all 0.3s ease-in-out;
    }
    .btn-custom:hover {
        background: #00a6d4;
        transform: scale(1.05);
    }
    .icon-input {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #fff;
    }
    .input-group {
        position: relative;
    }
    option {
        background: #16213e;
        color: #fff;
    }
</style>
</head>
<body>
<div class="d-flex justify-content-center align-items-center" style="height:100vh;">
    <div class="login-card">
        <h3 class="text-center mb-4"><i class="bi bi-shield-lock-fill"></i> Login</h3>
        <form method="post">
            <!-- Role Selector -->
            <div class="mb-3">
                <select name="role" class="form-select" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="teacher">Teacher</option>
                    <option value="student">Student</option>
                    <option value="coordinator">Coordinator</option>
                </select>
            </div>
            <!-- Username -->
            <div class="mb-3 input-group">
                <span class="icon-input"><i class="bi bi-person-fill"></i></span>
                <input type="text" name="username" class="form-control ps-5" placeholder="Username" required>
            </div>
            <!-- Password -->
            <div class="mb-3 input-group">
                <span class="icon-input"><i class="bi bi-lock-fill"></i></span>
                <input type="password" name="password" class="form-control ps-5" placeholder="Password" required>
            </div>
            <!-- Submit -->
            <button type="submit" class="btn btn-custom w-100">Login</button>
        </form>
        <p class="text-center mt-3">
            <small>Forgot your password? <a href="#" class="text-info">Reset</a></small>
        </p>
    </div>
</div>

<script>
// Handle SweetAlert notifications
<?php if (!empty($response)): 
    [$status, $message] = explode("|", $response);
    if ($status === "success"): ?>
        Swal.fire({
            title: 'Login Successful!',
            text: 'Redirecting to dashboard...',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            window.location.href = "<?= $message ?>";
        });
    <?php else: ?>
        Swal.fire({
            title: 'Login Failed!',
            text: '<?= htmlspecialchars($message) ?>',
            icon: 'error',
            confirmButtonColor: '#00d4ff'
        });
    <?php endif; ?>
<?php endif; ?>
</script>

</body>
</html>
