<?php
// process_files/process_delete_item.php

session_start();

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['IID']) || $_POST['IID'] === '') {
        $_SESSION['action_status'] = [
            'type' => 'error',
            'message' => "No Item ID provided for deletion."
        ];
        header("Location: ../dashboard_product_management.php");
        exit;
    }

    $IID = $_POST['IID'];

    // Check if the item exists first
    $stmtCheck = $conn->prepare("SELECT IID FROM item WHERE IID = ?");
    $stmtCheck->bind_param("s", $IID);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['action_status'] = [
            'type' => 'warning',
            'message' => "Item with ID $IID does not exist."
        ];
        header("Location: ../dashboard_product_management.php");
        exit;
    }
    $stmtCheck->close();

    // Attempt deletion
    $stmt = $conn->prepare("DELETE FROM item WHERE IID = ?");
    if (!$stmt) {
        $_SESSION['action_status'] = [
            'type' => 'error',
            'message' => "Database preparation error: " . $conn->error
        ];
        header("Location: ../dashboard_product_management.php");
        exit;
    }

    $stmt->bind_param("s", $IID);

    if ($stmt->execute()) {
        $_SESSION['action_status'] = [
            'type' => 'success',
            'message' => "Item with ID $IID deleted successfully."
        ];
    } else {
        $errorMsg = $stmt->error;

        // Detect foreign key constraint error
        if (strpos($errorMsg, 'foreign key') !== false) {
            $errorMsg = "Cannot delete item $IID because it is referenced by existing orders.";
        }

        $_SESSION['action_status'] = [
            'type' => 'error',
            'message' => "Deletion failed: $errorMsg"
        ];
    }

    $stmt->close();
    header("Location: ../dashboard_product_management.php");
    exit;

} else {
    $_SESSION['action_status'] = [
        'type' => 'warning',
        'message' => "Invalid request method. Please submit the deletion form properly."
    ];
    header("Location: ../dashboard_product_management.php");
    exit;
}
