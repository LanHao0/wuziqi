<?php
	header('Content-type:text/json');
	session_start();
	include_once 'dbconnect.php';
	

	$user_id=$_GET['user_id'];
	
	if(!ctype_digit ($user_id)){
		echo 'Error.';
	}else{
		$game_id=mysqli_escape_string($con,$_GET['user_id']);
		$result=mysqli_query($con,"select * from wzq_user_info where user_id=".$user_id);
		$row=mysqli_fetch_assoc($result);
		
		echo json_encode($row);
		
	}

	

?>