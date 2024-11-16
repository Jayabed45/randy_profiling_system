<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'Admin') {
    header("Location: home.php");
    exit();
}

$firstName = $_SESSION['first_name'];
$lastName = $_SESSION['last_name'];

// Database connection
$host = "127.0.0.1";
$dbname = "randydb";
$username = "root"; // replace with your database username
$password = "";     // replace with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Get user details for editing
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Fetch user details from the database
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Could not retrieve user details: " . $e->getMessage());
    }
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $usertype = $_POST['usertype'];
    $profile_picture = $user['profile_picture']; // Keep the existing picture if no new upload

    // Check if a new profile picture was uploaded
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "uploads/profile_pics/";
        $fileName = basename($_FILES['profile_picture']['name']);
        $targetFilePath = $targetDir . $fileName;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
            // Update the profile picture path in the database
            $profile_picture = $targetFilePath;
        }
    }

    // Update the user's data in the database
    try {
        $sql = "UPDATE users SET email = :email, first_name = :first_name, last_name = :last_name, age = :age, gender = :gender, usertype = :usertype, profile_picture = :profile_picture WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'age' => $age,
            'gender' => $gender,
            'usertype' => $usertype,
            'profile_picture' => $profile_picture,
            'id' => $userId
        ]);

        // Redirect to the admin dashboard after updating
        header("Location: admin_dashboard.php");
        exit();
    } catch (PDOException $e) {
        die("Could not update user: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f9fc;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #4a90e2;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
        }
        .form-container {
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="email"], input[type="text"], input[type="number"], select, input[type="file"] {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #3b7bd5;
        }
        img {
            border-radius: 50%;
            display: block;
            margin: 0 auto 20px;
            max-width: 100px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Edit User</h2>
        </div>
        <div class="form-container">
            <form action="edit_user.php?id=<?php echo $user['id']; ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>First Name:</label>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Last Name:</label>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Age:</label>
                    <input type="number" name="age" value="<?php echo htmlspecialchars($user['age']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Gender:</label>
                    <select name="gender" required>
                        <option value="Male" <?php echo $user['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $user['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo $user['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>User Type:</label>
                    <select name="usertype" required>
                        <option value="Admin" <?php echo $user['usertype'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="Student" <?php echo $user['usertype'] === 'Student' ? 'selected' : ''; ?>>Student</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Profile Picture:</label>
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
                    <?php endif; ?>
                    <input type="file" name="profile_picture">
                </div>
                <button type="submit">Update User</button>
            </form>
        </div>
    </div>
</body>
</html>
