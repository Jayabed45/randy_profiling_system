<?php
session_start();

// Check if user is logged in and is a Student
if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'Student') {
    header("Location: home.php");
    exit();
}

$firstName = $_SESSION['first_name'];
$lastName = $_SESSION['last_name'];
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

// Fetch the user's profile information
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found.");
    }

    $profilePicture = $user['profile_picture'] ?? 'default_profile.png'; // Handle missing profile picture
} catch (PDOException $e) {
    die("Could not retrieve profile: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(145deg, #d1d3e2, #f1f3f8);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Header Styling */
        header {
            background: #007bff;
            color: white;
            padding: 10px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1000;
            border-bottom: 3px solid #0056b3;
        }

        header h1 {
            font-size: 26px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown button {
            background-color: #0056b3;
            color: white;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s ease;
        }

        .dropdown button:hover {
            background-color: #003d82;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            left: 0;
            background-color: white;
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            border-radius: 8px;
            margin-top: 10px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s 0.3s;
        }

        .dropdown.active .dropdown-content {
            display: block;
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-content a {
            color: #333;
            padding: 12px;
            text-decoration: none;
            display: block;
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            border-radius: 8px;
            transition: background-color 0.2s ease;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        /* Main Content */
        .container {
            margin-top: 100px;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
            padding: 40px;
            background-color: white;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.3s ease;
        }

        .profile-header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            text-align: center;
        }

        .profile-header img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #007bff;
            transition: transform 0.3s ease;
        }

        .profile-header img:hover {
            transform: scale(1.1);
        }

        .profile-info h2 {
            font-size: 32px;
            color: #007bff;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .profile-info p {
            font-size: 18px;
            margin: 8px 0;
            font-weight: 400;
        }

        .profile-info .additional-info {
            font-size: 16px;
            margin-top: 20px;
            color: #555;
        }

        .profile-info .additional-info p {
            margin: 5px 0;
        }

        /* Footer */
        footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background-color: #f1f3f8;
            font-size: 14px;
            color: #777;
            border-top: 1px solid #ddd;
        }

        footer a {
            text-decoration: none;
            color: #007bff;
            font-weight: 500;
        }

        footer a:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>

<header>
    <div class="dropdown" id="account-settings">
        <button>
            <i class="fas fa-cog"></i> Account Settings
        </button>
        <div class="dropdown-content">
            <a href="edit_profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <h1>Student Profile</h1>
</header>

<div class="container">
    <div class="profile-header">
        <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile Picture">
        <div class="profile-info">
            <h2><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>
            <p><strong>Age:</strong> <?php echo htmlspecialchars($user['age']); ?></p>
            <p><strong>Gender:</strong> <?php echo htmlspecialchars($user['gender']); ?></p>
            <div class="additional-info">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>User Type:</strong> <?php echo htmlspecialchars($user['usertype']); ?></p>
            </div>
        </div>
    </div>
</div>

<footer>
    <p>© 2024 Student Portal | All rights reserved | <a href="terms_and_conditions.php">Terms & Conditions</a></p>
</footer>

<script>
    // Toggle the dropdown menu when the button is clicked
    document.getElementById('account-settings').addEventListener('click', function() {
        this.classList.toggle('active');
    });
</script>

</body>
</html>
