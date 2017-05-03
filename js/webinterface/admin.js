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
	admin_user_info: Button Click effect
*/
	function clickButton(id, idDatapicker, whichContent)
	{
		if(whichContent == "true")
		{
			if($("#"+id).hasClass("btn-success"))
			{
				document.getElementById(id).innerHTML = "<i class=\"fa fa-ban\" aria-hidden=\"true\"></i> "+textYes;
				$('#'+idDatapicker).fadeIn("slow");
			}
			else
			{
				document.getElementById(id).innerHTML = "<i class=\"fa fa-check\" aria-hidden=\"true\"></i> "+textNo;
				$('#'+idDatapicker).fadeOut("slow");
			};
		}
		else if(whichContent == "ports")
		{
			if($("#"+id).hasClass("btn-danger"))
			{
				document.getElementById(id).innerHTML = "<i class=\"fa fa-check\" aria-hidden=\"true\"></i> "+textYes;
			}
			else
			{
				document.getElementById(id).innerHTML = "<i class=\"fa fa-ban\" aria-hidden=\"true\"></i> "+textNo;
			};
		}
		else
		{
			if($("#"+id).hasClass("btn-danger"))
			{
				document.getElementById(id).innerHTML = "<i class=\"fa fa-check\" aria-hidden=\"true\"></i> "+textYes;
				$('#'+idDatapicker).fadeIn("slow");
			}
			else
			{
				document.getElementById(id).innerHTML = "<i class=\"fa fa-ban\" aria-hidden=\"true\"></i> "+textNo;
				$('#'+idDatapicker).fadeOut("slow");
			};
		};
		
		$("#"+id).toggleClass('btn-danger');
		$("#"+id).toggleClass('btn-success');
	};

/*
	admin_user: Show Userinformations
*/
	function showUser(id, mail, lastLogin)
	{
		$("#mainContent").fadeOut("slow", function()
		{
			$("#mainContent").load('web_admin_user_info.php', { "id" : id, "mail" : mail, "lastLogin" : lastLogin }, function()
			{
				$("#mainContent").fadeIn("slow");
			});
		});
	};

/*
	admin_user: Create a new User
*/
	function createUser()
	{
		var pwContent		=	$('#adminCreatePassword').val();
		var mailContent		=	$('#adminCreateUser').val();
		var regex_check		=	true;
		
		// Password pruefen
		var regex 				=	/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{4,100}$/;
		regex_check				= 	regex.test(pwContent);
		
		// Benutzer pruefen
		if(regex_check)
		{
			var regex 				=	/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i;
			regex_check				= 	regex.test(mailContent);
		}
		else
		{
			$.notify({
				title: '<strong>'+failed+'</strong><br />',
				message: password_needs,
				icon: 'fa fa-warning'
			},{
				type: 'danger',
				allow_dismiss: true,
				placement:
				{
					from: 'bottom',
					align: 'right'
				}
			});
		};
		
		if(regex_check)
		{
			// Werte übergeben
			var dataString 		= 	'action=createUser&username='+mailContent+'&password='+pwContent;
			
			$.ajax({
				type: "POST",
				url: "functionsPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data){
					if(data == 'done')
					{
						$('#modalCreateUser').modal('hide');
						adminUserInit();
					}
					else
					{
						$.notify({
							title: '<strong>'+failed+'</strong><br />',
							message: data,
							icon: 'fa fa-warning'
						},{
							type: 'danger',
							allow_dismiss: true,
							placement:
							{
								from: 'bottom',
								align: 'right'
							}
						});
					};
				}
			});
		}
		else
		{
			$.notify({
				title: '<strong>'+failed+'</strong><br />',
				message: username_needs,
				icon: 'fa fa-warning'
			},{
				type: 'danger',
				allow_dismiss: true,
				placement:
				{
					from: 'bottom',
					align: 'right'
				}
			});
		};
	};
	
/*
	admin_user: Delete All Users
*/
	function deleteAllUsers()
	{
		var pwContent		=	$('#adminDeleteAllUsersPassword').val();
		var mailContent		=	$('#adminDeleteAllUsersUser').val();
		var regex_check		=	true;
		
		// Password pruefen
		var regex 				=	/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{4,12}$/;
		regex_check				= 	regex.test(pwContent);
		
		// Benutzer pruefen
		if(regex_check)
		{
			var regex 				=	/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i;
			regex_check				= 	regex.test(mailContent);
		}
		else
		{
			$.notify({
				title: '<strong>'+failed+'</strong><br />',
				message: password_needs,
				icon: 'fa fa-warning'
			},{
				type: 'danger',
				allow_dismiss: true,
				placement:
				{
					from: 'bottom',
					align: 'right'
				}
			});
		};
		
		if(regex_check)
		{
			// Werte übergeben
			var dataString 		= 	'action=deleteAllUsers&username='+mailContent+'&password='+pwContent;
			
			$.ajax({
				type: "POST",
				url: "functionsPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data){
					if(data == 'done')
					{
						ausloggenInit();
					}
					else
					{
						$.notify({
							title: '<strong>'+failed+'</strong><br />',
							message: data,
							icon: 'fa fa-warning'
						},{
							type: 'danger',
							allow_dismiss: true,
							placement:
							{
								from: 'bottom',
								align: 'right'
							}
						});
					};
				}
			});
		}
		else
		{
			$.notify({
				title: '<strong>'+failed+'</strong><br />',
				message: username_needs,
				icon: 'fa fa-warning'
			},{
				type: 'danger',
				allow_dismiss: true,
				placement:
				{
					from: 'bottom',
					align: 'right'
				}
			});
		};
	};
	
/*
	admin_user: Delete a User
*/
	function deleteUser()
	{
		var pk				=	$('#deleteUserBttn').attr("pk");
		var dataString 		= 	'action=deleteUser&pk='+pk;
		
		$.ajax({
			type: "POST",
			url: "functionsPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data){
				if(data == 'done')
				{
					$('#modalDeleteUser').modal('hide');
					adminUserInit();
				}
				else
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: data,
						icon: 'fa fa-warning'
					},{
						type: 'danger',
						allow_dismiss: true,
						placement:
						{
							from: 'bottom',
							align: 'right'
						}
					});
				};
			}
		});
	};
	
