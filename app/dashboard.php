<?php
/** Generic app dashboard router by role */
class Ctl_dashboard {
    public function run(): void {
        $u = require_login();
        $map = ['sysadmin' => 'admin/dashboard', 'admin' => 'regional/dashboard', 'director' => 'director/dashboard', 'teacher' => 'teacher/dashboard', 'student' => 'student/dashboard', 'parent' => 'parent/dashboard'];
        redirect($map[$u['role']] ?? 'auth/login');
    }
}
