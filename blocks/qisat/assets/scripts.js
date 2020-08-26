<script type="text/javascript">

if (
	document.referrer == "https://portal.fulbright.org.br/login/index.php" 
	&& 
	document.location.href != "https://portal.fulbright.org.br/login/index.php"
	&&
	document.location.href != "https://portal.fulbright.org.br/login/signup.php"
	&&
	document.location.href != "https://portal.fulbright.org.br/login/signup.php?"
	&&
	document.location.href != "https://portal.fulbright.org.br/login/forgot_password.php"
	&&
	document.location.href != "https://portal.fulbright.org.br/"
){	
	//document.location.href = "https://portal.fulbright.org.br/report/coursestats_fulbright/available_courses.php"; 
	document.location.href = "https://portal.fulbright.org.br/report/coursestats_fulbright/pm_week_view.php"; 
}

document.addEventListener("DOMContentLoaded", function(event) { 
	//console.log(document.querySelectorAll('[data-key="home"]'));
	var novo_link = document.querySelectorAll('[data-key="home"]');

	if (novo_link.length != 0){
		novo_link[0].innerHTML = '<a href="https://portal.fulbright.org.br/report/coursestats_fulbright/available_courses.php"><span class="text m-l-0">Home</span></a>';	
	}				
	
	if (window.location.href.indexOf('user/edit.php') != -1){
		var tipo = document.getElementById('id_profile_field_fulbright_profile').value;		
		if (tipo == "Student"){
			document.getElementById('id_profile_field_fulbright_profile').innerHTML = '<option value="Student">Student</option>';	
		}		
	}

	if (window.location.href.indexOf('/login/signup.php') != -1){
		document.getElementById('id_profile_field_fulbright_profile').innerHTML = '<option value="Student">Student</option>';
	}

	if (window.location.href.indexOf('/course/edit.php') != -1){

		//document.getElementById('id_category').disabled = true;
		document.querySelector("#id_category").parentNode.parentNode.style.display = "none";

		var d = new Date();


		document.getElementById("id_startdate_year").value = d.getFullYear();
		document.getElementById("id_enddate_year").value = d.getFullYear();

		var url_string = window.location.href;
		var url = new URL(url_string);
		
		var course_name = url.searchParams.get("actname");

		if (course_name != undefined && course_name != ""){

			var bimester = url.searchParams.get("bimester");
			var semana = url.searchParams.get("semana");

			var syllabus = url.searchParams.get("syl");

			document.getElementById("id_summary_editor").innerText = syllabus;


			var atividade_nome = url.searchParams.get("actname");

			document.getElementById("id_fullname").value = atividade_nome;
			document.getElementById("id_shortname").value = atividade_nome;
			
			

			if (bimester == 1){			

				if (semana <= 3){
					var id_startdate_month = "3";
					var id_enddate_month = "3";
					if (semana == 1){
						var id_startdate_day = "11";
						var id_enddate_day = "16";
					}
					else if (semana == 2){
						var id_startdate_day = "18";
						var id_enddate_day = "23";
					}
					else if (semana == 3){
						var id_startdate_day = "25";
						var id_enddate_day = "30";
					}
				}
				else{
					var id_startdate_month = "4";
					var id_enddate_month = "4";

					if (semana == 4){
						var id_startdate_day = "1";
						var id_enddate_day = "6";
					}
					else if (semana == 5){
						var id_startdate_day = "8";
						var id_enddate_day = "13";
					}
					else if (semana == 6){
						var id_startdate_day = "15";
						var id_enddate_day = "20";
					}
					else if (semana == 7){
						var id_startdate_day = "22";
						var id_enddate_day = "27";
					}
					else if (semana == 8){
						var id_startdate_day = "29";
						var id_enddate_day = "4";
						id_enddate_month = "5";
					}				

				}

			}
			else if (bimester == 2){

				if (semana <= 4){
					var id_startdate_month = "5";
					var id_enddate_month = "5";
					if (semana == 1){
						var id_startdate_day = "6";
						var id_enddate_day = "11";
					}
					else if (semana == 2){
						var id_startdate_day = "13";
						var id_enddate_day = "18";
					}
					else if (semana == 3){
						var id_startdate_day = "20";
						var id_enddate_day = "25";
					}
					else if (semana == 4){
						var id_startdate_day = "27";
						var id_enddate_day = "1";
						id_enddate_month = "6";
					}
				}
				else{
					var id_startdate_month = "6";
					var id_enddate_month = "6";

					if (semana == 5){
						var id_startdate_day = "3";
						var id_enddate_day = "8";
					}
					else if (semana == 6){
						var id_startdate_day = "10";
						var id_enddate_day = "15";
					}
					else if (semana == 7){
						var id_startdate_day = "17";
						var id_enddate_day = "22";
					}
					else if (semana == 8){
						var id_startdate_day = "24";
						var id_enddate_day = "29";					
					}				

				}

			}
			else if (bimester == 3){

				if (semana <= 4){
					var id_startdate_month = "8";
					var id_enddate_month = "8";
					if (semana == 1){
						var id_startdate_day = "5";
						var id_enddate_day = "10";
					}
					else if (semana == 2){
						var id_startdate_day = "12";
						var id_enddate_day = "17";
					}
					else if (semana == 3){
						var id_startdate_day = "19";
						var id_enddate_day = "24";
					}
					else if (semana == 4){
						var id_startdate_day = "26";
						var id_enddate_day = "31";					
					}
				}
				else{
					var id_startdate_month = "9";
					var id_enddate_month = "9";

					if (semana == 5){
						var id_startdate_day = "2";
						var id_enddate_day = "7";
					}
					else if (semana == 6){
						var id_startdate_day = "9";
						var id_enddate_day = "14";
					}
					else if (semana == 7){
						var id_startdate_day = "16";
						var id_enddate_day = "21";
					}
					else if (semana == 8){
						var id_startdate_day = "23";
						var id_enddate_day = "28";					
					}				

				}

			}
			else if (bimester == 4){

				if (semana <= 1){
					var id_startdate_month = "9";
					var id_enddate_month = "10";
					if (semana == 1){
						var id_startdate_day = "30";
						var id_enddate_day = "2";
					}
				}
				else{
					var id_startdate_month = "10";
					var id_enddate_month = "10";

					if (semana == 2){
						var id_startdate_day = "7";
						var id_enddate_day = "12";
					}
					else if (semana == 3){
						var id_startdate_day = "14";
						var id_enddate_day = "19";
					}
					else if (semana == 4){
						var id_startdate_day = "21";
						var id_enddate_day = "26";					
					}
					else if (semana == 5){
						var id_startdate_day = "28";
						var id_enddate_day = "2";
						id_enddate_month = "11";
					}
					else if (semana == 6){
						var id_startdate_day = "2";
						var id_enddate_day = "9";
						id_startdate_month = "11";
						id_enddate_month = "11";
					}

				}

			}


			
			//document.getElementById("id_enddate_enabled").setAttribute("checked", "checked");

			document.getElementById("id_startdate_month").innerHTML = "<option value='"+id_startdate_month+"'>" + id_startdate_month + "</option>";
			document.getElementById("id_enddate_month").innerHTML = "<option value='"+id_enddate_month+"'>" + id_enddate_month + "</option>";

			document.getElementById("id_startdate_day").innerHTML = "<option value='"+id_startdate_day+"'>" + id_startdate_day + "</option>";
			document.getElementById("id_enddate_day").innerHTML = "<option value='"+id_enddate_day+"'>" + id_enddate_day + "</option>";

			document.getElementById("id_startdate_year").innerHTML = "<option value='"+id_startdate_year+"'>" + id_startdate_year + "</option>";
			document.getElementById("id_enddate_year").innerHTML = "<option value='"+id_enddate_year+"'>" + id_enddate_year + "</option>";
		}

		


	}

	//custom-select singleselect
	if (window.location.href.indexOf('mod/glossary/view.php?id=51&mode=cat') != -1){

		
		var selectobject = document.querySelector("select.custom-select.singleselect");

  		for (var i = 0; i < selectobject.length; i++){
  			if (selectobject.options[i].value == '-1' ){
     			selectobject.remove(i);
  			}
  		}

	}

	if (window.location.href.indexOf('user/profile.php') != -1){

		var source = document.getElementById('region-main').innerHTML;
		var pm = source.indexOf("<dd>Program Manager</dd>");

		var eta = source.indexOf("<dd>ETA</dd>");

		var student = source.indexOf("<dd>Student</dd>");
		//if (document.innerHTML.indexOf("<dt>Profile</dt>") != -1){
		//console.log(pm);
		//console.log(eta);
		if (pm != -1  || eta != -1 || student != -1){

			var list = document.getElementsByClassName("node_category")[0];
			var nodes = list.getElementsByClassName("contentnode");

			if (student != -1){
				for (i = 0; i < nodes.length; i++) {
					if (
						nodes[i].innerHTML.indexOf("Email address") === -1
						&&
						nodes[i].innerHTML.indexOf("Profile") === -1
						&&
						nodes[i].innerHTML.indexOf("Institution") === -1
						&&
						nodes[i].innerHTML.indexOf("Course") === -1
					){
						nodes[i].innerHTML = "";	
					}
				  	
				}
			}
			else{
				for (i = 0; i < nodes.length; i++) {
					if (
						nodes[i].innerHTML.indexOf("Email address") === -1
						&&
						nodes[i].innerHTML.indexOf("Profile") === -1
						&&
						nodes[i].innerHTML.indexOf("Institution") === -1
					){
						nodes[i].innerHTML = "";	
					}
				  	
				}	
			}

			

			console.log(nodes);

		}
			

		///}


	}


	if (window.location.href.indexOf('user/editadvanced.php') != -1){


		var e = document.getElementById("id_profile_field_fulbright_profile");
		var strUser = e.options[e.selectedIndex].value;

		//console.log(strUser);

		if (strUser == "Program Manager" || strUser == "ETA"){

			//console.log("teste");

			//document.querySelector("label[for=id_auth]").parentNode.parentNode.style.display = "none";
			document.querySelector("label[for=id_maildisplay]").parentNode.parentNode.style.display = "none";
			document.querySelector("label[for=id_city]").parentNode.parentNode.style.display = "none";
			document.querySelector("label[for=id_country]").parentNode.parentNode.style.display = "none";
			document.querySelector("label[for=id_timezone]").parentNode.parentNode.style.display = "none";
			//document.querySelector("label[for=id_lang]").parentNode.parentNode.style.display = "none";
			document.querySelector("div#id_error_suspended").parentNode.parentNode.style.display = "none";		

			var element = document.getElementById("id_category_2");
    		element.parentNode.removeChild(element);	

    		var element = document.getElementById("id_category_3");
    		element.parentNode.removeChild(element);	

    		var element = document.getElementById("id_category_4");
    		element.parentNode.removeChild(element);	

			//document.getElementById('id_profile_field_fulbright_profile').innerHTML = '<option value="Program Manager">Program Manager</option>';
			
		}


		var url_string = window.location.href;
		var url = new URL(url_string);
		//var tipo = url.searchParams.get("r");
		//var perfil = url.searchParams.get("d");
		var usuario = url.searchParams.get("u");

		if (usuario == 99){

			document.querySelector("label[for=id_auth]").parentNode.parentNode.style.display = "none";
			document.querySelector("label[for=id_maildisplay]").parentNode.parentNode.style.display = "none";
			document.querySelector("label[for=id_city]").parentNode.parentNode.style.display = "none";
			document.querySelector("label[for=id_country]").parentNode.parentNode.style.display = "none";
			document.querySelector("label[for=id_timezone]").parentNode.parentNode.style.display = "none";
			document.querySelector("label[for=id_lang]").parentNode.parentNode.style.display = "none";
			document.querySelector("div#id_error_suspended").parentNode.parentNode.style.display = "none";
			

		}

	}

	if (window.location.href.indexOf('mod/glossary/view.php?id=51') != -1){		
		var elem = document.querySelector("li.breadcrumb-item:nth-child(2)");
		elem.parentNode.removeChild(elem);
	}

	if (window.location.href.indexOf('course/view.php') != -1){

		var url_string = window.location.href;
		var url = new URL(url_string);		
		var role = url.searchParams.get("role");		

		if (role != undefined && role == "ETA"){

			for (var i = 3; i <= 12; i++) {
				var elem = document.querySelector("div.dropdown-item:nth-child(3)");				
				elem.parentNode.removeChild(elem);	
			}
			
			

		}

	}

	if (window.location.href.indexOf('course/index.php?') != -1){
		document.querySelector("a#dropdown-3").style.display = "none";
	}

	if (window.location.href.indexOf('course/') != -1){
		document.querySelector("button#id_saveandreturn").style.display = "none";
	}

});

</script>