/*
	admin_settings: Change Websitelanguage
*/
	function changeLanguage(lang)
	{
		var dataString 		= 	'action=setLanguage&lang='+lang;
		$.ajax({
			type: "POST",
			url: "functionsPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data){
				if(data == 'done')
				{
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: "Language set! Reload the page to see your site in the new language!",
						icon: 'fa fa-check'
					},{
						type: 'success',
						allow_dismiss: true,
						placement:
						{
							from: 'bottom',
							align: 'right'
						}
					});
				}
				else
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: "Ups, something Failed :/",
						icon: 'fa fa-warning'
					},{
						type: 'danger',
						allow_dismiss: true,
						placement:
						{
							from: 'bottom',
							align: 'right'
						}
					});
				};
			}
		});
	};
	
/*
	admin_settings: Change Websitetheme
*/
	function changeTheme(file)
	{
		if(file == "style.css")
		{
			file	=	"";
		};
		
		var dataString 		= 	'action=setTheme&file='+file;
		$.ajax({
			type: "POST",
			url: "functionsPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data){
				if(data == 'done')
				{
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: "Style set! Reload the page to see your site in the new style!",
						icon: 'fa fa-check'
					},{
						type: 'success',
						allow_dismiss: true,
						placement:
						{
							from: 'bottom',
							align: 'right'
						}
					});
				}
				else
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: "Ups, something Failed :/",
						icon: 'fa fa-warning'
					},{
						type: 'danger',
						allow_dismiss: true,
						placement:
						{
							from: 'bottom',
							align: 'right'
						}
					});
				};
			}
		});
	};
	
/*
	admin_settings: Change Config
*/
	function setConfig()
	{
		var heading		=	$('#heading').val().replace(/(<([^>]+)>)/ig,"");
		var chatname	=	$('#chatname').val().replace(/(<([^>]+)>)/ig,"");
		var selInstanz	=	$('#masterserverSelectInstanz').val();
		var selPort		=	$('#masterserverSelectPort').val();
		
		var regex 		=	/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i;
		regex_check		= 	regex.test($('#mailadress').val());
		
		if(heading == "" || chatname == "")
		{
			$.notify({
				title: '<strong>'+failed+'</strong><br />',
				message: field_cant_be_empty,
				icon: 'fa fa-warning'
			},{
				type: 'danger',
				allow_dismiss: true,
				placement:
				{
					from: 'bottom',
					align: 'right'
				}
			});
		}
		else if(!regex_check)
		{
			$.notify({
				title: '<strong>'+failed+'</strong><br />',
				message: settings_mail_needed,
				icon: 'fa fa-warning'
			},{
				type: 'danger',
				allow_dismiss: true,
				placement:
				{
					from: 'bottom',
					align: 'right'
				}
			});
		}
		else
		{
			var dataString 		= 	'action=setConfigSettings&heading='+heading+'&chatname='+chatname+'&selInstanz='+selInstanz+'&selPort='+selPort+'&mailadress='+$('#mailadress').val();
			$.ajax({
				type: "POST",
				url: "functionsPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data){
					if(data == 'done')
					{
						$.notify({
							title: '<strong>'+success+'</strong><br />',
							message: settigns_saved,
							icon: 'fa fa-check'
						},{
							type: 'success',
							allow_dismiss: true,
							placement:
							{
								from: 'bottom',
								align: 'right'
							}
						});
					}
					else
					{
						$.notify({
							title: '<strong>'+failed+'</strong><br />',
							message: "Ups, something Failed :/",
							icon: 'fa fa-warning'
						},{
							type: 'danger',
							allow_dismiss: true,
							placement:
							{
								from: 'bottom',
								align: 'right'
							}
						});
					};
				}
			});
		};
	};
	
/*
	admin_settings: Change Masterserver instanz
*/
	function adminSettingsChangePort()
	{
		var instanz		=	$('#masterserverSelectInstanz').val();
		
		if(instanz != 'nope')
		{
			var dataString 		= 	'action=getTeamspeakPorts&instanz='+instanz;
			
			$.ajax({
				type: "POST",
				url: "functionsTeamspeakPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data){
					var ports 	=	JSON.parse(data);
					var element = document.getElementById('masterserverSelectPort');
					
					// Element leeren
					while ( element.childNodes.length >= 1 )
					{
						element.removeChild(element.firstChild);
					};
					
					// Ports erstellen
					if(data != "\"No Connection\"")
					{
						for (i = 0; i < ports.length; i++)
						{
							port 					= 	document.createElement('option');
							port.value				=	ports[i];
							port.text				=	ports[i];
							element.appendChild(port);
						};
					}
					else
					{
						nope 						= 	document.createElement('option');
						nope.value					=	'nope';
						nope.text					=	unescape(no_masterserver);
						element.appendChild(nope);
					};
				}
			});
		}
		else
		{
			var element = document.getElementById('masterserverSelectPort');
			
			// Element leeren
			while ( element.childNodes.length >= 1 )
			{
				element.removeChild(element.firstChild);
			};
			
			// Kein Port einschreiben
			nope 						= 	document.createElement('option');
			nope.value					=	'nope';
			nope.text					=	unescape(no_masterserver);
			element.appendChild(nope);
		};
	};

