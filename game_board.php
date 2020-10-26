<?php

session_start();
include_once './api/dbconnect.php';
if(!isset($_SESSION['user']))
{
 header("Location: ../denglu.php");
}


//上一个进入房间界面,post过来的游戏房间id
//todo 改成POST,以及如果没有接收到post的异常处理
$game_id=$_GET["game_id"];
if(!ctype_digit($game_id)){
	echo 'Error.';
}else{
	$game_id=mysqli_escape_string($con,$game_id);

	$sql=mysqli_query($con,"select * from wzq_room where game_id=".$game_id);
	$game_info=mysqli_fetch_assoc($sql);
}



setcookie('game_id',$game_id);
setcookie('my_id',$_SESSION['user']);
setcookie('user_black_id',$game_info['user_black_id']);
setcookie('user_white_id',$game_info['user_white_id']);


?>
<html>
<head>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-82172506-4', 'auto');
  ga('send', 'pageview');

</script>
	<link rel="stylesheet" href="https://www.lanhaoo.club/css/bootstrap.min.css">
	<script src="https://www.lanhaoo.club/js/jquary3-4.js"></script>
	<script src="https://www.lanhaoo.club/js/bootstrap.min.js"></script>
<title>兰兰想的五子棋</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	
	<script>
		function Get(yourUrl){
			var Httpreq = new XMLHttpRequest(); // a new request
			var tt=new Date();
			
			Httpreq.open("GET",yourUrl+"&time="+tt.getTime(),false);
		
			Httpreq.send(null);
			
			return Httpreq.responseText;          
		}
		
		var over=false;
		var Chessboard=[];//-1 黑，0无，1白
		var chess_num=0;
		var nownow=0;
		
		function draw_board(){
			var tmp=document.getElementById('game_board');

			var canva=tmp.getContext('2d');
			tmp.setAttribute("width",500);
			tmp.setAttribute("height",500);
			
			var sUserAgent = navigator.userAgent;
			console.log(sUserAgent);
			if(sUserAgent.indexOf('Android')>-1||sUserAgent.indexOf('IPhone')>-1){
				//手机用户横屏预留代码
			}
			
			if(document.getElementById("div_board_width").clientWidth<500){
				tmp.setAttribute("width",document.getElementById("div_board_width").clientWidth);
				tmp.setAttribute("height",document.getElementById("div_board_width").clientWidth);
			}
			canva.clearRect(0,0,document.getElementById("div_board_width").clientWidth,document.getElementById("div_board_width").clientWidth);

				for(var i=0;i<=15;i++){
					canva.beginPath();
					canva.fillText(i,30+i*30,15);
					canva.moveTo(30+i*30,30);
					canva.lineTo(30+i*30,480);
					canva.stroke();
					if(i!=0){
						canva.fillText(i,5,30+i*30);
					}
					canva.moveTo(30,30+i*30);
					canva.lineTo(480,30+i*30);
					canva.closePath();
					canva.stroke();
			}
			
			for (var i = -1; i <= 16; i++) {
				Chessboard[i]=[];
				for (var j = -1; j <= 16; j++) {
					Chessboard[i][j]=0;
				}
			}
		}
		
		function getCookie(string_cookieName){
			var arr_all_cookie=document.cookie.split(";");
			for (var i=0;i<arr_all_cookie.length;i++){
				if(arr_all_cookie[i].indexOf(string_cookieName)!=-1){
					return arr_all_cookie[i].split("=")[1];
				}
			}
			return null;
		}
		
		var my_identity="black";
		var other_identity="white";
		function getPlaerinfo(){
			
			var room_json=JSON.parse(Get("./api/getRoominfo.php?game_id="+getCookie('game_id')));
			
			if(room_json.game_status=="lacking"){
				//lacking 一定是黑棋在等待所以获取黑棋的资料
				var black_user_info=JSON.parse(Get("./api/getUserinfo.php?user_id="+room_json.user_black_id));
				document.getElementById("string_my_info").innerHTML=
																"赢: "+black_user_info.wins+" 场"
														+"<br>"+"输: "+black_user_info.loses+" 场"
														+"<br>"+"平: "+black_user_info.draw+" 场";
				document.getElementById("string_my_name").innerHTML=getCookie('my_id')+" ("+my_identity+")";
				document.getElementById("string_another_name").innerHTML="等待玩家加入...(white)"
				var tmp_lacking_internal=setInterval(function(){
					room_json=JSON.parse(Get("./api/getRoominfo.php?game_id="+getCookie('game_id')));
					if(room_json.game_status=="wait"){
						
						getPlaerinfo();
						clearInterval(tmp_lacking_internal);
					}
				},5000);
				
			}else{
				
				var black_user_info=JSON.parse(Get("./api/getUserinfo.php?user_id="+room_json.user_black_id));
				var white_user_info=JSON.parse(Get("./api/getUserinfo.php?user_id="+room_json.user_white_id));

				if(room_json.user_black_id==getCookie('my_id')){
					//我是黑棋
					document.getElementById("string_my_info").innerHTML=
																"赢: "+black_user_info.wins+" 场"
														+"<br>"+"输: "+black_user_info.loses+" 场"
														+"<br>"+"平: "+black_user_info.draw+" 场";
					document.getElementById("string_another_name").innerHTML=white_user_info.user_id+" ("+other_identity+")";
					document.getElementById("string_another_info").innerHTML=
																"赢: "+white_user_info.wins+" 场"
														+"<br>"+"输: "+white_user_info.loses+" 场"
														+"<br>"+"平: "+white_user_info.draw+" 场";


				}else{
					my_identity="white";
					other_identity="black";
					document.getElementById("string_my_info").innerHTML=
																"赢: "+white_user_info.wins+" 场"
														+"<br>"+"输: "+white_user_info.loses+" 场"
														+"<br>"+"平: "+white_user_info.draw+" 场";

					document.getElementById("string_another_name").innerHTML=black_user_info.user_id+" ("+other_identity+")";
					document.getElementById("string_another_info").innerHTML=
																"赢: "+black_user_info.wins+" 场"
														+"<br>"+"输: "+black_user_info.loses+" 场"
														+"<br>"+"平: "+black_user_info.draw+" 场";
				}

				//将来还是输出用户名吧
				document.getElementById("string_my_name").innerHTML=getCookie('my_id')+" ("+my_identity+")";

				var getOther_status=setInterval(function(){
					var json_prepare=JSON.parse(Get("./api/getRoominfo.php?game_id="+getCookie("game_id")));
					console.log("我的身份"+my_identity);
					if(my_identity=="black"){
						console.log("white: "+json_prepare.white_user_status);
						if(json_prepare.white_user_status>="1"){
							document.getElementById("string_other_status").innerHTML="已准备!"
							document.getElementById("class_other_card").setAttribute("class","card bg-success text-white");
							clearInterval(getOther_status);
							getGameStatus();
						}

						if(json_prepare.black_user_status>="1"){
							document.getElementById("string_my_status").innerHTML="已准备!";
						}
					}else{
						console.log("b: "+json_prepare.black_user_status);
						if(json_prepare.black_user_status>="1"){
							document.getElementById("string_other_status").innerHTML="已准备!"
							document.getElementById("class_other_card").setAttribute("class","card bg-success text-white");
							clearInterval(getOther_status);
							getGameStatus();
						}
						if(json_prepare.white_user_status>="1"){
							document.getElementById("string_my_status").innerHTML="已准备!";
						}

					}



				},5000);
			}
		}
		
		function post_ready(){
			var tmp=Get("./api/postReady.php?game_id="+getCookie('game_id')+"&user_id="+getCookie('my_id'));
			
			if(tmp=="me_black_ready"||tmp=="me_white_ready"){
				document.getElementById("string_my_status").innerHTML="已准备";
			}
			
/*			//开始不断检测游戏状态,如果开始,那么同步服务器开始时间戳,如果我是黑棋,那么开始下棋,白棋则等待;
			setInterval(function(){
				var json_game_status=JSON.parse(Get("./api/getRoominfo.php?game_id="+getCookie("game_id")));
				if(json_game_status.game_status=="start"){
					
				}
				
			},5000);*/
		}
		
		var serverTime;
		var CountDownTime=120;
		
		
		var raw_json;
		function getGameStatus(){
			
			//todo取掉消息框
			if(raw_json!=null){
				if(raw_json.game_status=="start"){
					setInterval(function(){countDown(1)},1000);
				}
			}
			
			//Here is the loading code
			//在取得状态之前,先让所有都处于加载状态
			var getStatus=setInterval(function(){
				raw_json=JSON.parse(Get("./api/getRoominfo.php?game_id="+getCookie('game_id')));
				if(raw_json.x_y!=null){
					var arr_x_y=raw_json.x_y.split("#");
					for(var i=0;i<arr_x_y.length;i++){
						var location_x=arr_x_y[i].split(",")[0];
						var location_y=arr_x_y[i].split(",")[1];
						draw_chess(location_x,location_y,i);
						chess_num++;
					}
				}
				if(raw_json.game_status=="start"){
					
					serverTime=raw_json.timestamp;
					var tt=new Date().getTime()/1000;
					var nowtime=parseInt(tt.toString());
					CountDownTime=120-(nowtime-serverTime);
					over=false;
					
					//console.log(serverTime+","+nowtime);
					countDown(5);
					

					if(my_identity=="black"){
						//我是黑棋
						if(raw_json.black_user_status=="2"){
							my_turn=true;
							document.getElementById("string_my_status").innerHTML="我的回合";
							document.getElementById("class_my_card").setAttribute("class","card bg-warning");
							
							document.getElementById("string_other_status").innerHTML="对手等待中..."
							document.getElementById("class_other_card").setAttribute("class","card bg-primary text-white");
							
						}else if(raw_json.black_user_status=="3"){
							my_turn=false;
							document.getElementById("string_my_status").innerHTML="等待对手下棋";
							document.getElementById("class_my_card").setAttribute("class","card bg-primary");
							document.getElementById("string_other_status").innerHTML='<div><span class="spinner-grow"></span>思考中...</div>';
							document.getElementById("class_other_card").setAttribute("class","card bg-warning text-white");
						}
					}else{
						//我是白棋
						
						if(raw_json.white_user_status=="2"){
							my_turn=true;
							document.getElementById("string_my_status").innerHTML="我的回合";
							document.getElementById("class_my_card").setAttribute("class","card bg-warning text-white");
							
							document.getElementById("string_other_status").innerHTML="对手等待中..."
							document.getElementById("class_other_card").setAttribute("class","card bg-primary");
							
						}else if(raw_json.white_user_status=="3"){
							my_turn=false;
							document.getElementById("string_my_status").innerHTML="等待对手下棋";
							document.getElementById("class_my_card").setAttribute("class","card bg-primary text-white");
							document.getElementById("string_other_status").innerHTML='<div class="row"><div class="col-2"><span class="spinner-grow"></div><div class="col"></span>思考中...</div></div>';
							document.getElementById("class_other_card").setAttribute("class","card bg-warning");
						}
						
						
						
					}

				}else if(raw_json.game_status=="end"){
					over=true;
					if(raw_json.black_user_status=="5"&&raw_json.white_user_status!="5"){
						window.alert("本局游戏已结束,黑棋胜利");
						
					}else if(raw_json.white_user_status=="5"&&raw_json.black_user_status!="5"){
						window.alert("本局游戏已结束,白棋胜利");
					}else if(raw_json.black_user_status=="5"&&raw_json.white_user_status=="5"){
						window.alert("本局游戏已结束,平局");
					}else{
						window.alert("本局游戏已结束");
					}
					document.getElementById("string_my_status").innerHTML="游戏已结束";
					document.getElementById("class_my_card").setAttribute("class","card bg-danger text-white");
					document.getElementById("string_other_status").innerHTML="游戏已结束"
					document.getElementById("class_other_card").setAttribute("class","card bg-danger text-white");
					clearInterval(getStatus);
					
					var popup_end=confirm("点击确定放回大厅,取消则留下来看棋局!")
					if(popup_end){
						window.location="./index.php";
					}
					
					
				}
			},5000);
			
			
		}
		
		function countDown(num){
			CountDownTime-=num;
			if(CountDownTime>0){
				document.getElementById("p_left_time").innerHTML=Math.floor(CountDownTime/60)+":"+Math.floor(CountDownTime%60);
			}else{
				document.getElementById("p_left_time").innerHTML="0:0";
			}
			
		}
		
		function draw_chess(i,j,num){
			
			var canvas=document.getElementById('game_board');
			var canva=canvas.getContext('2d');
		
			canva.beginPath();
			canva.arc(30+i*30,30+j*30,13,0,2*Math.PI,true);
			canva.closePath();

			if(num%2==0){
				canva.fillStyle="#000";
				Chessboard[i][j]=-1;
			}else{
				canva.fillStyle="#fff";
				canva.strokeStyle="#000";
				canva.lineWidth=2;
				canva.stroke();
				Chessboard[i][j]=1;
			}
			canva.fill();
			
		}
		function post_chess(i,j,user_id,game_id){
			var tmp=JSON.parse(Get("./api/postChess.php?game_id="+getCookie('game_id')+"&user_id="+getCookie('my_id')+"&x="+i+"&y="+j));
			if(tmp.msg=="winner"){
				over=true;
				if(tmp.which_one=="black"){
					window.alert("黑棋赢了!");
				}else{
					window.alert("白棋赢了");
				}
			}
			
		}

		var my_turn=false;
		
		function hi(){
			//todo 打开消息框
			
			
			draw_board();
			//调整发送框大小
			document.getElementById("input_message").setAttribute("style","width:"+(document.getElementById("div_input_width").clientWidth-120)+"px");
			
			getPlaerinfo();
			
			
			var canva=document.getElementById('game_board');
			var canvas=canva.getContext('2d');
			
			
			
			canva.onclick=function(e){
				
				var my_status=document.getElementById("string_my_status");
				if(over){
					window.alert("本轮游戏已经结束，请进入新房间！");
					//走随机加入路线
			
					
				}else{
					if(my_turn){
						var x=e.offsetX;
						var y=e.offsetY;
						console.log(x+"  "+y);
						var i=Math.floor(x/32);
						var j=Math.floor(y/30);
						if(Chessboard[i][j]==0){
							if(i>=0&&j>=0&&i<=15&&j<=15){
								chess_num++;
								draw_chess(i,j,chess_num);
								post_chess(i,j);
							}
						}else{
							console.log("已经有棋子了");
						}
					}
					

				}
			};
		}
		
	</script>
	<style>
		.loading{
			z-index: 1;
			opacity: 0.5;
			position: relative;
		}
	</style>
	</head>
