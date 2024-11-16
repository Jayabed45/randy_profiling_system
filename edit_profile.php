<?php
session_start();

// Check if user is logged in and is a Student
if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'Student') {
    header("Location: home.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Database connection
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

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newFirstName = $_POST['first_name'];
    $newLastName = $_POST['last_name'];
    $newAge = $_POST['age'];
    $newGender = $_POST['gender'];
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    $updateProfilePicture = null;

    // Handle profile picture upload
    if (!empty($_FILES["profile_picture"]["name"])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $fileName = basename($_FILES["profile_picture"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFilePath)) {
            $updateProfilePicture = $targetFilePath;
        } else {
            $error = "Error uploading profile picture.";
        }
    }

    // Update profile details
    try {
        $updateQuery = "UPDATE users SET first_name = :first_name, last_name = :last_name, age = :age, gender = :gender";
        if ($updateProfilePicture) {
            $updateQuery .= ", profile_picture = :profile_picture";
        }

        // Handle password update
        if (!empty($currentPassword) && !empty($newPassword) && !empty($confirmPassword)) {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute(['id' => $userId]);
            $userPassword = $stmt->fetchColumn();

            if (password_verify($currentPassword, $userPassword)) {
                if ($newPassword === $confirmPassword) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $updateQuery .= ", password = :password";
                } else {
                    $error = "New passwords do not match.";
                }
            } else {
                $error = "Current password is incorrect.";
            }
        }

        $updateQuery .= " WHERE id = :id";

        $stmt = $pdo->prepare($updateQuery);
        $stmt->bindParam(':first_name', $newFirstName);
        $stmt->bindParam(':last_name', $newLastName);
        $stmt->bindParam(':age', $newAge);
        $stmt->bindParam(':gender', $newGender);
        if ($updateProfilePicture) {
            $stmt->bindParam(':profile_picture', $updateProfilePicture);
        }
        if (isset($hashedPassword)) {
            $stmt->bindParam(':password', $hashedPassword);
        }
        $stmt->bindParam(':id', $userId);
        $stmt->execute();

        $_SESSION['first_name'] = $newFirstName;
        $_SESSION['last_name'] = $newLastName;
        header("Location: profile.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error updating profile: " . $e->getMessage();
    }
}

// Fetch the user's current profile
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Could not retrieve profile: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 8px;
            color: #555;
        }
        input, select {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
        }
        .back-link a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }
        .back-link a:hover {
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Profile</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
            
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
            
            <label for="age">Age:</label>
            <input type="text" id="age" name="age" value="<?php echo htmlspecialchars($user['age']); ?>" required>
            
            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <option value="Male" <?php echo $user['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo $user['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                <option value="Other" <?php echo $user['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
            
            <label for="profile_picture">Profile Picture:</label>
            <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
            
            <h3>Change Password</h3>
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password">
            
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password">
            
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password">
            
            <button type="submit">Save Changes</button>
        </form>
        
        <div class="back-link">
            <a href="profile.php">Back to Profile</a>
        </div>
    </div>
</body>
</html>
