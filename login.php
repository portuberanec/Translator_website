<form method="post" action="" name="signin-form">
    <div class="form-element">
        <label>Username</label>
        <input type="text" name="username" pattern="[a-zA-Z0-9]+" required />
    </div>
    <div class="form-element">
        <label>Password</label>
        <input type="password" name="password" required />
    </div>
    <button type="submit" name="login" value="login">Log In</button>
</form>

<form method="post" action="" name="registration-form">
    <button type="submit" name="Registration" value="Registration">to Registration</button>
</form>

<?php
    require_once "dbconnect.php";
    session_start();
    include('config.php');
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $stmt = mysqli_prepare($link, "SELECT * FROM User WHERE UserName = (?)"); //WHERE UserName = (?)
        mysqli_stmt_bind_param($stmt, 's', $username);

        mysqli_stmt_execute($stmt);

        $result_all = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_row($result_all);
        print_r($row);
        $password_db = $row[2];
        $id_db = $row[0];

        mysqli_stmt_close($stmt);
        if (!$result_all) {
            echo '<p class="error">Неверное имя пользователя!</p>';

        } else {
            if (password_verify($password, $password_db)) {
                $_SESSION['user_id'] = $id_db;
                echo '<p class="success">Поздравляем, вы прошли авторизацию!</p>';
                header("Location: index.php");
            } else {
                echo '<p class="error"> Неверные пароль или имя пользователя!</p>';

            }
        }
    }
    if (isset($_POST['Registration'])) {
        header("Location: registration.php");
    }
?>
