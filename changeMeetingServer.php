<?php
	// скрипт редактирования вопросов
    require_once 'BaseData.php';

	$link = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	if ($link->connect_errno) {
		echo 'Не удалось подключиться к mysql: (" . $link->connect_errno . ") ' . $link->connect_error;
	}
	if(!$link->set_charset("utf8"))
		printf("Ошибка при загрузке набора символов utf8: %s\n", $link->error);

	// удалить собрание
	if(isset($_GET['deleteButton']))
	{
		if($link->query("DELETE FROM Meeting 
								where id_meeting = '".$_GET['meetingdata']."'"))
		    header('Location: adminProfile.php'); 
	}

	// подтвердить собрание
	if(isset($_GET['confirmButton']))
	{
		if($link->query("UPDATE Meeting 
								SET status = 1
								WHERE id_meeting = '".$_GET['meetingdata']."'"))

		    header('Location: adminProfile.php');
	}
    
	// изменить дату собрания
    if(isset($_POST['changeDateButton']))
    {
		
		$dateStart = new DateTime($_POST['dateStart']);
		$dateEnd = new DateTime($_POST['dateEnd']);
		
        $dateTimeStart = $dateStart->format("Y-m-d") . ' '. $_POST['timeStart'];
		$dateTimeEnd = $dateEnd->format("Y-m-d") . ' ' . $_POST['timeEnd'];
        
		if($dateTimeStart >= $dateTimeEnd)
		{
			print '2';

			$link->close();

			exit();
		}
		
		if($link->query("UPDATE Meeting 
								SET date_start = '".$dateTimeStart."',
                                    date_end = '".$dateTimeEnd."'
								WHERE id_meeting = '".$_POST['meetingdata']."'"))
			print '1';
		else 
			print '0';
		
		$link->close();
	}
	
	// добавить вопрос к собранию
	if(isset($_POST['addQuestionButton'])) 
	{
		$query = $link->query("SELECT MAX(sequence_no) AS max_sequence_no
									FROM Question
									WHERE id_meeting = '".$_POST['meetingdata']."'");
		
		if(!$query)
		{
			print '2';
			$link->close();
			exit();
		}
									
		$query = $query->fetch_assoc();
		
		$sequenceNo = $query['max_sequence_no'];	
						
		$sequenceNo = $sequenceNo + 1;
		
		$link->query("INSERT into Question  (id_meeting, sequence_no, question) 
									values ('".$_POST['meetingdata']."', '".$sequenceNo."', '".$_POST['newQuestion']."') ");
									
		 header('Location: changeMeeting.php');
	}
	
	// удалить вопрос
	if(isset($_POST['deleteQuestionButton'])) 
	{
		$link->query("DELETE FROM Question
							WHERE id_question = '".$_POST['questiondata']."'");
							
		header('Location: changeMeeting.php');
	}
	
	if(isset($_POST['changeQuestionBut']))
	{
		if($link->query("UPDATE Question 
								SET question = '".$_POST['newQuestion']."'
								WHERE id_question = '".$_POST['questiondata']."'"));
								
		header('Location: changeMeeting.php');
	}
	
?>