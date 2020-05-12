$(function() {
	
	$(function() {
		$(window).load(function(){
			$("#preloaderPaginaAndamento").remove();
			$("#paginaAndamento").show();
		});
	});
	
	$("img.abrir-detalhes").click(function (e) {
		  if($('#tr-aberta').length){
			  $('#tr-aberta').remove();
		  }
		  
		  var preloader = '<center><img src="imagens/preloader.gif" width="23" height="23"></center>';
			
		  e.stopPropagation();
		  var domEl = $(this).parent().parent();
		  domEl.after('<tr class="r0" id="tr-aberta"><td colspan="8"><div id="div-detalhes-cursos">'+preloader+'</div></td></tr>');
		  detalhesCursos($(this).attr('id'));
	});
});

function detalhesCursos(id){
	$(function() {
		$.ajax({
			type : 'POST',
			data : 'id='+id,
			url : 'ajax/detalhesRelatorioAndamento.php',
			success : function(retorno) {
				$('#div-detalhes-cursos').html(retorno);
			},
			error : function(x, t, m) {
				if(t==="timeout") {
					$('#div-detalhes-cursos').html('Erro: Tempo de processamento esgotado.');
				}else{
					$('#div-detalhes-cursos').html('Erro ao requisitar pagina.');
				}
				
			}
		});
	});
}
function fecharDetalhesCursos(){
	  $('#tr-aberta').remove();
}