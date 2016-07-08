<?php

	require_once 'BaseData.php';
	require_once 'GenerateCode.php';
	require_once 'meeting.php';

	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);

	$link = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	if ($link->connect_errno) {
		echo 'Не удалось подключиться к mysql: (" . $link->connect_errno . ") ' . $link->connect_error;
		exit();
	}
	if(!$link->set_charset("utf8"))
		printf("Ошибка при загрузке набора символов utf8: %s\n", $link->error);
	
	if( !isset($_POST['password']) || !isset($_POST['name']) || !isset($_POST['surname'])
		|| !isset($_POST['patronymic']) || !isset($_POST['phone']) ) {
			
			echo 'Не все данные переданы';
			exit();
	}
	
	register($link);	
		
	function sendMail($text, $adress)
	{
		$text = wordwrap($text, 70, "\r\n");
		
		if ( mail($adress, 'Регистрация на onlinemkd.ru', $text, 'From: admin@onlinemkd.ru') )
			return true;
		else
			return false;
	}	
	
	function checkEmail($link)
	{
		# проверяем, не существует ли пользоваетля с таким логином
		$query = $link->query("SELECT * 
									FROM Users 
									WHERE email = '".$link->real_escape_string($_POST['email'])."' ");
		if($query->num_rows > 0) 
			return false;
		return true;
	}
	
	function doRegister($link, $ownerId = NULL)
	{
		$password = $_POST['password']; // не шифруем, так как приходит уже в зашифрованном виде
		$name = $_POST['name'];
		$surname = $_POST['surname'];
		$patronymic = $_POST['patronymic'];
		$phone = $_POST['phone'];
		$email = $_POST['email'];
		$building = $_POST['building'];
		$street = $_POST['street'];
		$premiseNumber = $_POST['premiseNumber'];
		
		$options = [
			'salt' => GenerateCode(22) // генерация своей собственной "соли"
		];
		
		$building = preg_split("/[А-Яа-я]/", $building);
	
		if( count($building) == 1 )
			$premisedata = selectPremise($link, $building[0], $street, $premiseNumber);
		else 
			$premisedata = selectPremise($link, $building[0], $street, $premiseNumber, $building[2]);
					
		// шифруем "соленый" пароль
		// т.к. соль является частью хэша, то можно не заботиться о ее хранении
		$password = password_hash($password, PASSWORD_DEFAULT, $options); 
		
		if( is_null($ownerId) )
			if($link->query("INSERT INTO Users (name, surname, patronymic, password, phone, email, id_premise) 
								values ('".$name."', '".$surname."', '".$patronymic."',
										'".$password."', '".$phone."', '".$email."',
										'".$premisedata['id_premise']."') "))
				return true;
			else
				return false;
		else
			if($link->query("INSERT INTO Users (id_owner, name, surname, patronymic, password, phone, email, id_premise) 
								values ('".$ownerId."', '".$name."', '".$surname."', '".$patronymic."',
										'".$password."', '".$phone."', '".$email."', '".$premisedata['id_premise']."') "))
		
				return true;
			else 
				return false;	
	}
		
	function register($link)
	{	
		// если логин верный	
		if ( checkEmail($link) ) {
			
			// если пользователь хочет зарегистрироваться как несобственник
			if ($_POST['objName'] == "okReg" )
			{
				if ( doRegister($link) )
				{
					sendMail("Вы успешно зарегистрировались на onlinemkd.ru", $_POST['email']);
					print '1';
				}
				else 
					print 'Сервер временно недоступен. Повторите операцию позже';
			}
			else 
				// пользователь указывает на ошибку
				if($_POST['objName'] == "error")
				{
					$text = "Пользователь: ".$_POST['name'].' '
								.$_POST['patronymic'].' '.$_POST['surname']." СООБЩЕНИЕ: ".$_POST['msgToAdmin'];
					
					if( sendMail($text, 'mityacrazzy@gmail.com') )
						print 'Письмо админу отправлено
								 </p><input class = "button" type = "submit" 
								 onclick = turnBack() value = "Назад" style = "margin-right: 50px">
					 			<input class = "button" type = "submit" onclick = cancelRegister() 
								 value = "Вернуться на главную страницу" style = "margin-right: 50px"></p>';
					else
						print "Ошибка отправки почты";
				}
				else
				{
					# является ли пользователь собственником
					try {
						$query = selectOwner($link, $_POST['name'], $_POST['surname'], $_POST['patronymic']);
					}
					catch(Exception $exception){
   						echo $exception->getMessage();
						exit();
					}
			
					// если пользователь собственник
					if ( $query->num_rows > 0 )
					{
						$ownerdata = $query->fetch_assoc();
				
						if ( doRegister($link, $ownerdata['id_owner']) )
						{
							sendMail('Вы успешно зарегестировались на onlinemkd.ru', $_POST['email']);
					
							print '1';
						}
						else 
							print 'Сервер временно недоступен. Повторите операцию позже';
					}
					else
						print '<p>Вы не собственник. Продолжить регистрацию или отправить уведомление об ошибке? </p>
								<input class = "button" type = "submit" onclick = call(this) name = "okReg" value = "Зарегистрироваться как несобственник" style = "margin-right: 50px">
								<input class = "button" type = "submit" name = "canReg" onclick = turnBack() value = "Назад" style = "margin-right: 50px">
								<input class = "button" type = "submit" onclick = call(this) name = "error" value = "Отправить вопрос администратору">';
				}
		}
		else {
				print 'Пользователь с таким логином уже зарегистрирован
					 </p><input class = "button" type = "submit" onclick = turnBack() value = "Назад" style = "margin-right: 50px">
					 <input class = "button" type = "submit" onclick = cancelRegister() value = "Отменить регистрацию" style = "margin-right: 50px"></p>';
			
			}
			exit();
	}
?>

