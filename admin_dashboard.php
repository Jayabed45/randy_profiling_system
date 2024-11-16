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

// Fetch all users from the database
try {
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Could not retrieve users: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
            animation: fadeIn 1s ease-out;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 20px 30px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            animation: slideInFromTop 1s ease-out;
        }

        header h1 {
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 1s ease-out;
        }

        .welcome-message {
            font-size: 18px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-message strong {
            font-size: 20px;
        }

        .logout {
            text-decoration: none;
            padding: 10px 20px;
            background-color: #f44336;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s, transform 0.3s ease-in-out;
        }

        .logout:hover {
            background-color: #d32f2f;
            transform: scale(1.05);
        }

        h3 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            animation: fadeInUp 1.5s ease-out;
        }

        th, td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: #f2f2f2;
            font-weight: 600;
            color: #4CAF50;
        }

        td {
            background-color: #ffffff;
            color: #555;
        }

        img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .action-buttons a {
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            text-align: center;
            margin: 0 5px;
            font-weight: bold;
            transition: all 0.3s ease-in-out;
        }

        .action-buttons a.edit {
            background-color: #4CAF50;
            color: white;
        }

        .action-buttons a.delete {
            background-color: #f44336;
            color: white;
        }

        .action-buttons a:hover {
            opacity: 0.9;
            transform: scale(1.1);
        }

        /* Hover effect for rows */
        tr:hover {
            background-color: #f1f1f1;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transform: scale(1.02);
            transition: all 0.3s ease-in-out;
        }

        /* Animations for fade-in and slide-in */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInFromTop {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            table, th, td {
                font-size: 14px;
                padding: 12px;
            }

            header {
                padding: 15px 20px;
            }

            .container {
                margin: 20px;
                padding: 15px;
            }

            .welcome-message {
                font-size: 16px;
                flex-direction: column;
                align-items: flex-start;
            }

            .logout {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>Admin Dashboard</h1>
</header>

<div class="container">
    <div class="welcome-message">
        <p>Hello, <strong><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></strong>! You are logged in as an Admin.</p>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <h3>User List</h3>
    <table>
        <thead>
            <tr>
                <th>Profile Picture</th>
                <th>ID</th>
                <th>Email</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Age</th>
                <th>Gender</th>
                <th>User Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td>
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
                    <?php else: ?>
                        <img src="default_profile.png" alt="Default Profile Picture">
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                <td><?php echo htmlspecialchars($user['age']); ?></td>
                <td><?php echo htmlspecialchars($user['gender']); ?></td>
                <td><?php echo htmlspecialchars($user['usertype']); ?></td>
                <td class="action-buttons">
                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="edit">Edit</a>
                    <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
