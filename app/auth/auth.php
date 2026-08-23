<?php
class Ctl_forgot {
    public function run(): void {
        $sent = false;
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $email = strtolower(trim($_POST['email'] ?? ''));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Enter a valid email.';
            } else {
                $u = Database::one("SELECT * FROM users WHERE email = ?", [$email]);
                if ($u) {
                    $token = make_token(32);
                    Database::delete('password_resets', 'user_id = ?', [$u['id']]);
                    Database::insert('password_resets', [
                        'user_id' => $u['id'], 'token' => $token,
                        'expires_at' => date('Y-m-d H:i:s', time() + 3600),
                    ]);
                    $link = url('index.php?r=auth/reset&token=' . $token);
                    send_mail($email, 'Reset your Edunex password', "Hello " . $u['first_name'] . ",\n\nReset your password here (valid 1 hour):\n$link\n\n— Edunex");
                    log_activity('password_reset_requested', $email, $u['id']);
                }
                $sent = true; // always say sent (no user enumeration)
            }
        }
        Router::render('auth/forgot', ['title' => 'Reset password', 'sent' => $sent, 'error' => $error]);
    }
}

class Ctl_reset {
    public function run(): void {
        $token = $_GET['token'] ?? ($_POST['token'] ?? '');
        $row = Database::one("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()", [$token]);
        if (!$row) {
            flash('danger', 'This reset link is invalid or has expired.');
            redirect('auth/forgot');
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $p1 = $_POST['password'] ?? '';
            $p2 = $_POST['password2'] ?? '';
            [$ok, $msg] = Auth::password_ok($p1);
            if (!$ok) $error = $msg;
            elseif ($p1 !== $p2) $error = 'Passwords do not match.';
            else {
                Database::update('users', ['password_hash' => password_hash($p1, PASSWORD_DEFAULT)], 'id = ?', [$row['user_id']]);
                Database::update('password_resets', ['used' => 1], 'id = ?', [$row['id']]);
                Auth::bumpSessionVersion($row['user_id']);
                Database::delete('sessions', 'user_id = ?', [$row['user_id']]); // revoke all sessions
                log_activity('password_reset', 'Password reset completed', $row['user_id']);
                flash('success', 'Password updated. Please sign in.');
                redirect('auth/login');
            }
        }
        Router::render('auth/reset', ['title' => 'Set new password', 'token' => $token, 'error' => $error]);
    }
}

class Ctl_verify {
    public function run(): void {
        $email = $_SESSION['pending_verify_email'] ?? ($_GET['email'] ?? '');
        $uid = $_SESSION['pending_verify_uid'] ?? null;
        $error = '';
        $resend = false;
        if (!$email) {
            flash('info', 'Please sign in to verify your account.');
            redirect('auth/login');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (!empty($_POST['resend'])) {
                $u = Database::one("SELECT * FROM users WHERE email = ?", [$email]);
                if ($u) {
                    $otp = make_otp();
                    Database::insert('otp_codes', ['user_id' => $u['id'], 'code' => password_hash($otp, PASSWORD_DEFAULT), 'purpose' => 'verify', 'expires_at' => date('Y-m-d H:i:s', time() + OTP_EXPIRY_MINUTES * 60)]);
                    send_mail($email, 'Your new Edunex verification code', "Your new code: $otp");
                    flash('info', 'A new code was sent.');
                    $resend = true;
                }
            } else {
                $code = preg_replace('/\D/', '', $_POST['code'] ?? '');
                $u = Database::one("SELECT * FROM users WHERE email = ?", [$email]);
                if ($u) {
                    $otpRow = Database::one("SELECT * FROM otp_codes WHERE user_id = ? AND purpose = 'verify' AND used = 0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1", [$u['id']]);
                    if ($otpRow && password_verify($code, $otpRow['code'])) {
                        Database::update('otp_codes', ['used' => 1], 'id = ?', [$otpRow['id']]);
                        Database::update('users', ['verified' => 1, 'status' => 'active'], 'id = ?', [$u['id']]);
                        award_xp($u['id'], 50, 'Email verified');
                        unset($_SESSION['pending_verify_email'], $_SESSION['pending_verify_uid']);
                        flash('success', 'Email verified! Welcome to Edunex.');
                        Auth::login(Database::one("SELECT * FROM users WHERE id = ?", [$u['id']]), false);
                        redirect(dashboard_path());
                    } else {
                        $error = 'Invalid or expired code.';
                    }
                } else {
                    $error = 'Account not found.';
                }
            }
        }
        Router::render('auth/verify', ['title' => 'Verify email', 'email' => $email, 'error' => $error, 'resend' => $resend]);
    }
}

