$(window).load(function(){
	$('#buscarUsuario').keyup(function(){
		if($(this).val().length >= 3 ){
			$.ajax({
				type: 'POST',
				url: '../ajax/busca_usuario.php',
				data: 'nome='+$(this).val(),
				success:function(retorno){
					$('.ms-selectable').find('.ms-list').find('li').each(function(){
						if($(this).is(':visible')){
							$(this).remove();
						}
					});

					$('#id_usuarios_select').find('option').each(function(){
						if(!$(this).is(':selected')){
							$(this).remove();
						}
					});

					$("#id_usuarios_select").multiSelect("addOption", JSON.parse(retorno));
				}
			});
		}
	});

});