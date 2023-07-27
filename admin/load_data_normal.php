<?php
session_start();
// Replace with your database connection details
date_default_timezone_set('Asia/Manila');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tasks";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$username = $_SESSION['username'];

// Fetch tasks from the database
$sql = "SELECT * FROM tasks WHERE priority = 'Normal'";
$result = $conn->query($sql);

// Start building the HTML for the table
$tableHtml = "<table class='table' id='taskTable'>
    <thead>
        <tr class='tableRow table-secondary'>
            <th>Date Today</th>
            <th>Description</th>
            <th>Notify at</th>
            <th>Hour</th>
            <th>Minute</th>
            <th>Seconds</th>
            <th>Remaining</th>
            <th>Status</th>
            <th>Priority</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>";

$modalHtml = ""; // Store the modal HTML
$modalScript = ""; // Store the JavaScript code to trigger the modal

while ($row = $result->fetch_assoc()) {
    $taskId = $row['taskId'];
    $taskName = $row['taskName'];
    $startTime = $row['startTime'];
    $dateToday = $row['dateToday'];
    $priority = $row['priority'];
    $timestamp = $dateToday;
    $readableFormat = date("F j, Y, g:i a", strtotime($timestamp));

    // Calculate remaining time
    $currentTime = date("H:i:s");
    $remainingTime = strtotime($startTime) - strtotime($currentTime);
    $hours = gmdate('H', $remainingTime);
    $minutes = gmdate('i', $remainingTime);
    $seconds = gmdate('s', $remainingTime);

    // ! Modify by jasper make it so that when time elapses instead of 24 hour count start from 0 seconds
    // Get the current time
    $currentTimestamp = time();

    // Get the target time (startDateTime from the database)
    $startDateTime = $row['startTime'];
    $targetTimestamp = strtotime($startDateTime);

    // Calculate the elapsed time in seconds
    $elapsedTime = $currentTimestamp - $targetTimestamp;

    // Convert the elapsed time to hours, minutes, and seconds
    $hoursCurrent = floor($elapsedTime / 3600);
    $minutesCurrent = floor(($elapsedTime % 3600) / 60);
    $secondsCurrent = $elapsedTime % 60;

    // Modal logic
    if ($remainingTime == 0) {
        $tableHtml .= "<tr class='table-danger'>";

        echo "<script>show_elapse_alert();</script>";

        //  text to speech elapse
        echo "<script>convertToSpeechElapse('" . $taskName . "');</script>";

        // ! sleep did not work on delaying the modal it delayed this whole condition
        // sleep(3);
    } else if ($remainingTime < 0) {
        $tableHtml .= "<tr class='table-danger'>";
    } else {
        $tableHtml .= "<tr class='table-warning'>";
    }

    // Show table data
    $tableHtml .= "<td>$readableFormat</td>";
    $tableHtml .= "<td>$taskName</td>";
    $tableHtml .= "<td>$startTime</td>";
    if ($remainingTime < 0) {
        $tableHtml .= "<td>$hoursCurrent</td>";
        $tableHtml .= "<td>$minutesCurrent</td>";
        $tableHtml .= "<td>$secondsCurrent</td>";
    } else {
        $tableHtml .= "<td>$hours</td>";
        $tableHtml .= "<td>$minutes</td>";
        $tableHtml .= "<td>$seconds</td>";
    }
    $tableHtml .= "<td>$remainingTime</td>";

    if ($remainingTime < 0) {
        $tableHtml .= "<td>Time Elapsed</td>";
    } else {
        $tableHtml .= "<td>On Going</td>";
    }

    $tableHtml .= "<th>" . $priority . "</th>";
    $tableHtml .= "<td>";
    $tableHtml .= "<input class='btn btn-danger' type='button' value='Delete' onclick='deleteData($taskId);'></input>";
    $tableHtml .= "</td>";
    $tableHtml .= "</tr>";
}

$tableHtml .= "</tbody></table>";

// Return the generated table HTML, modals, and modal trigger script
echo $tableHtml;

$conn->close();