/*
	admin_settings: Change Websitemodul
*/
	function changeModul(id)
	{
		switch(id)
		{
			case 'setModulWebinterface':
				var value	=	$('#setModulWebinterface').is(':checked');
				var dataString 		= 	'action=setModulWebinterface&value='+value;
				$.ajax({
					type: "POST",
					url: "functionsPost.php",
					data: dataString,
					cache: true,
					async: true,
					success: function(data){
						if(data == 'done')
						{
							$.notify({
								
								title: '<strong>'+success+'</strong><br />',
								message: hp_modul_settings_done,
								icon: 'fa fa-check'
							},{
								type: 'success',
								allow_dismiss: true,
								placement:
								{
									from: 'bottom',
									align: 'right'
								}
							});
						}
						else
						{
							$.notify({
								title: '<strong>'+failed+'</strong><br />',
								message: hp_modul_settings_failed,
								icon: 'fa fa-warning'
							},{
								type: 'danger',
								allow_dismiss: true,
								placement:
								{
									from: 'bottom',
									align: 'right'
								}
							});
						};
					}
				});
				break;
			case 'setModulServerAntrag':
				var value	=	$('#setModulServerAntrag').is(':checked');
				var dataString 		= 	'action=setModulServerAntrag&value='+value;
				$.ajax({
					type: "POST",
					url: "functionsPost.php",
					data: dataString,
					cache: true,
					async: true,
					success: function(data){
						if(data == 'done')
						{
							$.notify({
								title: '<strong>'+success+'</strong><br />',
								message: hp_modul_settings_done,
								icon: 'fa fa-check'
							},{
								type: 'success',
								allow_dismiss: true,
								placement:
								{
									from: 'bottom',
									align: 'right'
								}
							});
						}
						else
						{
							$.notify({
								title: '<strong>'+failed+'</strong><br />',
								message: hp_modul_settings_failed,
								icon: 'fa fa-warning'
							},{
								type: 'danger'
							});
						};
					}
				});
				break;
			case 'setModulWriteNews':
				var value	=	$('#setModulWriteNews').is(':checked');
				var dataString 		= 	'action=setModulWriteNews&value='+value;
				$.ajax({
					type: "POST",
					url: "functionsPost.php",
					data: dataString,
					cache: true,
					async: true,
					success: function(data){
						if(data == 'done')
						{
							$.notify({
								title: '<strong>'+success+'</strong><br />',
								message: hp_modul_settings_done,
								icon: 'fa fa-check'
							},{
								type: 'success',
								allow_dismiss: true,
								placement:
								{
									from: 'bottom',
									align: 'right'
								}
							});
						}
						else
						{
							$.notify({
								title: '<strong>'+failed+'</strong><br />',
								message: hp_modul_settings_failed,
								icon: 'fa fa-warning'
							},{
								type: 'danger'
							});
						};
					}
				});
				break;
			case 'setModulFreeRegister':
				var value	=	$('#setModulFreeRegister').is(':checked');
				var dataString 		= 	'action=setModulFreeRegister&value='+value;
				$.ajax({
					type: "POST",
					url: "functionsPost.php",
					data: dataString,
					cache: true,
					async: true,
					success: function(data){
						if(data == 'done')
						{
							$.notify({
								title: '<strong>'+success+'</strong><br />',
								message: hp_modul_settings_done,
								icon: 'fa fa-check'
							},{
								type: 'success',
								allow_dismiss: true,
								placement:
								{
									from: 'bottom',
									align: 'right'
								}
							});
						}
						else
						{
							$.notify({
								title: '<strong>'+failed+'</strong><br />',
								message: hp_modul_settings_failed,
								icon: 'fa fa-warning'
							},{
								type: 'danger',
								allow_dismiss: true,
								placement:
								{
									from: 'bottom',
									align: 'right'
								}
							});
						};
					}
				});
				break;
			case 'setModulMasterserver':
				var value	=	$('#setModulMasterserver').is(':checked');
				var dataString 		= 	'action=setModulMasterserver&value='+value;
				$.ajax({
					type: "POST",
					url: "functionsPost.php",
					data: dataString,
					cache: true,
					async: true,
					success: function(data){
						if(data == 'done')
						{
							$.notify({
								title: '<strong>'+success+'</strong><br />',
								message: hp_modul_settings_done,
								icon: 'fa fa-check'
							},{
								type: 'success',
								allow_dismiss: true,
								placement:
								{
									from: 'bottom',
									align: 'right'
								}
							});
						}
						else
						{
							$.notify({
								title: '<strong>'+failed+'</strong><br />',
								message: hp_modul_settings_failed,
								icon: 'fa fa-warning'
							},{
								type: 'danger',
								allow_dismiss: true,
								placement:
								{
									from: 'bottom',
									align: 'right'
								}
							});
						};
					}
				});
				break;
		};
	};
	
/*
	admin_instanz: Create a new Instanz
*/
	function createInstanz()
	{
		// Daten abfragen
		var alias		=	$('#adminCreateInstanzAlias').val().replace(/(<([^>]+)>)/ig,"");
		var ip			=	$('#adminCreateInstanzIp').val().replace(/(<([^>]+)>)/ig,"");
		var queryport	=	$('#adminCreateInstanzQueryport').val().replace(/(<([^>]+)>)/ig,"");
		var client		=	$('#adminCreateInstanzClient').val().replace(/(<([^>]+)>)/ig,"");
		var passwort	=	$('#adminCreateInstanzPassword').val().replace(/(<([^>]+)>)/ig,"");
		
		if(ip != '' && queryport != '' && client != '' && passwort != '')
		{
			var dataString 		= 	'action=createInstanz&alias='+alias+'&ip='+ip+'&queryport='+queryport+'&client='+client+'&passwort='+encodeURIComponent(passwort);
			$.ajax({
				type: "POST",
				url: "functionsPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data){
					if(data == 'done' || data == 'Connection was not Successful!')
					{
						$('#modalCreateInstanz').modal('hide');
						
						$(".preloader").fadeIn("slow", function()
						{
							setTimoutInstanzCreate();
						});
					}
					else
					{
						$.notify({
							title: '<strong>'+failed+'</strong><br />',
							message: data,
							icon: 'fa fa-warning'
						},{
							type: 'danger',
							allow_dismiss: true,
							placement:
							{
								from: 'bottom',
								align: 'right'
							}
						});
					};
				}
			});
		}
		else
		{
			$.notify({
				title: '<strong>'+failed+'</strong><br />',
				message: admin_instanz_create_error,
				icon: 'fa fa-warning'
			},{
				type: 'danger',
				allow_dismiss: true,
				placement:
				{
					from: 'bottom',
					align: 'right'
				}
			});
		};
	};
	
	function setTimoutInstanzCreate()
	{
		setTimeout(function(){
			adminInstanzInit();
		}, 1500);
	};
	
