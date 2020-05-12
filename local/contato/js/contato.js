$(function(){
	$('#id_estado').change(function(){
		$('#idcidade').val('');
		if($(this).val().trim() != ''){
			if($(this).val() == 0){
				$('#idcidade').val($(this).val());
				$('#id_cidade').fadeOut();
				$('label[for="id_cidade"]').fadeOut();
			}else{
				selectCidade($(this).val(),false);
			}
		}
	});

	$('#id_cidade').change(function(){
		$('#idcidade').val($(this).val());
	});

	$(window).load(function(){
		if($('#id_estado').val().trim() != '' && $('#id_estado').val() != 0){
			var defaultCidade = ($('#idcidade').val() != 0);
			selectCidade($('#id_estado').val(),defaultCidade);
		}
	});
});

function selectCidade(id,defaultCidade){
	$.ajax({
		type: 'POST',
		url: 'ajax/busca_cidades.php',
		data: 'id='+id,
		success:function(retorno){
			$('#id_cidade').html(retorno);

			if(!$('#id_cidade').is(':visible')){
				$('#id_cidade').fadeIn();
				$('label[for="id_cidade"]').fadeIn();
			}
			if(defaultCidade){
				$('#id_cidade option[value="' + $('#idcidade').val()+ '"]').attr({selected : "selected"});
			}
		}
	});
}