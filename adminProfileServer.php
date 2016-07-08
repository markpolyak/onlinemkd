<?php
	//сервер админа, выполняет запросы, поступающие от администратора к БД
	require_once 'BaseData.php';

	// если админ запросил скачивание бюллетеня
	if(isset($_POST['downloadButton']))
	{
		header('Location: Generation.php?meetingdata='.$_POST['meetingdata'].''); 
	}
	
	// если админ запросил изменение
	if(isset($_POST['changeButton']))
	{
		header('Location: changeMeeting.php?meetingdata='.$_POST['meetingdata'].'&dateStart='.$_POST['dateStart'].
		'&dateEnd='.$_POST['dateEnd'].''); 
	}

	// если админ запросил изменение
	if(isset($_POST['deleteButton']))
	{
		header('Location: changeMeetingServer.php?meetingdata='.$_POST['meetingdata'].
		'&deleteButton='.$_POST["deletebutton"].''); 
	}

	if(isset($_POST['confirmButton']))
	{
		header('Location: changeMeetingServer.php?meetingdata='.$_POST['meetingdata'].
		'&confirmButton='.$_POST["confirmButton"].'');
	}

	if(!isset($_POST['dateStart']) or !isset($_POST['timeStart']) or !isset($_POST['dateEnd']) or 
		!isset($_POST['timeEnd']))
	{
		echo 'Не все данные переданы';
		exit();
	}

	// добавление нового собрания

	$link = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	if ($link->connect_errno) {
		echo 'Не удалось подключиться к mysql: (" . $link->connect_errno . ") ' . $link->connect_error;
	}
	if(!$link->set_charset("utf8"))
		printf("Ошибка при загрузке набора символов utf8: %s\n", $link->error);

	newMeet($link);
	
	function newMeet($link)
	{
		$dateStart = new DateTime($_POST['dateStart']);
		$dateEnd = new DateTime($_POST['dateEnd']);
	
		$dateTimeStart = $dateStart->format("Y-m-d") . ' '. $_POST['timeStart'];
		$dateTimeEnd = $dateEnd->format("Y-m-d") . ' ' . $_POST['timeEnd'];
		
		if ( !correctDate($dateTimeStart, $dateTimeEnd) ) {
			
			print '2';

			$link->close();

			exit();
		}
		
		if ( addMeet($link, $dateTimeStart, $dateTimeEnd) )
			print '1';
		else 
			print '0';		
	}
	
	function correctDate($dateTimeStart, $dateTimeEnd)
	{
		if($dateTimeStart >= $dateTimeEnd)
			return false;
		return true;
	}
	
	function addMeet($link, $dateTimeStart, $dateTimeEnd)
	{
		// добавление собрания
		if($link->query("INSERT INTO Meeting (id_building, date_start, date_end, status) 
									values (1, '".$dateTimeStart."', '".$dateTimeEnd."', 0) "))
			return true;
			
		return false;	
	}
?>