/*
	admin_instanz: Slide Instanz
*/
	function slideInstanz(instanz)
	{
		var icon		=	$('#instanzIcon'+instanz);
		var box 		=	$('#instanzBox'+instanz);
		
		if(icon.hasClass("fa-arrow-right"))
		{
			icon.removeClass("fa-arrow-right");
			icon.addClass("fa-arrow-down");
			
			box.slideDown("slow");
		}
		else
		{
			icon.removeClass("fa-arrow-down");
			icon.addClass("fa-arrow-right");
			
			box.slideUp("slow");
		};
	};
	
/*
	admin_instanz: Change a Instanz
*/
	function changeInstanz(what, instanz)
	{
		var someText				=	'';
		switch(what)
		{
			case 'ip':			someText	=	$('#instanzIp'+instanz).val();			break;
			case 'queryport':	someText	=	$('#instanzQueryport'+instanz).val();	break;
			case 'user':		someText	=	$('#instanzUser'+instanz).val();		break;
			case 'pw':			someText	=	$('#instanzPassword'+instanz).val();	break;
		};
		
		if(someText != '')
		{
			var dataString 		= 	'action=writeInstanz';
			dataString 			+= 	'&instanz='+instanz;
			dataString 			+= 	'&what='+what;
			dataString 			+= 	'&content='+encodeURIComponent(someText);
			
			$.ajax({
				type: "POST",
				url: "functionsPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data){
					// Hintergrund neu färben und Text ändern
					if(data == 'done')
					{
						$('#instanzTextBox'+instanz).addClass('label-success');
						$('#instanzTextBox'+instanz).removeClass('label-danger');
						
						document.getElementById('instanzTextBox'+instanz).innerHTML = '<i class="fa fa-check"></i> '+success;
						
						$.notify({
							title: '<strong>'+success+'</strong><br />',
							message: admin_instanz_change_done,
							icon: 'fa fa-check'
						},{
							type: 'success',
							allow_dismiss: true,
							placement:
							{
								from: 'bottom',
								align: 'right'
							}
						});
					}
					else
					{
						$('#instanzTextBox'+instanz).addClass('label-danger');
						$('#instanzTextBox'+instanz).removeClass('label-success');
						
						document.getElementById('instanzTextBox'+instanz).innerHTML = '<i class="fa fa-close"></i> '+failed;
						
						$.notify({
							title: '<strong>'+failed+'</strong><br />',
							message: data,
							icon: 'fa fa-warning'
						},{
							type: 'danger',
							allow_dismiss: true,
							placement:
							{
								from: 'bottom',
								align: 'right'
							}
						});
					};
				}
			});
		};
	};
	
/*
	admin_instanz: Login Serverconsole
*/
	$('.commandQueryConsole')
		.click(function()
		{
			commandQueryConsole($(this).attr("instanz"));
			
			return false;
		});

	function commandQueryConsole(instanz)
	{
		var command			=	$("#commandInput"+instanz).val().replace(/(<([^>]+)>)/ig,"");
		var dataString 		= 	'action=commandQueryConsole&instanz='+instanz+'&command='+command;
		var isUse			=	false;
		var serverId		=	"";
		
		document.getElementById("commandInputHistory"+instanz).innerHTML = command + "<br />" + document.getElementById("commandInputHistory"+instanz).innerHTML;
		
		if(command.substring(0, 3) == "use")
		{
			serverId		=	command.split(" ");
			isUse			=	true;
		};
		
		if(typeof(queryConsoleSelected[instanz]) != "undefined")
		{
			dataString		+=	'&server='+queryConsoleSelected[instanz];
		};
		
		$.ajax({
			type: "POST",
			url: "functionsTeamspeakPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data){
				if(data.trim() == "<br />")
				{
					if(isUse)
					{
						queryConsoleSelected[instanz]	=	serverId[1].trim();
					};
				}
				else
				{
					document.getElementById("commandOutput"+instanz).innerHTML = data + document.getElementById("commandOutput"+instanz).innerHTML;
				};
			}
		});
	};
	
	$('.clearConsole')
		.click(function()
		{
			clearConsole($(this).attr("remove-id"));
			
			return false;
		});
	
	function clearConsole(id)
	{
		document.getElementById(id).innerHTML = "";
	};
	
/*
	admin_user: Slide User Portrights
*/
	function slidePermissionbox(id, icon)
	{
		var icon		=	$('#'+icon);
		var box 		=	$('#'+id);
		
		if(icon.hasClass("fa-arrow-right"))
		{
			icon.removeClass("fa-arrow-right");
			icon.addClass("fa-arrow-down");
			
			box.slideDown("slow");
		}
		else
		{
			icon.removeClass("fa-arrow-down");
			icon.addClass("fa-arrow-right");
			
			box.slideUp("slow");
		};
	};
	
/*
	admin_user: Show the saved button in Serveredit
*/
	function showSaveButton(port, instanz)
	{
		if(!benutzerChange)
		{
			$('#saveButton_'+port+'_'+instanz).attr("onClick", "adminProfilUpdatePorts('"+port+"', '"+instanz+"');");
			$('#saveButton_'+port+'_'+instanz).removeClass("disabled");
		};
	};
	
