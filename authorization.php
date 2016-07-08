<?php
	require_once 'check.php';
	$status = checkCookies();
	if ($status == 'user') 
		header("Location: profile.php"); 
	else
		if($status == 'admin')
			header("Location: adminProfile.php");
?>
<html>
	<head>
		<meta charset = "utf-8">
		<title>Система электронного голосования</title>
		<style>
			body {
				background-color: #CEECF5;
			}
			.button {
				background-color: black;
				color: white;
			}
			#header {
				margin-top: 100px;
				width: 100%;
				text-align: center;	
				margin-bottom: 100px;
			}
			.floater {
				display: table;
				width: 100%;
				height: 100%;
				text-align: center;
			}
			.forms {
				display: table-cell;
			}
			mark {
				background: #ffec82
			}
			#results {
				position: absolute;
				width: 100%;
				text-align: center;
			}
		</style>
		<script type = "text/javascript" src = "jquery-1.11.3.js"></script>
		<script type = "text/javascript" src = "md5.js"></script>
		<script type="text/javascript" language="javascript">
			// вызов скрипта для проверки введенных данных
			function call() {
				// определение анализируемых данных
				var password = $('#password').val();
				
				// шифруем пароль
				password = hex_md5 ( hex_md5 ( password ) );
				var msg = $('#email').serialize() + '&password=' + password;
				$.ajax ({
					type: 'POST',
					url: 'login.php',
					data: msg,
					success: function (data) {
						// если данные неверные
						if (data == 0)
							$('#results').html('<mark>Неверный логин/пароль</mark>');
						else // проверка корректности куки
							if(data == 1)
								location.reload();
							else 
								alert(data);
					},
					error: function (data) {
						alert('Ошибка при обращении к серверу');
					}
				});
			}
		</script>
	</head>
	<body>
		<h1 id = "header"> Вас приветствует сиcтема электронного голосования </h1>
		<div class = "floater">
			<div class = "forms">
				<p> Необходимо авторизоваться </p>
				<!-- используется ajax для передачи шифрованного пароля -->
				<form class = "enter" method = "post" action = "javascript:void(NULL);" onsubmit = "call()">
					<p>E-mail <input id = "email" required type = "text" name = "email" size = 25 placeholder = "Введите логин"/></p>
					<p>Пароль <input id = "password" required type = "password" name = "password" size = 25 placeholder = "Введите пароль"/></p>
					<p> <label><input type = "checkbox" id = "anCompCheck" name = "anCompCheck" /> Чужой компьютер </label> </p> 
					<p><input class = "button" type = "submit" name = "enter" value = "Войти"/><p>
				</form>
				<form class = "register" method = "post" action = "register.php">
					<input class = "button" type = "submit" name = "register" value = "Зарегистрироваться"/>
				</form>
				<p id = "results"></p>
			</div>
		</div>
	</body>
</html>	