<?php
/** Generic app dashboard router by role */
class Ctl_dashboard {
    public function run(): void {
        $u = require_login();
        $map = ['ministry' => 'admin/dashboard', 'regional' => 'regional/dashboard', 'zonal' => 'zonal/dashboard', 'woreda' => 'woreda/dashboard', 'principal' => 'director/dashboard', 'teacher' => 'teacher/dashboard', 'student' => 'student/dashboard', 'parent' => 'parent/dashboard', 'it_admin' => 'it_admin/dashboard', 'bursar' => 'university/fees/manage', 'student_affairs' => 'university/clearance/manage', 'librarian' => 'library'];
        redirect($map[$u['role']] ?? 'auth/login');
    }
}
