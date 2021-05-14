<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>测试页面</title>
</head>
<body>
<header>
    <?php self::view('header'); ?>
</header>

<?php
echo $hello_world;
?>

<footer>
    <?php self::view('footer'); ?>
</footer>
</body>
</html>
