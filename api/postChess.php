<?php
	header('Content-type:text/json');
	session_start();
	include_once 'dbconnect.php';
	

	//config 等待时间 5秒是留给误差
	$wait_time=125;

	$post_time=time();
	
	$game_id=$_GET['game_id'];
	$user_id=$_GET['user_id'];
	$location_x=$_GET['x'];
	$location_y=$_GET['y'];
	

	if(!ctype_digit ($user_id)||!ctype_digit($game_id)||!ctype_digit($location_x)||!ctype_digit($location_y)){
		echo '{"msg":"Error"}';
	}elseif($user_id!=$_SESSION['user']){
		echo '{"msg":"Error"}';
		
	}elseif($location_x>15||$location_x<0||$location_y>15||$location_y<0){
		echo '{"msg":"Error"}';
	}else{
		$user_id=mysqli_escape_string($con,$_GET['user_id']);
		$game_id=mysqli_escape_string($con,$_GET['game_id']);
		$location_x=mysqli_escape_string($con,$_GET['x']);
		$location_y=mysqli_escape_string($con,$_GET['y']);
		$result=mysqli_query($con,"select * from wzq_room where game_id=".$game_id);
		$row=mysqli_fetch_assoc($result);
		
		$my_turn=false;
		$black_user=$row['user_black_id'];
		if($user_id==$black_user){
			//我是黑棋
			//检测黑棋状态
			if($row['black_user_status']==2){
				$my_turn=true;
			}else{
				$my_turn=false;
			}
		}else{
			//我是白棋
			if($row['white_user_status']==2){
				$my_turn=true;
			}else{
				$my_turn=false;
			}
		}
		//游戏状态是否正确
		if($row['game_status']=="start"){
			//是否超时
			if($post_time-$row['timestamp']<=$wait_time){
				//是否为当前用户回合
				if($my_turn){

					//当前位置是否有棋子
					//将数据库中棋盘数据附到这里;
					$chessBoard=[];
					for ($ii = -1; $ii <= 16; $ii++) {
						$chessBoard[$ii]=[];
						for ( $jj = -1; $jj <= 16; $jj++) {
							$chessBoard[$ii][$jj]=0;
						}
					}

					$arr_chess=explode("#",$row['x_y']);

					for($i=0;$i<count($arr_chess);$i++){
						$arr=explode(",",$arr_chess[$i]);
						if($i%2==0){
							$chessBoard[$arr[0]][$arr[1]]=-1;
						}else{
							$chessBoard[$arr[0]][$arr[1]]=1;
						}

					}
					//echo json_encode($chessBoard);
					//echo $chessBoard[$location_x][$location_y];
					//当前位置是否有棋子
					if($chessBoard[$location_x][$location_y]==0){
						
						if($row['x_y']!=""){
							$tmp1=$row['x_y']."#".$location_x.",".$location_y;
						}else{
							$tmp1=$location_x.",".$location_y;
						}
						
						
						
						//更新当前用户状态为3,另一用户状态为2,更新时间
						$nownow=0;
						
						if($user_id==$black_user){
							//我是黑棋
							$nownow=-1;
							$chessBoard[$location_x][$location_y]=-1;
							mysqli_query($con,"UPDATE wzq_room SET black_user_status=3,white_user_status=2,timestamp='".time()."',x_y='".$tmp1."' where game_id=".$game_id);
						}else{
							//我是白棋
							$chessBoard[$location_x][$location_y]==1;
							$nownow=1;
							mysqli_query($con,"UPDATE wzq_room SET black_user_status=2,white_user_status=3,timestamp='".time()."',x_y='".$tmp1."' where game_id=".$game_id);
						}
						
						$status_winner_exist=false;
						$tmp_store_winner="black";
						//是否赢了,跟js的判断一样,当棋子大于8的时候开始判断
						if(count($arr_chess)>8){
							
							$count1=0;
							$count2=0;
							$count3=0;
							$count4=0;
							for($cc=$location_x;$cc>=0;$cc--){
								if($chessBoard[$cc][$location_y]!=$nownow){
									break;
								}
								$count1++;
							}//左
							
							
							for($dd=$location_x+1;$dd<=15;$dd++){
								if($chessBoard[$dd][$location_y]!=$nownow){
									break;
								}
								$count1++;
							}//右
							
							for($ee=$location_y;$ee>=0;$ee--){
								if($chessBoard[$location_x][$ee]!=$nownow){
									break;
								}
								$count2++;
							}//上
							for($ff=$location_y+1;$ff<=15;$ff++){
								if($chessBoard[$location_x][$ff]!=$nownow){
									break;
								}
								$count2++;
							}//下
							
							
							for($gg=$location_x,$hh=$location_y;$gg>=0&&$hh>=0;$gg--,$hh--){
								if($chessBoard[$gg][$hh]!=$nownow){
									break;
								}
								$count3++;

							}//左上
							for($jj=$location_x+1,$kk=$location_y+1;$jj<=15&&$kk<=15;$jj++,$kk++){
								if($chessBoard[$jj][$kk]!=$nownow){
									break;
								}
								$count3++;
							}//右下
							
							for($ll=$location_x,$mm=$location_y;$ll>=0&&$mm<=15;$ll++,$mm--){
								if($chessBoard[$ll][$mm]!=$nownow){
									break;
								}
								$count4++;
							}


							for($nn=$location_x-1,$oo=$location_y+1;$nn<=15&&$oo>=0;$nn--,$oo++){
								if($chessBoard[$nn][$oo]!=$nownow){
									break;
								}
								$count4++;
							}
							
							//todo,count4有点问题,需要6个棋子..不知道为什么
							if($count1>=5||$count2>=5||$count3>=5||$count4>=4){
								$status_winner_exist=true;
								
								if($nownow==-1){
									//$tmp_store_winner="black";
									//上面已经默认了所以不用管
								}else if($nownow==1){
									$tmp_store_winner="white";
								}
								//echo 'winner';
							}
						}else if(count($arr_chess)==255&&!$status_winner_exist){
							//平局
							$tmp_sql_about_winner="UPDATE wzq_room SET game_status='end',black_user_status=5,white_user_status=5 where game_id=".$game_id.";
								
							UPDATE wzq_user_info SET total=total+1,draw=draw+1 where user_id=".$row['user_black_id'].";
								
							UPDATE wzq_user_info SET total=total+1,draw=draw+1 where user_id=".$row['user_white_id'];
							
							mysqli_multi_query($con,$tmp_sql_about_winner);
							echo '{"msg":"winner","which_one":"draw"}';
						}
						
						if($status_winner_exist){
							
							
							//更新游戏状态为结束,所有玩家局数+1,胜负分配一下+1;
							if($tmp_store_winner=="black"){
								//赢家为黑
								$tmp_sql_about_winner="UPDATE wzq_room SET game_status='end',black_user_status=5 where game_id=".$game_id.";
								
								UPDATE wzq_user_info SET total=total+1,wins=wins+1 where user_id=".$row['user_black_id'].";
								
								UPDATE wzq_user_info SET total=total+1,loses=loses+1 where user_id=".$row['user_white_id'];
								
							}else{
								//赢家为白
								$tmp_sql_about_winner="UPDATE wzq_room SET game_status='end',white_user_status=5 where game_id=".$game_id.";
								
								UPDATE wzq_user_info SET total=total+1,wins=wins+1 where user_id=".$row['user_white_id'].";
								
								UPDATE wzq_user_info SET total=total+1,loses=loses+1 where user_id=".$row['user_black_id'];
							}
							
							mysqli_multi_query($con,$tmp_sql_about_winner);
							
							echo '{"msg":"winner","which_one":"'.$tmp_store_winner.'"}';
						}else{
							echo '{"msg":"OK"}';
						}
						
						
						

					}else{
						echo '{"msg":"已经有棋子了"}';
					}


				}else{
					//虽然不是我的回合,但是对手超时,则自动结束游戏,只在对战局数上增加,而不增加成绩!
					if($post_time-$row['timestamp']>$wait_time){
						
						$tmpsql="UPDATE wzq_room SET game_status='end' where game_id=".$game_id.";UPDATE wzq_user_info SET total=total+1 where user_id in (".$row['user_black_id'].",".$row['user_white_id'].");";

						mysqli_multi_query($con,$tmpsql);
						echo '{"msg":"对手超时"}';
						
					}else{
						echo '{"msg":"不是你的回合"}';
					}
					
				}

				
			}else{
				
				$tmpsql="UPDATE wzq_room SET game_status='end' where game_id=".$game_id.";UPDATE wzq_user_info SET total=total+1 where user_id in (".$row['user_black_id'].",".$row['user_white_id'].");";
				
				mysqli_multi_query($con,$tmpsql);
				
				echo '{"msg":"超时"}';
			}
		}else{
			echo '{"msg":"游戏还没有开始"}';
		}
		

	}

	

?>