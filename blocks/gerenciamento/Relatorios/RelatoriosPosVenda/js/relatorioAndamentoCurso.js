$(function(){
	$("#id_curso").change(function(){
		$('#turmaSelecionado').val('');
		ajaxSelectTurmaCurso($(this).val());
	});
	
	$( window ).load(function() {
		ajaxSelectTurmaCurso($("#id_curso").val());
	});
	
	$("#id_turma").change(function(){
		$('#turmaSelecionado').val($(this).val());
	});
	
});

function ajaxSelectTurmaCurso(id){
	if(id > 0){
		
		var preloader = '<img src="imagens/preloader.gif" width="23" height="23" id="preloaderTurma" />';
		
		$("#id_turma").after(preloader);
		
		$.ajax({
			type : 'POST',
			data : 'curso='+id,
			url : 'ajax/selectTurmas.php',
			success : function(retorno) {
				$('#id_turma').html(retorno);
				if($.trim($('#turmaSelecionado').val()) != ''){
					$('#id_turma option[value="' + $('#turmaSelecionado').val()+ '"]').attr({
						selected : "selected"
					});
				}
				$("#preloaderTurma").remove();
			},
			error : function(x, t, m) {
				if(t==="timeout") {
					$('#div-detalhes-cursos').html('Erro: Tempo de processamento esgotado.');
				}else{
					$('#div-detalhes-cursos').html('Erro ao requisitar pagina.');
				}
				
				$("#preloaderTurma").remove();
			}
		});
	}
}