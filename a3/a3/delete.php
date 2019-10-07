<?php
require 'includes/functions.php';

session_start();
if(!isset($_SESSION['loggedin']))
{
    header('Location: index.php');
    exit();
}

// if this was an actual database, you'd be validating the ID
deletePost($_GET['id'], $_SESSION['username']);
//if it is the admin,delete without username check
if($_SESSION['admin']==true){
    admindeletePost($_GET['id']);
}
header('Location: posts.php');
exit();