/*
	admin_user: Save Userdata
*/
	function setBenutzerdaten(userPk)
	{
		var status;
		
		status = adminProfilUpdate(userPk, '', 'benutzer_blocked', 'adminCheckboxBlocked', 'adminDatapickerBlocked', true);
		
		if($('#adminUsername').val() != "" && status)
		{
			status = adminProfilUpdate(userPk, 'adminUsername', true);
		};
		
		if($('#adminPassword').val() != "" && status)
		{
			status = adminProfilUpdate(userPk, 'adminPassword', true)
		};
		
		if(status)
		{
			$.notify({
				title: '<strong>'+success+'</strong><br />',
				message: hp_user_edit_done,
				icon: 'fa fa-check'
			},{
				type: 'success',
				allow_dismiss: true,
				placement:
				{
					from: 'bottom',
					align: 'right'
				}
			});
		}
		else
		{
			$.notify({
				title: '<strong>'+failed+'</strong><br />',
				message: hp_user_edit_failed,
				icon: 'fa fa-warning'
			},{
				type: 'danger',
				allow_dismiss: true,
				placement:
				{
					from: 'bottom',
					align: 'right'
				}
			});
		};
	};

/*
	admin_user: Save Global permissions
*/
	function setHomepagerechte(userPk)
	{
		// Constants declare
		var right 			=	new Array('right_hp_main', 'right_hp_ts3', 'right_hp_user_create', 'right_hp_user_delete', 'right_hp_user_edit', 'right_hp_ticket_system', 'right_hp_mails');
		var idCheckbox 		= 	new Array('adminCheckboxRightsEdit', 'adminCheckboxRightsTSEdit', 'adminCheckboxRightsUserCreate', 'adminCheckboxRightsUserDelete', 'adminCheckboxRightsUserEdit', 'adminCheckboxRightsTicketsystem', 'adminCheckboxRightsMails');
		var idDatapicker 	= 	new Array('adminDatapickerRightsEdit', 'adminDatapickerRightsTSEdit', 'adminDatapickerRightsUserCreate', 'adminDatapickerRightsUserDelete', 'adminDatapickerRightsUserEdit', 'adminDatapickerRightsTicketsystem', 'adminDatapickerRightsMails');
		var status			=	true;
		
		for(var i = 0;i < right.length;i++)
		{
			if(status)
			{
				status		=	adminProfilUpdate(userPk, "", right[i], idCheckbox[i], idDatapicker[i], true);
			};
		};
		
		if(status)
		{
			$.notify({
				title: '<strong>'+success+'</strong><br />',
				message: hp_user_edit_done,
				icon: 'fa fa-check'
			},{
				type: 'success',
				allow_dismiss: true,
				placement:
				{
					from: 'bottom',
					align: 'right'
				}
			});
		}
		else
		{
			$.notify({
				title: '<strong>'+failed+'</strong><br />',
				message: hp_user_edit_failed,
				icon: 'fa fa-warning'
			},{
				type: 'danger',
				allow_dismiss: true,
				placement:
				{
					from: 'bottom',
					align: 'right'
				}
			});
		};
	};
	
	function setTeamspeakrechte(userPk)
	{
		// Constants declare
		var right 			=	new Array('right_web', 'right_web_global_message_poke', 'right_web_server_create', 'right_web_server_delete', 'right_web_global_server');
		var idCheckbox 		= 	new Array('adminCheckboxRightWeb', 'adminCheckboxRightWebGlobalMessagePoke', 'adminCheckboxRightWebServerCreate', 'adminCheckboxRightWebServerDelete', 'adminCheckboxRightsWebGlobalServer');
		var idDatapicker 	= 	new Array('adminDatapickerRightsWeb', 'adminDatapickerRightsWebGlobalMessagePoke', 'adminDatapickerRightsWebServerCreate', 'adminDatapickerRightsWebServerDelete', 'adminDatapickerRightsWebGlobalServer');
		var status			=	true;
		
		for(var i = 0;i < right.length;i++)
		{
			if(status)
			{
				adminProfilUpdate(userPk, "", right[i], idCheckbox[i], idDatapicker[i], true);
			};
		};
		
		if(status)
		{
			$.notify({
				title: '<strong>'+success+'</strong><br />',
				message: hp_user_edit_done,
				icon: 'fa fa-check'
			},{
				type: 'success',
				allow_dismiss: true,
				placement:
				{
					from: 'bottom',
					align: 'right'
				}
			});
		}
		else
		{
			$.notify({
				title: '<strong>'+failed+'</strong><br />',
				message: hp_user_edit_failed,
				icon: 'fa fa-warning'
			},{
				type: 'danger',
				allow_dismiss: true,
				placement:
				{
					from: 'bottom',
					align: 'right'
				}
			});
		};
	};
	
	function adminProfilUpdate(userPk, id, right, idCheckbox, idDatapicker, returnData = false)
	{
		pk								=	$('#mailOverview').attr('pk');
		idContent 						= 	$('#'+id).val();
		
		if((id == 'adminUsername' || id == 'adminPassword') && idContent != '')
		{
			var regex_check				=	true;
			var pw_check				=	true;
			
			// Textfelder auf Fehler prüfen
			if(id == 'adminPassword')
			{
				var regex 				=	/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{4,12}$/;
				regex_check				= 	regex.test(idContent);
				
				if(idContent != $('#adminPassword2').val())
				{
					pw_check			=	false;
				};
			}
			else if(id == 'adminUsername')
			{
				var regex 				=	/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i;
				regex_check				= 	regex.test(idContent);
			};
			
			if(regex_check && pw_check)
			{
				var dataString	=	"action=updateUser&id="+id+"&content="+idContent+"&pk="+pk+"&adminpk="+userPk;
				var returnValue;
				$.ajax({
					type: "POST",
					url: "functionsPost.php",
					data: dataString,
					cache: true,
					async: false,
					success: function(data)
					{
						if(data == 'done')
						{
							returnValue = true;
							
							if(!returnData)
							{
								$.notify({
									title: '<strong>'+success+'</strong><br />',
									message: hp_user_edit_done,
									icon: 'fa fa-check'
								},{
									type: 'success',
									allow_dismiss: true,
									placement:
									{
										from: 'bottom',
										align: 'right'
									}
								});
							};
						}
						else
						{
							returnValue = false;
							
							if(!returnData)
							{
								$.notify({
									title: '<strong>'+failed+'</strong><br />',
									message: hp_user_edit_failed,
									icon: 'fa fa-warning'
								},{
									type: 'danger',
									allow_dismiss: true,
									placement:
									{
										from: 'bottom',
										align: 'right'
									}
								});
							};
						};
					}
				});
				
				return returnValue;
			}
			else if(id == 'adminUsername')
			{
				$.notify({
					title: '<strong>'+failed+'</strong><br />',
					message: username_needs,
					icon: 'fa fa-warning'
				},{
					type: 'danger',
					allow_dismiss: true,
					placement:
					{
						from: 'bottom',
						align: 'right'
					}
				});
			}
			else
			{
				$.notify({
					title: '<strong>'+failed+'</strong><br />',
					message: password_needs,
					icon: 'fa fa-warning'
				},{
					type: 'danger',
					allow_dismiss: true,
					placement:
					{
						from: 'bottom',
						align: 'right'
					}
				});
			};
		}
		else
		{
			var checkBoxValue;
			if(right == "benutzer_blocked")
			{
				checkBoxValue		=	$('#'+idCheckbox).hasClass("btn-danger");
			}
			else
			{
				checkBoxValue		=	$('#'+idCheckbox).hasClass("btn-success");
			};
			
			if($('#'+idDatapicker).val() != '')
			{
				datapickerValue		=	$('#'+idDatapicker).val().split(".");
				datapickerValue		=	datapickerValue[1]+'.'+datapickerValue[0]+'.'+datapickerValue[2];
				datapickerValue		=	new Date(datapickerValue).getTime() / 1000;
			}
			else
			{
				datapickerValue		=	0;
			};
			
			var dataString			=	"action=clientEdit&right="+right+"&checkbox="+checkBoxValue+"&time="+datapickerValue+"&pk="+pk;
			var returnValue;
			$.ajax({
				type: "POST",
				url: "functionsPost.php",
				data: dataString,
				cache: true,
				async: false,
				success: function(data)
				{
					if(data == 'done')
					{
						returnValue = true;
						
						if(!returnData)
						{
							$.notify({
								title: '<strong>'+success+'</strong><br />',
								message: hp_user_edit_done,
								icon: 'fa fa-check'
							},{
								type: 'success',
								allow_dismiss: true,
								placement:
								{
									from: 'bottom',
									align: 'right'
								}
							});
						};
					}
					else
					{
						returnValue = false;
						
						if(!returnData)
						{
							$.notify({
								title: '<strong>'+failed+'</strong><br />',
								message: hp_user_edit_failed,
								icon: 'fa fa-warning'
							},{
								type: 'danger',
								allow_dismiss: true,
								placement:
								{
									from: 'bottom',
									align: 'right'
								}
							});
						};
					};
				}
			});
			
			return returnValue;
		};
	};
	
