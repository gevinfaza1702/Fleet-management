<?php
session_start();
include 'config/db.php';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username dan Password wajib diisi!";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = $username;
                $_SESSION['login_success'] = true;

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Username tidak ditemukan!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login - Fleet Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 400px;
            padding: 30px;
            animation: fadeIn 1s ease;
        }
        .login-card h3 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .login-card .form-control {
            border-radius: 20px;
        }
        .login-card .btn-primary {
            border-radius: 20px;
            transition: background-color 0.3s ease;
        }
        .login-card .btn-primary:hover {
            background-color: #0056b3;
        }
        .login-icon {
            font-size: 60px;
            color: #007bff;
            text-align: center;
            margin-bottom: 20px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .footer-text {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #888;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-icon">
        <i class="fas fa-truck"></i>
    </div>
    <h3>Fleet Management Login</h3>
    <form method="POST" novalidate>
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required placeholder="Masukkan username">
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required placeholder="Masukkan password">
        </div>
        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
    </form>
    <div class="footer-text">
        © <?php echo date("Y"); ?> Fleet Management System
    </div>
</div>

<!-- SweetAlert2 untuk Notifikasi -->
<?php if (isset($error)): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Login Gagal!',
        text: '<?php echo $error; ?>',
    });
</script>
<?php endif; ?>

</body>
</html>
