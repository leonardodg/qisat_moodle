var socket = io('https://io.qisat.com');

var jq = document.createElement('script');
jq.src = "https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js";
document.getElementsByTagName('head')[0].appendChild(jq);
jQuery.noConflict();

$(function(){
	$('body').css('display','none');
	$.ajax({
		type: 'POST',
		url: '/local/controle_acesso/get/get_dados_acesso.php',
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