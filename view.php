<?php

# view.php displayed the selected resume. Once user is finished viewing
# the resume, they can select the 'Done' button to go back to index.php

// Database library
require_once "pdo.php";
// See util.php for some of the functions used in this class
require_once "util.php";
session_start();

# CONTROLLER

	// Deny access if user is not logged in
	checkUserLoggedIn();

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
	
	
	// Load up the position rows
	$positions = loadPos($pdo, $_REQUEST['profile_id']);
	// Load up the education rows
	$schools = loadEdu($pdo, $_REQUEST['profile_id']);
    
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
  <h1>Profile Information</h1>
  
 <p>First Name:
 <?php echo $first_name?></p>
 <p>Last Name:
 <?php echo $last_name?></p>
 <p>Email:
 <?php echo $email?></p>
 <p>Headline:<br>
 <?php echo $headline?>
 </p>
 <p>Summary:<br>
 <?php echo $summary?></p>
 <p>Education:
 <?php
	if($schools) {
		echo("<ul>");
		foreach($schools as $school) {
			echo("<li>");
			echo $school['year'].": ".$school['name'];
			echo("</li>");
		}
		echo("</ul>");
	}
?>	
</p>
<p>Postion:
<?php
	if($positions) {
		echo("<ul>");
		foreach($positions as $position) {
			echo("<li>");
			echo $position['year'].": ".$position['description'];
			echo("</li>");
		}
		echo("</ul>");
	}
 ?>
 </p>
 <a href="index.php">Done</a>
</div>
</body>
</html>