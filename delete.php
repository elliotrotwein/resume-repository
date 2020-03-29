<?php

# delete.php deletes a specific resume

// Database library
require_once "pdo.php";
// See util.php for some of the functions used in this class
require_once "util.php";
session_start();

# CONTROLLER

	// Deny access if user is not logged in
	checkUserLoggedIn();

	if (isset($_POST['cancel'])){
		header("Location: index.php");
		exit();
	}

# MODEL

	// Query for pre-populated data
	$stmt = $pdo->query("SELECT * FROM Profile");
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	for ($i=0;$i<count($rows);$i++){
	  if ($rows[$i]['profile_id'] == $_REQUEST['profile_id']){
	    $user_id = $rows[$i]['user_id'];
	    $first_name = $rows[$i]['first_name'];
	    $last_name = $rows[$i]['last_name'];
		$email = $rows[$i]['email'];
		$headline = $rows[$i]['headline'];
		$summary = $rows[$i]['summary'];
	    }
	}
	// Delete the resume 
	if (isset($_POST['delete'])) {
          $stmt = $pdo->prepare('DELETE FROM Profile WHERE profile_id = :id'); 
	  	  $stmt->bindParam(':id', $_REQUEST['profile_id'], PDO::PARAM_INT);
  	  	  $stmt->execute();
          $_SESSION['success'] = "Record deleted";
          header("Location: index.php");
          return;	
	}
?>
<!-- VIEW -->
<!DOCTYPE html>
<html>
  <head>
	<title>Elliot Rotwein's Resume Registry</title>
   <?php require_once "bootstrap.php"; ?>
  </head>
  <body>
<div class="container">
<h3>Deleting Profile</h3>
<p><?php echo "First Name: ".htmlentities($first_name)."<br>"."Last Name: ".htmlentities($last_name);?></p>
<form method="post">
<input type="submit" name="delete" value="Delete">
<input type="submit" name="cancel" value="Cancel">
</form>
</div>
</body>
</html>
