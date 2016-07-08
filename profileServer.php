<?php
	//сервер обычного пользователя
	require_once 'BaseData.php';

	// если админ запросил скачивание бюллетеня
	if(isset($_POST['downloadButton']))
	{
		header('Location: Generation.php?meetingdata='.$_POST['meetingdata'].''); 
	}
?>