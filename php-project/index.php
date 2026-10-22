<?php
// WHY: session_start() must run before ANY output (even <!DOCTYPE>), because it sends a
// cookie header, and PHP can't send headers once HTML has already gone to the browser.
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <title>Discuss Project</title>
   <?php include('./client/commonFiles.php') ?>
</head>

<body>
  <?php
include('./client/header.php');

// WHY isset(): when nobody is logged in, $_SESSION['user'] doesn't exist, so reading
// $_SESSION['user']['username'] gives a warning. isset() checks safely, without warnings.
if (isset($_GET['signup']) && !isset($_SESSION['user'])) {
   include('./client/signup.php');

} else if (isset($_GET['login']) && !isset($_SESSION['user'])) {
   include('./client/login.php');

} else if (isset($_GET['ask'])) {
   // WHY: a question is saved with the asker's user id, so only logged-in users may ask.
   // Guests see the login form instead.
   if (isset($_SESSION['user'])) {
      include('./client/ask.php');
   } else {
      include('./client/login.php');
   }

} else if (isset($_GET['q-id'])) {
   // WHY (int): ids are always numbers. Casting turns anything else (like "1 OR 1=1") into a plain number.
   $qid = (int) $_GET['q-id'];
   include('./client/question-details.php');

} else if (isset($_GET['c-id'])) {
   $cid = (int) $_GET['c-id'];
   include('./client/questions.php');

} else if (isset($_GET['u-id'])) {
   $uid = (int) $_GET['u-id'];
   include('./client/questions.php');

} else if (isset($_GET['latest'])) {
   include('./client/questions.php');

} else if (isset($_GET['search'])) {
   $search = $_GET['search'];
   include('./client/questions.php');

} else {
   include('./client/questions.php');
}
?>
</body>

</html>
