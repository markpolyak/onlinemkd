<!-- Личный кабинет пользователя -->
<?php
	require_once 'check.php';
	require_once 'BaseData.php';
	require_once 'meeting.php';

	$link = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	if ($link->connect_errno) {
		echo 'Не удалось подключиться к mysql: (" . $link->connect_errno . ") ' . $link->connect_error;
	}
	if(!$link->set_charset("utf8"))
		printf("Ошибка при загрузке набора символов utf8: %s\n", $link->error);
	
	if (checkCookies() == false)
		header("Location: authorization.php");

	// выбор пользователя из БД
	try {
		$userdata = selectUser($link);
	}
	catch(Exception $exception){
   		echo $exception->getMessage();
		exit();
	}

	// выбор зданий из БД
	try {
		$buildingdata = selectBuilding($link, $userdata);
	}
	catch(Exception $exception){
   		echo $exception->getMessage();
		exit();
	}
	$buildingdata = $buildingdata->fetch_assoc();

	// выбор собраний из БД
	try {
		$meetingdata = selectMeeting($link, $userdata);
	}
	catch(Exception $exception){
   		echo $exception->getMessage();
		exit();
	}
	
	$link->close();
?>
<html>
	<head>
		<meta charset = "utf-8">
		<title>Работа с бюллетенем</title>
		<style>
			body {
				background-color: #CEECF5;
			}
			.button {
				background-color: black;
				color: white;
			}
			#exit {
				position: absolute;
				height: 400px; 
				/*top: 150px; */
				right: 5px;
				line-height: 35px;
			}
			#mark1 {
				background: #ffffff
			}
			#title {
				font-family: 'Times New Roman', Times, serif; 
				font-size: 250%; 
				position: absolute;
				/*left: 200px;	*/		
				width: 100%;
				text-align: center;
				top: 60px;
				margin-bottom: 60px;

			}
			.list {
				position: absolute;
				top: 200px;
				width: 100%;
				text-align: center;
			}
			table {
    			width: 99%; /* Ширина таблицы */
    			background: white; /* Цвет фона таблицы */
   				color: black; /* Цвет текста */
    			border-spacing: 1px; /* Расстояние между ячейками */
				align: center;
   			}
		</style>
	</head>
	<body>
		<p><div id = "title">Список собраний</div></p>
		<form id = "exit" method = "post" action = "login.php">
			<input class = "button" type = "submit" name = "exit" value = "Выйти"/>
		</form>
		<div class = "list">
			<table border = "1">
				<tr>
					<th>Дата начала собрания</th>
					<th>Дата окончания собрания</th>
					<th>Статус собрания</th>
				<tr>
			<?php
				while($row = $meetingdata->fetch_array()){ 

					$dateStart = new DateTime($row['date_start']);
					$dateEnd = new DateTime($row['date_end']);

					// если собрание уже утверждено
					if($row['status'] != 0)
					{
						echo '<tr>
							<th><font size = "5" style = "margin-right: 30px;">'.$dateStart->format('d-m-Y H:i').'</font></th>
							<th><font size = "5" style = "margin-right: 30px;">'.$dateEnd->format('d-m-Y H:i').'</font></th>';
												
						// если собрание еще не завершилось
						if(date("Y-m-d H:i:s") >= $row['date_start'] and date("Y-m-d H:i:s") <= $row['date_end'])
							echo '<form id = "gen" method = "post" action = "profileServer.php">
										<th><input name = "downloadButton" class = "button" type = "submit" onclick = beforeLoad() value = "Скачать бюллетень">
											<input class = "button" type = "submit" value = "Загрузить изображение"></th>
										<input type = "hidden" name = "meetingdata" value = "'.$row['id_meeting'].'">
									</form>
								  </tr>';

						else // если собрание еще не началось
							if(date("Y-m-d H:i:s") < $row['date_start'])
								echo '<form method = "post" action = "questions.php">
											<th><font size = "5" style = "margin-right: 30px;">Собрание еще не началось</font>
											<input class = "button" type = "submit" value = "Посмотреть список вопросов"></th>
											<input type = "hidden" name = "dateStart" value = "'.$row['date_start'].'">
											<input type = "hidden" name = "dateEnd" value = "'.$row['date_end'].'">
											<input type = "hidden" name = "meetingdata" value = "'.$row['id_meeting'].'">
										</form>
									  <tr>';

							else
								echo '<form>
											<th>Собрание уже закончилось</font></th>
										</form>
									  <tr>';
					}
					
				}
			?>
			</table>
		</div>
	</body>
</html>	