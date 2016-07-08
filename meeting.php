<?php

if(!isset($_SESSION))
	session_start();

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// функция для выбора собрания
function selectMeeting($link, $userdata)
{	
	$query = $link->query("SELECT *
								FROM Meeting, Premise
								WHERE Premise.id_premise = '".$userdata['id_premise']."' and
								Meeting.id_building = Premise.id_building
								ORDER BY date_end DESC");
	if($query == false) 
		throw new Exception('Ошибка выбора собрания из БД');
							
	$num = $query->num_rows; // количество результатов запроса
	if($num > 0)
		return $query;
	else
		return false;
}

// Выбор информации о пользователе
function selectUser($link) {
	
	// Запрос для извлечения информации и пользователе
	$query = $link->query("SELECT *
							FROM Users
							WHERE email = '".$_SESSION['user_email']."'");
	if($query == false) 
		throw new Exception('Ошибка выбора пользователя из БД');

	$userdata = $query->fetch_assoc();
	
	return $userdata;
}

// Выбор информации об адресе
function selectBuilding($link, $userdata) {
	
	$query = $link->query("SELECT *
								FROM Building, Premise
								WHERE Premise.id_premise = '".$userdata['id_premise']."' and
								Building.id_building = Premise.id_building");	
	if($query == false) 
		throw new Exception('Ошибка выбора дома из БД');
	
	return $query;
}

// Выбор информации о доле в праве собственности
function selectShare($link, $userdata)
{
	$query = $link->query("SELECT *
							FROM Property_rights
							WHERE id_owner = '".$userdata['id_owner']."' ");
							
	if($query == false)
		throw new Exception('Ошибка выбора доли в праве собственности из БД');
							
	return $query;
	
}

// выбор всех вопросов конкретного собрания
function selectQuestion($link, $meetingId) {
	
	$query = $link->query("SELECT *
							FROM Question
							WHERE id_meeting = '$meetingId'");
							
	if($query == false)
		throw new Exception('Ошибка выбора вопросов из БД');
							
	return $query;
}

// выбор всех имеющихся адресов
function selectAllBuilding($link)
{
	$query = $link->query("SELECT *
								FROM Building");	
								
	if($query == false)
		throw new Exception('Ошибка выбора всех домов из БД');
								
	return $query;
}

// функция для выбора собственника
function selectOwner($link, $name, $surname, $patronymic)
{
	$query = $link->query("SELECT *
								FROM Owner
								WHERE  name = '".$name."' and
								surname = '".$surname."' and
								patronymic = '".$patronymic."' ");
	
	if($query == false)
		throw new Exception('Ошибка выбора собственника из БД');				

	return $query;
}

function selectPremise($link, $buildingNum, $street, $premiseNumber, $block = NULL)
{
	if( is_null($block) )
		$query = $link->query("SELECT *
								FROM Building
								WHERE street = '".$street."'
								and street_number = '".$buildingNum."'");
	else 
		$query = $link->query("SELECT *
								FROM Building
								WHERE street = '".$street."'
								and street_number = '".$buildingNum."'
								and block = '".$block."' ");
	
	$query = $query->fetch_assoc();
	
	$query = $link->query("SELECT *
							FROM Premise
							WHERE id_building ='".$query['id_building']."'
							and number = '".$premiseNumber."'");
	
	$query = $query->fetch_assoc();
	
	return $query;	
}




