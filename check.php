<?php
// Скрипт проверки доступа к закрытому контенту
	require_once 'BaseData.php';
	
function checkCookies()
{
	$link = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	if ($link->connect_errno) {
		echo 'Не удалось подключиться к mysql: (" . $link->connect_errno . ") ' . $link->connect_error;
	}
	
	if(!isset($_SESSION))
		session_start();
	
	if(!isset($_COOKIE['id'])  or !isset($_SESSION['user_email']) ) // если есть какая-то ошибка
	{
		session_destroy();
		return false;
	}
	else { // если куки и сессия не пустые
		
		$query = $link->query("SELECT *
								FROM Users 
								WHERE email = '".$_SESSION['user_email']."' and
										hash = '".$_COOKIE['id']."'");
		if( !$query) {
			session_destroy();
			return false;
		}
		$userdata = $query->fetch_assoc();
			
		// если пользователь найден
		if(!empty($userdata['id'])) {
				
			// Генерируем случайное число и шифруем его
			$hash = openssl_random_pseudo_bytes(10, $cstrong);
			$hash = bin2hex($hash);
			
			setcookie('id', $hash, time() + 604800);
				
			//print 'true';				
			if( !$link->query("UPDATE Users 
							SET hash = '".$hash."' 
							WHERE id = '".$userdata['id']."' ") ) {
				session_destroy();
				return false;
			}

			if($userdata['admin'] == 1)
				return 'admin';
			else
				return 'user';				
		}
		else
			return false;
	}
			
	//}
	//else
		//print '0';
}
?>