$(document).ready(function (){
	//alert("Hello");

	$("button#btnSaveNewBimester").click(function (e){

		e.preventDefault();

		var ETAs = [];

		$('input.chkETA:checked').each(function(i, e) {
		    ETAs.push($(this).val());
		});

		$.ajax({

			url : "assets/ajax/ajax_add_new_bimester.php",
			type: "POST",
			data: {
				starting_date : $("input#newBimesterDate").val(),
				etas : ETAs
			},
			dataType: "text",
			success: function(data){
				console.log("OK - " + data);

				if (data == "ok"){
					alert("New bimester saved!");
					//location.reload();
					if ($("input[name=txtStartDate]").val() == "" && $("input[name=txtEndDate]").val() == ""){
						reload_week_view();
					}
					else{
						$("button#btnSearchBimester").trigger("click");	
					}
					
				}
				else{
					alert("Some error occur!");	
				}

			},
			error: function(data){
				console.log("ERRO - " + data);
			}

		});

	});

	
	$(document).on("click", "button.btnAddEtaModal", function (){
		console.log("teste");

		$("input#bimesterIdEta").val($(this).attr("data-bimester"));

		$.ajax({

			url : "assets/ajax/ajax_load_etas.php",
			type: "POST",
			data: {id : $(this).attr("data-bimester")},
			dataType: "html",
			success: function(data){
				//console.log("OK - " + data);

				if (data == "n"){					
					alert("Some error occur!");	
				}
				else{
					$("div#listOfEtasBimester").html(data);
				}

			},
			error: function(data){
				console.log("ERRO - " + data);
			}

		});

	});


	$(document).on("click", "button.btnAddActivityToEta", function (){

		$("input#bimesterIdEtaActivity").val($(this).attr("data-bimester"));
		$("input#weekIdEtaActivity").val($(this).attr("data-week"));
		$("input#etaIdActivity").val($(this).attr("data-eta"));

		$("span#nome_eta").html($(this).attr("data-etanome"));
		$("span#semana_id").html($(this).attr("data-semana"));

		$.ajax({

			url : "assets/ajax/ajax_load_activities_bimester.php",
			type: "POST",
			data: {id : $(this).attr("data-bimester")},
			dataType: "html",
			success: function(data){
				//console.log("OK - " + data);

				if (data == "n"){					
					alert("Some error occur!");	
				}
				else{
					$("div#listOfActivitiesBimester").html(data);
				}

			},
			error: function(data){
				console.log("ERRO - " + data);
			}

		});

	});


	$(document).on("click", "button#btnSaveEtaBimester", function (e){

		e.preventDefault();

		var ETAs = [];

		$('input.chkETA:checked').each(function(i, e) {
		    ETAs.push($(this).val());
		});

		$.ajax({

			url : "assets/ajax/ajax_add_eta_bimester.php",
			type: "POST",
			data: {
				id : $("input#bimesterIdEta").val(),
				etas : ETAs
			},
			dataType: "text",
			success: function(data){
				console.log("OK - " + data);

				if (data == "ok"){
					alert("ETAs added!");
					if ($("input[name=txtStartDate]").val() == "" && $("input[name=txtEndDate]").val() == ""){
						reload_week_view();
					}
					else{
						$("button#btnSearchBimester").trigger("click");	
					}
				}
				else{
					alert("Some error occur!");	
				}

			},
			error: function(data){
				console.log("ERRO - " + data);
			}

		});

	});


	$(document).on("click", "button#btnSaveEtaActivityBimester", function (e){

		e.preventDefault();

		var total_horas = 0;
		//$("#success-alert").hide();
  		
		/*$("input.txtAddEstimatedHours").each(function (){

			var horas = $(this).val();
			//horas = horas.substring(0, horas.indexOf(":") - 1);
			total_horas = parseInt(total_horas) + parseInt(horas);			

		});*/

		if (total_horas > 25){

			/*$("#success-alert").fadeTo(2000, 500).slideUp(500, function(){
	       		$("#success-alert").slideUp(500);
	        });*/
	        //$("#success-alert").show();
			return false;

		}
		

		var ACTIVITIES = [];

		$('input.chkACTIVITY:checked').each(function(i, e) {
		    ACTIVITIES.push($(this).val());
		});

		$.ajax({

			url : "assets/ajax/ajax_add_activity_bimester.php",
			type: "POST",
			data: $("form#frmAddEtaActivity").serialize(),
			dataType: "text",
			success: function(data){
				console.log("OK - " + data);

				if (data == "ok"){
					alert("Activities added!");
					
					if ($("input[name=txtStartDate]").val() == "" && $("input[name=txtEndDate]").val() == ""){
						reload_week_view();
					}
					else{
						$("button#btnSearchBimester").trigger("click");	
					}

					$("#modalAddActivity").modal("toggle");

				}
				else{
					alert("Some error occur!");	
				}

			},
			error: function(data){
				console.log("ERRO - " + data);
			}

		});

	});

	$(document).on("click", "a#btnAddACtivityToTable", function (){
		
		var actName = $("select#selActivity option:selected").text();
		var btnClose = '<button class="btn btn-danger btn-sm btnDeleteLine">X</button>';
		var actId = $("select#selActivity").val();
		var textEstimatedHours = '<input type="text" class="form-control txtAddEstimatedHours" name="activity_estimated_'+actId+'" />';
		var inputHiddenId = '<input type="hidden" name="activity_eta_bimester[]" value="'+actId+'" />';

		$("tbody#tbListOfActivities").append('<tr>'+inputHiddenId+'<td>'+actName+'</td><td>'+textEstimatedHours+'</td><td>'+btnClose+'</td></tr>');		

	});

	$(document).on("click", "button.btnDeleteLine", function (){
		$(this).parent().parent().remove();
	});

	$(document).on('DOMNodeInserted', 'input.txtAddEstimatedHours', function(e) {
  		$(this).inputmask("99:99");
	});

	$(document).on("click", "button.btnActivityInfo", function() {

		var eta_selecao = ($(this).attr("data-eta"));
		var eta_online = ($(this).attr("data-eta-online"));
		var bimester_id = ($(this).attr("data-bimester-id"));
		var week_conta = ($(this).attr("data-week-conta"));
		
		$.ajax({

			url : "assets/ajax/ajax_load_activity_info.php",
			type: "POST",
			data: {
				id : $(this).attr("data-id"),
				eta_id : $(this).attr("data-eta"),
				week_id : $(this).attr("data-week")
			},
			dataType: "html",
			success: function(data){				

				$("div#etaActivityInformation").html(data);	


				if (eta_selecao != eta_online){
					console.log("teste");
					$("a#activity_create_new_link").attr("disabled", "disabled");
					$("a#activity_create_new_link").removeAttr("href");
					$("a#activity_create_new_link").hide();
				}
				else{
					$("a#activity_create_new_link").show();
					$("a#activity_create_new_link").removeAttr("disabled");
					$("a#activity_create_new_link").attr(
						"href", 
						M.cfg.wwwroot + "/course/edit.php?category=" + 
						$("input#institution_id").val() + "&returnto=catmanage&actname=" + 
						$("h5#activity_name_info").html() + "&bimester=" + bimester_id + "&semana=" + week_conta + "&syl=" + $("span#syllabus_info").text()
					);
				}

				

			},
			error: function(data){
				console.log("ERRO - " + data);
			}

		});
	});

	$("button#btnDeleteEtaActivityBimester").click(function (){

		if (confirm("Do you really want to remove this activity from this ETA?")){

			$.ajax({

				url : "assets/ajax/ajax_delete_activity_eta.php",
				type: "POST",
				data: {
					id : $("input#activityDeleteEtaActivityBimester").val(),
					eta_id : $("input#etaDeleteEtaActivityBimester").val(),
					week_id : $("input#etaDeleteEtaActivityWeek").val()
				},
				dataType: "html",
				success: function(data){				

					console.log("OK - " + data);

					if (data == "ok"){
						alert("Activity removed!");

						if ($("input[name=txtStartDate]").val() == "" && $("input[name=txtEndDate]").val() == ""){
							reload_week_view();
						}
						else{
							$("button#btnSearchBimester").trigger("click");	
						}
						
						$("#modalInfoActivity").modal("toggle");

						//location.reload();
					}
					else{
						alert("Some error occur!");	
					}

				},
				error: function(data){
					console.log("ERRO - " + data);
				}

			});

		}

	});


	$(document).on("click", "#btnEditEtaActivityBimester", function (){
		window.location.href = "planning.php?id=" + $("input#activityEditEtaActivityBimester").val();
	});


	$("div.loadWeekView").html("");

	var userETA = 0;

	if ($("input#isUserEta").val() != undefined){
		userETA = $("input#isUserEta").val();
	}

	var adminInstitution = 0;

	if ($("select#admin_institution").val() != undefined){
		adminInstitution = $("select#admin_institution").val();
	}

	console.log("A - " + adminInstitution);

	$.ajax({

		url : "assets/ajax/ajax_load_week_view_dates.php",
		type: "POST",
		data: {
			id : $("select#selBimester").val(),
			eta : userETA,
			institution : adminInstitution
		},
		dataType: "html",
		success: function(data){				

			$("div.loadWeekView").html(data);	

			/*$("span.horas_total_semana").each(function (){

				if ( parseInt($(this).html()) > 25){
					$("#success-alert_week").fadeTo(2000, 500).slideUp(500, function(){
	               		$("#success-alert_week").slideUp(500);
	                });
				} 

			});*/

		},
		error: function(data){
			console.log("ERRO - " + data);
		}

	});

	$("button#btnSearchBimester").click(function (e){
		e.preventDefault();

		$("div.loadWeekView").html("");


		if ($("select#selBimester").val() == 9999){
			$("div.mensagem_july").show();
		}
		else{
			$("div.mensagem_july").hide();

			var userETA = 0;

			if ($("input#isUserEta").val() != undefined){
				userETA = $("input#isUserEta").val();
			}

			var adminInstitution = 0;

			if ($("select#admin_institution").val() != undefined){
				adminInstitution = $("select#admin_institution").val();
			}

			console.log("A - " + adminInstitution);

			$.ajax({

				url : "assets/ajax/ajax_load_week_view_dates.php",
				type: "POST",
				data: {
					//start : $("input[name=txtStartDate]").val(), 
					//end : $("input[name=txtEndDate]").val(),
					id : $("select#selBimester").val(),
					eta : userETA,
					institution : adminInstitution
				},
				dataType: "html",
				success: function(data){				

					$("div.loadWeekView").html(data);	

				},
				error: function(data){
					console.log("ERRO - " + data);
				}

			});

		}
		

	});

	$(document).on("click", "a.btnModalEtaSendReport", function (){

		var week_id = $(this).attr("data-week");
		var bimester_id = $(this).attr("data-id");
		$("form#frmEtaSendReport").find("input[name=week_id]").val(week_id);
		$("form#frmEtaSendReport").find("input[name=bimester_id]").val(bimester_id);

	});

	$(document).on("click", "button#btnEtaSendReport", function (){

		$.ajax({

			url : "assets/ajax/ajax_add_new_eta_report.php",
			type: "POST",
			data: $("form#frmEtaSendReport").serialize(),
			dataType: "text",
			success: function(data){

				console.log("OK - " + data);

				if (data == "ok"){
					alert("Report sent!");
					
					if ($("input[name=txtStartDate]").val() == "" && $("input[name=txtEndDate]").val() == ""){
						reload_week_view();
					}
					else{
						$("button#btnSearchBimester").trigger("click");	
					}

					$("#modalAddActivity").modal("toggle");

				}
				else{
					alert("Some error occur!");	
				}

			},
			error: function(data){
				console.log("ERRO - " + data);
			}

		});

	});

	$(document).on("click", "a.btnModalEtaSendReportInfo", function (){

		$.ajax({

			url : "assets/ajax/ajax_load_eta_report_info.php",
			type: "POST",
			data: {id : $(this).attr("data-id")},
			dataType: "text",
			success: function(data){

				$("div#divEtaSendReportInfo").html(data);				

			},
			error: function(data){
				console.log("ERRO - " + data);
			}

		});

	});

	//console.log($("input[name=txtStartDate]").val());

	$("select#admin_institution").change(function (){

		var start = 9999;
		var end = 9999;

		if ($("input[name=txtStartDate]").val() != ""){
			start = $("input[name=txtStartDate]").val();
		}

		if ($("input[name=txtEndDate]").val() != ""){
			end = $("input[name=txtEndDate]").val();
		}

		$("div.loadWeekView").html("");

		var userETA = 0;

		if ($("input#isUserEta").val() != undefined){
			userETA = $("input#isUserEta").val();
		}

		var adminInstitution = 0;

		if ($("select#admin_institution").val() != undefined){
			adminInstitution = $("select#admin_institution").val();
		}

		console.log("A - " + adminInstitution);

		$.ajax({

			url : "assets/ajax/ajax_load_week_view_dates.php",
			type: "POST",
			data: {
				id : $("select#selBimester").val(),
				start : start, 
				end : end,
				eta : userETA,
				institution : adminInstitution
			},
			dataType: "html",
			success: function(data){				

				$("div.loadWeekView").html(data);

			},
			error: function(data){
				console.log("ERRO - " + data);
			}

		});

	});

	
});


function reload_week_view(){


	if ($("select#selBimester").val() == 9999){
		$("div.mensagem_july").show();
	}
	else{
		$("div.mensagem_july").hide();

		var adminInstitution = 0;

		if ($("select#admin_institution").val() != undefined){
			adminInstitution = $("select#admin_institution").val();
		}

		console.log("A - " + adminInstitution);
		
		$.ajax({

			url : "assets/ajax/ajax_load_week_view_dates.php",
			type: "POST",
			data: {
				id : $("select#selBimester").val(),
				eta : userETA,
				institution : adminInstitution
			},
			dataType: "html",
			success: function(data){				

				$("div.loadWeekView").html(data);		

			},
			error: function(data){
				console.log("ERRO - " + data);
			}

		});

	}

}