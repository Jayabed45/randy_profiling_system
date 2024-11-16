<?php
session_start();

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

// Registration functionality
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    $usertype = $_POST['usertype']; // Get user type from the dropdown
    $age = trim($_POST['age']); // Get age from the input field
    $gender = $_POST['gender']; // Get gender from the dropdown

    // Validate inputs
    if (!empty($firstName) && !empty($lastName) && !empty($email) && !empty($password) && !empty($confirmPassword) && !empty($usertype) && !empty($age) && !empty($gender)) {
        if (!is_numeric($age) || $age < 0) {
            $error = "Please enter a valid age.";
        } elseif ($password !== $confirmPassword) {
            $error = "Passwords do not match.";
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                $error = "Email already exists.";
            } else {
                // Profile Picture Upload Handling
                $profilePicture = null;
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    $fileType = $_FILES['profile_picture']['type'];
                    
                    if (in_array($fileType, $allowedTypes)) {
                        // Define file upload directory
                        $uploadDir = 'uploads/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }

                        // Get file extension
                        $fileExtension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                        $profilePicture = $uploadDir . uniqid() . '.' . $fileExtension;

                        // Move the uploaded file to the directory
                        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profilePicture);
                    } else {
                        $error = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
                    }
                }

                // Insert new user into the database with profile picture path, age, and gender
                $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, usertype, profile_picture, age, gender) 
                                       VALUES (:first_name, :last_name, :email, :password, :usertype, :profile_picture, :age, :gender)");
                $stmt->execute([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'password' => $password, // Store plain password
                    'usertype' => $usertype,
                    'profile_picture' => $profilePicture,
                    'age' => $age,
                    'gender' => $gender  // Store the gender
                ]);

                // Automatically log the user in after registration
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['first_name'] = $firstName;
                $_SESSION['last_name'] = $lastName;
                $_SESSION['usertype'] = $usertype;

                // Redirect to student dashboard or admin dashboard
                if ($usertype == 'Admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: student_dashboard.php");
                }
                exit;
            }
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link rel="stylesheet" href="createacc.css">
</head>
<body>
    <div class="wrapper signup">
        <div class="container">
            <div class="col-left">
                <div class="signup-text">
                </div>
            </div>
            <div class="col-right">
                <div class="signup-form">
                    <h2>Register</h2>
                    <?php if (!empty($error)) echo "<p style='color: red;'>$error</p>"; ?>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" required><br>
                        
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" required><br>
                        
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required><br>
                        
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required><br>
                        
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required><br>
                        
                        <label for="usertype">User Type:</label>
                        <select id="usertype" name="usertype" required>
                            <option value="Student">Student</option>
                        </select><br>
                        
                        <label for="age">Age:</label>
                        <input type="number" id="age" name="age" required min="1"><br>
                        
                        <label for="gender">Gender:</label>
                        <select id="gender" name="gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select><br>
                        
                        <label for="profile_picture">Profile Picture:</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*"><br>
                        
                        <button type="submit" name="register">Create Account</button>
                        <div class="col-left">
                <div class="signup-text">
                </div>
                        <a href="home.php" class="btn">Already Have an Account? Log In</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
