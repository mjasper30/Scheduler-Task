<!-- Almost Complete the error now is the numbers in the hr mn sc row and elapsed time -->
<!-- AJAX NLNG KULANG FOR REAL TIME  -->
<!-- ELAPSED NEXT -->
<!-- BUTTONS FOR CLEAR ROW AND TASK DONE -->
<!-- DATE TODAY NOW ADDED -->
<!-- almost complete with the help of jasper -->
<!-- since task 6.php failed now coding it here -->
<!-- added clear all task so that it deletes everything on the database -->
<!-- php used delete_data_all.php, load_data.php, delete_data.php -->

<!-- file with modal and text to speach at the end -->

<?php
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

// Function to calculate remaining time
function calculateRemainingTime($startTime)
{
    $currentTime = date("H:i:s");
    $remainingTime = strtotime($startTime) - strtotime($currentTime);
    return gmdate("H:i:s", $remainingTime);
}

if (isset($_POST['submitTask'])) {
    $taskName = $_POST['taskName'];
    $startTime = $_POST['startTime'];
    $priority = $_POST['priority'];


    // Insert task into the database
    $sql = "INSERT INTO tasks (taskName, startTime, priority) VALUES ('$taskName', '$startTime', '$priority')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Task scheduled successfully');</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Scheduler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* CSS FOR HEADER */
        h1 {
            text-align: center;
            background-color: #e0e2e2;
            padding: 15px;
            margin: 0;
        }

        label[for="taskName"] {
            font-weight: bold;
        }

        /* CSS FOR TASKNAME */
        #taskName {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            width: 300px;
        }

        #taskName::placeholder {
            color: #999;
        }

        #taskName:focus {
            outline: none;
            border-color: #0066cc;
            box-shadow: 0 0 5px #0066cc;
        }

        /* CSS FOR PRIORITY */
        label[for="Priority"] {
            font-weight: bold;
        }

        /* CSS FOR TASK TIME */
        .input-container {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .input-container label {
            margin-right: 10px;
            font-weight: bold;
        }

        .input-container input[type="time"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            width: 150px;
        }

        .input-container input[type="time"]:focus {
            outline: none;
            border-color: #0066cc;
            box-shadow: 0 0 5px #0066cc;
        }

        /* CSS FOR TABLES */
        #taskTable {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        #taskTable th,
        #taskTable td {
            border: 1px solid #e0e0e0;
            padding: 8px;
            text-align: left;
        }

        #taskTable tr:nth-child(even) {
            background-color: #f8f8f8;
        }

        #taskTable tr:first-child {
            font-weight: bold;
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h1>Task Scheduler</h1>
    <br>

    <div class="container">
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="taskName">Task Name:</label>
            <input type="text" id="taskName" name="taskName" placeholder="Input task name here" required>
            <div class="input-container mt-3">
                <label for="startTime">Start Time:</label>
                <input type="time" id="startTime" name="startTime" required>
            </div>
            <label for="Priority">Priority</label>
            <div class="input-group mb-3" style="width: 25%;">
                <div class="input-group">
                    <select class="custom-select form-control" id="inputGroupSelect04" name="priority" required>
                        <!-- kaya ayaw mag required sa select form kahit may required na tag wala value sa choose priority -->
                        <option selected disabled value="">Choose the priority</option>
                        <option value="High">High</option>
                        <option value="Normal">Normal</option>
                        <option value="Minimal">Minimal</option>
                    </select>
                </div>
            </div>
            <br>
            <button onclick="convertToSpeech()" name="submitTask" class="btn btn-success" style="margin-left: 10px;">Schedule a task</button>
        </form>

        <button onclick="deleteDataAll()" class="btn btn-warning" style="margin-left: 10px;">Clear all tasks</button>
        <!-- kaya hindi nag dikit ang button nasa baba ng delete button ang form reminder tags CLOSINGS!!! -->
    </div>
    <br>

    <table class='table' id="taskTable">
        <tbody>
            <tr class="tableRow table-secondary">
                <th>Date Today</th>
                <th>Description</th>
                <th>Notify at</th>
                <th>Hour</th>
                <th>Minute</th>
                <th>Seconds</th>
                <th>Remaining</th>
                <th>Status</th>
                <th>Priority</th>
                <th></th>
            </tr>

            <?php
            // Fetch tasks from the database
            $sql = "SELECT * FROM tasks";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $taskId = $row['taskId'];
                    $taskName = $row['taskName'];
                    $startTime = $row['startTime'];
                    $dateToday = $row['dateToday'];
                    $timestamp = $dateToday;
                    $readableFormat = date("F j, Y, g:i a", strtotime($timestamp));

                    $remainingTime = calculateRemainingTime($startTime);
                    $remainingSeconds = strtotime($remainingTime) - strtotime('TODAY');

                    $hours = gmdate('H', $remainingSeconds);
                    $minutes = gmdate('i', $remainingSeconds);
                    $seconds = gmdate('s', $remainingSeconds);

                    echo "<tr class='table-warning'>";
                    echo "<td>$readableFormat</td>";
                    echo "<td>$taskName</td>";
                    echo "<td>$startTime</td>";
                    echo "<td><span id='hours-$taskId'>$hours</span></td>";
                    echo "<td><span id='minutes-$taskId'>$minutes</span></td>";
                    echo "<td><span id='seconds-$taskId'>$seconds</span></td>";
                    echo "<td>$remainingTime</td>";
                    echo "<td id='elapsed-$taskId'>||</td>";
                    echo "<td>";
                    echo '<input class="btn btn-danger" type="button" id="deleteBtn" value="Delete" onclick="deleteData(' . $taskId . ');"></input>';
                    echo "</td>";
                    echo "<td>Priority Status no function yet!!</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='9' class='text-center'>No tasks found</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <script>
        $(document).ready(function() {
            // event.preventDefault(); // Prevent the default form submission behavior

            loadData();
            updateTimeValues(); // Call it once initially to set the initial time values

            // Update the time values every second
            setInterval(loadData, 1000);
            setInterval(updateTimeValues, 1000);
        });

        function updateTimeValues() {
            // Iterate through each table row and update the time values
            $("#taskTable tbody tr").each(function() {
                var startTime = $(this).find(".startTime").text();
                var currentTime = new Date();
                var remainingTime = calculateRemainingTime(startTime, currentTime);

                // Update the hours, minutes, seconds, and remaining time
                var hours = Math.floor(remainingTime / 3600);
                var minutes = Math.floor((remainingTime % 3600) / 60);
                var seconds = remainingTime % 60;

                $(this).find(".hours").text(hours);
                $(this).find(".minutes").text(minutes);
                $(this).find(".seconds").text(seconds);
                $(this).find(".remainingTime").text(formatTime(remainingTime));
            });
        }

        function calculateRemainingTime(startTime, currentTime) {
            var startTimestamp = new Date(startTime).getTime();
            var currentTimestamp = currentTime.getTime();
            var remainingSeconds = Math.floor((startTimestamp - currentTimestamp) / 1000);

            return remainingSeconds;
        }

        function formatTime(time) {
            var hours = Math.floor(time / 3600);
            var minutes = Math.floor((time % 3600) / 60);
            var seconds = time % 60;

            return ("0" + hours).slice(-2) + ":" + ("0" + minutes).slice(-2) + ":" + ("0" + seconds).slice(-2);
        }


        function deleteData(id) {
            $.ajax({
                url: "delete_data.php",
                type: "POST",
                data: {
                    taskId: id
                }, // *Send the ID to the PHP script
                success: function(response) {
                    // *Handle the response from the server
                    console.log(response);
                    // *Load data after successful deletion
                    loadData(); // Call a function to load data or perform any desired actions
                },
                error: function(xhr, status, error) {
                    // *Handle error if AJAX request fails
                    console.log(xhr.responseText);
                }
            });
        }

        function deleteDataAll() {
            $.ajax({
                url: "delete_data_all.php",
                type: "POST",
                success: function(response) {
                    // Handle the response from the server
                    console.log(response);
                    // Load data after successful deletion
                    loadData(); // Call a function to load data or perform any desired actions
                },
                error: function(xhr, status, error) {
                    // Handle error if AJAX request fails
                    console.log(xhr.responseText);
                }
            });
        }

        function loadData() {
            // Perform actions to load data or update the page as needed
            // For example, you can make another AJAX request to fetch and display updated data
            $.ajax({
                url: "load_data.php",
                type: "GET",
                success: function(response) {
                    // *Handle the response and update the page with the loaded data
                    // console.log(response);
                    $("#taskTable tbody").html(response); // Replace the table body with the updated data

                    // *Check if remaining time is 0
                    // * Used as a example to see if alert shows
                    // if ($(".remainingTime:contains('00:00:00')").length > 0) {
                    //     alert("Task completed!"); // Show an alert
                    // }
                },
                error: function(xhr, status, error) {
                    // *Handle error if AJAX request fails
                    console.log(xhr.responseText);
                }
            });
            // *Update the time values every second
            setInterval(updateTimeValues, 1000);
        }


        // Schedule a task Text to Speech
        // Uncoment to add the function back
        // function convertToSpeech() {

        // Get the text input from the user
        //     var textInput = document.getElementById("taskName").value;

        //     // Create a new SpeechSynthesisUtterance instance
        //     var speech = new SpeechSynthesisUtterance();

        //     // Set the text to be spoken
        //     speech.text = textInput;

        //     // Speak the text
        //     speechSynthesis.speak(speech);
        // }

        // *Elapsed Text to Speech
        // * TEXT TO SPEECH THE TASK NAME WHEN TEH TIME HAS ELAPSED
        function convertToSpeechElapse(taskName) {
            // Get the text input from the user

            var taskName;
            var textInput1 = taskName + 'this task has now elapsed';

            // Create a new SpeechSynthesisUtterance instance
            var speech = new SpeechSynthesisUtterance();

            // Set the text to be spoken
            speech.text = textInput1;

            // Speak the text
            // problem was in the modal version which is this the text to speech after the time elapse wont work
            // because the statement move fast so we put delay to let the code execute this 
            speechSynthesis.speak(speech).delay(3000);
        }

        // *Sound notification
        // *Testing to see if i can put a notification without a file like mp3

        // function playTextToSpeech(text) {
        //     if ('speechSynthesis' in window) {
        //         var msg = new SpeechSynthesisUtterance();
        //         msg.text = text;

        //         // Set the voice for speech synthesis (optional)
        //         // Uncomment and modify according to your preferred voice
        //         // var voices = speechSynthesis.getVoices();
        //         // msg.voice = voices[0];

        //         // Play the speech
        //         speechSynthesis.speak(msg);
        //     }
        // }
    </script>

</body>

</html>