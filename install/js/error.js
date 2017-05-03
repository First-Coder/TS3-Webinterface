/*
	First-Coder Teamspeak 3 Webinterface for everyone
	Copyright (C) 2017 by L.Gmann

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
	
	for help look http://first-coder.de/
*/

/*
	Start Install
*/
function installInterface()
{
	$('#installContentLeft').fadeOut("slow", function()
	{
		document.getElementById("installSubText").innerHTML		=	"";
		document.getElementById("installMainBttn").innerHTML	=	weiter+" <i class=\"fa fa-arrow-right\" aria-hidden=\"true\"></i>";
		
		$('#installMainBttn').attr("onClick", "goToStep1();");
		
		$('#installContentLeft').load("install/install_step_0.php", function()
		{
			$('#installContentLeft').fadeIn("slow");
		});
	});
};

/*
	Go to Step 1
*/
function goToStep1()
{
	var lang	=	"english";
	if($('#checkGerman').prop("checked"))
	{
		lang	=	"german";
	};
	
	var dataString = 'action=set_language&lang='+lang;
	$.ajax({
		type: "POST",
		url: "install/functionsPost.php",
		data: dataString,
		dataTyp: "json",
		cache: false,
		success: function(data)
		{
			if(data == 'done')
			{
				$('#installMainBttn').attr("onClick", "goToStep2();");
				
				$('#installContentLeft').fadeOut("slow", function()
				{
					$('#installContentLeft').load("install/install_step_1.php", function()
					{
						$('#installContentLeft').fadeIn("slow");
					});
				});
			};
		}
	});
};

/*
	Go to Step 2
*/
function goToStep2()
{
	if(!document.getElementById("check_datenschutz").checked)
	{
		$('.checkBoxes1').css("background-color", "rgba(199,0,0,0.2)");
		$('html, body').animate({
			scrollTop: $('.checkBoxes1').offset().top
		}, 'slow');
	}
	else if(!document.getElementById("check_haftungsausschluss").checked)
	{
		$('.checkBoxes1').css("background-color", "rgba(0,199,0,0.2)");
		$('.checkBoxes2').css("background-color", "rgba(199,0,0,0.2)");
		$('html, body').animate({
			scrollTop: $('.checkBoxes2').offset().top
		}, 'slow');
	}
	else if(!document.getElementById("check_ts_damage").checked)
	{
		$('.checkBoxes1').css("background-color", "rgba(0,199,0,0.2)");
		$('.checkBoxes2').css("background-color", "rgba(0,199,0,0.2)");
		$('.checkBoxes3').css("background-color", "rgba(199,0,0,0.2)");
		$('html, body').animate({
			scrollTop: $('.checkBoxes3').offset().top
		}, 'slow');
	}
	else
	{
		$('html, body').animate({
			scrollTop: top
		}, 'slow');
		
		$('#installMainBttn').attr("onClick", "goToStep3();");
		$('#installMainBttn').attr("disabled", "disabled");
		
		// Auf Seite 2 wechseln
		$('#steps').fadeOut("slow", function()
		{
			$('#steps').load("install/install_step_2.php", function()
			{
				// Zeitleiste aktualisieren
				if($('#step_1').hasClass("active"))
				{
					$('#step_1').removeClass("active");
					$('#step_1').addClass("complete");
				};
				
				setTimeout(function() {
					if($('#step_2').hasClass("disabled"))
					{
						$('#step_2').removeClass("disabled");
						$('#step_2').addClass("active");
					}
				}, 500);
				
				$('#steps').fadeIn("slow");
			});
		});
	};
};

/*
	Go to Step 3
*/
function goToStep3()
{
	$('#installMainBttn').attr("onClick", "goToStep4();");
	$('#installMainBttn').attr("disabled", "disabled");
	
	$('#steps').fadeOut("slow", function()
	{
		$('#steps').load("install/install_step_3.php", function()
		{
			// Zeitleiste aktualisieren
			if($('#step_2').hasClass("active"))
			{
				$('#step_2').removeClass("active");
				$('#step_2').addClass("complete");
			};
			
			setTimeout(function() {
				if($('#step_3').hasClass("disabled"))
				{
					$('#step_3').removeClass("disabled");
					$('#step_3').addClass("active");
				};
			}, 500);
			
			$('#steps').fadeIn("slow");
		});
	});
};

/*
	Go to Step 4
*/
function goToStep4()
{
	$('#installMainBttn').attr("onClick", "goToStep5();");
	$('#installMainBttn').attr("disabled", "disabled");
	
	document.getElementById("installSubText").innerHTML = weiter_info;
	
	$('#steps').fadeOut("slow", function()
	{
		$('#steps').load("install/install_step_4.php", function()
		{
			// Zeitleiste aktualisieren
			if($('#step_3').hasClass("active"))
			{
				$('#step_3').removeClass("active");
				$('#step_3').addClass("complete");
			};
			
			setTimeout(function() {
				if($('#step_4').hasClass("disabled"))
				{
					$('#step_4').removeClass("disabled");
					$('#step_4').addClass("active");
				};
			}, 500);
			
			$('#steps').fadeIn("slow", function()
			{
				check_table = setInterval(check_table, 3000);
			});
		});
	});
};

