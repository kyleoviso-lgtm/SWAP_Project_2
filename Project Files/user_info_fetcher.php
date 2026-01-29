<?php
// Default values (always defined)
$userName = 'Guest';
$userRole = 'Visitor';
$userInitials = 'G';

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    $sql = "
        SELECT u.username, r.RoleName
        FROM user u
        JOIN roles r ON u.role_ID = r.RID
        WHERE u.UID = ?
        LIMIT 1
    ";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $stmt->bind_result($username, $roleName);

        if ($stmt->fetch()) {
            $userName = $username;
            $userRole = $roleName;

            $parts = explode(' ', $userName);
            $userInitials = strtoupper(
                substr($parts[0], 0, 1) .
                (isset($parts[1]) ? substr($parts[1], 0, 1) : '')
            );
        }

        $stmt->close();
    }
}
