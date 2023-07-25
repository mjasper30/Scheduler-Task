<?php
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
$id = $_POST['taskId'];

// Delete data based on ID
$sql = "DELETE FROM tasks WHERE taskId = $id";

if (mysqli_query($conn, $sql)) {
    echo "Data deleted successfully.";
} else {
    echo "Error deleting data: " . mysqli_error($conn);
}

// Close the database connection (similar to previous steps)
mysqli_close($conn);
