<?php
class Ctl_home {
    public function run(): void {
        Router::render('landing/home');
    }
}
