<!-- Форма для регистрации нового пользователя -->
<?php
					require_once 'BaseData.php';
					require_once 'meeting.php';
										
					$link = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
					if ($link->connect_errno) {
						echo 'Не удалось подключиться к mysql: (" . $link->connect_errno . ") ' . $link->connect_error;
					}
					if(!$link->set_charset("utf8"))
						printf("Ошибка при загрузке набора символов utf8: %s\n", $link->error);
?>
<html>
	<head>
		<meta charset = "utf-8">
		<title>Регистрация на портале</title>
		
		<style>
			body {
				background-color: #CEECF5;
			}
			.button {
				background-color: black;
				color: white;
			}
			.floater {
				display: table;
				width: 100%;
				height: 100%;
				text-align: center;
			}
			#form1 {
				position: absolute;
				height: 400px; 
				top: 70px; 
				left: 400px; 
				line-height: 35px;
				width: 100px;
			}
			#form2 {
				position: absolute;
				height: 400px; 
				top: 105px; 
				right: 400px; 
				line-height: 35px;
				width: 230px;
			}
			.required {
				color: red;
			}
			#results {
				display: table-cell;
				vertical-align: middle;
			}
		</style>
		
		<link href="kladrapi/jquery.kladr.min.css" rel="stylesheet"> <!-- подсказки при вводе адреса-->
		<link type="text/css" href="jquery-ui-1.11.4/jquery-ui.min.css" rel="stylesheet" />
		
		<script type = "text/javascript" src = "jquery-1.11.3.js"> </script>
		<script type="text/javascript" src="jquery-ui-1.11.4/jquery-ui.min.js"></script> 	
		<script src="kladrapi/jquery.kladr.min.js" type="text/javascript"></script>	<!-- библиотека cladr-->
		<script src="kladrapi/searchAdress.js" type="text/javascript"></script> <!-- функции поиска адреса-->
		<script src="checkRegisterData.js" type="text/javascript"></script> <!-- проверка данных регистрации-->
		<script type = "text/javascript" src = "md5.js"> </script>
		<script type="text/javascript" language="javascript"> 
		
			  $(function() {
    				$( '#password1').tooltip({
						content: "Минимальная длина пароля 8 символов!",
						position: { my: "left-75 center", at: "left center" }
					});
 				});
		
			function cancelRegister() {
				
				document.location.href = 'authorization.php';
			}
		
			function turnBack() {
				$('#results').empty();
				$('#registerForms').show();
			}
		
			function isError()
			{
				var passwordError = $('#pswError').is(':empty'); // ошибка пароля
				var password2Error = $('#psw2Error').is(':empty'); // ошибка второго пароля
				var streetError = $('#streetError').is(':empty'); // ошибка ввода улицы
				var buildingError = $('#buildingError').is(':empty'); // ошибка ввода номера дома
				
				if ( !passwordError || !password2Error || !streetError || !buildingError)
					return true;
				return false;
			}
		
			function register(msg)
			{
				$.ajax ({
						type: 'POST',
						url: 'checkRegister.php',
						data: msg, 
						success: function (data) {
							if (data == 1) {
								$('#results').html('<b>Вы успешно зарегистрировались<br>Пожалуйста, подождите...</b>');
								$('#registerForms').hide();
								setTimeout( function() { document.location.href = 'authorization.php'; }, 5000);
							}
							else 
								if(data == "Сервер временно недоступен. Повторите операцию позже")
									alert(data);
								else {
									$('#results').html(data);
									$('#registerForms').hide();
								}					
						},
						error: function (data) {
							alert("Ошибка обращения к серверу. Повторите операцию позже")
						}
					});
			}
		
			// функция для отправки данных на сервер, если не произошла ошибка 
			// в качестве параметра тпринимает объект, вызвавший функцию
			function call(obj) {

				if ( isError() )
					$('#regErrors').html('<b>Исправьте ошибки<b>');
				else {
					
					var password = $('#password1').val();
					
					if (obj.name == "error") {
					
						var msgToAdmin = prompt('Введите СНИЛС');
																		
						if ( msgToAdmin != null  ) {
							// шифруем пароль
							password = hex_md5 ( hex_md5 ( password ) );
							var msg = $('.ser').serialize() + '&password=' + password + '&objName=' + obj.name + 
														'&msgToAdmin=' + msgToAdmin;
							register(msg);
						}
					}
						
					else {
						// шифруем пароль
						password = hex_md5 ( hex_md5 ( password ) );
						var msg = $('.ser').serialize() + '&password=' + password + "&objName=" + obj.name;
						register(msg);
					}
				}
			}
		</script>
	</head>
	<body>
		<div id = "registerForms">
		<p style = "position: absolute; width: 1360px; text-align: center; top: 50px"> Обязательные поля помечены <font color = "red">*</font> </p>
		 	<form method = "post" class = "registerForm" name = "regForm" action = "javascript:void(null);" onsubmit = "call(this)">
					<div id = "form1">
						<p> Фамилия <span class = "required">*</span> <input class = "ser" type = "text" name = "surname" required size = 25 placeholder = "Введите фамилию"/></p>
						<p> Имя <span class = "required">*</span> <input class = "ser" type = "text" name = "name" required size = 25 placeholder = "Введите имя"/></p>
						<p> Отчество <span class = "required">*</span> <input class = "ser" type = "text" name = "patronymic" required size = 25 placeholder = "Ведите отчество"/></p>
						<p> E-mail <span class = "required">*</span> <input class = "ser" type = "email" name = "email" required size = 25 placeholder = "Введите действующий e-mail"/></p>
						<p> Пароль <span class = "required">*</span> <input id = "password1" title = "jr" type = "password" name = "password" required size = 25 placeholder = "Введите пароль"/>
							<div id = "pswError" style = "line-height: 15px"></div> </p> 
						<p> Подтвердите пароль <span class = "required">*</span> <input id = "password2" type = "password" name = "password2" required size = 25 placeholder = "Введите пароль"/>
							<div id = "psw2Error" style = "line-height: 15px"></div> </p>	
					</div>
					<div id = "form2">
           	  			<p> Улица <span class = "required">*</span> <input class = "ser" type = "text" name = "street" required size = 25 placeholder = "Введите улицу"/>
							<div id = "streetError" style = "line-height: 15px"></div> </p>
						<p> Дом <span class = "required">*</span> <input class = "ser" type = "text" name = "building" required size = 25 placeholder = "Введите номер дома"/>
							<div id = "buildingError" style = "line-height: 15px"></div> </p>
						<p> Квартира <span class = "required">*</span> <input class = "ser" type = "text" name = "premiseNumber" required size = 25 placeholder = "Введите номер квартиры"/></p>
						<p> Телефон <span class = "required">*</span> <input class = "ser" type = "tel" name = "phone" required size = 25 placeholder = "Введите номер телефона"/></p>
						<p><input class = "button" type = "submit" name = "checkRegister" value = "Зарегистрировать"/>
							<input class = "button" type = "submit" name = "exit" onclick = cancelRegister() value = "Отмена регистрации"/></p>
							<div id = "regErrors" style = "line-height: 15px"></div> </p>
					</div>
				</form>	
		</div>
		<div class = "floater">
			<div id = "results"></div>
		</div>		
	</body>
</html>	