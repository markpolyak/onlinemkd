// вывод календаря
			$(function() {

				$.datepicker.setDefaults( $.datepicker.regional[ "ru" ] );

	    		$( "#dateStart" ).datepicker({
					dateFormat: 'dd.mm.yy'
				});
				$("#dateEnd").datepicker({
					dateFormat: 'dd.mm.yy'
				});
			});

