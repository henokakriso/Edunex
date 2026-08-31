<?php
/**
 * User settings: profile, password, security (2FA), notifications, preferences, sessions
 */

class Ctl_profile {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $tab = $_POST['tab_save'] ?? 'profile';
            if ($tab === 'theme') {
                Database::update('users', [
                    'theme' => $_POST['theme'] ?? 'dark',
                    'language' => $_POST['language'] ?? 'en',
                ], 'id = ?', [$uid]);
                Auth::refreshUser();
                flash('success', 'Preferences saved.');
                redirect('settings/profile&tab=theme');
            } elseif ($tab === 'fayda') {
                Database::update('users', [
                    'fayda_id' => trim($_POST['fayda_id'] ?? ''),
                    'national_id' => trim($_POST['national_id'] ?? ''),
                    'employee_id' => trim($_POST['employee_id'] ?? ''),
                    'birth_cert_number' => trim($_POST['birth_cert_number'] ?? ''),
                ], 'id = ?', [$uid]);
                Auth::refreshUser();
                flash('success', 'ID information saved.');
                redirect('settings/profile&tab=fayda');
            } else {
                $upd = [
                    'first_name' => trim($_POST['first_name'] ?? ''), 'last_name' => trim($_POST['last_name'] ?? ''),
                    'phone' => trim($_POST['phone'] ?? ''), 'bio' => trim($_POST['bio'] ?? ''),
                    'language' => $_POST['language'] ?? 'en', 'birth_date' => ($_POST['birth_date'] ?? '') ?: null,
                    'gender' => ($_POST['gender'] ?? '') ?: null,
                    'alt_phone' => trim($_POST['alt_phone'] ?? ''),
                    'address' => trim($_POST['address'] ?? ''),
                    'emergency_contact' => trim($_POST['emergency_contact'] ?? ''),
                ];
                if (!empty($_FILES['avatar']['name'])) {
                    $res = upload_file($_FILES['avatar'], 'avatars', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    if (!$res['error']) $upd['avatar'] = $res['path'];
                }
                Database::update('users', $upd, 'id = ?', [$uid]);
                Auth::refreshUser();
                flash('success', 'Profile updated.');
                redirect('settings/profile&tab=profile');
            }
        }
        $activeTab = $_GET['tab'] ?? 'profile';
        $twofa = Database::one("SELECT twofa_secret, twofa_enabled FROM users WHERE id = ?", [$uid]);
        $henaEnabled = Auth::henaEnabled($uid);
        $mode = $henaEnabled ? 'hena' : ((int)($twofa['twofa_enabled'] ?? 0) ? 'totp' : 'off');
        $sessions = Database::all("SELECT * FROM sessions WHERE user_id = ? ORDER BY expires_at DESC", [$uid]);
        Router::render('app/settings/profile', [
            'title' => 'Settings',
            'activeTab' => $activeTab,
            'twofa' => $twofa,
            'mode' => $mode,
            'hena_new' => $_SESSION['hena_new_file'] ?? '',
            'sessions' => $sessions,
        ]);
    }
}

class Ctl_password {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $row = Database::one("SELECT password_hash FROM users WHERE id = ?", [$uid]);
            $cur = (string)($_POST['current'] ?? '');
            if (!password_verify($cur, $row['password_hash'] ?? '')) { flash('danger', 'Current password is incorrect.'); redirect('settings/password'); }
            [$ok, $err] = Auth::password_ok((string)($_POST['new'] ?? ''));
            if (!$ok) { flash('danger', $err); redirect('settings/password'); }
            if ($_POST['new'] !== ($_POST['confirm'] ?? '')) { flash('danger', 'New passwords do not match.'); redirect('settings/password'); }
            Database::update('users', ['password_hash' => password_hash($_POST['new'], PASSWORD_DEFAULT)], 'id = ?', [$uid]);
            Auth::bumpSessionVersion($uid);
            Database::delete('sessions', 'user_id = ?', [$uid]);
            log_activity('password_change', 'Password changed', $uid);
            flash('success', 'Password changed. Remember-me tokens revoked.');
            redirect('settings/password');
        }
        Router::render('app/settings/password', ['title' => 'Change Password']);
    }
}