/*
	admin_user: Save Serverspezfic permissions
*/
	function adminProfilUpdatePorts(port, instanz)
	{
		var pk										=	$('#mailOverview').attr('pk');
		
		// Toggle abfragen
		var hp_server_view_switch					=	$("#adminCheckboxRightWebServerView_"+port+"_"+instanz).hasClass("btn-success");
		var ts_rights_server_edit_switch			=	$("#adminCheckboxRightWebServerEdit_"+port+"_"+instanz).hasClass("btn-success");
		var ts_rights_server_start_stop_switch		=	$("#adminCheckboxRightWebServerStartStop_"+port+"_"+instanz).hasClass("btn-success");
		var ts_rights_server_msg_poke_switch		=	$("#adminCheckboxRightWebServerMessagePoke_"+port+"_"+instanz).hasClass("btn-success");
		var ts_rights_server_mass_actions_switch	=	$("#adminCheckboxRightWebServerMassActions_"+port+"_"+instanz).hasClass("btn-success");
		var ts_rights_server_protokoll_switch		=	$("#adminCheckboxRightWebServerProtokoll_"+port+"_"+instanz).hasClass("btn-success");
		var ts_rights_server_icons_switch			=	$("#adminCheckboxRightWebServerIcons_"+port+"_"+instanz).hasClass("btn-success");
		var ts_rights_server_bans_switch			=	$("#adminCheckboxRightWebServerBans_"+port+"_"+instanz).hasClass("btn-success");
		var ts_rights_server_token_switch			=	$("#adminCheckboxRightWebServerToken_"+port+"_"+instanz).hasClass("btn-success");
		var ts_rights_server_filelist_switch		=	$("#adminCheckboxRightWebServerFilelist_"+port+"_"+instanz).hasClass("btn-success");
		var ts_rights_server_backups_switch			=	$("#adminCheckboxRightWebServerBackups_"+port+"_"+instanz).hasClass("btn-success");
		var ts_rights_server_clients_switch			=	$("#adminCheckboxRightWebServerClients_"+port+"_"+instanz).hasClass("btn-success");
		var ts_rights_client_actions_switch			=	$("#adminCheckboxRightWebServerClientActions_"+port+"_"+instanz).hasClass("btn-success");
		var ts_rights_client_rights_switch			=	$("#adminCheckboxRightWebServerClientRights_"+port+"_"+instanz).hasClass("btn-success");
		var ts_rights_channel_actions_switch		=	$("#adminCheckboxRightWebServerChannelActions_"+port+"_"+instanz).hasClass("btn-success");
		
		// Zeitabfragen (Falls es später noch dazu kommen sollte)
		var time_server_view						=	0;
		var time_server_edit						=	0;
		var time_server_start_stop					=	0;
		var time_server_msg_poke					=	0;
		var time_server_mass_actions 				=	0;
		var time_server_protokoll					=	0;
		var time_server_icons						=	0;
		var time_server_bans						=	0;
		var time_server_token						=	0;
		var time_server_filelist					=	0;
		var time_server_backups						=	0;
		var time_server_clients						=	0;
		var time_client_actions						=	0;
		var time_client_rights						=	0;
		var time_channel_actions					=	0;
		
		var dataString								=	'action=clientEditPorts';
		
		// Informationen
		dataString									+=	'&pk='+pk;
		dataString									+=	'&teamspeak_port='+port;
		dataString									+=	'&teamspeak_instanz='+instanz;
		
		dataString									+=	'&server_view='+hp_server_view_switch;
		dataString									+=	'&server_edit='+ts_rights_server_edit_switch;
		dataString									+=	'&server_start_stop='+ts_rights_server_start_stop_switch;
		dataString									+=	'&server_msg_poke='+ts_rights_server_msg_poke_switch;
		dataString									+=	'&server_mass_actions='+ts_rights_server_mass_actions_switch;
		dataString									+=	'&server_protokoll='+ts_rights_server_protokoll_switch;
		dataString									+=	'&server_icons='+ts_rights_server_icons_switch;
		dataString									+=	'&server_bans='+ts_rights_server_bans_switch;
		dataString									+=	'&server_token='+ts_rights_server_token_switch;
		dataString									+=	'&server_filelist='+ts_rights_server_filelist_switch;
		dataString									+=	'&server_backups='+ts_rights_server_backups_switch;
		dataString									+=	'&server_clients='+ts_rights_server_clients_switch;
		
		dataString									+=	'&client_actions='+ts_rights_client_actions_switch;
		dataString									+=	'&client_rights='+ts_rights_client_rights_switch;
		
		dataString									+=	'&channel_actions='+ts_rights_channel_actions_switch;
		
		// Zeitstempel
		dataString 									+=	'&time_server_view='+time_server_view;
		dataString 									+=	'&time_server_edit='+time_server_edit;
		dataString 									+=	'&time_server_start_stop='+time_server_start_stop;
		dataString 									+=	'&time_server_msg_poke='+time_server_msg_poke;
		dataString 									+=	'&time_server_mass_actions='+time_server_mass_actions;
		dataString 									+=	'&time_server_protokoll='+time_server_protokoll;
		dataString 									+=	'&time_server_icons='+time_server_icons;
		dataString 									+=	'&time_server_bans='+time_server_bans;
		dataString 									+=	'&time_server_token='+time_server_token;
		dataString 									+=	'&time_server_filelist='+time_server_filelist;
		dataString 									+=	'&time_server_backups='+time_server_backups;
		dataString 									+=	'&time_server_clients='+time_server_clients;
		
		dataString 									+=	'&time_client_actions='+time_client_actions;
		dataString 									+=	'&time_client_rights='+time_client_rights;
		
		dataString 									+=	'&time_channel_actions='+time_channel_actions;
		
		$.ajax({
			type: "POST",
			url: "functionsPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data)
			{
				// Status Leiste einbelnden
				if(data == 'done')
				{
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: hp_user_edit_done,
						icon: 'fa fa-check'
					},{
						type: 'success',
						allow_dismiss: true,
						placement:
						{
							from: 'bottom',
							align: 'right'
						}
					});
					
					// Text einfärben
					if(hp_server_view_switch)
					{
						$("#colorText_"+port+"_"+instanz).addClass("text-success");
					}
					else
					{
						$("#colorText_"+port+"_"+instanz).removeClass("text-success");
					};
					
					// Button ausblenden lassen
					$('#saveButton_'+port+'_'+instanz).attr("onClick", "");
					$('#saveButton_'+port+'_'+instanz).addClass("disabled");
				}
				else
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: hp_user_edit_failed,
						icon: 'fa fa-check'
					},{
						type: 'success',
						allow_dismiss: true,
						placement:
						{
							from: 'bottom',
							align: 'right'
						}
					});
				};
			}
		});
	};
	
