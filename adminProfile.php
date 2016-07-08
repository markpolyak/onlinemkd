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
		
		<!-- Стили datepicker -->
		<link type="text/css" href="jquery-ui-1.11.4/jquery-ui.min.css" rel="stylesheet" />
		
		<script type = "text/javascript" src = "jquery-1.11.3.js"></script>
		<!-- Скрипт datepicker -->
		<script type="text/javascript" src="jquery-ui-1.11.4/jquery-ui.min.js"></script>
		<script type="text/javascript" src="jquery-ui-1.11.4/datepicker-ru.js"></script>
		<script type="text/javascript" src="jquery-ui-1.11.4/datepicker.js"></script>
		<script type="text/javascript" language="javascript">
		
			function showForm(flag) {

				if(flag == 0) {

					$("#newMeet").show();
					$("#newMeetForm").hide();
					$("#results").html("");
				}
				else {

					$("#newMeet").hide();
					$("#newMeetForm").show();
				}
			}

			function addMeet() {
				
				var msg = $('#newMeetForm').serialize();

				$.ajax ({
					type: 'POST',
					url: 'adminProfileServer.php',
					data: msg,
					success: function (data) {
						
						if(data == 1)
							location.reload();
						else
							if(data == 0)
								$('#results').html("<b>Ошибка при добавлении нового собрания</b>");
							else
								if(data == 2)
									$('#results').html("<b>Неправильно задан временной диапазон</b>");
								else
									alert("Ошибка при обращении к серверу. Повторите действия позже");
					},
					error: function (data) {
						alert('Ошибка при обращении к серверу');
					}
				});
			}

		</script>
	</head>
	<body onload = showForm(0)>
		<p><div id = "title">Добро пожаловать на страницу администратора</div></p>
		<form id = "exit" method = "post" action = "login.php">
			<p>Админ</p>
			<input class = "button" type = "submit" name = "exit" value = "Выйти"/>
		</form>
		<div class = "list">
			<div style = "margin-bottom: 40px;">
				<input id = "newMeet" class = "button" type = "button" onclick = showForm(1) value = "Добавить собрание">

				<form id = "newMeetForm" method = "post" action = "javascript:void(NULL);" onsubmit = "addMeet()">
					<p>Дата начала собрания: <input id = "dateStart" required type = "text" name = "dateStart" value =""/>
					<input id = "timeStart" required type = "time" name = "timeStart" size = 25 style = "margin-right: 15px;">
					Дата окончания собрания: <input id = "dateEnd" required type = "text" name = "dateEnd"/>
					<input id = "timeEnd" required type = "time" name = "timeEnd" size = 25 style = "margin-right: 15px;"></p>
					<p><input class = "button" type = "submit" name = "addMeeting" value = "Добавить собрание">
					<input class = "button" type = "button" onclick = showForm(0) value = "Отменить">
					</p>
				</form>

				<div id = "results">

				</div>

			</div>
			
			<!-- Модальное окно -->		
			<div id="confirmDialog"></div>				
				
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

					echo '<tr>
							<th><font size = "5" style = "margin-right: 30px;">'.$dateStart->format('d-m-Y H:i').'</font></th>
							<th><font size = "5" style = "margin-right: 30px;">'.$dateEnd->format('d-m-Y H:i').'</font></th>';

					// если собрание еще не утверждено
					if($row['status'] == 0)
					{						
						echo '<form id = "changeMeetForm" method = "post" action = "adminProfileServer.php">
								<input type = "hidden" name = "dateStart" value = "'.$row['date_start'].'">
								<input type = "hidden" name = "dateEnd" value = "'.$row['date_end'].'">
								<input type = "hidden" name = "meetingdata" value = "'.$row['id_meeting'].'">
								<th><input name = "changeButton" class = "button" type = "submit" value = "Изменить собрание">
									<input name = "deleteButton" onclick = "return confirm(\'Вы действительно хотите удалить собрание?\')" 
									    class = "button" type = "submit" value = "Удалить собрание">
									<input name = "confirmButton" onclick = "return confirm(\'Вы действительно хотите подтвердить собрание? После подтверждения редактировать собрание невозможно\')" 
											class = "button" type = "submit" value = "Утвердить собрание"></th>
							  </form>
							</tr>';
					}

					else // собрание уже утверждено
					{
						// если собрание еще не завершилось
						if(date("Y-m-d H:i:s") >= $row['date_start'] and date("Y-m-d H:i:s") <= $row['date_end'])
							echo '<form id = "gen" method = "post" action = "adminProfileServer.php">
								  	<th></font><input name = "downloadButton" class = "button" type = "submit" onclick = beforeLoad() value = "Скачать бюллетень">
										<input class = "button" type = "submit" value = "Загрузить изображение"></th>
										<input type = "hidden" name = "meetingdata" value = "'.$row['id_meeting'].'">
								  </form>
							 <tr>';

						else // если собрание еще не началось
							if(date("Y-m-d H:i:s") < $row['date_start'])
								echo '<form method = "post" action = "questions.php">
										<th><input class = "button" type = "submit" value = "Посмотреть список вопросов"></th>
											<input type = "hidden" name = "dateStart" value = "'.$row['date_start'].'">
											<input type = "hidden" name = "dateEnd" value = "'.$row['date_end'].'">
											<input type = "hidden" name = "meetingdata" value = "'.$row['id_meeting'].'">
									  </form>
							  	<tr>';

							else
								echo '<form>
										<th><font size = "5" style = "margin-right: 30px;">Собрание уже закончилось</font></th>
									  </form>
								</tr>';
					}
					
				}
			?>
			</table>
		</div>
	</body>
</html>	
