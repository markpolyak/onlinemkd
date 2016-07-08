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

	$meetingdata = $_GET['meetingdata'];
	$dateStart = new DateTime($_GET['dateStart']);
	$dateEnd = new DateTime($_GET['dateEnd']);

	try {
		$questiondata = selectQuestion($link, $meetingdata);
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
				height: 30px;
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

			.forms {
				margin-bottom: 40px;
			}

		</style>
		
		<!-- Стили datepicker -->
		<link type="text/css" href="jquery-ui-1.11.4/jquery-ui.min.css" rel="stylesheet" />
		
		<link rel = "stylesheet" href = "cleditor/jquery.cleditor.css" />

		<script type = "text/javascript" src = "jquery-1.11.3.js"> </script>
		<script src = "cleditor/jquery.cleditor.min.js"> </script>
		
		<!-- Скрипт datepicker -->
		<script type="text/javascript" src="jquery-ui-1.11.4/jquery-ui.min.js"></script>
		<script type="text/javascript" src="jquery-ui-1.11.4/datepicker-ru.js"></script>
		<script type="text/javascript" src="jquery-ui-1.11.4/datepicker.js"></script>
		<script type = "text/javascript" language = "javascript">

			function onLoad() {

				// определение параметров текстового редактора
				$.cleditor.defaultOptions.width = 400;

				$("textarea").cleditor();
				
				// скрытие всех форм
				$("#changeDateForm").hide();
				$("#changeTitleForm").hide();
				$(".changeQuestionForm").hide();
			}

			function showDateForm(flag) {

				if(flag == 0) {

					$("#changeDateBut").show();
					$("#changeDateForm").hide();
					$("#results").html("");
				}
				else {

					$("#changeDateBut").hide();
					$("#changeDateForm").show();
				}
			}

			function showQuestionForm (flag) {

				if(flag == 0) {

					$("#changeQuestionBut").show();
					$(".changeQuestionForm").hide();
				}
				else {

					$("#changeQuestionBut").hide();
					$(".changeQuestionForm").show();
					$("#addQuestionForm").hide();
				}
			}

			function showNewQuestionForm (flag) {

				if(flag == 0) {

					$("#addQuestionBut").show();
					$("#addQuestionForm").hide();
				}
				else {

					$("#addQuestionBut").hide();
					$("#addQuestionForm").show();
				}
			}

			function changeDate() {
				
				var msg = $('#changeDateForm').serialize();
				
				$.ajax ({
					type: 'POST',
					url: 'changeMeetingServer.php',
					data: msg,
					success: function (data) {
						if(data == 1)
							alert("Дата собрания успешно изменена");
						else
							if(data == 0)
								alert("Ошибка при изменении даты собрания. Повторите позже");
							else
								if(data == 2)
									alert("Неправильно задан временной диапазон");
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
	<body onload = onLoad()>
		<p><div id = "title">Изменить собрание</div></p>
		<form id = "exit" method = "post" action = "login.php">
			<p>Админ</p>
			<input class = "button" type = "submit" name = "exit" value = "Выйти"/>
		</form>
		<div class = "list">
			<div style = "margin-bottom: 40px;">

				<p> <input id = "changeDateBut" class = "button" type = "button" onclick = showDateForm(1) value = "Изменить дату собрания"> 
					<input id = "changeQuestionBut" class = "button" type = "button" onclick = showQuestionForm(1) value = "Редактировать вопросы">
					<input id = "addQuestionBut" class = "button changeQuestionForm" type = "button" onclick = showNewQuestionForm(1) value = "Добавить новый вопрос"> 
					 <input class = "button changeQuestionForm forms" type = "button" onclick = showQuestionForm(0) value = "Отменить редактирование вопросов"> </p>
					 
				<form class = "forms" id = "changeDateForm" method = "post" action = "javascript:void(NULL);" onsubmit = "changeDate()">
					<p>Дата начала собрания: <input id = "dateStart" required type = "text" name = "dateStart" value = <?php echo $dateStart->format('d.m.Y') ?> />
					<input id = "timeStart" required type = "time" name = "timeStart" size = 25 style = "margin-right: 15px;" value = <?php echo $dateStart->format('H:i') ?> >
					Дата окончания собрания: <input id = "dateEnd" required type = "text" name = "dateEnd" value = <?php echo $dateEnd->format('d.m.Y') ?> />
					<input id = "timeEnd" required type = "time" name = "timeEnd" size = 25 style = "margin-right: 15px;" value = <?php echo $dateEnd->format('H:i') ?> ></p>
					<p><input class = "button" type = "submit" name = "changeDateButton" value = "Изменить дату">	
					<input type = "hidden" name = "meetingdata" value = <?php echo $meetingdata ?> >
					<input type = "hidden" name = "changeDateButton">
					<input class = "button" type = "button" onclick = showDateForm(0) value = "Отменить"> </p>
				</form>
					<div id = "results">
				</form>

			</div>

			<form class = "changeQuestionForm forms" id = "addQuestionForm" method = "post" action = "changeMeetingServer.php">
				<p> Введите новый вопрос: <textarea name = "newQuestion" required> </textarea> </p>
				<input type = "hidden" name = "meetingdata" value = <?php echo $meetingdata ?> >
				<p> <input class = "button" type = "submit" name = "addQuestionButton" value = "Добавить вопрос">
				<input class = "button" type = "button" onclick = showNewQuestionForm(0) value = "Отменить"> </p>
			</form>

			<?php
				while($row = $questiondata->fetch_array()) { 

					echo '<form class = "changeQuestionForm" method = "post" action = "changeMeetingServer.php">
							<p> <textarea class = "questionTxt" cols = "40"> '.$row['question'].' </textarea>
							<input type = "hidden" name = "questiondata" value = "'.$row['id_question'].'">
							<input class = "button" type = "submit" name = "changeQuestionBut" value = "Изменить вопрос">
							<input class = "button" type = "submit" name = "deleteQuestionButton" onclick = "return confirm(\'Вы действительно хотите удалить вопрос?\')" value = "Удалить вопрос"> </p>
						</form>';
				}
			?>

			<form method = "get" action = "changeMeetingServer.php">
				<input class = "button" type = "submit" name = "deleteButton" onclick = "return confirm('Вы действительно хотите удалить собрание?')" value = "Удалить собрание"> </p>
				<input type = "hidden" name = "meetingdata" value = <?php echo $meetingdata ?> >
			</form>

			<form method = "get" action = "changeMeetingServer.php">
				<input class = "button" type = "submit" name = "confirmButton" onclick = "return confirm('Вы действительно хотите утвердить собрание? После утверждения редактировать собрание невозможно')" value = "Утвердить собрание"> </p>
				<input type = "hidden" name = "meetingdata" value = <?php echo $meetingdata ?> >
			</form>

		</div>
	</body>
</html>	