/*
	admin_user: Save Serverspezific Server Edit permissions
*/
	function saveServerEditSettingsBttn()
	{
		// Hauptdaten abfragen
		var pk													=	$('#saveServerEditSettingsBttn').attr("pk");
		var instanz												=	$('#saveServerEditSettingsBttn').attr("instanz");
		var port												=	$('#saveServerEditSettingsBttn').attr("port");
		
		// Toggle abfragen
		var adminCheckboxRightServerEditPort					=	$("#adminCheckboxRightServerEditPort").is(':checked');
		var adminCheckboxRightServerEditSlots					=	$("#adminCheckboxRightServerEditSlots").is(':checked');
		var adminCheckboxRightServerEditAutostart				=	$("#adminCheckboxRightServerEditAutostart").is(':checked');
		var adminCheckboxRightServerEditMinClientVersion		=	$("#adminCheckboxRightServerEditMinClientVersion").is(':checked');
		var adminCheckboxRightServerEditMainSettings			=	$("#adminCheckboxRightServerEditMainSettings").is(':checked');
		var adminCheckboxRightServerEditDefaultServerGroups		=	$("#adminCheckboxRightServerEditDefaultServerGroups").is(':checked');
		var adminCheckboxRightServerEditHostSettings			=	$("#adminCheckboxRightServerEditHostSettings").is(':checked');
		var adminCheckboxRightServerEditComplaintSettings		=	$("#adminCheckboxRightServerEditComplaintSettings").is(':checked');
		var adminCheckboxRightServerEditAntiFloodSettings		=	$("#adminCheckboxRightServerEditAntiFloodSettings").is(':checked');
		var adminCheckboxRightServerEditTransferSettings		=	$("#adminCheckboxRightServerEditTransferSettings").is(':checked');
		var adminCheckboxRightServerEditProtokollSettings		=	$("#adminCheckboxRightServerEditProtokollSettings").is(':checked');
		
		var dataString								=	'action=clientEditServerEdit';
		dataString									+=	'&pk='+pk;
		dataString									+=	'&port='+port;
		dataString									+=	'&instanz='+instanz;
		
		dataString									+=	'&adminCheckboxRightServerEditPort='+adminCheckboxRightServerEditPort;
		dataString									+=	'&adminCheckboxRightServerEditSlots='+adminCheckboxRightServerEditSlots;
		dataString									+=	'&adminCheckboxRightServerEditAutostart='+adminCheckboxRightServerEditAutostart;
		dataString									+=	'&adminCheckboxRightServerEditMinClientVersion='+adminCheckboxRightServerEditMinClientVersion;
		dataString									+=	'&adminCheckboxRightServerEditMainSettings='+adminCheckboxRightServerEditMainSettings;
		dataString									+=	'&adminCheckboxRightServerEditDefaultServerGroups='+adminCheckboxRightServerEditDefaultServerGroups;
		dataString									+=	'&adminCheckboxRightServerEditHostSettings='+adminCheckboxRightServerEditHostSettings;
		dataString									+=	'&adminCheckboxRightServerEditComplaintSettings='+adminCheckboxRightServerEditComplaintSettings;
		dataString									+=	'&adminCheckboxRightServerEditAntiFloodSettings='+adminCheckboxRightServerEditAntiFloodSettings;
		dataString									+=	'&adminCheckboxRightServerEditTransferSettings='+adminCheckboxRightServerEditTransferSettings;
		dataString									+=	'&adminCheckboxRightServerEditProtokollSettings='+adminCheckboxRightServerEditProtokollSettings;
		
		$.ajax({
			type: "POST",
			url: "functionsPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data)
			{
				// Status Leiste einbelnden
				if(data == 'done')
				{
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: hp_user_edit_done,
						icon: 'fa fa-check'
					},{
						type: 'success',
						allow_dismiss: true,
						placement:
						{
							from: 'bottom',
							align: 'right'
						}
					});
				}
				else
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: hp_user_edit_failed,
						icon: 'fa fa-close'
					},{
						type: 'danger',
						allow_dismiss: true,
						placement:
						{
							from: 'bottom',
							align: 'right'
						}
					});
				};
			}
		});
	};
	
