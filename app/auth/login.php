<?php
class Ctl_login {
    public function run(): void {
        if (me()) redirect('dashboard');
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $id = trim($_POST['identifier'] ?? '');
            $pass = $_POST['password'] ?? '';
            if ($id === '' || $pass === '') {
                $error = 'Please enter your student ID, email or phone, and your password.';
            } else {
                $key = 'login:' . ($_SERVER['REMOTE_ADDR'] ?? '') . ':' . $id;
                if (rate_limit_blocked($key, MAX_LOGIN_ATTEMPTS, 900)) {
                    $error = 'Too many failed attempts. Please wait 5 minutes.';
                } else {
                    [$ok, $msg] = Auth::attempt($id, $pass, !empty($_POST['remember']));
                    if ($ok && $msg === '2FA') {
                        redirect('auth/2fa');
                    } elseif ($ok) {
                        log_activity('login', 'Signed in');
                        if (AiRouter::available()) AiRouter::warmAsync();
                        redirect(dashboard_path());
                    } else {
                        rate_limit($key, MAX_LOGIN_ATTEMPTS, 900);
                        $error = $msg;
                    }
                }
            }
        }
        Router::render('auth/login', ['title' => 'Sign in', 'error' => $error, 'id' => $_POST['identifier'] ?? '']);
    }
}
