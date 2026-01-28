<?php
declare(strict_types=1);
require __DIR__ . '/../app/helpers.php';
session_start_safe();
unset($_SESSION['user']);
redirect(base_path() . '/admin/login.php');
