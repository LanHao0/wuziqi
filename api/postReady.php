<?php
	header('Content-type:text/json');
	session_start();
	include_once 'dbconnect.php';
	
	
	$game_id=$_GET['game_id'];
	$user_id=$_GET['user_id'];
	
	if(!ctype_digit ($user_id)||!ctype_digit($game_id)){
		echo 'Error.';
	}elseif($user_id!=$_SESSION['user']){
		echo 'Error.';
		
	}else{
		$user_id=mysqli_escape_string($con,$_GET['user_id']);
		$game_id=mysqli_escape_string($con,$_GET['game_id']);
		
		$result=mysqli_query($con,"select user_black_id,user_white_id,game_status from wzq_room where game_id=".$game_id);
		$row=mysqli_fetch_assoc($result);
		if($row['game_status']!="wait"&&$row['game_status']!="lacking"){
			echo 'Surprising_Nothing!';
		}else{
			if($row['game_status']=="wait"){
				if($row['user_black_id']==$user_id){
					mysqli_query($con,"UPDATE wzq_room SET black_user_status=1 where game_id=".$game_id);
					echo 'me_black_ready';
				}else{
					mysqli_query($con,"UPDATE wzq_room SET white_user_status=1 where game_id=".$game_id);
					echo 'me_white_ready';
				}
				
				$result=mysqli_query($con,"select black_user_status,white_user_status from wzq_room where game_id=".$game_id);
				$row=mysqli_fetch_assoc($result);
				//重新查询数据库,如果黑白双方都为1,那么更新游戏状态为开始,时间戳同步,
				if($row['black_user_status']==1&&$row['white_user_status']==1){
					
					mysqli_query($con,"UPDATE wzq_room SET game_status='start',timestamp='".time()."',black_user_status=2,white_user_status=3 where game_id=".$game_id);
				}
				
				
			}
		}
		
		
		
	}

	

?>