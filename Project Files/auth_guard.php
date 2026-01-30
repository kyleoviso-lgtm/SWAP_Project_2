<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login_page.php");
    exit;
}
