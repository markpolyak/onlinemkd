<?php
	
	# Функция для генерации случайной строки
	function generateCode($length=6) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
		$code = "";
		$clen = strlen($chars) - 1;
		while (strlen($code) < $length) {
				$code .= $chars[mt_rand(0,$clen)];
		}
		return $code;
	}
	
?>