<?php

  //require_once 'check.php'; // Подключение скрипта для проверки доступа к закрытому контенту
  require_once 'BaseData.php'; // параметры для доступа к БД
  require_once 'meeting.php'; // Подключение скрипта с запросами к БД
  require_once 'tcpdf/tcpdf.php'; // Подключение основной библиотеки
  require_once 'tcpdf/tcpdf_barcodes_2d.php'; // Подключение библиотекеи для работы с QR-кодами
  
 ini_set('error_reporting', E_ALL);
 ini_set('display_errors', 1);
 ini_set('display_startup_errors', 1);
 ini_set('memory_limit', '640M');
 
 ob_end_clean(); 
   
# Соединение с БД
$link = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($link->connect_errno) {
	echo 'Не удалось подключиться к mysql: (" . $link->connect_errno . ") ' . $link->connect_error;
}

// Установка кодировки для базы данных
$link->set_charset("utf8");
	
// Извлечение информации о пользователе
$userdata = selectUser($link);
  
  // Класс для создания собственного пользовательского заголовка и сноски
class MYPDF extends TCPDF {

    // Заголовок страницы
    public function Header() {
	
		global $userdata;
		global $link;

		$this->SetXY(20, 20); // Позиция заголовка
        $this->SetFont('dejavusans', '', 20); // Установка шрифта
        $this->Cell(0, 15, 'Анкета', 0, false, 'C', 0, '', 0, false, 'M', 'M'); // Надпись заголовка

		$this->SetFont('dejavusans', '', 12); // Установка шрифта и его размера 

		$this->Rect(10, 10, 5, 5, 'F', array(), array(0, 0, 0)); // Метка слева сверху
		$this->Rect($this->getPageWidth() - 15, 10, 5, 5, 'F', array(), array(0, 0, 0)); // Метка справа сверху
    
		$this->Ln(20);

		// Вывод ФИО
		$textHeight = 5;
		$textWidth = 20;
		$this->Cell($textWidth, $textHeight, 'ФИО:'); // текст шириной 20 и высотой 5

		if( is_null($userdata['id_owner']) )
			$this->MultiCell(110, $textHeight, '-', 0, 'L', false, 1);
		else
			$this->MultiCell(110, $textHeight, $userdata['surname'].' '. $userdata['name'].' '.$userdata['patronymic'], 0, 'L', false, 1);
		$this->Ln(2);
	
		global $link;

		// Извлечение информации о здании (адресе)
		$query = selectBuilding($link, $userdata);
		while($buildingdata = $query->fetch_array()){
			$this->MultiCell(110, $textHeight, 'Адрес:   ул. '.$buildingdata['street'].',дом '.$buildingdata['street_number'].', кв. '.$buildingdata['number'], 0, 'L', false, 1); // текст шириной 20 и высотой 5
			$this->Ln(2);
		}
	
		// Если пользователь является собственником, ты вывести долю в праве собственности по каждому помещению
		$query = selectShare($link, $userdata);
		if(empty($query))
		{
			$this->Cell ($textWidth, $textHeight, 'Доля в праве собственности: -');
		}
		else
			while ($sharedata = $query->fetch_array()) {
				$this->Cell ($textWidth, $textHeight, 'Доля в праве собственности: '.($sharedata['share_numerator']/$sharedata['share_denominator']*100).'%');
			}

		// QRCODE,H : QR-CODE с наилучшей коррекцией ошибок в шапке документа
		// id пользователя, id собственника, id собрания, номер страницы, версия
		$this->write2DBarcode('user_id = '.$userdata['id'].' owner_id = '.$userdata['id_owner'].' meeting_id = '.$_GET['meetingdata'].' page = '.$this->getPage().'/'.$this->getNumPages().' version = 1', 'QRCODE,H', $this->getPageWidth() - 50, 40, 25, 25, '', 'N');

	}
	
	// Нижний колонтитул с номером страницы
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', '', 8);
        // Page number
        $this->Cell(0, 10, $this->getPage().'/'.$this->getNumPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        $this->Rect(10, $this->getPageHeight() - 15, 5, 5, 'F', array(), array(0, 0, 0)); // Метка слева снизу
		$this->Rect($this->getPageWidth() - 15, $this->getPageHeight() - 15, 5, 5, 'F', array(), array(0, 0, 0)); // Метка справа снизу
    }
}

generatePDF();

function generatePDF() {
	
  /* Создаём объект TCPDF.
  - Книжная ориентация
  - Единица измерения - миллиметры
  - Формат А4
  - Использование unicode
  - Кодировка - UTF-8
  */
  $pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8');
   
	$marginFromTop = 40;
	$marginFromLeft = 20;
	$pdf->SetMargins($marginFromLeft, $marginFromTop, 20);

	$pdf->AddPage(); // Добавление страницы

	$pdf->SetFont('dejavusans', '', 16); // Установка шрифта и его размера
  
	$marginFromTop = 80;
	$marginFromLeft = 10;
	$pdf->SetMargins($marginFromLeft, $marginFromTop); // Установка отсупов
  
	global $link;

	// Выбор вопросов
	$query = selectQuestion($link, $_GET['meetingdata']);	

	$i = 0; 

	while ($questiondata = $query->fetch_array()) {

		if(($pdf->GetY() + $pdf->getStringHeight(180, $questiondata['question'])) >= $pdf->getPageHeight() - 40)
		{
			$pdf->AddPage();
			$pdf->SetXY($marginFromLeft, $marginFromTop);
		}

		if($i == 0)
			$pdf->SetXY($marginFromLeft, $marginFromTop);
		
		// Вывод вопроса - ширина, высота, текст, толщина рамки, выравнивание по левому краю, без заднего фона, переход курсора присутствует
		//$pdf->MultiCell(180, 0, $questiondata['question'], 0, 'L', false, 1);
		
		$question = ''. $questiondata['question'];
		
		$pdf->writeHTML($question, true, false, true, false, '');
		// Вывод вариантов ответа
		$pdf->Ln(5);
		
		// Вывод QR-кода с наилучшей коррекцией ошибок
		$pdf->write2DBarcode($questiondata['id_question'], 'QRCODE,H', $pdf->getPageWidth() - 20, '', 10, 10, '', '');
		
		$pdf->Cell(7, 7, '', 1); // строка для отображения квадратика
		$pdf->Cell(3, 7, '');
		$pdf->Cell(33, 5, 'За');
		$pdf->Cell(20, 5, '');
		$pdf->Cell(7, 7, '', 1); // строка для отображения квадратика
		$pdf->Cell(3, 7, '');
		$pdf->Cell(33, 5, 'Против');
		$pdf->Cell(20, 5, '');
		$pdf->Cell(7, 7, '', 1); // строка для отображения квадратика
		$pdf->Cell(3, 7, '');
		$pdf->Cell(33, 5, 'Воздержался');
		$pdf->Ln(15);
		
		$i++;
		
	}

	$pdf->Output('test.pdf', 'I'); // Выводим в браузер
 }
?>