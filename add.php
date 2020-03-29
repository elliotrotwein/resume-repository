<?php

# add.php adds a resume record for a given user

// Database library
require_once "pdo.php";
// See util.php for some of the functions used in this class
require_once "util.php";
session_start();

# CONTROLLER

	// Deny access if user is not logged in
	checkUserLoggedIn();

	// Redirect to index page if user clicks cancel
	if (isset($_POST['cancel'])){
		header("Location: index.php");
		exit();
	}

	if (isset($_POST['add'])){
		// Form validation:
		// 1. Require that all fields are populated
		if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email']) || empty($_POST['headline']) || empty($_POST['summary'])) {
			$_SESSION['add'] = "All fields are required";
                header("Location: add.php");
                return;	
		}
		// 2. Email must have an @ sign
		if (!strpos($_POST['email'],'@')) {
			$_SESSION['add'] = "Email must have an at-sign (@)";
			header("Location: add.php");
			return;
		}
	}

	// Validate position entries if present
	$msg = validatePos();
	if (is_string($msg)) {
		$_SESSION['error'] = $msg;
		header("Location: add.php");
		return;
	}

	// Validate education entries if present
	$msg = validateEdu();
	if (is_string($msg)) {
		$_SESSION['error'] = $msg;
		header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
		return;
	}

# MODEL
		  
	// Now that validation has been passed, add the new resume
	if (isset($_POST['add']) && !isset($_SESSION['add']) ){
        $stmt = $pdo->prepare('INSERT INTO Profile
	(user_id, first_name, last_name, email, headline, summary) VALUES 
	( :uid, :fn, :ln, :em, :hl, :su)');
         $stmt->execute(array(
		':uid' => $_SESSION['user_id'],
		':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
		':em' => $_POST['email'],
		':hl' => $_POST['headline'],
        ':su' => $_POST['summary'])
	);

	$profile_id = $pdo->lastInsertId();

	$rank = 1;
	for($i=1; $i<=9; $i++) {
		if ( ! isset($_POST['year'.$i]) ) continue;
		if ( ! isset($_POST['desc'.$i]) ) continue;

		$year = $_POST['year'.$i];
		$desc = $_POST['desc'.$i];
		$stmt = $pdo->prepare('INSERT INTO Position
			(profile_id, rank, year, description)
			VALUES ( :pid, :rank, :year, :desc)');

		$stmt->execute(array(
		':pid' => $profile_id,
		':rank' => $rank,
		':year' => $year,
		':desc' => $desc)
		);

		$rank++;
		}

		$rank = 1;
		for($i=1; $i<=9; $i++) {
			if ( ! isset($_POST['edu_year'.$i]) ) continue;
			if ( ! isset($_POST['edu_school'.$i]) ) continue;
			
			$year = $_POST['edu_year'.$i];
			$school = $_POST['edu_school'.$i];
			$institution_id = false;
			
			$stmt = $pdo->prepare('SELECT institution_id FROM
				 Institution WHERE name = :name'); 
			$stmt->execute(array(':name' => $school));
			$row = $stmt->fetch(PDO::FETCH_ASSOC);	 
			if ($row !== false) $institution_id = $row['institution_id'];
			
			// insert if there were no institutions
			if ( $institution_id === false ) {

				$stmt = $pdo->prepare('INSERT INTO Institution
				(name) VALUES (:name)');
				$stmt->execute(array(':name'=>$school));
				$institution_id = $pdo->lastInsertId();
			} 

			$stmt = $pdo->prepare('INSERT INTO Education
				(profile_id, rank, year, institution_id)
				VALUES ( :pid, :rank, :year, :id)');
	
			$stmt->execute(array(
			':pid' => $profile_id,
			':rank' => $rank,
			':year' => $year,
			':id' => $institution_id)
			);
	
			$rank++;
		}	

	$_SESSION['success'] = "Record added";
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
<?php
if ( isset($_SESSION['name']) ) {
    echo "<h1>Adding Profile for ".htmlentities($_SESSION['name'])."</h1>\n";
}
?>
<form method="post">
<?php
flashMessage();
?>
 <p><label for="first_name">First Name:</label>
 <input type="text" name="first_name"></p>
 <p><label for="last_name">Last_Name:</label>
 <input type="text" name="last_name"></p>
 <p><label for="email">Email:</label>
 <input type="text" name="email"></p>
 <p><label for="headline">Headline:</label></br>
 <input type="text" name="headline"></p>
 <p><label for="summary">Summary:</label></br>
 <textarea name="summary" rows="8" cols="80"></textarea>
 </p>
 <p> Education: <input type="submit" id="addEdu" value="+">
<div id="edu_fields">
</div>
</p>
 <p> Position: <input type="submit" id="addPos" value="+">
<div id="position_fields">
</div>
</p>
<input type="submit" name="add" value="Add">
<input type="submit" name="cancel" value="Cancel">
</form>
<script>
countPos = 0;
countEdu = 0;
// http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
$(document).ready(function(){
    window.console && console.log('Document ready called');
    $('#addPos').click(function(event){
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if ( countPos >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position "+countPos);
        $('#position_fields').append(
            '<div id="position'+countPos+'"> \
            <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
            <input type="button" value="-" \
                onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
            <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
            </div>');
    });

	$('#addEdu').click(function(event){
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if ( countEdu >= 9 ) {
            alert("Maximum of nine education entries exceeded");
            return;
        }
        countEdu++;
        window.console && console.log("Adding education " + countEdu);
        
		var source = $('#edu-template').html();
		$('#edu_fields').append(source.replace(/@COUNT@/g,countEdu));
		$('.school').autocomplete({
			source: "school.php"
		});		
    });

	$('.school').autocomplete({
		source: "school.php"
	});
});
</script>
<script id="edu-template" type="text">
	<div id ="edu@COUNT@">
		<p>Year: <input type="text" name="edu_year@COUNT@" value="" /> 
            <input type="button" value="-" onclick="$('#edu@COUNT@').remove();return false;"></p><br> 
				<p>School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" /> 
				</p>
	</div>
</script>
</div>
</body>
</html>
