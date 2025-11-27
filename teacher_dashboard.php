<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Teacher Dashboard</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
body {
    font-family: Arial, sans-serif;
    background: #eef3ff;
    margin: 0;
}
nav {
    background: #2563eb;
    padding: 15px;
    color: white;
    font-size: 20px;
}
.container {
    max-width: 900px;
    background: #fff;
    margin: 40px auto;
    padding: 20px;
    border-radius: 10px;
}
.course {
    background: #dbe6ff;
    padding: 10px;
    margin: 8px 0;
    border-radius: 8px;
    cursor: pointer;
}
.course:hover {
    background: #bcd3ff;
}
button {
    background: #2563eb;
    border: none;
    padding: 10px 20px;
    color: white;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 10px;
}
button:hover {
    filter: brightness(0.9);
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
table, th, td {
    border: 1px solid #000;
    padding: 8px;
}
.hidden { display:none; }
</style>
</head>
<body>

<nav>
Welcome, <?php echo $_SESSION['username']; ?> 👋 (Teacher)
<a href="logout.php" style="float:right;color:white;">Logout</a>
</nav>

<div class="container">

<h2>Your Courses</h2>
<div id="course-list">Loading...</div>

<div id="session-page" class="hidden">
    <h3 id="session-title"></h3>

    <table>
        <thead>
            <tr><th>Student</th><th>Present</th></tr>
        </thead>
        <tbody id="student-table-body"></tbody>
    </table>

    <button id="save-attendance">Save Attendance</button>
    <button id="back">Back</button>
</div>

</div>

<script>
$(document).ready(function () {

    loadCourses();

    // --------------------------------------------------------------
    // LOAD COURSES
    // --------------------------------------------------------------
    function loadCourses() {
        $.post("teacher_actions.php", { action: "get_courses" }, function (response) {

            let courses = JSON.parse(response);
            let html = "";

            courses.forEach(c => {
                html += `
                    <div class="course" data-id="${c.id}">
                        ${c.course_name}
                    </div>`;
            });

            $("#course-list").html(html);
        });
    }

    // --------------------------------------------------------------
    // WHEN CLICKING A COURSE → LOAD STUDENTS
    // --------------------------------------------------------------
    $(document).on("click", ".course", function () {
        let course_id = $(this).data("id");

        $("#session-page").data("course", course_id);
        $("#course-list").hide();
        $("#session-page").show();
        $("#session-title").text("Session for Course #" + course_id);

        $.post("teacher_actions.php",
            { action: "get_students_for_course", course_id: course_id },
            function (response) {

                let students = JSON.parse(response);
                let html = "";

                students.forEach(s => {
                    html += `
                        <tr>
                            <td>${s.first_name} ${s.last_name}</td>
                            <td><input type="checkbox" class="present" data-id="${s.id}"></td>
                        </tr>`;
                });

                $("#student-table-body").html(html);
            }
        );
    });

    // --------------------------------------------------------------
    // SAVE ATTENDANCE
    // --------------------------------------------------------------
    $("#save-attendance").click(function () {

        let attendance = [];
        let course_id = $("#session-page").data("course");

        $(".present").each(function () {
            attendance.push({
                student_id: $(this).data("id"),
                present: $(this).is(":checked") ? 1 : 0
            });
        });

        $.post("teacher_actions.php",
            {
                action: "save_attendance",
                course_id: course_id,
                data: JSON.stringify(attendance)
            },
            function (response) {

                console.log("SAVE RESPONSE:", response);

                let res = JSON.parse(response);

                if (res.status === "ok") {
                    alert("Attendance Saved!");
                } else {
                    alert("Error: " + res.message);
                }
            }
        );
    });

    // --------------------------------------------------------------
    // BACK BUTTON
    // --------------------------------------------------------------
    $("#back").click(function () {
        $("#session-page").hide();
        $("#course-list").show();
    });

});
</script>

</body>
</html>


