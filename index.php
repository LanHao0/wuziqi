<?php

session_start();
include_once './api/dbconnect.php';
if(!isset($_SESSION['user']))
{
 header("Location: ../denglu.php");
}




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
		<link rel="stylesheet" href="https://www.lanhaoo.club/js/Chart.css">
		<script src="https://www.lanhaoo.club/js/Chart.js"></script>
		<title>兰兰想的魔法五子棋</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
		<script>
			function Get(yourUrl){
				var Httpreq = new XMLHttpRequest(); // a new request
				var tt=new Date();

				Httpreq.open("GET",yourUrl+"&time="+tt.getTime(),false);

				Httpreq.send(null);

				return Httpreq.responseText;          
			}
			function renderMyChart(){
				var ctx = document.getElementById('myChart').getContext('2d');
				var myChart = new Chart(ctx, {
					type: 'pie',
					data: {
						labels: ['赢', '输', '平'],
						datasets: [{
							
							data: [wins,loses,draw],
							backgroundColor: [
								'rgba(255, 99, 132, 0.7)',
								'rgba(54, 162, 235, 0.7)',
								'rgba(255, 206, 86, 0.7)',
							],
							borderColor: [
								'rgba(255, 99, 132, 1)',
								'rgba(54, 162, 235, 1)',
								'rgba(255, 206, 86, 1)',
							],
							
						}]
					},
					options: {
					}
				});
			}
			var wins,loses,draw;
			function hi(){
				wins=parseInt(document.getElementById("span_wins").innerHTML);
				loses=parseInt(document.getElementById("span_loses").innerHTML);
				draw=parseInt(document.getElementById("span_draw").innerHTML);
				renderMyChart();
			}
			
			function btn_create_Room(){
				var tmp_cap=document.getElementById("captcha").value;
				var raw_json=JSON.parse(Get("./api/createRoom.php?captcha="+tmp_cap));
				console.log(raw_json);
				if(raw_json.game_id!=undefined){
					//引领用户到正确房间
					console.log(raw_json.game_id);
					if(raw_json.game_id>0){
						window.location.href="./game_board.php?game_id="+raw_json.game_id;
					}
					
					
				}else{
					if(raw_json.msg!=undefined){
						if(raw_json.msg=="验证码错误"){
							document.getElementById("captcha").value="验证码错误";
						}else{
							document.getElementById("captcha").value="未知错误";
						}
					}

				}
			}
			
			function btn_joinGame(){
				var join_id=document.getElementById("input_join_Room_id").value;
				
				if(join_id!=""&&!join_id.isNaN){
					//指定id加入
					console.log("指定id"+join_id);
					var joing_jsons=JSON.parse(Get("./api/getWaitingRoom.php?game_id="+join_id));
					if(joing_jsons.msg!=undefined){
						if(joing_jsons.msg=="OK"){
							window.location.href="./game_board.php?game_id="+join_id;
						}else{
							document.getElementById("input_join_Room_id").value=joing_jsons.msg;
						}
					}
				}else{
					
					//随机加入
					var join_json=JSON.parse(Get("./api/getWaitingRoom.php?a=1"));
					console.log(join_json);
					if(join_json.game_id!=undefined){
						//可以加入
						window.location.href="./game_board.php?game_id="+join_json.game_id;
					}else{
						if(join_json.msg!=undefined){
								document.getElementById("input_join_Room_id").value=join_json.msg;
						}
					}
				}
				
			}
			
		</script>
	</head>
	<body onLoad="hi();">
		<br>
		<div class="container">
			<div class="row">
				<div class="col text-center" style="border: solid 1px #eee;
													border-radius: 15px;">
					<h5>欢迎来到</h5>
					<h2>兰兰想的魔法五子棋</h2>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col">
					<div class="card">
						<div class="card-body">
							<h5 class="card-title">我的信息</h5>
							<hr>
						<div class="row">
							<div class="col">
								<?php
									$res=mysqli_query($con,"select * from wzq_user_info where user_id=".$_SESSION['user']);
								
									if(mysqli_num_rows($res)<1){
										mysqli_query($con,"insert into wzq_user_info(user_id,total,wins,loses,draw,join_time) value(".$_SESSION['user'].",0,0,0,0,".time().")");
									}
									$res=mysqli_query($con,"select * from wzq_user_info where user_id=".$_SESSION['user']);
									$row=mysqli_fetch_assoc($res);
								?>
								<p>总局数: <?php echo $row['total'];?></p>
								<p>赢: <span id="span_wins"><?php echo $row['wins'];?></span></p>
								<p>输: <span id="span_loses"><?php echo $row['loses'];?></span></p>
								<p>平: <span id="span_draw"><?php echo $row['draw'];?></span></p>
							</div>
							<div class="col">
								<canvas id="myChart"></canvas>
							</div>
						</div>
						</div>
					</div>
				</div>
				<div class="col">
					<div class="card">
						<div class="card-body">
							<h5 class="card-title">公告栏</h5>
							<hr>
							等我想想写点什么,在此之前先放个广告吧!<br>
				<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
				<!-- lanha0_ads_AdSense1_300x250_as -->
				<ins class="adsbygoogle"
					 style="display:block"
					 data-ad-client="ca-pub-1635707682070310"
					 data-ad-slot="7165794688"
					 data-ad-format="auto"
					 data-full-width-responsive="true"></ins>
				<script>
					 (adsbygoogle = window.adsbygoogle || []).push({});
				</script>

						</div>
					</div>
				</div>
				
			</div>
			<hr>
			<div class="row">
				<div class="col">
					<input type="button" value="创建房间" class="btn btn-warning btn-lg btn-block" onClick="btn_create_Room();" />
					<br>
					<div class="card">
						<div class="card-body">
							<p class="card-text">将为您创建新房间, 但这也会让您等待一些时间,请输入验证码后点击创建房间</p>
							<input type="text" id="captcha" class="form-control" placeholder="输入验证码(区分大小写)" />
								<?php

								include("../captcha/simple-php-captcha.php");
								$_SESSION['captcha'] = simple_php_captcha();
							
								  echo '<img src="' . $_SESSION['captcha']['image_src'] . '" alt="CAPTCHA code">';

								?>
						</div>
					</div>
				</div>
				
				<div class="col" style="border-left: solid 1px #979797">
					<input type="button" value="加入房间" class="btn btn-primary btn-lg btn-block" onClick="btn_joinGame();" />
					<br>
					<input type="text" id="input_join_Room_id" class="form-control" placeholder="可以 选择 输入房间id" />
					<br>
					<div class="card">
						<div class="card-body">
							<p class="card-text">您将随机加入一个正在等待其他玩家的房间</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>