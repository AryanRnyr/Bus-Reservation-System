<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if($_SESSION["s_role"]=="customer"){
	header('Location: home/');
    }
}else{
    header("Location: ../login");
}

if (isset($_SESSION['message'])) {
    echo "<p style='color: green;'>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']); // Clear the message after displaying
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
$sql = "SELECT id, username, firstname, lastname, phoneno, email, status, user_role FROM users";
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
<style>
    #editModal {
    display: none; /* Initially hidden */
}
.modal.show {
    display: block; /* Show the modal when the "show" class is added */
}

</style>
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
                 <!-- <li><a class="navbreak" href="list_tempbookings.php" >Temporary Bookings</a></li> -->
                 <li ><a href="profile.php" class="profile-btn" style="margin-left: 100px;"><i class="fas fa-user-circle"></i> <?=$_SESSION['firstname']?></a></li>
                <li><a href="logout.php" class="login-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="listtop">
    <h2>List of Users</h2>
    <!-- <form action="add_bus.php" method="post" name="add"> -->
    <div class="filter-options">
    <label>
        <input type="radio" name="filter" value="all" checked> All Users
    </label>
    <label>
        <input type="radio" name="filter" value="admin"> Admin
    </label>
    <label>
        <input type="radio" name="filter" value="customer"> Customer
    </label>
</div>

    <button name="add"  type="button" onclick="window.location.href='add_user.php'">Add New <i class="fa fa-plus"></i></button>
<!-- </form> -->
</div>
<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Username</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Phone No.</th>
            <th>E-mail</th>
            <th>Role</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php $index = 1; ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <!-- Column 1: Serial Number -->
                    <td><?= $index++; ?></td>

                    <!-- Column 2: Bus Name -->
                    <td><?= htmlspecialchars($row["username"]); ?></td>

                    <!-- Column 3: Bus Number -->
                    <td><?= htmlspecialchars($row["firstname"]); ?></td>

                    <td><?= htmlspecialchars($row["lastname"]); ?></td>

                    <td><?= htmlspecialchars($row["phoneno"]); ?></td>

                    <td><?= htmlspecialchars($row["email"]); ?></td>

                   

                    <td><?= htmlspecialchars($row["user_role"]); ?></td>

                    <!-- <td class="<?= $row["status"] ? 'status-active' : 'status-inactive'  ?>">
                        <?= $row["status"] ? 'Active' : 'Inactive' ?>
                    </td> -->
                    <td class="<?= $row["status"] == 1 ? 'status-active' : ($row["status"] == 2 ? 'status-deactivated' : 'status-inactive') ?>">
                        <?= $row["status"] == 1 ? 'Active' : ($row["status"] == 2 ? 'Deactivated' : 'Inactive') ?>
                    </td>


                   
                    <!-- <td class="<?= $row["status"] ? 'status-active' : 'status-inactive' ?>">
                        <?= $row["status"] ? 'Active' : 'Inactive' ?>
                    </td> -->

                   
                    <td>
                        <?php if($row["user_role"]=="customer"):?>
                        <a class="action-btn-edit" onclick="openEditModal(<?= $row['id']; ?>, '<?= htmlspecialchars($row['username']); ?>', '<?= htmlspecialchars($row['firstname']); ?>', '<?= htmlspecialchars($row['lastname']); ?>', '<?= htmlspecialchars($row['phoneno']); ?>', '<?= htmlspecialchars($row['email']); ?>', '<?= htmlspecialchars($row['user_role']); ?>', '<?= htmlspecialchars($row['status']); ?>')">Edit</a>

                        <a href="delete_user.php?id=<?= $row["id"]; ?>" class="action-btn-delete" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                            <?php else:?>
                                <?php endif;?>
                        
                        
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No Users found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


<!-- Edit User Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2>Edit User Details</h2>
        <form id="editUserForm" method="post" action="edit_user.php">
            <input type="hidden" id="userId" name="id">
            
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required disabled>
            <label style="font-weight:lighter; font-size: 12px; color:grey;">Username cannot be changed!</label><br><br>

            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" required><br><br>
            
            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" required><br><br>
            
            <label for="phoneno">Phone No.:</label>
            <input type="text" id="phoneno" name="phoneno" required><br><br>
            
            <label for="email">E-mail:</label>
            <input type="text" id="email" name="email" required disabled>
            <label style="font-weight:lighter; font-size: 12px; color:grey;">E-mail cannot be changed!</label><br><br>
            
             <label for="user_role">Role:</label>
           <!-- <input type="text" id="user_role" name="user_role" required><br><br> -->

            <select class="form-control" id="user_role" name="user_role" required>
        <option value="admin" <?php echo ($_SESSION['s_role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
        <option value="customer" <?php echo ($_SESSION['s_role'] == 'customer') ? 'selected' : ''; ?>>Customer</option>
    </select><br><br>

    <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="1">Active</option>
                <option value="2">Deactivate</option>
            </select><br><br>

            <button type="submit">Update User</button>
        </form>
    </div>
</div>




<?php
// Close the database connection
$conn->close();
?>
    </main>
<br><br>
      
<footer class="footer">
    <h3 class="dev">Developed By</h3>
    <p><a class="mylink" href="https://aryanrauniyar.com.np">&copy; Aryan Rauniyar</a></p>
</footer>
<script>
    // Get the modal and close button elements
    var modal = document.getElementById("editModal");
    var closeModal = document.getElementById("closeModal");

    // Open the modal when clicking the "Edit" button
    function openEditModal(userId, username, firstname, lastname, phoneno, email, userRole, status) {
        console.log("openEditModal called with:", { userId, username, firstname, lastname, phoneno, email, userRole, status });

        // Set the current user details in the modal form
        document.getElementById("userId").value = userId;
        document.getElementById("username").value = username;
        document.getElementById("firstname").value = firstname;
        document.getElementById("lastname").value = lastname;
        document.getElementById("phoneno").value = phoneno;
        document.getElementById("email").value = email;
        document.getElementById("user_role").value = userRole;
        document.getElementById("status").value = status;

        // Show the modal
        modal.style.display = "block";
    }

    // Close the modal when clicking on the "X" button
    closeModal.onclick = function() {
        modal.style.display = "none";
    }

    // Close the modal if clicked outside of the modal content
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
    const filterRadios = document.querySelectorAll('input[name="filter"]');
    const rows = document.querySelectorAll('table tbody tr');

    // Function to filter rows based on user role
    function filterUsers() {
        const selectedFilter = document.querySelector('input[name="filter"]:checked').value;

        rows.forEach(row => {
            const userRole = row.cells[6].textContent.toLowerCase(); // Assuming the role is in the 7th column
            if (selectedFilter === 'all' || userRole === selectedFilter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Add event listener to radio buttons
    filterRadios.forEach(radio => {
        radio.addEventListener('change', filterUsers);
    });

    // Initial filter when the page loads
    filterUsers();
});

</script>

</body>
</html>
