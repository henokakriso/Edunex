<?php
class Ctl_register {
    public function run(): void {
        if (me()) redirect('dashboard');
        $errors = [];
        $schools = Database::all("SELECT * FROM schools WHERE status = 'active' ORDER BY name");
        $groups = Database::all("SELECT g.*, s.name AS school_name FROM student_groups g JOIN schools s ON s.id = g.school_id ORDER BY s.name, g.name");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $first = trim($_POST['first_name'] ?? '');
            $last = trim($_POST['last_name'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));
            $phone = trim($_POST['phone'] ?? '');
            $pass = $_POST['password'] ?? '';
            $pass2 = $_POST['password2'] ?? '';
            $schoolId = (int)($_POST['school_id'] ?? 0);
            $group = (int)($_POST['group_id'] ?? 0);
            $referral = strtoupper(trim($_POST['referral'] ?? '')); // transfer referral code
            $studentId = trim($_POST['student_id'] ?? '');          // special ID for transfers

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
            if (Database::one("SELECT id FROM users WHERE email = ?", [$email])) $errors[] = 'An account with this email already exists.';
            if ($phone && Database::one("SELECT id FROM users WHERE phone = ?", [$phone])) $errors[] = 'An account with this phone number already exists.';
            [$ok, $msg] = Auth::password_ok($pass);
            if (!$ok) $errors[] = $msg;
            if ($pass !== $pass2) $errors[] = 'Passwords do not match.';
            if (!$schoolId) $errors[] = 'Select your school.';
            if (setting('registration_open') !== '1') $errors[] = 'Registration is currently closed.';

            // Transfer referral redemption: new student joins with a referral from another school
            if (!$errors && $referral !== '') {
                $code = Database::one("SELECT * FROM transfer_codes WHERE code = ? AND used = 0 AND (expires_at IS NULL OR expires_at > NOW())", [$referral]);
                if (!$code) {
                    $errors[] = 'Invalid or expired referral code. Remove it to register normally.';
                }
            }

            if (!$errors) {
                $uid = Database::transaction(function () use ($first, $last, $email, $phone, $pass, $schoolId, $group, $referral, $studentId) {
                    $sid = $studentId !== '' ? $studentId : generate_student_id($schoolId);
                    if (Database::one("SELECT id FROM users WHERE student_id = ?", [$sid])) {
                        $sid = generate_student_id($schoolId);
                    }
                    $uid = Database::insert('users', [
                        'school_id' => $schoolId, 'role' => 'student',
                        'first_name' => $first, 'last_name' => $last,
                        'email' => $email, 'phone' => $phone,
                        'student_id' => $sid, 'group_id' => $group ?: null,
                        'password_hash' => password_hash($pass, PASSWORD_DEFAULT),
                        'status' => 'pending', 'verified' => 0,
                    ]);
                    // referral consumption
                    if ($referral !== '') {
                        $code = Database::one("SELECT * FROM transfer_codes WHERE code = ? AND used = 0", [$referral]);
                        if ($code) {
                            $sourceStudentId = $code['student_id'] ?? null;
                            Database::update('transfer_codes', ['used' => 1, 'student_id' => $uid], 'id = ?', [$code['id']]);
                            Database::insert('transfer_requests', [
                                'student_id' => $uid, 'source_student_id' => $sourceStudentId, 'from_school_id' => $code['school_id'],
                                'to_school_id' => $schoolId, 'referral_code' => $code['code'],
                                'status' => 'pending', 'reason' => 'Registered via referral code',
                            ]);
                        }
                    }
                    return $uid;
                });
                // No email OTP — the homeroom teacher verifies the account within 24h.
                flash('info', 'Account created! Your homeroom teacher will verify it within 24 hours. You can log in after approval.');
                redirect('auth/login');
            }
        }
        Router::render('auth/register', [
            'title' => 'Create account', 'errors' => $errors, 'schools' => $schools, 'groups' => $groups,
        ]);
    }
}
