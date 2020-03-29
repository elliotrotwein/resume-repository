<?php
# util.php
# Utility class of commonly used code
// Flash messages

// Deny access unless user is logged in
// Used in add.php, delete.php, edit.php, view.php
function checkUserLoggedIn() {
    if (!isset($_SESSION['name'])) {
		die("ACCESS DENIED");
	}
}

// Checks if email has an @ sign
// Used in 
function emailValidation () {
    if (!strpos($_POST['email'],'@')) {
        $_SESSION['add'] = "Email must have an at-sign (@)";
        header("Location: add.php");
        return;
    }
}

// Flashes success or error message based on form input
// Used in add.php, edit.php, login.php, index.php
function flashMessage() {
    if (isset($_SESSION['add'])){
        echo('<p style="color: red;">'.htmlentities($_SESSION['add'])."</p>");
        unset($_SESSION['add']);
    }
    if (isset($_SESSION['success'])) {
        echo('<p style="color: green;">'.htmlentities($_SESSION['success'])."</p>\n");
    unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo('<p style="color:red;">'.htmlentities($_SESSION['error'])."</p>\n");
        unset($_SESSION['error']);
    }
}

// Validates that all fields in the Position fields (year and description)
// are not empty and the year is numeric
// Used in add.php, edit.php
function validatePos() {
    for($i=1; $i<=9; $i++) {
      if ( ! isset($_POST['year'.$i]) ) continue;
      if ( ! isset($_POST['desc'.$i]) ) continue;
  
      $year = $_POST['year'.$i];
      $desc = $_POST['desc'.$i];
  
      if ( strlen($year) == 0 || strlen($desc) == 0 ) {
        return "All fields are required";
      }
  
      if ( ! is_numeric($year) ) {
        return "Position year must be numeric";
      }
    }
    return true;
  }

  // Validates that all fields in the Education fields (year and School)
// are not empty and the year is numeric
// Used in add.php, edit.php
function validateEdu() {
  for($i=1; $i<=9; $i++) {
    if ( ! isset($_POST['edu_year'.$i]) ) continue;
			if ( ! isset($_POST['edu_school'.$i]) ) continue;

    $year = $_POST['edu_year'.$i];
    $school = $_POST['edu_school'.$i];

    if ( strlen($year) == 0 || strlen($school) == 0 ) {
      return "All fields are required";
    }

    if ( ! is_numeric($year) ) {
      return "Education year must be numeric";
    }
  }
  return true;
}

// Queries the db for positions for a given profile
// Used in edit.php
function loadPos($pdo, $profile_id) {
    $stmt = $pdo->prepare('SELECT * FROM Position 
    WHERE profile_id = :prof ORDER BY rank');
    $stmt->execute(array( ':prof' => $profile_id));
    $positions = array();
    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
        $positions[] = $row;
    }
    return $positions;
}

// Queries the db for education for a given profile
// Used in edit.php
function loadEdu($pdo, $profile_id) {
  $stmt = $pdo->prepare('SELECT year,name FROM Education
    JOIN Institution 
    ON Education.institution_id = Institution.institution_id
    WHERE profile_id = :prof ORDER BY rank');
  $stmt->execute(array( ':prof' => $profile_id));
  $educations = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $educations;
}