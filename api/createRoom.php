<?php
	header('Content-type:text/json');
	session_start();
	include_once 'dbconnect.php';
	
	$user_id=$_SESSION['user'];
	//创建一个房间
	//要求输入验证码
	$recaptcha=$_GET['captcha'];

	if($recaptcha !=$_SESSION['captcha']['code']){
		echo '{"msg":"验证码错误"}';
	}else{
		
		$recaptcha=mysqli_real_escape_string($con,$_GET['captcha']);
		mysqli_query($con,"Insert into wzq_room(user_black_id,user_white_id,game_status,black_user_status,white_user_status) values(".$user_id.",0,'lacking',0,0);");
		$game_id=mysqli_insert_id($con);
		//echo json_encode($game_id);
		
		$result=mysqli_query($con,"select game_id from wzq_room where game_id=".$game_id);
		$row=mysqli_fetch_assoc($result);
		
		
		echo json_encode($row);
		include("../../captcha/simple-php-captcha.php");
		$_SESSION['captcha'] = simple_php_captcha();
	}
	
	

?>