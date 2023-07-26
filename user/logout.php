<?php
// !Logout of Session
session_start();
session_destroy();
header("Location: ../index.php");
exit();
