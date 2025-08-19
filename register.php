<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Pelayanan Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <style>
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .bg-gradient {
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
        }

        .google-btn {
            transition: all 0.3s ease;
        }

        .google-btn:hover {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transform: translateY(-1px);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #6b7280;
            margin: 20px 0;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #e5e7eb;
        }

        .divider::before {
            margin-right: 10px;
        }

        .divider::after {
            margin-left: 10px;
        }

        #g_id_onload {
            position: absolute;
            left: -9999px;
        }

        .password-strength {
            height: 4px;
            margin-top: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .password-hint {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 4px;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans" style="opacity: 0; transition: opacity 0.3s ease;">
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center">
                <div class="w-16 h-16 rounded-full bg-gradient flex items-center justify-center shadow-lg">
                    <i class="fas fa-user-plus text-white text-2xl"></i>
                </div>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Buat Akun Baru
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Daftar untuk mengakses layanan desa kami
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md fade-in">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <form class="space-y-6" id="registerForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">
                                Nama Depan
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input id="first_name" name="first_name" type="text" required
                                    class="py-2 pl-10 block w-full border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Nama depan">
                            </div>
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">
                                Nama Belakang
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input id="last_name" name="last_name" type="text"
                                    class="py-2 pl-3 block w-full border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Nama belakang">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Alamat Email
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input id="email" name="email" type="email" required
                                class="py-2 pl-10 block w-full border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="email@contoh.com">
                        </div>
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">
                            Nomor Telepon
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-phone text-gray-400"></i>
                            </div>
                            <input id="phone" name="phone" type="tel" required
                                class="py-2 pl-10 block w-full border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="081234567890">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Kata Sandi
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input id="password" name="password" type="password" required
                                class="py-2 pl-10 block w-full border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="••••••••" oninput="checkPasswordStrength(this.value)">
                            <div id="password-strength" class="password-strength bg-gray-200"></div>
                            <div id="password-hint" class="password-hint">
                                Gunakan minimal 8 karakter dengan kombinasi huruf dan angka
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                            Konfirmasi Kata Sandi
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input id="confirm_password" name="confirm_password" type="password" required
                                class="py-2 pl-10 block w-full border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="••••••••">
                        </div>
                        <div id="password-match" class="password-hint hidden text-green-600">
                            <i class="fas fa-check-circle mr-1"></i> Kata sandi cocok
                        </div>
                        <div id="password-mismatch" class="password-hint hidden text-red-600">
                            <i class="fas fa-times-circle mr-1"></i> Kata sandi tidak cocok
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input id="terms" name="terms" type="checkbox" required
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="terms" class="ml-2 block text-sm text-gray-700">
                            Saya menyetujui <a href="#" class="text-blue-600 hover:text-blue-500">Syarat & Ketentuan</a> dan <a href="#" class="text-blue-600 hover:text-blue-500">Kebijakan Privasi</a>
                        </label>
                    </div>

                    <div>
                        <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-user-plus mr-2"></i> Daftar Sekarang
                        </button>
                    </div>
                </form>

                <div class="divider">
                    <span class="text-sm">ATAU</span>
                </div>

                <!-- Google Sign-Up Button -->
                <div class="mt-6">
                    <div id="g_id_onload"
                        data-client_id="YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com"
                        data-context="signup"
                        data-ux_mode="popup"
                        data-callback="handleGoogleSignUp"
                        data-auto_prompt="false">
                    </div>

                    <div class="g_id_signin w-full"
                        data-type="standard"
                        data-shape="rectangular"
                        data-theme="outline"
                        data-text="signup_with"
                        data-size="large"
                        data-logo_alignment="left"
                        data-width="100%">
                    </div>
                </div>

                <!-- Alternative Google Button (fallback) -->
                <div class="mt-4 hidden">
                    <button onclick="signUpWithGoogle()" class="google-btn w-full flex items-center justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google logo" class="h-5 w-5 mr-2">
                        Daftar dengan Google
                    </button>
                </div>
            </div>

            <div class="mt-6 text-center text-sm text-gray-600">
                <p>Sudah punya akun?
                    <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">
                        Masuk disini
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Initialize Google Sign-Up
        function handleGoogleSignUp(response) {
            // Handle the Google Sign-Up response
            console.log("Google Sign-Up response:", response);

            // The ID token you need to send to your backend
            const id_token = response.credential;

            // Here you would typically send this token to your backend for verification
            // and then handle the registration process on your server
            sendTokenToBackend(id_token);
        }

        function sendTokenToBackend(token) {
            // In a real application, you would send this token to your backend
            // using fetch or XMLHttpRequest
            console.log("Sending token to backend:", token);

            // Example fetch request (you would need to implement the endpoint)
            /*
            fetch('/api/google-register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ token: token }),
            })
            .then(response => response.json())
            .then(data => {
                console.log("Backend response:", data);
                if (data.success) {
                    // Redirect to dashboard or home page
                    window.location.href = '/dashboard';
                } else {
                    alert("Registration failed: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred during registration");
            });
            */

            // For demo purposes, we'll just redirect after a short delay
            setTimeout(() => {
                alert("Registration with Google successful! Redirecting to dashboard...");
                window.location.href = "dashboard.php";
            }, 1000);
        }

        // Fallback function if Google's button doesn't work
        function signUpWithGoogle() {
            // This would trigger the Google Sign-Up flow programmatically
            console.log("Initiating Google Sign-Up");

            // In a real implementation, you might use Google's auth2 library directly
            // or redirect to your backend's Google OAuth endpoint
            alert("Google Sign-Up would be initiated here");
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('password-strength');
            const hint = document.getElementById('password-hint');

            // Reset
            strengthBar.style.width = '0%';
            strengthBar.className = 'password-strength bg-gray-200';

            if (password.length === 0) {
                hint.textContent = 'Gunakan minimal 8 karakter dengan kombinasi huruf dan angka';
                return;
            }

            if (password.length < 8) {
                strengthBar.style.width = '30%';
                strengthBar.classList.add('bg-red-500');
                hint.textContent = 'Kata sandi terlalu pendek (minimal 8 karakter)';
                return;
            }

            // Check for complexity
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            const hasSpecialChars = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            let strength = 0;
            if (hasUpperCase) strength++;
            if (hasLowerCase) strength++;
            if (hasNumbers) strength++;
            if (hasSpecialChars) strength++;

            if (strength <= 1) {
                strengthBar.style.width = '40%';
                strengthBar.classList.add('bg-yellow-500');
                hint.textContent = 'Kata sandi lemah, tambahkan huruf besar/angka/karakter khusus';
            } else if (strength <= 3) {
                strengthBar.style.width = '70%';
                strengthBar.classList.add('bg-blue-500');
                hint.textContent = 'Kata sandi cukup kuat';
            } else {
                strengthBar.style.width = '100%';
                strengthBar.classList.add('bg-green-500');
                hint.textContent = 'Kata sandi sangat kuat!';
            }
        }

        // Password match checker
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const match = document.getElementById('password-match');
            const mismatch = document.getElementById('password-mismatch');

            if (confirmPassword.length === 0) {
                match.classList.add('hidden');
                mismatch.classList.add('hidden');
                return;
            }

            if (password === confirmPassword) {
                match.classList.remove('hidden');
                mismatch.classList.add('hidden');
            } else {
                match.classList.add('hidden');
                mismatch.classList.remove('hidden');
            }
        });

        // Form submission handler
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate form
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                alert('Kata sandi tidak cocok!');
                return;
            }

            if (!document.getElementById('terms').checked) {
                alert('Anda harus menyetujui Syarat & Ketentuan');
                return;
            }

            // In a real application, you would send the form data to your backend
            console.log('Form submitted:', {
                firstName: document.getElementById('first_name').value,
                lastName: document.getElementById('last_name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                password: password
            });

            // For demo purposes, we'll just redirect after a short delay
            setTimeout(() => {
                alert('Pendaftaran berhasil! Anda akan diarahkan ke halaman login.');
                window.location.href = 'login.php';
            }, 1000);
        });

        // Add smooth transition when page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.body.style.opacity = '1';

            // Check if Google Sign-In is loaded
            if (typeof google === 'undefined') {
                console.log("Google Sign-In script not loaded, showing fallback button");
                document.querySelector('.g_id_signin').classList.add('hidden');
                document.querySelector('.hidden').classList.remove('hidden');
            }
        });
    </script>
</body>

</html>