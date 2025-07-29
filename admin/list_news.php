<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if($_SESSION["s_role"]=="customer"){
	header('Location: home/');
    }
}else{
    header("Location: ../login");
}


// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rn_bus_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all buses
$sql = "SELECT * FROM news";
$result = $conn->query($sql);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">


    <title>Bus Booking System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="list_bus.css">
    <link rel="icon" type="images/x-icon" href="../images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">

</head>
<body>
    <header>
        <nav class="navbar">
           
            <a class="linklogo" href="../"><img class="logo" src="../images/logo2.png" style="height: inherit; margin: 0px 6px;">RN Bus Pvt. Ltd.</a>
            <ul class="nav-links">
                <li><a href="list_reservations.php">Reservations</a></li>
                
                <li class="dropdown">
                        <a href="#">Services <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                         <ul class="dropdown-content">
                            <li><a href="list_bus.php">List Buses</a></li>
                            <li><a href="list_location.php">List Location</a></li>
                            <li><a href="list_users.php">List Users</a></li>
                        </ul>
                </li>
                <li><a href="manage_schedule.php">Manage Schedule</a></li>
                <li><a href="list_cancelrequest.php">Cancellation Requests</a></li>
                <li><a href="profile.php" class="profile-btn" style="margin-left: 100px;"><i class="fas fa-user-circle"></i> <?=$_SESSION['firstname']?></a></li>                <li><a href="logout.php" class="login-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
    <div class="listtop">
        <h2>List of News & Updates</h2>
        <button name="add" type="button" onclick="window.location.href='add_news.php'">Add New <i class="fa fa-plus"></i></button>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Title</th>
                <th>Description</th>
                <th style="width:8%">Date</th>
                <th style="width:11%">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php $index = 1; ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $index++; ?></td>
                        <td><?= htmlspecialchars($row["title"]); ?></td>
                        <td><?= htmlspecialchars($row["description"]); ?></td>
                        <td><?= htmlspecialchars($row["date"]); ?></td>
                        <td>
                            <a class="action-btn-edit" onclick="openEditModal(<?= $row['id']; ?>, '<?= htmlspecialchars($row['title']); ?>', '<?= htmlspecialchars($row['description']); ?>', '<?= htmlspecialchars($row['date']); ?>')">Edit</a>
                            <a href="delete_news.php?id=<?= $row["id"]; ?>" class="action-btn-delete" onclick="return confirm('Are you sure you want to delete this news update?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No news & updates found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Modal for editing news details -->
    <div id="editNewsModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h2>Edit News Details</h2>
            <form id="editNewsForm" method="post" action="edit_news.php" onsubmit="return validateNewsTitle()">
                <input type="hidden" id="newsId" name="news_id">
                <label for="news_title">Title:</label><br>
                <input type="text" id="news_title" name="title" required style="width:100%;"><br><br>
                <!-- <label for="news_description">Description:</label>
                <input type="text" id="news_description" name="description" required><br><br> -->
                <label for="news_description">Description:</label><br>
                <textarea id="news_description" name="description" rows="4" cols="50" style="width:100%;"></textarea><br><br>
                <label for="news_date">Date:</label><br>
                <input type="date" id="news_date" name="date" required style="width:100%;"><br><br>
                <button type="submit">Update News</button>
            </form>
        </div>
    </div>
</main>

<br><br>
<footer class="footer">
    <h3 class="dev">Developed By</h3>
    <p><a class="mylink" href="https://aryanrauniyar.com.np">&copy; Aryan Rauniyar</a></p>
</footer>

<script>
    // Get modal and close button
    var modal = document.getElementById("editNewsModal");
    var closeModal = document.getElementById("closeModal");

    // Open the modal when clicking "Edit" button
    function openEditModal(newsId, newsTitle, newsDescription, newsDate) {
        // Set the current news details in the modal
        document.getElementById("newsId").value = newsId;
        document.getElementById("news_title").value = newsTitle;
        document.getElementById("news_description").value = newsDescription;
        document.getElementById("news_date").value = newsDate;

        // Show the modal
        modal.style.display = "block";
    }

    // Close the modal when clicking on "X"
    closeModal.onclick = function() {
        modal.style.display = "none";
    }

    // Close the modal if clicked outside of the modal content
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Validation function for news title (no numbers allowed)
    function validateNewsTitle() {
        const newsTitle = document.getElementById("news_title").value;

        // Check if news title contains numbers
        const regex = /\d/; // Regular expression to check for numbers
        if (regex.test(newsTitle)) {
            alert("News title should not contain numbers.");
            return false; // Prevent form submission
        }

        return true; // Allow form submission
    }
</script>



<!-- <script src="script.js"></script> -->
</body>
</html>
