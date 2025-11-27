<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance Management System</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
body { font-family: Arial, sans-serif; background: #f4f7fb; margin: 0; padding: 0; }
nav { background-color: #2563eb; padding: 12px; display: flex; gap: 15px; }
.nav-link { color: #fff; text-decoration: none; font-weight: bold; padding: 6px 12px; border-radius: 6px; }
.nav-link:hover { opacity: 0.9; filter: brightness(0.9); }
.container { max-width: 900px; margin: 50px auto; text-align: center; }
button { background-color: #2563eb; color: #fff; border: none; padding: 10px 20px; cursor: pointer; font-size: 16px; border-radius: 6px; }
button:hover { opacity: 0.9; filter: brightness(0.9); }
.section { display: none; margin-top: 30px; padding: 20px; background: #fff; border-radius: 10px; }
input, select { padding: 10px; border-radius: 6px; border: 1px solid #ccc; width: 200px; margin-bottom: 15px; }
#error { color: red; display: none; }
table { border-collapse: collapse; width: 100%; margin-top: 10px; }
table, th, td { border: 1px solid #000; padding: 6px; text-align: left; }
</style>
</head>
<body>

<nav>
  <a class="nav-link" href="#" id="homeLink">Home</a>
  <a class="nav-link" href="#" id="logoutBtn" style="display:none;">Logout</a>
</nav>

<div class="container">

<!-- LOGIN -->
<div id="login-page" class="section" style="display:block;">
<h1>Login</h1>
<form id="loginForm">
    <select name="role" id="role" required>
        <option value="">-- Select Role --</option>
        <option value="student">Student</option>
        <option value="teacher">Teacher</option>
        <option value="admin">Admin</option>
    </select><br>

    <input type="text" name="username" id="username" placeholder="Username" required><br>
    <input type="password" name="password" id="password" placeholder="Password" required><br>

    <button type="submit">Login</button>
</form>
<p id="error">Incorrect login information</p>
</div>

<!-- STUDENT DASHBOARD -->
<div id="student-dashboard" class="section">
<h2>Welcome Student: <span id="student-name"></span></h2>
<p>Your Student ID: <span id="student-id"></span></p>
</div>

<!-- TEACHER DASHBOARD -->
<div id="teacher-dashboard" class="section">
<h2>Welcome Teacher: <span id="teacher-name"></span></h2>
<p>Your Teacher ID: <span id="teacher-id"></span></p>
</div>

<!-- ADMIN DASHBOARD -->
<div id="admin-dashboard" class="section">
<h2>Welcome Admin: <span id="admin-name"></span></h2>
</div>

</div>

<script>
$(document).ready(function() {

    $("#loginForm").submit(function(e){
        e.preventDefault();
        $("#error").hide();

        $.ajax({
            url: "auth.php",
            method: "POST",
            data: {
                username: $("#username").val(),
                password: $("#password").val(),
                role: $("#role").val()
            },
            dataType: "json",
            success: function(res) {
                if(res.status === "success") {

                    // 🔥 FIX: RELOAD THE PAGE SO SESSION WORKS
                    window.location.href = res.role + "_dashboard.php";
                } 
                else {
                    $("#error").show();
                }
            },
            error: function(xhr){
                alert("AJAX Error: " + xhr.responseText);
            }
        });
    });

});
</script>


</body>
</html>


