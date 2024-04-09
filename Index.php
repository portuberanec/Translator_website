<?php
    session_start();
    if(!isset($_SESSION['user_id'])){
        header('Location: login.php');
        exit;
    } else {
    }
?>

<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Translatorwebsite</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>




<form method="POST">
    <button type="submit" name="logout" value="logout" >Log out</button>
</form>

<?php
    if (isset($_POST['logout'])) {
        $_SESSION['user_id'] = NULL;
        header('Location: login.php');
        exit;
    } else {
}
?>



<h1>Сайт со всеми переводами</h1>

<h2>Поля для ввода новых фраз</h2>


<form method="POST">
    <div class="form-element">
        <label>Введите фразу на японском</label>
        <input type="Sended" name="Sended"/>
    </div>
    <button type="submit" name="Send" value="Send" >Send text</button>
</form>


<?php
if (isset($_POST['Send'])) {
    $SendedText = $_POST['Sended'];
    if ($SendedText == '') {
        echo 'Вы пытались отправить пустую строку';
        header("Refresh: 5");
    }
    else {
        file_put_contents('C:\OSPanel\domains\Translatorwebsite\Stub.txt', $SendedText);
        file_put_contents('C:\OSPanel\domains\Translatorwebsite\Stub.txt', "\n", FILE_APPEND);
        file_put_contents('C:\OSPanel\domains\Translatorwebsite\Stub.txt', $_SESSION['user_id'], FILE_APPEND);
        exec('C:\OSPanel\domains\Translatorwebsite\translator_php.py');
        header("Refresh: 3");
    }
}
else {
}
?>



<h2>Пользователь</h2>

<?php
    require_once "dbconnect.php";

    $stmt = mysqli_prepare($link, "SELECT * FROM User WHERE ID = (?);");
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);

    mysqli_stmt_execute($stmt);

    $result_all = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    while ($row = mysqli_fetch_row($result_all)) {
        echo "{$row[1]}";
    }
?>


<h2>Фразы и слова</h2>


<?php
    require_once "dbconnect.php";

$stmt = mysqli_prepare($link, "SELECT * FROM `Bind(phrase-word)` WHERE UserID = (?)");
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);

mysqli_stmt_execute($stmt);

$result_all = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

while($row = mysqli_fetch_row($result_all)) {
    array_pop($row);
    array_shift($row);
    $array[] = $row; //массив из второго и третьего столбца таблицы
}

//print_r($array);

//Прогоняем общий массив на поиск значений слов с одной и той же фразы
for ($i = 1; $i <= count($array); ++$i) {
    if ($array[$i][0] == $array[$i-1][0]) {
        }
    else {
        //Делаем запрос по фразам и создаём таблицу
        $stmt = mysqli_prepare($link, "SELECT * FROM `Phrase` WHERE ID = (?)");
        mysqli_stmt_bind_param($stmt, 'i', $array[$i-1][0]);

        mysqli_stmt_execute($stmt);

        $result_all = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        echo '<br>';
        echo ' <table border="1">';
        echo '<tr>';
        echo '    <th>Фраза: </th>';
        echo '    <th>Транслит:</th>';
        echo '    <th>Перевод:</th>';
        echo '</tr>';
        echo '<tr>';

        while ($row = mysqli_fetch_row($result_all)) {
            echo "<td> {$row[1]} </td> <td> {$row[2]} </td> <td> {$row[3]} </td> </tr>";
        }
        echo '</table> </ul>';

        //Теперь делаем запрос по словам и создаём таблицу со словами
        $stmt = mysqli_prepare($link, "SELECT * FROM `Words` WHERE Phrase_ID = (?)");
        mysqli_stmt_bind_param($stmt, 'i', $array[$i-1][0]);

        mysqli_stmt_execute($stmt);

        $result_all = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        echo ' ';
        echo ' <table border="1">';
        echo '<tr>';
        echo '    <th>Слово: </th>';
        echo '    <th>Транслит:</th>';
        echo '    <th>Перевод:</th>';
        echo '</tr>';
        echo '<tr>';

        while ($row = mysqli_fetch_row($result_all)) {
            echo "<td> {$row[1]} </td> <td> {$row[2]} </td> <td> {$row[3]} </td> </tr>";
        }
        echo '</table> </ul>';

    }
}

?>



</body>
</html>