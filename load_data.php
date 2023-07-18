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
$sql = "SELECT * FROM tasks WHERE username = '$username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Start building the HTML for the table
    $tableHtml = "<table class='table' id='taskTable' >
    <tbody><tr class='tableRow table-secondary'>
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
                </tr>";
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

        // ! Modify by jasper
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
            // working above pero sa modal wala

            // Play the text-to-speech notification
            // the same as text to speech
            // $modalScript .= "<script>playTextToSpeech('Your notification text goes here.');</script>";

            // $modalHtml .= "<div class='modal show' id='modal_$taskId' tabindex='-1' role='dialog' aria-labelledby='modal_$taskId' aria-hidden='true'> data-backdrop='static'";
            // ! sudden background square with modal fixed with missing ' from the statement 
            // ! by adding div class modal error all rows move above and by including the ' modal class it now has a white sqaure background??
            // * fixed the error in white background you know whats the problem? this class='modal-dialog modal-dialog-centered' did not remove the other modal-dialog 
            // * took long enough to fix hahahah
            // ! now fixing the size cause when it pops up it stretches all the way
            // ! modal-md and modal -lg wont work it still stretches

            // $modalHtml .= "<div class='modal show' id='modal_$taskId' tabindex='-1' role='dialog' aria-labelledby='modal_$taskId' aria-hidden='true'>";
            // $modalHtml .= "<div class='modal show' id='modal_$taskId' tabindex='-1' role='dialog' aria-labelledby='modal_$taskId' aria-hidden='true'>";
            // $modalHtml .= "    <div class='modal-dialog-centered'  role='document' >";
            // $modalHtml .= "        <div class='modal-content' style=' background-color: #e64322;' ";
            // $modalHtml .= "            <div class='modal-header'>";
            // $modalHtml .= "                <h5 class='modal-title'>The task has now elapsed</h5>";
            // $modalHtml .= "                <button type='button' class='close' data-dismiss='modal' aria-label='Close'>";
            // $modalHtml .= "                    <span aria-hidden='true'>&times;</span>";
            // $modalHtml .= "                </button>";
            // $modalHtml .= "            </div>";
            // $modalHtml .= "            <div class='modal-body'>";
            // $modalHtml .= "                <p>The task $taskName has been completed.</p>";
            // $modalHtml .= "            </div>";
            // $modalHtml .= "            <div class='modal-footer'>";
            // $modalHtml .= "                <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>";
            // $modalHtml .= "            </div>";
            // $modalHtml .= "        </div>";
            // $modalHtml .= "    </div>";
            // $modalHtml .= "</div>";

            $modalHtml .= "<div class='modal show' id='modal_$taskId' tabindex='-1' role='dialog' aria-labelledby='modal_$taskId' aria-hidden='true'>";
            $modalHtml .= "    <div class='modal-dialog-centered modal-dialog-md' role='document'>";
            $modalHtml .= "        <div class='modal-content' style='background-color: #e64322;'>";
            $modalHtml .= "            <div class='modal-header'>";
            $modalHtml .= "                <h5 class='modal-title'>The task has now elapsed</h5>";
            $modalHtml .= "                <button type='button' class='close' data-dismiss='modal' aria-label='Close'>";
            $modalHtml .= "                    <span aria-hidden='true'>&times;</span>";
            $modalHtml .= "                </button>";
            $modalHtml .= "            </div>";
            $modalHtml .= "            <div class='modal-body'>";
            $modalHtml .= "                <p>The task $taskName has been completed.</p>";
            $modalHtml .= "            </div>";
            $modalHtml .= "            <div class='modal-footer'>";
            $modalHtml .= "                <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>";
            $modalHtml .= "            </div>";
            $modalHtml .= "        </div>";
            $modalHtml .= "    </div>";
            $modalHtml .= "</div>";



            $modalScript .= "<script>document.getElementById('modal_$taskId').style.display = 'block';</script>";


            // make the modal last long 
            echo $modalScript;
            echo $modalHtml;

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

    // Return the generated table HTML, modals, and modal trigger script

    echo $tableHtml;
} else {
    echo "<table class='table' id='taskTable'>
    <tbody><tr class='tableRow table-secondary'>
            <th>Date Today</th>
            <th>Description</th>
            <th>Notify at</th>
            <th>Hour</th>
            <th>Minute</th>
            <th>Seconds</th>
            <th>Remaining</th>
            <th>Status</th>
            <th>Action</th>
            </tr>";
    echo "<tr><td colspan='9' class='text-center'>No tasks found</td></tr>";
    // last issue when the task is done the data wont show and became wonky to fix put tbody / table here
    echo "</tbody></table>";
}
$conn->close();
