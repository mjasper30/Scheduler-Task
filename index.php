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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
</head>

<body>
    <h1>Login Page</h1>
    <?php if (isset($error)) { ?>
        <p><?php echo $error; ?></p>
    <?php } ?>
    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br><br>
        <input type="submit" value="Login">
    </form>

    <p>Don't have an account? <a href="sign_in.php">Sign up</a></p>
</body>

</html>