<body onLoad="hi();">
	
	<div class="container">
		<h1>房间内对战</h1>

		<hr>

		<div class="row">
			<div class="col">
				<div class="card" id="class_my_card">
					<div class="card-body">
						<h3 class="card-title"><span id="string_my_name"></span></h3>
						<hr>
						<p class="card-text">
							<span id="string_my_info"></span>
						</p>
					</div>
					<div class="card-footer" id="string_my_status">
						<input class="btn btn-warning" type="button" value="准备" id="btn_ready" onClick="post_ready();" />
					</div>
				</div>
				<div class="alert alert-primary" role="alert" id="alert_screen">
		  			手机用户将<u><i>手机横屏</i></u>以显示完整棋盘<br>刷新后等待, 游戏会自动恢复!
				</div>
			</div>
			
			<div class="col">
				<div class="card text-center" id="div_board_width">
					<div class="card-header">
						剩余时间: <b><span id="p_left_time">2:00</span></b>
						
					</div>
					<canvas id="game_board"></canvas>
				</div>
			</div>
			<div class="col">
				<div class="card bg-warning" id="class_other_card">
					<div class="card-body">
						<h3 class="card-title"><span id="string_another_name"></span></h3>
						<hr>
						<p class="card-text">
							<span id="string_another_info"></span>
						</p>
					</div>
					<div class="card-footer" id="string_other_status">
						<div class="spinner-grow"></div>
					</div>
				</div>
				<br>
				<div class="card">
					<div class="card-header" id="div_input_width">
						
						<input id="input_message" type="text" placeholder="说些什么.." />
						<input id="button_send_message" type="button" class="btn btn-primary" value="发送" />
					</div>
					<div class="card-body">
						<h3 class="card-title">聊天</h3>
						<div class="card">
							<div class="card-body">
								
							</div>
						</div>
					</div>
					
				</div>
			</div>
		</div>
	</div>
	<!-- Modal -->
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
	  <div class="modal-dialog-centered" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="modal_title"></h5>
		  </div>
		  <div class="modal-body">
			<p id="modal_text"></p>
		  </div>

		</div>
	  </div>
	</div>
</body>
</html>
