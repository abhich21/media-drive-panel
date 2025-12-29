<?php
/**
 * MDM Login Page
 * Universal login for all roles
 */

session_start();

// Get the base path (folder where this script lives)
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'] ?? 'client';
    $redirects = [
        'superadmin' => $basePath . '/pages/admin/dashboard.php',
        'client' => $basePath . '/pages/client/dashboard.php',
        'promoter' => $basePath . '/pages/promoter/dashboard.php',
        'cleaning_staff' => $basePath . '/pages/cleaning/dashboard.php',
    ];
    header('Location: ' . ($redirects[$role] ?? $basePath . '/'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MDM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= $basePath ?>/assets/css/styles.css" rel="stylesheet">
</head>

<body class="bg-mdm-bg font-sans antialiased min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo / Branding -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-mdm-sidebar rounded-2xl mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M8 17h.01M16 17h.01M3 11l1.5-5A2 2 0 016.4 4.5h11.2a2 2 0 011.9 1.5L21 11M3 11v6a1 1 0 001 1h1m16-7v6a1 1 0 01-1 1h-1M3 11h18" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-mdm-text">Media Drive Management</h1>
            <p class="text-mdm-text/60 mt-1">Sign in to your account</p>
        </div>

        <!-- Login Card -->
        <div class="mdm-card">
            <form id="loginForm" method="POST" action="<?= $basePath ?>/api/auth.php">
                <input type="hidden" name="action" value="login">

                <!-- Email -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-mdm-text mb-2">Email Address</label>
                    <input type="email" name="email" required
                        class="w-full px-4 py-3 rounded-xl border border-mdm-tag bg-white focus:border-mdm-sidebar focus:outline-none transition-colors"
                        placeholder="you@example.com">
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-mdm-text mb-2">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="passwordInput" required
                            class="w-full px-4 py-3 rounded-xl border border-mdm-tag bg-white focus:border-mdm-sidebar focus:outline-none transition-colors pr-12"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-mdm-text/50 hover:text-mdm-text">
                            <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Error Message -->
                <div id="errorMessage" class="hidden mb-4 p-3 bg-red-100 text-red-700 rounded-xl text-sm"></div>

                <!-- Submit -->
                <button type="submit"
                    class="w-full py-3 bg-mdm-sidebar text-white font-medium rounded-xl hover:bg-black transition-colors flex items-center justify-center gap-2">
                    <span>Sign In</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>
        </div>

        <!-- Demo Credentials -->
        <div class="mt-6 p-4 bg-mdm-card rounded-xl text-center">
            <p class="text-sm text-mdm-text/60 mb-2">Demo Credentials</p>
            <div class="flex flex-wrap justify-center gap-2">
                <button onclick="fillDemo('admin@cloudplay.com', 'admin123')" class="mdm-tag text-xs">Admin</button>
                <button onclick="fillDemo('client@demo.com', 'client123')" class="mdm-tag text-xs">Client</button>
                <button onclick="fillDemo('promoter@demo.com', 'promoter123')" class="mdm-tag text-xs">Promoter</button>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-sm text-mdm-text/40 mt-8">
            &copy; <?= date('Y') ?> CloudPlay. All rights reserved.
        </p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        function fillDemo(email, password) {
            document.querySelector('input[name="email"]').value = email;
            document.getElementById('passwordInput').value = password;
        }

        document.getElementById('loginForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const errorEl = document.getElementById('errorMessage');
            errorEl.classList.add('hidden');

            try {
                const response = await fetch('<?= $basePath ?>/api/auth.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    window.location = data.data.redirect;
                } else {
                    errorEl.textContent = data.message;
                    errorEl.classList.remove('hidden');
                }
            } catch (error) {
                errorEl.textContent = 'An error occurred. Please try again.';
                errorEl.classList.remove('hidden');
            }
        });
    </script>
</body>

</html>