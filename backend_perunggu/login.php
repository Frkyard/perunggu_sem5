<?php
define('PERUNGGU_AUTH_PAGE', true);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/connect.php';

if (admin_session_is_valid()) {
    header('Location: dist/index.php');
    exit;
}

$admin_count_result = mysqli_query($conn, 'SELECT COUNT(*) AS admin_count FROM admins');
if (!$admin_count_result) {
    http_response_code(500);
    exit('Tidak dapat memeriksa akun administrator.');
}
$admin_count = (int)mysqli_fetch_assoc($admin_count_result)['admin_count'];
$is_first_admin = $admin_count === 0;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_admin_csrf();

    if ($is_first_admin && ($_POST['action'] ?? '') === 'setup') {
        $username = trim((string)($_POST['username'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if (strlen($username) < 3 || strlen($username) > 50) {
            $error_message = 'Username harus berisi 3 sampai 50 karakter.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $error_message = 'Alamat email tidak valid.';
        } elseif (strlen($password) < 12) {
            $error_message = 'Password administrator minimal 12 karakter.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, 'INSERT INTO admins (username, email, password) VALUES (?, ?, ?)');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'sss', $username, $email, $password_hash);
                try {
                    $created = mysqli_stmt_execute($stmt);
                } catch (mysqli_sql_exception $exception) {
                    error_log('Pembuatan admin pertama gagal: ' . $exception->getMessage());
                    $created = false;
                }
                if ($created) {
                    $_SESSION['admin_id'] = mysqli_insert_id($conn);
                    $_SESSION['admin_username'] = $username;
                    session_regenerate_id(true);
                    unset($_SESSION['admin_csrf_token']);
                    header('Location: dist/index.php');
                    exit;
                }
                $error_message = 'Akun administrator tidak dapat dibuat. Pastikan username dan email belum digunakan.';
                mysqli_stmt_close($stmt);
            } else {
                http_response_code(500);
                exit('Tidak dapat menyiapkan pembuatan akun administrator.');
            }
        }
    } elseif (!$is_first_admin && ($_POST['action'] ?? '') === 'login') {
        $identity = trim((string)($_POST['identity'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $stmt = mysqli_prepare($conn, 'SELECT id, username, password FROM admins WHERE username = ? OR email = ? LIMIT 1');
        if (!$stmt) {
            http_response_code(500);
            exit('Tidak dapat menyiapkan autentikasi administrator.');
        }

        mysqli_stmt_bind_param($stmt, 'ss', $identity, $identity);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            unset($_SESSION['admin_csrf_token']);
            header('Location: dist/index.php');
            exit;
        }

        $error_message = 'Username/email atau password tidak sesuai.';
    }
}

$csrf_token = admin_csrf_token();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $is_first_admin ? 'Buat Admin' : 'Login Admin' ?> — PERUNGGU</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: #101014; color: #f5f5f5; font: 16px Arial, sans-serif; }
        main { width: min(100%, 420px); padding: 32px; border: 1px solid #39343a; border-radius: 14px; background: #1b1a1f; }
        h1 { margin-top: 0; color: #d4a373; }
        label { display: block; margin: 16px 0 6px; }
        input { box-sizing: border-box; width: 100%; padding: 12px; border: 1px solid #514a52; border-radius: 6px; background: #111; color: #fff; }
        button { width: 100%; margin-top: 22px; padding: 12px; border: 0; border-radius: 6px; background: #d4a373; color: #171412; font-weight: bold; cursor: pointer; }
        .password-field { display: flex; align-items: stretch; gap: 8px; }
        .password-field input { flex: 1; min-width: 0; }
        .password-toggle { width: auto; margin: 0; padding: 0 12px; border: 1px solid #514a52; background: #2a282e; color: #f5f5f5; white-space: nowrap; }
        .password-toggle:hover { background: #39343a; }
        .password-toggle:focus-visible { outline: 2px solid #d4a373; outline-offset: 2px; }
        .error { padding: 10px; border-radius: 6px; background: #512e32; color: #ffd9dc; }
        .hint { color: #c4bdc4; line-height: 1.5; }
    </style>
</head>
<body>
<main>
    <h1><?= $is_first_admin ? 'Buat Admin Pertama' : 'Dashboard PERUNGGU' ?></h1>
    <p class="hint"><?= $is_first_admin ? 'Belum ada akun admin. Buat akun administrator pertama untuk mengamankan dashboard.' : 'Masuk menggunakan username atau email administrator.' ?></p>
    <?php if ($error_message !== ''): ?>
        <p class="error" role="alert"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if (!$is_first_admin): ?>
        <p class="hint">Pendaftaran admin publik dinonaktifkan. Untuk menambah admin, masuk dengan akun yang sudah ada lalu pilih menu Akun Admin.</p>
    <?php endif; ?>
    <form method="post" action="login.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
        <?php if ($is_first_admin): ?>
            <input type="hidden" name="action" value="setup">
            <label for="username">Username</label>
            <input id="username" name="username" required minlength="3" maxlength="50" autocomplete="username">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" required maxlength="150" autocomplete="email">
            <label for="password">Password (minimal 12 karakter)</label>
            <div class="password-field">
                <input id="password" name="password" type="password" required minlength="12" autocomplete="new-password">
                <button class="password-toggle" id="passwordToggle" type="button" aria-controls="password" aria-pressed="false">Lihat</button>
            </div>
            <button type="submit">BUAT AKUN ADMIN</button>
        <?php else: ?>
            <input type="hidden" name="action" value="login">
            <label for="identity">Username atau email</label>
            <input id="identity" name="identity" required autocomplete="username">
            <label for="password">Password</label>
            <div class="password-field">
                <input id="password" name="password" type="password" required autocomplete="current-password">
                <button class="password-toggle" id="passwordToggle" type="button" aria-controls="password" aria-pressed="false">Lihat</button>
            </div>
            <button type="submit">MASUK</button>
        <?php endif; ?>
    </form>
</main>
<script>
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('passwordToggle');

    passwordToggle.addEventListener('click', () => {
        const showPassword = passwordInput.type === 'password';
        passwordInput.type = showPassword ? 'text' : 'password';
        passwordToggle.textContent = showPassword ? 'Sembunyikan' : 'Lihat';
        passwordToggle.setAttribute('aria-pressed', String(showPassword));
    });
</script>
</body>
</html>
