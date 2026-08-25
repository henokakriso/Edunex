<?php
/** Generic app dashboard router by role */
class Ctl_dashboard {
    public function run(): void {
        $u = require_login();
        $map = ['ministry' => 'admin/dashboard', 'regional' => 'regional/dashboard', 'zonal' => 'zonal/dashboard', 'woreda' => 'woreda/dashboard', 'principal' => 'director/dashboard', 'teacher' => 'teacher/dashboard', 'student' => 'student/dashboard', 'parent' => 'parent/dashboard'];
        redirect($map[$u['role']] ?? 'auth/login');
    }
}
