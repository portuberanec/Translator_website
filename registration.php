<head>
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>

<form method="post" action="" name="signup-form">
    <div class="form-element">
        <label>Username</label>
        <input type="text" name="username" pattern="[a-zA-Z0-9]+" required />
    </div>
    <div class="form-element">
        <label>Password</label>
        <input type="password" name="password" required />
    </div>
    <button type="submit" name="register" value="register">Register</button>
</form>

<form method="post" action="" name="registration-form">
    <button type="submit" name="login" value="login">to Log in</button>
</form>

<?php
    require_once "dbconnect.php";
    session_start();
    include('config.php');
    if (isset($_POST['register'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = mysqli_prepare($link, "SELECT * FROM User WHERE UserName = (?)"); //UserName=:username;
        mysqli_stmt_bind_param($stmt, 's', $username); // не s, но я не знаю, какой тип применить для char и varchar
        mysqli_stmt_execute($stmt);

        mysqli_stmt_store_result($stmt);

        $rows = mysqli_stmt_num_rows($stmt);
        echo "$rows";
        mysqli_stmt_close($stmt);
        if ($rows > 0) { //->rowCount()

            echo '<p class="error">Этот ник уже зарегистрирован!</p>';

        }
        if ($rows == 0) { //->rowCount()
            $stmt = mysqli_prepare($link, "INSERT INTO User(UserName,Password) VALUES (?,?)"); //UserName=:username;
            mysqli_stmt_bind_param($stmt, 'ss', $username, $password_hash);

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            if ($stmt) {
                echo '<p class="success">Регистрация прошла успешно!</p>';
            } else {
                echo '<p class="error">Неверные данные!</p>';
            }
        }
    }
    if (isset($_POST['login'])) {
        header("Location: login.php");
    }

?>