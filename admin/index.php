<?php
/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Admin Login
   ═══════════════════════════════════════════════════════════════ */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/analytics.php';
require_once __DIR__ . '/_auth.php';

if (admin_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$hint = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = (string)($_POST['password'] ?? '');
    $hint = 'Default credentials — Username: <strong>IUCEducation</strong> · Password: <strong>Iuc@12345</strong>';
    if ($u === '' || $p === '') {
        $error = 'Please enter both username and password.';
    } elseif (admin_verify($u, $p)) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $u;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

if (empty($error) && empty($hint)) {
    $hint = 'Default credentials — Username: <strong>IUCEducation</strong> · Password: <strong>Iuc@12345</strong>';
}

$pageTitle = 'Admin Login – IUC Edu Analytics';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?= $pageTitle ?></title>
<meta name="robots" content="noindex, nofollow" />
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/iuc_pyramid_logo.png" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
<link rel="stylesheet" href="admin.css?v=1" />
</head>
<body class="login-body">

<div class="login-shell">
    <div class="login-card">

        <div class="login-card-top">
            <div class="login-logo"><img src="<?= BASE_URL ?>/assets/images/iuc_pyramid_logo.png" alt="IUC Edu" /></div>
            <h1>IUC <span>Analytics</span></h1>
            <p>Digital Marketing Dashboard</p>
        </div>

        <div class="login-card-body">
            <h2 class="login-title"><i class="bi bi-person-lock"></i> Admin Login</h2>
            <p class="login-sub">Sign in to access the analytics dashboard.</p>

            <?php if ($error): ?>
            <div class="login-alert login-alert-error" role="alert"><i class="bi bi-exclamation-triangle-fill"></i> <span><?= htmlspecialchars($error) ?></span></div>
            <?php endif; ?>

            <form method="POST" action="" autocomplete="off" id="loginForm">
                <div class="field">
                    <label class="field-label" for="loginUsername">Username</label>
                    <div class="input-icon">
                        <i class="bi bi-person"></i>
                        <input type="text" id="loginUsername" name="username" placeholder="Enter username" value="<?= htmlspecialchars($u ?? '') ?>" required autofocus autocomplete="username" />
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="loginPassword">Password</label>
                    <div class="input-icon">
                        <i class="bi bi-key"></i>
                        <input type="password" id="loginPassword" name="password" placeholder="Enter password" required autocomplete="current-password" />
                        <button type="button" class="pass-toggle" id="passToggle" aria-label="Show password"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <button type="submit" class="login-btn" id="loginBtn">
                    <i class="bi bi-box-arrow-in-right"></i> <span>Login to Dashboard</span>
                </button>
            </form>

            <div class="login-hint"><i class="bi bi-info-circle"></i> <span><?= $hint ?></span></div>
        </div>

        <div class="login-card-foot">
            <a href="<?= BASE_URL ?>/" class="login-back"><i class="bi bi-arrow-left"></i> Back to Website</a>
            <span class="login-secure"><i class="bi bi-lock-fill"></i> Secure access</span>
        </div>

    </div>
</div>

<div class="login-page-foot">&copy; <?= date('Y') ?> IUC Edu · Admin Analytics Dashboard</div>

<script>
(function () {
    'use strict';
    var toggle = document.getElementById('passToggle');
    var pass = document.getElementById('loginPassword');
    if (toggle && pass) {
        toggle.addEventListener('click', function () {
            var show = pass.type === 'password';
            pass.type = show ? 'text' : 'password';
            toggle.innerHTML = '<i class="bi ' + (show ? 'bi-eye-slash' : 'bi-eye') + '"></i>';
            toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    }
    var form = document.getElementById('loginForm');
    var btn = document.getElementById('loginBtn');
    if (form && btn) {
        form.addEventListener('submit', function () {
            btn.disabled = true;
            btn.classList.add('is-loading');
            btn.querySelector('span').textContent = 'Signing in…';
        });
    }
})();
</script>

</body>
</html>
