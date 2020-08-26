$(document).ready(function(){

	$("input.number_students").keyup(function (){

		var subtotal = 0;

		$("input.number_students").each(function (){
			
			subtotal = subtotal + parseInt($(this).val());
		
		});

		$("input.total_students").val(subtotal);

	});

	$("input.number_students").change(function (){

		var subtotal = 0;

		$("input.number_students").each(function (){
			
			subtotal = subtotal + parseInt($(this).val());
		
		});

		$("input.total_students").val(subtotal);

	});

	$("a.deletar_atividade").click(function (){

		if (confirm("Do you really want to remove this subject from the platform?")){
			$.ajax({

				url : "assets/ajax/ajax_delete_activity.php",
				type: "POST",
				data: {
					id : $(this).attr("data-id")
				},
				dataType: "html",
				success: function(data){				
					console.log(data);
					if (data == "ok"){
						alert("Subject deleted!");
						window.location.reload();
					}
					else{
						alert("The subject could not be deleted!");
					}

				},
				error: function(data){
					console.log("ERRO - " + data);
				}					

			});
		}

	});


	$("input#classroom_time").inputmask("99:99");
	$("input#online_time").inputmask("99:99");


});