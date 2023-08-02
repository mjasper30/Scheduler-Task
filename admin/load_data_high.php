<?php
session_start();
// *Set time zone to Manila
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
$sql = "SELECT * FROM tasks WHERE priority = 'High'";
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

    // Modal logic old
    // if ($remainingTime == 0) {
    //     $tableHtml .= "<tr class='table-danger'>";

    //     echo "<script>
    //     setTimeout(function() {
    //         document.getElementById('notificationSound').play();
    //     }, 2000); // 2000 milliseconds (2 seconds) delay
    // </script>";

    //     echo "<script>show_elapse_alert();</script>";

    //  text to speech elapse
    //     echo "<script>convertToSpeechElapse('" . $taskName . "');</script>";

    // * Modal Logic
    // * MODAL, NOTIFICATION SOUND, TEXT TO SPEECH with delay
    if ($remainingTime == 0) {
        $tableHtml .= "<tr class='table-danger'>";

        //* Notification Sound
        echo "<script>
          document.getElementById('notificationSound').play();  
        </script>";

        //* Modal Alert
        echo "<script>
            show_elapse_alert(); // Show the modal first
        </script>";

        //* Text to Speech with delay
        echo "<script>
            async function convertToSpeechElapse(taskName) {
                var textInput1 = taskName + ' this task has now elapsed';
                var speech = new SpeechSynthesisUtterance();
                speech.text = textInput1;
                speech.lang = 'fil';
                speech.onend = function() {
                    // Place any code here that you want to execute after the text-to-speech finishes
                };
                await speechSynthesis.speak(speech);
            }
    
            setTimeout(function() {
                convertToSpeechElapse('" . $taskName . "'); // Call the text-to-speech function after a 5-second delay
            }, 1000); // 1000 milliseconds (1 seconds) delay
        </script>";
        // "blank space" >_<

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