/*
	admin_mail: Slide Mailbody
*/
	function showMailCode(id)
	{
		var showBttn	=	$('#'+id+'CodeBttn');
		var box 		=	$('#'+id+'Code');
		
		showBttn.css("display", "none");
		box.removeClass("display-none");
	};
	
/*
	admin_mail: Save Mailsettings
*/
	function saveMail(id)
	{
		var headline		=	$('#'+id+'Headline').val();
		var title			=	$('#'+id+'Title').val();
		var body;
		
		switch(id)
		{
			case "createRequest":
				body		=	CreateRequestEditor.getValue();
				break;
			case "requestNo":
				body		=	RequestNoEditor.getValue();
				break;
			case "requestYes":
				body		=	RequestYesEditor.getValue();
				break;
			case "createTicket":
				body		=	CreateTicketEditor.getValue();
				break;
			case "answerTicket":
				body		=	AnswerTicketEditor.getValue();
				break;
			case "closeTicket":
				body		=	CloseTicketEditor.getValue();
				break;
		};
		
		var dataString 		= 	'action=saveMail&request='+id+'&headline='+encodeURIComponent(headline)+'&title='+encodeURIComponent(title)+'&body='+encodeURIComponent(body);
		
		$.ajax({
			type: "POST",
			url: "functionsMailPost.php",
			data: dataString,
			cache: true,
			async: false,
			success: function(data){
				if(data == 'done')
				{
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: emailSaved,
						icon: 'fa fa-check'
					},{
						type: 'success',
						allow_dismiss: true,
						placement:
						{
							from: 'bottom',
							align: 'right'
						}
					});
					
					var showBttn	=	$('#'+id+'CodeBttn');
					var box 		=	$('#'+id+'Code');
					
					showBttn.css("display", "inline");
					box.addClass("display-none");
				}
				else
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: data,
						icon: 'fa fa-warning'
					},{
						type: 'danger',
						allow_dismiss: true,
						placement:
						{
							from: 'bottom',
							align: 'right'
						}
					});
				};
			}
		});
	};
	
/*
	admin_mail: Send Test Mail
*/
	function sendTestMail(id)
	{
		var mailContent			=	$('#'+id+'TestMail').val();
		var regex 				=	/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i;
		regex_check				= 	regex.test(mailContent);
		
		if(regex_check)
		{
			var headline		=	$('#'+id+'Headline').val();
			var title			=	$('#'+id+'Title').val();
			var body;
			
			switch(id)
			{
				case "createRequest":
					body		=	CreateRequestEditor.getValue();
					break;
				case "requestNo":
					body		=	RequestNoEditor.getValue();
					break;
				case "requestYes":
					body		=	RequestYesEditor.getValue();
					break;
				case "createTicket":
					body		=	CreateTicketEditor.getValue();
					break;
				case "answerTicket":
					body		=	AnswerTicketEditor.getValue();
					break;
				case "closeTicket":
					body		=	CloseTicketEditor.getValue();
					break;
			};
			
			var dataString 		= 	'action=writeMail&mail='+mailContent+'&headline='+encodeURIComponent(headline)+'&title='+encodeURIComponent(title)+'&body='+encodeURIComponent(body);
			
			$.ajax({
				type: "POST",
				url: "functionsMailPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data){
					if(data == 'done')
					{
						$.notify({
							title: '<strong>'+success+'</strong><br />',
							message: emailSended,
							icon: 'fa fa-check'
						},{
							type: 'success',
							allow_dismiss: true,
							placement:
							{
								from: 'bottom',
								align: 'right'
							}
						});
					}
					else
					{
						$.notify({
							title: '<strong>'+failed+'</strong><br />',
							message: data,
							icon: 'fa fa-warning'
						},{
							type: 'danger',
							allow_dismiss: true,
							placement:
							{
								from: 'bottom',
								align: 'right'
							}
						});
					};
				}
			});
		}
		else
		{
			$.notify({
				title: '<strong>'+failed+'</strong><br />',
				message: username_needs,
				icon: 'fa fa-warning'
			},{
				type: 'danger',
				allow_dismiss: true,
				placement:
				{
					from: 'bottom',
					align: 'right'
				}
			});
		};
	};