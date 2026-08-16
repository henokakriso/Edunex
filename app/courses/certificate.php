<?php
require_once __DIR__ . "/../certificates/certificates.php";

class Ctl_certificate {
    public function run(): void {
        redirect("certificates");
    }
}
