<?php
// login.php - Simplified Login page for Desa Profesional
session_start();
require_once './config/database.php';
require_once './config/functions.php';

// Basic login validation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $error = '';

    // Sample validation (replace with actual authentication logic)
    if ($username === "admin" && $password === "admin123") {
        $_SESSION['loggedin'] = true;
        header("Location: ./admin/dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Desa Profesional</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #1d4ed8; /* Modern blue */
            --primary-dark: #1e3a8a;
            --light: #f8fafc;
            --gray: #475569;
            --light-gray: #e2e8f0;
            --danger: #dc2626;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            max-width: 400px;
            width: 100%;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 40px 30px;
            position: relative;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo {
            width: 60px;
            margin-bottom: 20px;
        }

        .login-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 8px;
        }

        .login-subtitle {
            font-size: 0.9rem;
            color: var(--gray);
        }

        .form-label {
            font-weight: 500;
            color: var(--primary-dark);
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .form-control {
            height: 44px;
            border-radius: 8px;
            border: 1px solid var(--light-gray);
            padding: 10px 14px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
        }

        .btn-login {
            background: var(--primary);
            border: none;
            height: 44px;
            font-weight: 600;
            font-size: 0.9rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(29, 78, 216, 0.2);
        }

        .forgot-password {
            display: block;
            text-align: right;
            color: var(--gray);
            font-size: 0.85rem;
            text-decoration: none;
            margin-top: 10px;
            margin-bottom: 10px;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        .alert {
            border-radius: 8px;
            padding: 12px;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }

        .copyright {
            text-align: center;
            margin-top: 20px;
            color: var(--gray);
            font-size: 0.8rem;
        }

        .language-switcher {
            position: absolute;
            top: 15px;
            right: 15px;
        }

        .language-switcher .form-select {
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 0.8rem;
            border-color: var(--light-gray);
            background: white;
        }

        /* Password toggle */
        .password-input-group {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--gray);
            z-index: 5;
        }

        /* Back button */
        .back-button {
            position: absolute;
            top: 15px;
            left: 15px;
            color: var(--gray);
            font-size: 1.2rem;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .back-button:hover {
            color: var(--primary);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-container {
            animation: fadeIn 0.5s ease-out;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .login-container {
                padding: 30px 20px;
            }

            .login-title {
                font-size: 1.6rem;
            }

            .login-subtitle {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <a href="landingpage.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
        </a>
        
        <div class="language-switcher">
            <select class="form-select">
                <option selected>ID - Indonesia</option>
                <option value="1">EN - English</option>
            </select>
        </div>

        <div class="login-header">
            <img src="./assets/images/logo.png" alt="Desa Profesional Logo" class="login-logo">
            <h2 class="login-title">Masuk</h2>
            <p class="login-subtitle">Sistem Administrasi Desa Profesional</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <div><?php echo $error; ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="mb-3">
                <label for="username" class="form-label">Nama Pengguna</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Kata Sandi</label>
                <div class="password-input-group">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                </div>
            </div>

            <a href="#" class="forgot-password">Lupa password?</a>

            <button type="submit" class="btn btn-login btn-primary">
                <i class="fas fa-sign-in-alt me-2"></i>Masuk
            </button>
        </form>

        <div class="copyright">
            &copy; <?php echo date('Y'); ?> Desa Profesional<br>
            Hak Cipta Dilindungi
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple input focus animation
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.style.background = 'var(--light)';
            });
            
            input.addEventListener('blur', function() {
                this.style.background = 'white';
            });
        });

        // Password toggle functionality
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        
        togglePassword.addEventListener('click', function() {
            // Toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Toggle the icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>