<?php
session_start();
// if (isset($_SESSION['username'])) {
//     // Redirect to the dashboard or homepage if the user is already logged in
//     header("Location: dashboard.php");
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Replace with your database connection details
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "tasks";

    // Create a database connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute the SQL query
    $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // User is found, log in the user
        $row = $result->fetch_assoc();
        $role = $row['role'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];

        if ($role == 'admin') {
            // Redirect to the dashboard or homepage
            header("Location: dashboard.php");
            exit();
        } else if ($role == 'user') {
            // Redirect to the dashboard or homepage
            header("Location: test7.php");
            exit();
        } else {
            // Redirect to the dashboard or homepage
            header("Location: index.php");
            exit();
        }

        // Redirect to the dashboard or homepage
        header("Location: dashboard.php");
        exit();
    } else {
        // Invalid login credentials
        $error = "Invalid username or password";
    }

    $stmt->close();
    $conn->close();
}
?>

</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <!-- Add Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container">
        <h1 class="mt-5">Login Page</h1>
        <?php if (isset($error)) { ?>
            <div class="alert alert-danger mt-4"><?php echo $error; ?></div>
        <?php } ?>
        <form class="mt-4" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <p class="mt-3">Don't have an account? <a href="sign_in.php">Sign in here</a></p>
    </div>

    <!-- Add Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>