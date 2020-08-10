var socket = io('https://io.qisat.com');

$(function(){
	$('body').css('display','none');
	$.ajax({
		type: 'POST',
		url: '/local/controle_acesso/get_dados_acesso.php',
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