<?php
session_start();
// Establish database connection (similar to previous steps)
$hostname = "localhost";
$username = "root";
$password = "";
$database = "tasks";

$conn = mysqli_connect($hostname, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the ID from the AJAX request
// !changes made to only delete all tasks from a specific user
$username = $_SESSION['username'];

// Delete data based on ID
$sql = "DELETE FROM tasks WHERE username = '$username'";

if (mysqli_query($conn, $sql)) {
    echo "Tasks deleted successfully.";
} else {
    echo "Error deleting tasks: " . mysqli_error($conn);
}

// Close the database connection (similar to previous steps)
mysqli_close($conn);
