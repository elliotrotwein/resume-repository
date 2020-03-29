<?php 

// Database library
require_once "pdo.php";
// See util.php for some of the functions used in this class
require_once "util.php";
session_start();

# CONTROLLER

if ( isset($_POST['cancel'] ) ) {
    header("Location: index.php");
    return;
}

$salt = 'XyZzy12*_';
// Hash that corresponds to password 'php123'
// is '1a52e17fa899cf40fb04cfc42e6352f1';

// Check to see if we have some POST data, if we do process it
if ( isset($_POST['email']) && isset($_POST['pass']) ) {
	$check = hash('md5', $salt.$_POST['pass']);

	// Validation to protect against empty email or password 
	if ( strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1 ) {
        $_SESSION['error'] = "User name and password are required";
	    error_log("Login fail ".$_POST['email']." $check");
	    header("Location: login.php");
	    return;
	}  

	// check if there is an '@' sign
	$containsAtSign = false;
	for($i=0;$i<strlen($_POST['email']);$i++){
		if ($_POST['email'][$i] === '@') {
			$containsAtSign = true;
     	}
	}

	// Validation to protect against email without '@' sign	
    if (!$containsAtSign) {
		$_SESSION['error'] = "Email must have an at-sign (@)";
		error_log("Login fail ".$_POST['email']." $check");
		header("Location: login.php");
		return;
	}

	# MODEL

	$stmt = $pdo->prepare('SELECT user_id, name FROM users
	WHERE email = :em AND password = :pw');
	$stmt->execute(array( ':em' => $_POST['email'], ':pw' => $check));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	# CONTROLLER

	// Redirect user to index.php page if user name is valid and passoword is correct
	if ($row) {
		unset($_SESSION['name']);
		$_SESSION['name'] = $row['name'];
		$_SESSION['user_id'] = $row['user_id'];
		error_log("Login success ".$_SESSION['email']);
		header("Location: index.php");
		return;

	// Notify the user if email is valid but they've input an incorrect password	
	} else {
	    $_SESSION['error'] = "Incorrect password";
        error_log("Login fail ".$_POST['email']." $check");
	    header("Location: login.php");
            return;
     }
}
?>
<!-- VIEW -->
<!DOCTYPE html>
<html>
<head>
<?php require_once "bootstrap.php"; ?>
<title>Elliot Rotwein's Resume Registry</title>
</head>
<body>
<div class="container">
<h1>Please log in</h1>
<?php
// Flash error on screen in red if the user has not passed validation
flashMessage();
?>
<form method="POST">
<label>User Name</label>
<input type="text" name="email" id="id_email"><br/>
<label>Password</label>
<input type="text" name="pass" id="id_pass"><br/>
<input type="submit" onclick="return doValidate();" value="Log In">
<input type="submit" name="cancel" value="Cancel">
</form>
<script>
function doValidate() {
  console.log('Validating...');
  try {
	addr = document.getElementById('id_email').value;
    pw = document.getElementById('id_pass').value;
    console.log("Validating pw="+pw);
	if (pw == null || pw == "" || addr == null || addr == "") {
      alert("Both fields must be filled out");
      return false;
    }
	if (addr.indexOf('@') == -1) {
		alert("Invalid email address");
		return false;
	}
	return true;
	} catch(e) {
	  return false;
	}
	  return false;
	}
</script>
</div>
</body>
