<?php
    // подключение к БД
	error_reporting(0);

    require_once "config.php";

	$link = mysqli_connect($db_host, $db_user, $db_password, $db_name);
	if (!$link) {
        die('<p style="color:red">'.mysqli_connect_errno().' - '.mysqli_connect_error().'</p>');
    }

