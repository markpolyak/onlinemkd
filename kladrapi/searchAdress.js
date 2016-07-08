(function($){
    			$(function() {
       				var container = $('#form2');
        		
					// Изменение родительского объекта для автодополнения улиц по СПб
					container.find( '[name="street"]').kladr('parentId', '7800000000000');
		
        			// Автодополнение улиц
        			container.find( '[name="street"]' ).kladr({
            			token: '572886ff0a69dee32f8b45b8',
            			key: '',
            			type: $.kladr.type.street,
            			parentType: $.kladr.type.city,
						verify: true, 
						select: function( obj ) {
							// Изменения родительского объекта для автодополнения домов
							container.find( '[name="building"]' ).kladr('parentId', obj.id);
						},
						check: function ( obj ) {
							
							if (obj) 
								$('#streetError').html('');
							 else 
								$('#streetError').html('<font size = "2" color = "red">Неверный ввод</font>');					
						},
						checkBefore: function ( obj ) {
							
							$('#streetError').html('');
						}
        			});
					
					// Автодополнение домов
        			container.find( '[name="building"]' ).kladr({
            			token: '572886ff0a69dee32f8b45b8',
            			key: '',
            			type: $.kladr.type.building,
            			parentType: $.kladr.type.street, 
						verify: true, 
						check: function ( obj ) {
							
							if (obj) 
								$('#buildingError').html('');
							 else 
								$('#buildingError').html('<font size = "2" color = "red">Неверный ввод</font>');					
						},
						checkBefore: function ( obj ) {
							
							$('#buildingError').html('');
						}
        			});				
   				});
			})(jQuery);
