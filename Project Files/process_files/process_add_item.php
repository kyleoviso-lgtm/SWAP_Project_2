<?php
// process_files/process_add_item.php

session_start();

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Required fields
    $requiredFields = ['name', 'price', 'description', 'availability', 'role_id'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || $_POST[$field] === '') {
            $_SESSION['action_status'] = [
                'type' => 'error',
                'message' => "The field '$field' is required."
            ];
            header("Location: ../add_item.php");
            exit;
        }
    }

    // Sanitize & assign
    $name = $_POST['name'];
    $price = (float) $_POST['price'];
    $description = $_POST['description'];
    $availability = (int)$_POST['availability'];
    $role_id = (int)$_POST['role_id'];

    // Validate numeric price
    if (!is_numeric($price) || $price < 0) {
        $_SESSION['action_status'] = [
            'type' => 'error',
            'message' => "Price must be a valid positive number."
        ];
        header("Location: ../add_item.php");
        exit;
    }

    // Prepare INSERT statement
    $stmt = $conn->prepare("
        INSERT INTO item (name, price, description, availability, role_id)
        VALUES (?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        $_SESSION['action_status'] = [
            'type' => 'error',
            'message' => "Database preparation error: " . $conn->error
        ];
        header("Location: ../add_item.php");
        exit;
    }

    $stmt->bind_param("sdsii", $name, $price, $description, $availability, $role_id);

    if ($stmt->execute()) {
        $_SESSION['action_status'] = [
            'type' => 'success',
            'message' => "Item added successfully!"
        ];
        header("Location: ../dashboard_product_management.php");
        exit;
    } else {
        $_SESSION['action_status'] = [
            'type' => 'error',
            'message' => "Insert failed: " . $stmt->error
        ];
        header("Location: ../add_item.php");
        exit;
    }

    $stmt->close();

} else {
    $_SESSION['action_status'] = [
        'type' => 'warning',
        'message' => "Invalid request method. Please submit the form properly."
    ];
    header("Location: ../add_item.php");
    exit;
}