class Ctl_otp {
    public function run(): void {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $code = preg_replace('/\D/', '', $_POST['code'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $u = Database::one("SELECT * FROM users WHERE email = ?", [$email]);
            if (!$u) { $error = 'Account not found.'; }
            else {
                $row = Database::one("SELECT * FROM otp_codes WHERE user_id = ? AND used = 0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1", [$u['id']]);
                if ($row && password_verify($code, $row['code'])) {
                    Database::update('otp_codes', ['used' => 1], 'id = ?', [$row['id']]);
                    flash('success', 'Code verified.');
                    if ($row['purpose'] === 'verify' && !$u['verified']) {
                        Database::update('users', ['verified' => 1, 'status' => 'active'], 'id = ?', [$u['id']]);
                        Auth::login($u, false);
                        redirect(dashboard_path());
                    }
                    redirect('auth/login');
                } else {
                    $error = 'Invalid or expired code.';
                }
            }
        }
        Router::render('auth/otp', ['title' => 'Enter code', 'error' => $error, 'email' => $_GET['email'] ?? '']);
    }
}

class Ctl_twofa {
    public function run(): void {
        if (empty($_SESSION['twofa_pending'])) {
            flash('info', 'Please sign in first.');
            redirect('auth/login');
        }
        $error = '';
        $use_hena = Auth::henaEnabled($_SESSION['twofa_pending']);
        $new_file = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if ($use_hena) {
                $file = $_FILES['hena_file']['tmp_name'] ?? '';
                $name = $_FILES['hena_file']['name'] ?? '';
                if (!$file || empty($name)) {
                    $error = 'Insert your USB key and choose the .hena file to import.';
                } else {
[$ok, $msg, $replacement] = Auth::verify_twofa_hena(file_get_contents($file));
                    if ($ok) {
                        $_SESSION['hena_new_file'] = $replacement;
                        $_SESSION['hena_email'] = $u['email'];
                        log_activity('login', 'Signed in with USB 2FA key',
                            $_SESSION['twofa_pending']);
                        flash('info', 'Identity verified — your USB key was rotated. Download the refreshed .hena file and replace the one on your USB stick.');
                        redirect('auth/hena_ready');
                    }
                    $error = $msg;
                }
            } else {
                [$ok, $msg] = Auth::verify_twofa($_POST['code'] ?? '');
                if ($ok) {
                    log_activity('login', 'Signed in with 2FA');
                    redirect(dashboard_path());
                }
                $error = $msg;
            }
        }
        Router::render('auth/twofa', ['title' => 'Two-factor authentication', 'error' => $error, 'use_hena' => $use_hena]);
    }
}

class Ctl_hena_ready {
    public function run(): void {
        $u = require_login();
        if (empty($_SESSION['hena_new_file'])) {
            redirect(dashboard_path());
        }
        Router::render('auth/hena_ready', ['title' => 'Save your new key', 'email' => $u['email']]);
    }
}

class Ctl_logout {
    public function run(): void {
        $all = isset($_GET['all']);
        Auth::logout($all);
        flash('info', 'You have been signed out.');
        redirect('auth/login');
    }
}

class Ctl_sessions {
    public function run(): void {
        $u = require_login();
        $sessions = Database::all("SELECT s.*, u.email FROM sessions s JOIN users u ON u.id = s.user_id WHERE s.user_id = ? ORDER BY s.expires_at DESC", [$u['id']]);
        Router::render('auth/sessions', ['title' => 'Active sessions', 'sessions' => $sessions]);
    }
}
