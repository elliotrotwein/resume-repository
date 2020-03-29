<?php

# edit.php pre-populates fields with an existing resume and then updates 
# the resume based on user input. The same form validation as add.php 
# still applies.

// Database library
require_once "pdo.php";
// See util.php for some of the functions used in this class
require_once "util.php";
session_start();

#CONTROLLER

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
	
# CONTROLLER
	
	if (isset($_POST['save'])){
			// Form validation:
			// 1. Require that all fields are populated
		   if (empty($_POST['first_name']) || empty($_POST['last_name']) 
		   || empty($_POST['email']) || empty($_POST['headline']) 
		   || empty($_POST['summary'])) {
                  $_SESSION['save'] = "All fields are required";
                  header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
                  return;
				}
			// 2. Email must have an @ sign
			if (!strpos($_POST['email'],'@')) {
				$_SESSION['save'] = "Email must have an at-sign (@)";
				header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
				return;
				 }
				 
		// Validate position entries if present
		$msg = validatePos();
		if (is_string($msg)) {
			$_SESSION['error'] = $msg;
			header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
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

	// Update resume record
	$stmt = $pdo->prepare('UPDATE Profile SET
	user_id = :uid, first_name = :fn, last_name = :ln, email = :em, 
	headline = :hl, summary = :su WHERE profile_id = :id'); 
         $stmt->execute(array(
		':id' => $_REQUEST['profile_id'],
		':uid' => $_SESSION['user_id'],
		':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
		':em' => $_POST['email'],
		':hl' => $_POST['headline'],
        ':su' => $_POST['summary'])
	);

	// Clear out the old position entries
	$stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
	$stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

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
		':pid' => $_REQUEST['profile_id'],
		':rank' => $rank,
		':year' => $year,
		':desc' => $desc)
		);

		$rank++;
		}

    	$_SESSION['success'] = "Profile updated";
        header("Location: index.php");
        return;	

	// Clear out the old education entries
	$stmt = $pdo->prepare('DELETE FROM Education WHERE profile_id=:pid');
	$stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

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
			':pid' => $_REQUEST['profile_id'],
			':rank' => $rank,
			':year' => $year,
			':id' => $institution_id)
			);
	
			$rank++;
		}	
	}
	// Load up the position rows
	$positions = loadPos($pdo, $_REQUEST['profile_id']);
	// Load up the education rows
	$educations = loadEdu($pdo, $_REQUEST['profile_id']);
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
  <h1>Editing Profile for <?php echo $_SESSION['name'];?></h1>
<form method="post">
<?php
flashMessage();
?>
 <p><label for="first_name">First Name:</label>
 <input type="text" name="first_name" value="<?php echo htmlentities($first_name)?>"></p>
 <p><label for="last_name">Last_Name:</label>
 <input type="text" name="last_name" value="<?php echo htmlentities($last_name)?>"></p>
 <p><label for="email">Email:</label>
 <input type="text" name="email" value="<?php echo htmlentities($email)?>"></p>
 <p><label for="headline">Headline:</label>
 <input type="text" name="headline" size="80" value="<?php echo htmlentities($headline)?>"></p>
 <p><label for="summary">Summary:</label>
 <textarea name="summary" rows="8" cols="80"><?php echo htmlentities($summary)?></textarea>
 </p>
 <?php 
 $countEdu=0;
 echo('<p> Education: <input type="submit" id="addEdu" value="+">'."\n");
 echo('<div id="edu_fields">'."\n");
 if (count($educations) > 0) {
		foreach($educations as $education ) {
			$countEdu++;
			echo '<div id="edu'.$countEdu.'">';
			echo 
			'<p>Year: <input type="text" name="edu_year'.$countEdu.'" value="'.$education['year'].'"/>
			<input type="button" value="-" onclick="$(\'#edu'.$countEdu.'\').remove();return false; /></p>
			<p>School: <input type="text" size="80" name="edu_school'.$countEdu.'" class="school" 
			value="'.htmlentities($education['name']).'" />';
			echo "\n</div>\n";
	 }
	}
echo("</div></p>\n");

 $pos=0;
 echo('<p> Position: <input type="submit" id="addPos" value="+">'."\n");
 echo('<div id="position_fields">'."\n");
 foreach($positions as $position ) {
	 $pos++;
	 echo('<div id="position'.$pos.'">'."\n");
	 echo('<p>Year: <input type="text" name="year'.$pos.'"');
	 echo('value="'.$position['year'].'"/>'."\n");
	 echo('<input type="button" value="-"');
	 echo('onclick="$(\'#position'.$pos.'\').remove();return false;">'."\n");
	 echo("</p>\n");
	 echo('<textarea name="desc'.$pos.'" rows="8" cols="80">'."\n");
	 echo(htmlentities($position['description'])."\n");
	 echo("\n</textarea>\n</div>\n");
	}
	echo("</div></p>\n");
?>
<p>
<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>

<script>
countPos = <?= $pos ?>;
countEdu = <?= $pos ?>;

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