/*
	Go to Step 5
*/
function goToStep5()
{
	$('#installMainBttn').attr("onClick", "pageReload();");
	
	document.getElementById("installSubText").innerHTML		=	"";
	document.getElementById("installMainBttn").innerHTML	=	"<i class=\"fa fa-smile-o\" aria-hidden=\"true\"></i> "+fertigstellen;
	
	$('#installContentLeft').fadeOut("slow", function()
	{
		$('#installContentLeft').load("install/install_step_5.php", function()
		{
			$('#installContentLeft').fadeIn("slow", function()
			{
				var dataString = 'action=del_install';
				$.ajax({
					type: "POST",
					url: "install/functionsPost.php",
					data: dataString,
					dataTyp: "json",
					cache: false,
					success: function(data)
					{
						if(data != 'done')
						{
							$('#installFailed').slideDown("slow");
						};
					}
				});
			});
		});
	});
};

/*
	Reload the Page
*/
function pageReload()
{
	window.location.reload();
};

/*
	Check Rights in the table
*/
function check_table()
{
	if(!document.getElementById("tablePermissions"))
	{
		clearInterval(check_table);
	}
	else
	{
		var dataString = 'action=check_table';
		$.ajax({
			type: "POST",
			url: "install/functionsPost.php",
			data: dataString,
			dataTyp: "json",
			cache: false,
			success: function(data)
			{
				var table_infos	= 	JSON.parse(data);
				
				// Tabelle anpassen
				if(table_infos['install'] == 'true')
				{
					$('#tbl_install').css("background-color", "rgba(0,199,0,0.2)");
				}
				else
				{
					$('#tbl_install').css("background-color", "rgba(199,0,0,0.2)");
				};
				document.getElementById('tbl_install_txt').innerHTML = table_infos['install_txt'];
				
				if(table_infos['install/js'] == 'true')
				{
					$('#tbl_install_js').css("background-color", "rgba(0,199,0,0.2)");
				}
				else
				{
					$('#tbl_install_js').css("background-color", "rgba(199,0,0,0.2)");
				};
				document.getElementById('tbl_install_js_txt').innerHTML = table_infos['install/js_txt'];
				
				// Weiter button erstellen oder löschen
				if(table_infos['install'] == 'true' && table_infos['install/js'] == 'true')
				{
					$('#installMainBttn').removeAttr("disabled");
				}
				else
				{
					$('#installMainBttn').attr("disabled", "disabled");
				};
			}
		});
	};
};


/*
	Check the Database and create all entrys
*/
function checkDatabaseConnection()
{
	var host		=	$('#check_hostname').val();
	var user		=	$('#check_username').val();
	var pw 			=	$('#check_password').val();
	var database	=	$('#check_database').val();
	var port		=	$('#check_port').val();
	var mode		=	$('#check_sqlmode').val();
	var ssl			=	$('#checkSslRequire').prop("checked");
	
	var dataString = 'action=check_database&host='+host+'&user='+user+'&pw='+pw+'&database='+database+'&port='+port+'&mode='+mode+'&ssl='+ssl;
	$.ajax({
		type: "POST",
		url: "install/functionsPost.php",
		data: dataString,
		dataTyp: "json",
		cache: false,
		success: function(data)
		{
			if(data == 'done')
			{
				document.getElementById('database_errorbox').innerHTML	=	"<b>Databaseconnection Successful!</b><button onClick=\"createDatabase();\" class=\"btn btn-success\" style=\"width: 100%;\"><i class=\"fa fa-edit\" aria-hidden=\"true\"></i> "+datenbank_erstellen+"</button>";
				$('#database_errorbox').removeClass("alert-warning");
				$('#database_errorbox').removeClass("alert-danger");
				$('#database_errorbox').addClass("alert-success");
				
				$('#check_hostname').attr('disabled', 'disabled');
				$('#check_username').attr('disabled', 'disabled');
				$('#check_password').attr('disabled', 'disabled');
				$('#checkSslRequire').attr('disabled', 'disabled');
				$('#check_port').attr('disabled', 'disabled');
				$('#check_sqlmode').attr('disabled', 'disabled');
				$('#check_database').attr('disabled', 'disabled');
				$('#check_database_connection').attr('disabled', 'disabled');
			}
			else
			{
				document.getElementById('database_errorbox').innerHTML	=	data;
				$('#database_errorbox').removeClass("alert-warning");
				$('#database_errorbox').addClass("alert-danger");
			};
		}
	});
};

