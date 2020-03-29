<?php

# index.php queries the db for all the resume entries and displays the
# results on screen. If the user is logged in, index.php will display
# an option to add a new resume or delete/edit an existing one. If
# there are no resumes in the database, index.php will display a message
# on screen.

// Database library
require_once "pdo.php";
session_start();
// See util.php for some of the functions used in this class
require_once "util.php";

# CONTROLLER

// Check to see if user is logged in
// $_SESSION['name'] is created in login.php once the user logs in successfully
$loggedIn = isset($_SESSION['name']);

# MODEL

// Query for data
$stmt = $pdo->query("SELECT * FROM Profile");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

# VIEW

?>
<!DOCTYPE html>
<html>
  <head>
	<title>Elliot Rotwein's Resume Registry</title>
  </head>
  <body>
	<h3>Elliot Rotwein's Resume Registry</h3>
  <p>
    <?php
      flashMessage();
    ?>
  </p> 
<?php
  if (!$loggedIn) {
    echo "<a href='login.php'>Please log in</a>";
    $addNewEntry="";
  } else {
    $addNewEntry="Add New Entry";
    echo "<a href='logout.php'>Logout</a>";
  }
  if ($rows) { 
    // if there are resumes in the database	  
    ?>  
    <table border="1">
      <tr>
        <th>Name</th>
        <th>Headline</th>
        <th>Action</th>
      </tr>
	<?php for ($i=0;$i<count($rows);$i++){ ?>
	<tr>
    <td>
    <a href="view.php?profile_id=<?php echo htmlentities($rows[$i]['profile_id']);?>"><?php echo htmlentities($rows[$i]['first_name'])." ".htmlentities($rows[$i]['last_name']);?></a>
    </td>
    <td><?php echo htmlentities($rows[$i]['headline']); ?></td>
	  <td><a href="edit.php?profile_id=<?php echo htmlentities($rows[$i]['profile_id']);?>">Edit</a>/<a href="delete.php?profile_id=<?php echo htmlentities($rows[$i]['profile_id']);?>">Delete</a></td>
	</tr>
  <?php }
  echo "</table>";
  } else {
    echo "<p>There are currently no resumes in the datbase.</p>";
  } 

  echo "<p><a href='add.php'>$addNewEntry</a></p>";
  ?>
  </body>
</html>
