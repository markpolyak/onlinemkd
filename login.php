<?php
	
	require_once 'BaseData.php';

	if (isset($_POST['exit'])) 
	{
		// Ставим куки
		session_start();
		session_destroy();
		header("Location: authorization.php"); 
		
		exit();
	}
	
	if(!isset($_POST['email']) || !isset($_POST['password']))
	{
			print 'Не передан логин или пароль';
			exit();
	}	

	// Соединяемся с БД
	$link = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	if ($link->connect_errno) {
		echo 'Не удалось подключиться к mysql: (" . $link->connect_errno . ") ' . $link->connect_error;
	}
	
		// Вытаскиваем из БД запись, у которой логин расняется введенному
		$query = $link->query("SELECT *
								FROM Users 
								WHERE email = '".$link->real_escape_string($_POST['email'])."'");
		if( !$query ) {
			print 'Выборка из БД не сработала';
			exit();
		}
		$userdata = $query->fetch_assoc();

		// Сравниваем пароли, пришедший пароль не шифруем, так как приходит уже в зашифрованном виде
		// соль является частью хэша, т.е. о ней можно не заботиться
		if(password_verify($_POST['password'], $userdata['password']))
		{

			// Генерируем случайное число и шифруем его
			$hash = openssl_random_pseudo_bytes(10, $cstrong);
			$hash = bin2hex($hash);
			
			// Записываем в БД новый хеш авторизации
			if( !$link->query("UPDATE Users 
							SET hash = '".$hash."' 
							WHERE id = '".$userdata['id']."' ") ) {

				print 'Не обновилась таблица БД';
				exit();
			}
			//print ' '.$random_number;
				
			// Ставим куки
			setcookie("id", $hash);
			// стартуем сессию
			session_start();
			$_SESSION['user_email'] = $userdata['email'];
			
			print '1'; // пользователь авторизовался
		}
		else
		{
			print '0'; // авторизация не прошла
		}
?>