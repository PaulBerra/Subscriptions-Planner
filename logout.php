<?php
require_once 'session.php';

secure_session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit;
