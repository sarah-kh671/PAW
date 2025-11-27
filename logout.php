<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html>
<head>
<title>Logged Out</title>
<style>
body {
    background: #eef3ff;
    font-family: Arial;
    text-align: center;
    padding-top: 100px;
}

.box {
    background: white;
    width: 350px;
    margin: auto;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 0 10px #0002;
}

button {
    background: #2563eb;
    color: white;
    border: none;
    padding: 12px 20px;
    font-size: 16px;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 15px;
}
button:hover {
    filter: brightness(0.9);
}
</style>
</head>
<body>

<div class="box">
    <h2>You have been logged out</h2>
    <p>See you soon 👋</p>

    <button onclick="window.location='index.php'">Return to Login</button>
</div>

</body>
</html>

