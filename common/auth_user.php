<?php
require_once __DIR__ . '/functions.php';

if (!isStudentLoggedIn() || isAdminLoggedIn()) {
    redirect('../user/login.php');
}
