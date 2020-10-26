<?php
	header('Content-type:text/json');
	session_start();
	include_once 'dbconnect.php';
	

	$game_id=$_GET['game_id'];
	
	if(!ctype_digit ($game_id)){
		echo 'Error.';
	}else{
		$game_id=mysqli_escape_string($con,$_GET['game_id']);
		$result=mysqli_query($con,"select * from wzq_room where game_id=".$game_id);
		$row=mysqli_fetch_assoc($result);
		
		echo json_encode($row);
		
	}

	

?>