function createDatabase()
{
	var host		=	$('#check_hostname').val();
	var user		=	$('#check_username').val();
	var pw 			=	$('#check_password').val();
	var database	=	$('#check_database').val();
	var port		=	$('#check_port').val();
	var mode		=	$('#check_sqlmode').val();
	var ssl			=	$('#checkSslRequire').prop("checked");
	
	document.getElementById('database_errorbox').innerHTML	=	"Creating...";
	$('#database_errorbox').removeClass("alert-success");
	$('#database_errorbox').addClass("alert-warning");
	
	dataString = 'action=create_database&host='+host+'&user='+user+'&pw='+pw+'&database='+database+'&port='+port+'&mode='+mode+'&ssl='+ssl;
	$.ajax({
		type: "POST",
		url: "install/functionsPost.php",
		data: dataString,
		dataTyp: "json",
		cache: false,
		success: function(data)
		{
			document.getElementById('database_errorbox').innerHTML	=	data;
			if(document.getElementById('database_errorbox').getElementsByClassName("console_error").length <= 0)
			{
				document.getElementById('database_errorbox').innerHTML	=	document.getElementById('database_errorbox').innerHTML+"<button onClick=\"goToUp();\" class=\"btn btn-success\" style=\"width: 100%;\"><i class=\"fa fa-arrow-up\" aria-hidden=\"true\"></i></button>";
				$('#installMainBttn').removeAttr("disabled");
			}
			else
			{
				document.getElementById('database_errorbox').innerHTML	=	document.getElementById('database_errorbox').innerHTML+"<button onClick=\"createDatabase();\" class=\"btn btn-danger\" style=\"width: 100%;\"><i class=\"fa fa-edit\" aria-hidden=\"true\"></i> "+erneut_versuchen+"</button>";
			};
		}
	});
};

function goToUp()
{
	$('html, body').animate({
		scrollTop: top
	}, 'slow');
};

/*
	Create the User / Settings
*/
function submitSettings()
{
	$('#submitBttn').fadeOut("slow", function()
	{
		$('#submitConsole').fadeIn("slow", function()
		{
			var username		=	$('#username').val();
			var password		=	$('#password').val();
			var heading			=	$('#heading').val();
			var ts3chatname		=	$('#ts3Chatname').val();
			var regex_check		=	true;
			
			// Password pruefen
			var regex 				=	/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{4,12}$/;
			regex_check				= 	regex.test(password);
			
			// Benutzer pruefen
			if(regex_check)
			{
				var regex 				=	/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i;
				regex_check				= 	regex.test(username);
			}
			else
			{
				$('#database_errorbox').removeClass("alert-warning");
				$('#database_errorbox').addClass("alert-danger");
				
				document.getElementById('database_errorbox').innerHTML	=	falsches_passwort+"<button onClick=\"submitSettings();\" class=\"btn btn-danger\" style=\"width: 100%;\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i> "+uebernehmen+"</button>";
				return;
			};
			
			if(regex_check)
			{
				$('#database_errorbox').removeClass("alert-danger");
				$('#database_errorbox').addClass("alert-warning");
				
				document.getElementById('database_errorbox').innerHTML	=	"Creating...";
				
				var dataString 		= 	'action=create_user&user='+username+'&pw='+password+'&heading='+heading+'&tschatname='+ts3chatname;
				$.ajax({
					type: "POST",
					url: "install/functionsPost.php",
					data: dataString,
					dataTyp: "json",
					cache: false,
					success: function(data){
						if(document.getElementById('console_block').getElementsByClassName("console_error").length <= 0)
						{
							$('#database_errorbox').removeClass("alert-danger");
							$('#database_errorbox').removeClass("alert-warning");
							$('#database_errorbox').addClass("alert-success");
							
							document.getElementById('database_errorbox').innerHTML = data;
							
							$('#installMainBttn').removeAttr("disabled");
						}
						else
						{
							$('#database_errorbox').removeClass("alert-warning");
							$('#database_errorbox').addClass("alert-danger");
							
							document.getElementById('database_errorbox').innerHTML = data;
							
							document.getElementById('database_errorbox').innerHTML	=	document.getElementById('database_errorbox').innerHTML+"<button onClick=\"submitSettings();\" class=\"btn btn-danger\" style=\"width: 100%;\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i> "+erneut_versuchen+"</button>";
						};
					}
				});
			}
			else
			{
				$('#database_errorbox').removeClass("alert-warning");
				$('#database_errorbox').addClass("alert-danger");
				
				document.getElementById('database_errorbox').innerHTML	=	falscher_benutzer+"<button onClick=\"submitSettings();\" class=\"btn btn-danger\" style=\"width: 100%;\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i> "+uebernehmen+"</button>";
				return;
			};
		});
	});
};