<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'Admin') {
    header("Location: home.php");
    exit();
}

$host = "127.0.0.1";
$dbname = "randydb";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        echo "User deleted successfully.";
    } catch (PDOException $e) {
        echo "Error deleting user: " . $e->getMessage();
    }
}

header("Location: admin_dashboard.php");
exit();
?>
