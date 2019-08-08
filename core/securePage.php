<?php	
session_start();

try{	
	if(!$_SESSION["loggedIn"]){
		session_start();
		session_destroy();
		header("Location: index.php");
		exit;	
	}	
} catch(Exception $e){
	session_start();
	session_destroy();
	header("Location: index.php");
	exit;
}
?>