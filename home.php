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

// Login functionality
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Validate inputs
    if (!empty($email) && !empty($password)) {
        // Prepare SQL to prevent SQL injection
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
        $stmt->execute(['email' => $email, 'password' => $password]);
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Store user details in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['usertype'] = $user['usertype'];

            // Redirect based on user type
            if ($user['usertype'] == 'Admin') {
                header("Location: admin_dashboard.php");
            } elseif ($user['usertype'] == 'Student') {
                header("Location: profile.php");
            }
            exit;
        } else {
            // Invalid login
            $error = "Invalid email or password.";
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
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper login">
        <div class="container">
            <div class="col-left">
                <div class="login-text">
                    <h2>Welcome!</h2>
                    <a href="signup.php" class="btn">Sign Up</a>
                </div>
            </div>
            <div class="col-right">
                <div class="login-form">
                    <h2>Login</h2>
                    <?php if (!empty($error)) echo "<p style='color: red;'>$error</p>"; ?>
                    <form method="POST" action="">
                        <p>
                            <label for="email">Email:</label>
                            <input type="email" name="email" required placeholder="Username or Email">
                        </p>
                        <p>
                            <label for="password">Password:</label>
                            <input type="password" name="password" required placeholder="Password">
                        </p>
                        <p>
                            <input type="submit" name="login" value="Sign In">
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
