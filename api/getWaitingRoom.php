<?php
	header('Content-type:text/json');
	session_start();
	include_once 'dbconnect.php';


	if(isset($_GET['game_id'])){
		//加入指定游戏
		//echo $_GET['game_id'];
		if(!ctype_digit($_GET['game_id'])){
			echo '{"msg":"Error"}';
		}else{
			$game_id=mysqli_real_escape_string($con,$_GET['game_id']);
			$res=mysqli_query($con,"select game_status,user_black_id from wzq_room where game_id=".$game_id);
			$one_row=mysqli_fetch_assoc($res);
			if($one_row['game_status']=="lacking"&&$one_row['user_black_id']!=$_SESSION['user']){
				//正确的游戏状态,缺少白棋
				mysqli_query($con,"UPDATE wzq_room SET game_status='wait',user_white_id=".$_SESSION['user']." where game_id=".$game_id);
				
				echo '{"msg":"OK"}';
			}else{
				//当前游戏状态不正确
				echo '{"msg":"当前房间无法加入"}';
			}
		}
		
	}else{
		//随机加入游戏
		$result=mysqli_query($con,"select game_id,user_black_id from wzq_room where game_status='lacking' order by game_id asc limit 1");

		$row=mysqli_fetch_assoc($result);
	/*	$arr_result=[];
		while($row=mysqli_fetch_assoc($result)){
			array_push($arr_result,$row);
		}
		以后真正随机加入房间
		echo json_encode($arr_result);
		*/
		if($row==null){
			echo '{"msg":"当前没有可加入房间"}';
		}elseif($row['user_black_id']==$_SESSION['user']){
			echo '{"msg":"当前没有可加入房间"}';
		}else{
			
			mysqli_query($con,"UPDATE wzq_room SET game_status='wait',user_white_id=".$_SESSION['user']." where game_id=".$row['game_id']);
				
			echo '{"msg":"OK","game_id":'.$row['game_id'].'}';
			
		}
		
	}
	

	
	
	//加入游戏后将游戏状态改为wait
	
	
	

?>