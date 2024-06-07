<?php

include "../classes/User.php";

//create an obj
$user = new User;

//call the method

$user->login($_POST);

// $_POST :holds the data from the form views > register.php
/*
    $_POST['username];
    $_POST['password'];
*/    

?>