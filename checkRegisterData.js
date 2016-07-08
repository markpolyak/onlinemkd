function tooltip(obj, msg)
{
	$( ".selector" ).tooltip( "open" );
	$("#"+obj.id).tooltip ({
		
		/*show: {
			//effect: 'drop',
			duration: 500			
		},*/
		content: msg
	});
}

// функция для проверки введенных данных в реальном времени
			$(document).ready(function() {
				
				$('#password1').change(function() {
					var password = $("#password1").val();
					var password2 = $("#password2").val();
					
					if ( password.length < 8 && password.length != 0 ) // проверка длины пароля
						//$('#pswError').html('<font size = "2" color = "red">Длина пароля должна быть не меньше 8 символов</font>');
						tooltip( $('#password1')[0], "Длина пароля должна быть не меньше 8 символов");
					else
							if ( ( password2 != password ) && (password2.length != 0 && password.length != 0) ) { // проверка равенства паролей
								$('#psw2Error').html('<font size = "2" color = "red">Пароли не совпадают</font>');
								$('#pswError').html('');
							}
							else {
								$('#pswError').html('');
								$('#psw2Error').html('');
							}
				});
				
				$('#password2').change(function() {
					var password = $("#password1").val();
					var password2 = $("#password2").val();
					
					if ( ( password2 != password ) && (password.length != 0 && password2.length != 0) ) // проверка равенства паролей
						$('#psw2Error').html('<font size = "2" color = "red">Пароли не совпадают</font>');
					else
						$('#psw2Error').html('');
				});
			});
