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
    <link rel="stylesheet" href="../css/alert.css">
</head>

<body>
    <?php
    // ! Start Session in the code
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
        $username = $_SESSION['username'];

        // !added the session username for roles like user or admin

        // Insert task into the database
        $sql = "INSERT INTO tasks (username, taskName, startTime, priority) VALUES ('$username', '$taskName', '$startTime', '$priority')";

        if ($conn->query($sql) === TRUE) {
            echo "<script src='../js/alert.js'></script><script>task_added_success();</script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    ?>
    <!-- Test to see if the username displays in the session -->
    <?php //echo $_SESSION['username']; 
    ?>
    <h1>Task Scheduler</h1>
    <br>

    <!-- !Edited here for a better look in the user page -->
    <div class="container mt-2">
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="mb-3">
                <label for="taskName" class="form-label">Task Name:</label>
                <input type="text" id="taskName" name="taskName" class="form-control" placeholder="Input task name here" required>
            </div>
            <div class="mb-3">
                <label for="startTime" class="form-label">Start Time:</label>
                <input type="time" id="startTime" name="startTime" class="form-select-sm" required>
            </div>
            <div class="mb-3">
                <label for="priority" class="form-label">Priority:</label>
                <select class="form-select-sm" id="priority" name="priority" required>
                    <option selected disabled value="">Choose the priority</option>
                    <option value="High">High</option>
                    <option value="Normal">Normal</option>
                    <option value="Minimal">Minimal</option>
                </select>
            </div>
            <button onclick="convertToSpeech()" name="submitTask" class="btn btn-success">Schedule a task</button>
        </form>

        <div class="mt-3">
            <button onclick="deleteDataAll()" class="btn btn-warning mr-2">Clear all tasks</button>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
        <!-- !this code is newly added -->

        <div class="container mt-2">
            <label for="priorityFilter" class="form-label">Filter by Priority:</label>
            <select class="form-select-sm" id="priorityFilter" name="priorityFilter" onchange="navigateToPriorityPage()">
                <option selected value="">All Priorities</option>
                <option value="High">High</option>
                <option value="Normal">Normal</option>
                <option value="Minimal">Minimal</option>
            </select>
        </div>
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

        </tbody>
    </table>

    <script>
        // ! this code is newly added
        var selectedPriority = "";

        $(document).ready(function() {
            // event.preventDefault(); // Prevent the default form submission behavior

            loadData();
            updateTimeValues(); // Call it once initially to set the initial time values

            // Update the time values every second
            setInterval(loadData, 1000);
            setInterval(updateTimeValues, 1000);

            // ! this code is newly added
            // loadAllTasks();


        });
        // ! this code is newly added
        function confirmFilterByPriority() {
            displaySelectedPriority(selectedPriority);
        }

        // ! this code is newly added
        function displaySelectedPriority() {
            // Hide all table rows
            $("#taskTable tbody tr").hide();

            if (selectedPriority === "") {
                // If the "All Priorities" option is selected, show all table rows
                $("#taskTable tbody tr").show();
            } else {
                // Show table rows with the selected priority
                $("#taskTable tbody tr:has(th:contains('" + selectedPriority + "'))").show();
            }
        }

        // ! this code is newly added
        // function loadAllTasks() {

        //     $.ajax({
        //         url: "load_data.php",
        //         type: "GET",
        //         success: function(response) {
        //             // Handle the response and update the table with the loaded data
        //             filterTasksByPriority(); // Filter the tasks based on the selected priority
        //             $("#taskTable tbody").html(response);
        //         },
        //         error: function(xhr, status, error) {
        //             console.log(xhr.responseText);
        //         }
        //     });
        // }

        // ! this code is newly added
        function filterTasksByPriority() {
            selectedPriority = $("#priorityFilter").val();

            if (selectedPriority === "") {
                // If the "All Priorities" option is selected, load all tasks
                loadData();
            } else {
                // Perform actions to filter tasks by priority
                // For example, make an AJAX request to fetch tasks with the selected priority
                $.ajax({
                    url: "load_data.php",
                    type: "GET",
                    data: {
                        priority: selectedPriority
                    },
                    success: function(response) {
                        // Handle the response and update the table with the filtered data
                        $("#taskTable tbody").html(response);
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            }
        }

        function navigateToPriorityPage() {
            const selectElement = document.getElementById("priorityFilter");
            const selectedValue = selectElement.value;
            if (selectedValue === "High") {
                window.location.href = "high_priority_page.php";
            } else if (selectedValue === "Normal") {
                window.location.href = "normal_priority_page.php";
            } else if (selectedValue === "Minimal") {
                window.location.href = "minimal_priority_page.php";
            }
        }


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
                url: "load_data_high.php",
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
    <script src="../js/alert.js"></script>
</body>

</html>