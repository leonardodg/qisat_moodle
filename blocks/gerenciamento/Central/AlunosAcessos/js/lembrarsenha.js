$(function(){
	$('.enviarLembreteSenha').click(function(){

		var obj = $(this).data();
		
		if(!obj.enviado){
			obj.enviado = true;
			var elemento =  $(this);

			var srcImg = elemento.attr('src');
			elemento.attr('src',obj.load);
			if(elemento.parent().find('#erroEnvio').length > 0){
				elemento.parent().find('#erroEnvio').remove();
			}

			$.ajax({
				type: 'POST',
				data: 'id='+obj.id,
				url: 'enviaremailsenha.php',
				success:function(retorno){

					retorno = JSON.parse(retorno);
					if(retorno.enviado){
						elemento.css('cursor','default');
						elemento.attr('src',obj.ok);
					}else{
						obj.enviado = false;
						elemento.attr('src',srcImg);
						var imagemErro = '<img src="'+obj.erro+'">';
						elemento.after('<span id="erroEnvio" style="color:red;"><br />'+imagemErro+retorno.mensagem+'</span>');
					}
				},
				error : function(result) {
					var imagemErro = '<img src="'+obj.erro+'">';
					var mensagem = 'Erro ao enviar requisição';

					elemento.attr('src',srcImg);
					elemento.after('<span id="erroEnvio" style="color:red;"><br />'+imagemErro+mensagem+'</span>');
				}
			});
		}
	});
});