class Ctl_security {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $row = Database::one("SELECT twofa_secret, twofa_enabled FROM users WHERE id = ?", [$uid]);
            if (isset($_POST['enable_2fa'])) {
                $file = Auth::henaIssue($uid);
                $_SESSION['hena_new_file'] = $file;
                $_SESSION['hena_email'] = $u['email'];
                flash('success', 'USB 2-key enabled. Import and save the .hena file to your USB stick before signing out.');
                redirect('settings/security&show=hena');
            }
            if (isset($_POST['disable_2fa']) && Auth::henaEnabled($uid)) {
                Auth::henaReset($uid);
                flash('success', 'File-based two-factor authentication disabled.');
                redirect('settings/security');
            }
            if (isset($_POST['disable_2fa'])) {
                $code = trim($_POST['code'] ?? '');
                if (!Auth::totpVerify($row['twofa_secret'], $code)) { flash('danger', 'Invalid verification code.'); redirect('settings/security'); }
                Database::update('users', ['twofa_secret' => '', 'twofa_enabled' => 0], 'id = ?', [$uid]);
                flash('success', 'Two-factor authentication disabled.');
                redirect('settings/security');
            }
        }
        $row = Database::one("SELECT twofa_secret, twofa_enabled, verified FROM users WHERE id = ?", [$uid]);
        $mode = Auth::henaEnabled($uid) ? 'hena' : ((int)$row['twofa_enabled'] ? 'totp' : 'off');
        Router::render('app/settings/security', [
            'title' => 'Security',
            'row' => $row,
            'mode' => $mode,
            'hena_new' => $_SESSION['hena_new_file'] ?? '',
        ]);
    }
}

/** Serve the freshly generated .hena key file download. */
class Ctl_hena_download {
    public function run(): void {
        $u = require_login();
        $name = $_SESSION['hena_email'] ?? $u['email'];
        if (empty($_SESSION['hena_new_file'])) {
            flash('danger', 'No .hena key file is pending. Enable USB 2FA to generate one.');
            redirect('settings/security');
        }
        $name = preg_replace('/[^A-Za-z0-9_.@-]/', '_', $name);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $name . '.hena"');
        header('Content-Length: ' . strlen($_SESSION['hena_new_file']));
        echo $_SESSION['hena_new_file'];
        unset($_SESSION['hena_new_file'], $_SESSION['hena_email']);
        exit;
    }
}

class Ctl_notifications {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $types = ['assignment', 'exam', 'feedback', 'announcement', 'achievement', 'message', 'system'];
            $p = (array)($_POST['prefs'] ?? []);
            $privacy = [];
            foreach ($types as $t) $privacy['notify_' . $t] = in_array($t, $p, true) ? '1' : '0';
            Database::update('users', ['privacy' => json_encode($privacy)], 'id = ?', [$uid]);
            Auth::refreshUser();
            flash('success', 'Notification preferences saved.');
            redirect('settings/notifications');
        }
        $privacy = json_decode($u['privacy'] ?? '{}', true) ?: [];
        $types = [['assignment', 'Assignment updates'], ['exam', 'Exam reminders'], ['feedback', 'Feedback'], ['announcement', 'Announcements'], ['achievement', 'Achievements'], ['message', 'Messages'], ['system', 'System alerts']];
        Router::render('app/settings/notifications', ['title' => 'Notifications', 'types' => $types, 'privacy' => $privacy]);
    }
}

class Ctl_preferences {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            Database::update('users', ['theme' => $_POST['theme'] ?? 'dark', 'language' => $_POST['language'] ?? 'en'], 'id = ?', [$uid]);
            Auth::refreshUser();
            flash('success', 'Preferences saved.');
            redirect('settings/preferences');
        }
        Router::render('app/settings/preferences', ['title' => 'Preferences']);
    }
}

class Ctl_sessions {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['kill_all'])) {
                Database::delete('sessions', 'user_id = ?', [$uid]);
                flash('success', 'All sessions revoked.');
            } elseif (($sid = (int)($_POST['kill'] ?? 0))) {
                Database::delete('sessions', 'id = ? AND user_id = ?', [$sid, $uid]);
                flash('success', 'Session revoked.');
            }
            redirect('settings/sessions');
        }
        $sessions = Database::all("SELECT * FROM sessions WHERE user_id = ? ORDER BY id DESC", [$uid]);
        Router::render('app/settings/sessions', ['title' => 'Active Sessions', 'sessions' => $sessions]);
    }
}
