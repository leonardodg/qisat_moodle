function bloquearUsuarioPlataforma(bloquear) {
	var msg = 'Tem certeza que deseja bloquear esse usuário?';
	if(!bloquear){
		msg = 'Tem certeza que deseja desbloquear esse usuário?';
	}
	if (confirm(msg)) {
		var inp = document.createElement("input");
		inp.setAttribute("type", "hidden");
		inp.setAttribute("name", "bloquear");
		inp.setAttribute("value", bloquear);

		document.form1.appendChild(inp);
		document.form1.submit();
	}
}
function bloquearCursoUsuario() {
	if (confirm('Tem certeza que deseja bloquear/desbloquear esse(s) curso(s)?')) {
		document.form1.submit();
	}
}
function marcarTodos() {
	$(":checkbox").prop('checked', $("#bloquearTodos").is(":checked"));
}

$(function(){
	$('input[type*=checkbox]').click(function(){
		var data = $(this).data();

		if($(this).is(':checked'))
			$('input[data-fase*='+data.fase+']').prop('checked', true);
	});
});