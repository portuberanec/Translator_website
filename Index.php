<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Translatorwebsite</title>
</head>
<body>
<h1>Сайт со всеми переводами</h1>


<h2>Фразы</h2>

<table border="1">
    <tr>
        <th>Фраза: </th>
        <th>Транслит:</th>
        <th>Перевод:</th>
    </tr>
    <tr>
<?php
    require_once "dbconnect.php";

    $result = mysqli_query($link, "SELECT * FROM Phrase;");


while ($row = mysqli_fetch_row($result)) {
    echo "<td> {$row[1]} </td> <td> {$row[2]} </td> <td> {$row[3]} </td> </tr>";
}
echo '</table> </ul>';
?>

<h2>Слова</h2>

<table border="1">
    <tr>
        <th>Фраза: </th>
        <th>Транслит:</th>
        <th>Перевод:</th>
    </tr>
    <tr>

<?php
        require_once "dbconnect.php";

        $result = mysqli_query($link, "SELECT * FROM Words;");


        while ($row = mysqli_fetch_row($result)) {
            echo "<td> {$row[1]} </td> <td> {$row[2]} </td> <td> {$row[3]} </td> </tr>";
        }
        echo '</table> </ul>';
        ?>

</body>
</html>
