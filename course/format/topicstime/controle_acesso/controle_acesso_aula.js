var socket = io('http://localhost:3000');

$(function(){
	$('body').css('display','none');
	$.ajax({
		type: 'POST',
		url: '/course/format/topicstime/controle_acesso/get_dados_acesso.php',
		success:function(retorno){
			$('body').css('display','block');
			retorno = JSON.parse(retorno);
			if(retorno.acesso){
				socket.emit('set dados_acesso', retorno);
			}else{
				$('body').html(retorno.mensagem);
			}
		}
	});
});