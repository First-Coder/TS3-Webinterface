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
				document.getElementById(id).innerHTML = "<i class=\"fa fa-ban\" aria-hidden=\"true\"></i> "+lang.blocked;
				document.getElementById(idDatapicker).removeAttribute("disabled");
			}
			else
			{
				document.getElementById(id).innerHTML = "<i class=\"fa fa-check\" aria-hidden=\"true\"></i> "+lang.unblocked;
				document.getElementById(idDatapicker).setAttribute("disabled","disabled");
			};
		}
		else if(whichContent == "ports")
		{
			if($("#"+id).hasClass("btn-danger"))
			{
				document.getElementById(id).innerHTML = "<i class=\"fa fa-check\" aria-hidden=\"true\"></i> "+lang.unblocked;
			}
			else
			{
				document.getElementById(id).innerHTML = "<i class=\"fa fa-ban\" aria-hidden=\"true\"></i> "+lang.blocked;
			};
		}
		else
		{
			if($("#"+id).hasClass("btn-danger"))
			{
				document.getElementById(id).innerHTML = "<i class=\"fa fa-check\" aria-hidden=\"true\"></i> "+lang.yes;
				document.getElementById(idDatapicker).removeAttribute("disabled");
			}
			else
			{
				document.getElementById(id).innerHTML = "<i class=\"fa fa-ban\" aria-hidden=\"true\"></i> "+lang.no;
				document.getElementById(idDatapicker).setAttribute("disabled","disabled");
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
		$("#mainContent").fadeOut("fast", function()
		{
			$("#mainContent").load('./php/admin/web_admin_user_info.php', { "id" : id, "mail" : mail, "lastLogin" : lastLogin }, function()
			{
				$("#mainContent").fadeIn("fast");
			});
		});
	};
	
/*
	admin_user: Show User Teamspeakpermission
*/
	function showUserServerPermission(id, mail)
	{
		$("#mainContent").fadeOut("fast", function()
		{
			$("#mainContent").load('./php/admin/web_admin_user_serverpermission.php', { "id" : id, "mail" : mail }, function()
			{
				$("#mainContent").fadeIn("fast");
			});
		});
	};

/*
	admin_user: Create a new User
*/
	function createUser()
	{
		var pwContent		=	escapeText($('#adminCreatePassword').val());
		var mailContent		=	escapeText($('#adminCreateUser').val());
		var regex_check		=	true;
		var regex 			=	/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{4,100}$/;
		regex_check			= 	regex.test(pwContent);
		
		if(regex_check)
		{
			regex_check		= 	emailRegex.test(mailContent);
		}
		else
		{
			setNotifyFailed(lang.password_needs);
		};
		
		if(regex_check)
		{
			$.ajax({
				type: "POST",
				url: "./php/functions/functionsSqlPost.php",
				data: {
					action:		'createUser',
					username:	mailContent,
					password:	pwContent
				},
				success: function(data){
					if(data == 'done')
					{
						$('#modalCreateUser').modal('hide');
						adminUserInit();
					}
					else
					{
						setNotifyFailed(data);
					};
				}
			});
		}
		else
		{
			setNotifyFailed(lang.username_needs);
		};
	};
	
/*
	admin_user: Delete All Users
*/
	function deleteAllUsers()
	{
		var pwContent				=	$('#adminDeleteAllUsersPassword').val(),
			mailContent				=	$('#adminDeleteAllUsersUser').val(),
			regex_check				=	true,
			regex 					=	/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{4,99}$/;
		
		regex_check					= 	regex.test(pwContent);
		
		if(regex_check)
		{
			regex_check				= 	emailRegex.test(mailContent);
		}
		else
		{
			setNotifyFailed(lang.password_needs);
		};
		
		if(regex_check)
		{
			$.ajax({
				type: "POST",
				url: "./php/functions/functionsSqlPost.php",
				data: {
					action:		'deleteAllUsers',
					username:	mailContent,
					password:	encodeURIComponent(pwContent)
				},
				success: function(data){
					if(data == 'done')
					{
						ausloggenInit();
					}
					else
					{
						setNotifyFailed(data);
					};
				}
			});
		}
		else
		{
			setNotifyFailed(lang.username_needs);
		};
	};
	
/*
	admin_user: Delete a User
*/
	function deleteUser(pk)
	{
		$.ajax({
			type: "POST",
			url: "./php/functions/functionsSqlPost.php",
			data: {
				action:		'deleteUser',
				pk:			escapeText(pk)
			},
			success: function(data){
				if(data == 'done')
				{
					deleteRow			=	Array(pk);
					$('#userList').bootstrapTable('remove', {
						field: 'pk',
						values: deleteRow
					});
					setNotifySuccess(lang.user_successful_deleted);
					$('#modalAreYouSure').modal('hide');
				}
				else
				{
					setNotifyFailed(data);
				};
			}
		});
	};
	
/*
	admin_settings: Change Websitelanguage
*/
	function changeLanguage(lang)
	{
		setConfig("language", lang);
	};
	
/*
	admin_settings: Change Websitetheme
*/
	function changeTheme(file)
	{
		setConfig("theme", (file == "style") ? "" : file);
	};
	
/*
	admin_settings: Change Config
*/
	function setConfig(which, data)
	{
		switch(which)
		{
			case "homepagesettings":
				var postData	=	{
					HEADING: 				escapeText($('#heading').val()),
					TS3_CHATNAME:			escapeText($('#chatname').val()),
					MASTERSERVER_INSTANZ:	escapeText($('#masterserverSelectInstanz').val()),
					MASTERSERVER_PORT:		escapeText($('#masterserverSelectPort').val())
				};
				break;
			case "ownsites":
				var postData	=	{
					CUSTOM_NEWS_PAGE: 		($('#setOwnNewsSite').is(':checked')) ? "true" : "false",
					CUSTOM_DASHBOARD_PAGE:	($('#setOwnDashboardSite').is(':checked')) ? "true" : "false"
				};
				break;
			case "mailsettings":
				regex_check		= 	emailRegex.test($('#mailadress').val());
				
				var postData	=	{
					USE_MAILS: 				($('#setMails').is(':checked')) ? "true" : "false",
					MAILADRESS: 			escapeText($('#mailadress').val()),
					MAIL_SMTP: 				($('#setMailSmtp').is(':checked')) ? "true" : "false",
					MAIL_SMTP_HOST:			escapeText($('#smtpHost').val()),
					MAIL_SMTP_PORT:			escapeText($('#smtpPort').val()),
					MAIL_SMTP_USERNAME:		escapeText($('#smtpUser').val()),
					MAIL_SMTP_PASSWORD:		escapeText($('#smtpPassword').val()),
					MAIL_SMTP_ENCRYPTION:	escapeText($('#smtpEncoding').val())
				};
				break;
			case "language":
				var postData	=	{
					LANGUAGE: 				escapeText(data)
				};
				break;
			case "theme":
				var postData	=	{
					STYLE: 					escapeText(data)
				};
				break;
		};
		
		if(which == "homepagesettings" && ($('#heading').val() == "" || $('#chatname').val() == ""))
		{
			setNotifyFailed(lang.field_cant_be_empty);
		}
		else if(which == "mailsettings" && !regex_check)
		{
			setNotifyFailed(lang.settings_mail_needed);
		}
		else
		{
			$.ajax({
				type: "POST",
				url: "./php/functions/functionsPost.php",
				data: {
					action:		'setConfig',
					data:		JSON.stringify(postData)
				},
				success: function(data){
					if(data == "done" && which == "language")
					{
						setNotifySuccess("Language set! Reload the page to see your site in the new language!");
					}
					else if(data == 'done')
					{
						setNotifySuccess(lang.settigns_saved);
					}
					else
					{
						setNotifyFailed("Ups, something Failed :/");
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
			$.ajax({
				type: "POST",
				url: "./php/functions/functionsTeamspeakPost.php",
				data: {
					action:		'getTeamspeakPorts',
					instanz:	instanz
				},
				success: function(data){
					var element = document.getElementById('masterserverSelectPort');
					
					while ( element.childNodes.length >= 1 )
					{
						element.removeChild(element.firstChild);
					};
					
					if(data != "")
					{
						var ports 	=	JSON.parse(data);
						for (i = 0; i < ports.length; i++)
						{
							port 					= 	document.createElement('option');
							port.value				=	ports[i];
							port.text				=	ports[i];
							element.appendChild(port);
						};
						$('#masterserverSelectPort').prop("disabled", false);
					}
					else
					{
						nope 						= 	document.createElement('option');
						nope.value					=	'nope';
						nope.text					=	unescape(lang.no_masterserver);
						element.appendChild(nope);
						$('#masterserverSelectPort').prop("disabled", true);
					};
				}
			});
		}
		else
		{
			var element = document.getElementById('masterserverSelectPort');
			
			while ( element.childNodes.length >= 1 )
			{
				element.removeChild(element.firstChild);
			};
			
			nope 						= 	document.createElement('option');
			nope.value					=	'nope';
			nope.text					=	unescape(lang.no_masterserver);
			element.appendChild(nope);
			$('#masterserverSelectPort').prop("disabled", true);
		};
	};

/*
	admin_settings: Change Websitemodul
*/
	function changeModul(id)
	{
		dataJson	=	{"action":"setModul", "id":id, "value":$('#'+id).is(':checked')};
		
		$.ajax({
			type: "POST",
			url: "./php/functions/functionsSqlPost.php",
			data: dataJson,
			success: function(data){
				if(data == 'done')
				{
					setNotifySuccess(lang.modul_settings_done);
				}
				else
				{
					setNotifyFailed(lang.modul_settings_failed);
				};
			}
		});
	};
	
/*
	admin_instanz: Slide Instanzshell login
*/
	function slideInstanzShell(which)
	{
		if(which == "client")
		{
			$('#shellKeyArea').slideUp("slow", function()
			{
				$('#shellClientArea').slideDown("slow");
			});
		}
		else
		{
			$('#shellClientArea').slideUp("slow", function()
			{
				$('#shellKeyArea').slideDown("slow");
			});
		};
	};
	
/*
	admin_instanz: Create a new Instanz
*/
	function createInstanz()
	{
		document.getElementById("addInstanz").disabled = true;
		
		var alias		=	escapeText($('#adminCreateInstanzAlias').val());
		var ip			=	escapeText($('#adminCreateInstanzIp').val());
		var queryport	=	escapeText($('#adminCreateInstanzQueryport').val());
		var client		=	escapeText($('#adminCreateInstanzClient').val());
		var passwort	=	escapeText($('#adminCreateInstanzPassword').val());
		
		if(ip != '' && queryport != '' && client != '' && passwort != '')
		{
			$.ajax({
				type: "POST",
				url: "./php/functions/functionsPost.php",
				data: {
					action:		"changeInstanz",
					subaction:	"create",
					alias:		alias,
					ip:			ip,
					queryport:	queryport,
					client:		client,
					passwort:	encodeURIComponent(passwort)
				},
				success: function(data){
					if(data == 'done')
					{
						$('#modalCreateInstanz').modal('hide');
						setTimoutInstanzCreate();
					}
					else
					{
						document.getElementById("addInstanz").disabled = false;
						setNotifyFailed(data);
					};
				}
			});
		}
		else
		{
			setNotifyFailed(lang.instanz_add_empty);
		};
	};
	
	function setTimoutInstanzCreate()
	{
		setTimeout(function(){
			adminInstanzInit();
		}, 2000);
	};
	
/*
	admin_instanz: Change a Instanz
*/
	function changeInstanz(what, instanz)
	{
		var someText				=	'';
		switch(what)
		{
			case 'ip':			someText	=	escapeText($('#instanzIp'+instanz).val());			break;
			case 'queryport':	someText	=	escapeText($('#instanzQueryport'+instanz).val());	break;
			case 'user':		someText	=	escapeText($('#instanzUser'+instanz).val());		break;
			case 'pw':			someText	=	escapeText($('#instanzPassword'+instanz).val());	break;
		};
		
		if(someText != '')
		{
			$.ajax({
				type: "POST",
				url: "./php/functions/functionsTeamspeakPost.php",
				data: {
					action:		'editInstance',
					instanz:	instanz,
					what:		what,
					content:	encodeURIComponent(someText)
				},
				success: function(data){
					if(data == 'done')
					{
						$('#instanzTextBox'+instanz).addClass('label-success');
						$('#instanzTextBox'+instanz).removeClass('label-danger');
						
						document.getElementById('instanzTextBox'+instanz).innerHTML = '<i class="fa fa-check"></i> '+lang.success;
						setNotifySuccess(lang.instanz_change_done);
					}
					else
					{
						$('#instanzTextBox'+instanz).addClass('label-danger');
						$('#instanzTextBox'+instanz).removeClass('label-success');
						
						document.getElementById('instanzTextBox'+instanz).innerHTML = '<i class="fa fa-close"></i> '+lang.failed;
						
						setNotifyFailed(data);
					};
				}
			});
		};
	};
	
/*
	admin_instanz: Delete a Instanz
*/
	function deleteInstanz(instanz)
	{
		$.ajax({
			type: "POST",
			url: "./php/functions/functionsSqlPost.php",
			data: {
				action:		'deleteInstanz',
				instanz:	instanz
			},
			dataTyp: "json",
			cache: false,
			success: function(data) {
				if(data == "done")
				{
					setNotifySuccess(lang.instanz_successfull_deleted);
					$('#modalAreYouSure').modal('hide');
					$("#instanzMain"+instanz).remove();
				}
				else
				{
					setNotifyFailed(data);
				};
			}
		});
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
		var command			=	escapeText($("#commandInput"+instanz).val());
		var dataString 		= 	'action=commandQueryConsole&instanz='+instanz+'&command='+command;
		var serverId		=	"";
		var oldInnerText	=	document.getElementById("commandInputHistory"+instanz).innerHTML;
		
		document.getElementById("commandInputHistory"+instanz).innerText = command;
		document.getElementById("commandInputHistory"+instanz).innerHTML += "<br />" + oldInnerText;
		
		if(command.substring(0, 3) == "use")
		{
			serverId		=	command.split(" ");
		};
		
		if(typeof(queryConsoleSelected[instanz]) != "undefined")
		{
			dataString		+=	'&server='+queryConsoleSelected[instanz];
		};
		
		$.ajax({
			type: "POST",
			url: "./php/functions/functionsTeamspeakPost.php",
			data: dataString,
			success: function(data){
				if(data.trim() == "<br />")
				{
					if(serverId != "")
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
	admin_user: Save User Edit
*/
	function setGlobalPermissions(which)
	{
		var right, idCheckbox, idDatapicker;
		
		switch(which)
		{
			case "homepage":
				right 			=	new Array('right_hp_main', 'right_hp_ts3', 'right_hp_user_create', 'right_hp_user_delete', 'right_hp_user_edit', 'right_hp_ticket_system', 'right_hp_mails', 'right_hp_logs');
				idCheckbox 		= 	new Array('adminCheckboxRightsEdit', 'adminCheckboxRightsTSEdit', 'adminCheckboxRightsUserCreate', 'adminCheckboxRightsUserDelete', 'adminCheckboxRightsUserEdit', 'adminCheckboxRightsTicketsystem', 'adminCheckboxRightsMails', 'adminCheckboxRightsLogs');
				idDatapicker 	= 	new Array('adminDatapickerRightsEdit', 'adminDatapickerRightsTSEdit', 'adminDatapickerRightsUserCreate', 'adminDatapickerRightsUserDelete', 'adminDatapickerRightsUserEdit', 'adminDatapickerRightsTicketsystem', 'adminDatapickerRightsMails', 'adminDatapickerRightsLogs');
				break;
			case "teamspeak":
				right 			=	new Array('right_web', 'right_web_global_message_poke', 'right_web_server_create', 'right_web_server_delete', 'right_web_global_server');
				idCheckbox 		= 	new Array('adminCheckboxRightWeb', 'adminCheckboxRightWebGlobalMessagePoke', 'adminCheckboxRightWebServerCreate', 'adminCheckboxRightWebServerDelete', 'adminCheckboxRightsWebGlobalServer');
				idDatapicker 	= 	new Array('adminDatapickerRightsWeb', 'adminDatapickerRightsWebGlobalMessagePoke', 'adminDatapickerRightsWebServerCreate', 'adminDatapickerRightsWebServerDelete', 'adminDatapickerRightsWebGlobalServer');
				break;
			case "user":
				right 			=	new Array('benutzer_blocked');
				idCheckbox		=	new Array('adminCheckboxBlocked');
				idDatapicker	=	new Array('adminDatapickerBlocked');
				break;
		};
		
		var status				=	true;
		
		for(var i = 0;i < right.length;i++)
		{
			if(status)
			{
				status			=	adminProfilUpdate("", right[i], idCheckbox[i], idDatapicker[i]);
			};
		};
		
		if(which == "user")
		{
			if($('#adminUsername').val() != "" && status)
			{
				status 			= 	adminProfilUpdate('adminUsername');
			};
			
			if($('#adminPassword').val() != "" && status)
			{
				status 			= 	adminProfilUpdate('adminPassword');
			};
		};
		
		if(status)
		{
			setNotifySuccess(lang.user_edit_done);
		}
		else
		{
			setNotifyFailed(lang.user_edit_failed);
		};
	};
	
	function adminProfilUpdate(id, right, idCheckbox, idDatapicker)
	{
		pk								=	$('#mailOverview').attr('pk');
		idContent 						= 	$('#'+id).val();
		
		if((id == 'adminUsername' || id == 'adminPassword') && idContent != '')
		{
			var regex_check				=	true;
			var pw_check				=	true;
			
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
				regex_check				= 	emailRegex.test(idContent);
			};
			
			if(regex_check && pw_check)
			{
				var returnValue			=	false;
				$.ajax({
					type: "POST",
					url: "./php/functions/functionsSqlPost.php",
					data: {
						action:		'updateUser',
						id:			id,
						content:	idContent,
						pk:			pk
					},
					async: false,
					success: function(data)
					{
						if(data == 'done')
						{
							returnValue = true;
						};
					}
				});
				
				return returnValue;
			}
			else if(id == 'adminUsername')
			{
				setNotifyFailed(lang.username_needs);
			}
			else
			{
				setNotifyFailed(lang.password_needs);
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
				if(datapickerValue.length == 1)
				{
					datapickerValue		=	datapickerValue[0].substr(0, datapickerValue[0].length-3);
					datapickerValue		=	datapickerValue.split("/");
					datapickerValue		=	datapickerValue[0]+'.'+datapickerValue[1]+'.'+datapickerValue[2];
				}
				else 
				{
					datapickerValue		=	datapickerValue[1]+'.'+datapickerValue[0]+'.'+datapickerValue[2];
				};
				
				datapickerValue		=	new Date(datapickerValue).getTime() / 1000;
			}
			else
			{
				datapickerValue		=	0;
			};
			
			var returnValue			=	false;
			$.ajax({
				type: "POST",
				url: "./php/functions/functionsSqlPost.php",
				data: {
					action:		'clientEdit',
					right:		right,
					checkbox:	checkBoxValue,
					time:		datapickerValue,
					pk:			pk
				},
				async: false,
				success: function(data)
				{
					if(data == 'done')
					{
						returnValue = true;
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
		$.ajax({
			type: "POST",
			url: "./php/functions/functionsSqlPost.php",
			data: {
				action:					'clientEditPorts',
				pk:						escapeText($('#mailOverview').attr('pk')),
				port:					port,
				instanz:				instanz,
				server_view:			$("#adminCheckboxRightWebServerView_"+port+"_"+instanz).hasClass("btn-success"),
				server_banner:			$("#adminCheckboxRightWebServerBanner_"+port+"_"+instanz).hasClass("btn-success"),
				server_edit:			$("#adminCheckboxRightWebServerEdit_"+port+"_"+instanz).hasClass("btn-success"),
				server_start_stop:		$("#adminCheckboxRightWebServerStartStop_"+port+"_"+instanz).hasClass("btn-success"),
				server_msg_poke:		$("#adminCheckboxRightWebServerMessagePoke_"+port+"_"+instanz).hasClass("btn-success"),
				server_mass_actions:	$("#adminCheckboxRightWebServerMassActions_"+port+"_"+instanz).hasClass("btn-success"),
				server_protokoll:		$("#adminCheckboxRightWebServerProtokoll_"+port+"_"+instanz).hasClass("btn-success"),
				server_icons:			$("#adminCheckboxRightWebServerIcons_"+port+"_"+instanz).hasClass("btn-success"),
				server_bans:			$("#adminCheckboxRightWebServerBans_"+port+"_"+instanz).hasClass("btn-success"),
				server_token:			$("#adminCheckboxRightWebServerToken_"+port+"_"+instanz).hasClass("btn-success"),
				server_filelist:		$("#adminCheckboxRightWebServerFilelist_"+port+"_"+instanz).hasClass("btn-success"),
				server_backups:			$("#adminCheckboxRightWebServerBackups_"+port+"_"+instanz).hasClass("btn-success"),
				server_clients:			$("#adminCheckboxRightWebServerClients_"+port+"_"+instanz).hasClass("btn-success"),
				client_actions:			$("#adminCheckboxRightWebServerClientActions_"+port+"_"+instanz).hasClass("btn-success"),
				client_rights:			$("#adminCheckboxRightWebServerClientRights_"+port+"_"+instanz).hasClass("btn-success"),
				channel_actions:		$("#adminCheckboxRightWebServerChannelActions_"+port+"_"+instanz).hasClass("btn-success")
			},
			success: function(data)
			{
				if(data == "done")
				{
					setNotifySuccess(lang.user_edit_done);
					
					if($("#adminCheckboxRightWebServerView_"+port+"_"+instanz).hasClass("btn-success"))
					{
						$("#colorBox_"+port+"_"+instanz).removeClass("alert-danger");
						$("#colorBox_"+port+"_"+instanz).addClass("alert-success");
					}
					else
					{
						$("#colorBox_"+port+"_"+instanz).removeClass("alert-success");
						$("#colorBox_"+port+"_"+instanz).addClass("alert-danger");
					};
					
					$('#saveButton_'+port+'_'+instanz).attr("onClick", "");
					$('#saveButton_'+port+'_'+instanz).addClass("disabled");
				}
				else
				{
					setNotifyFailed(lang.user_edit_failed);
				};
			}
		});
	};
	
/*
	admin_user: Save Serverspezific Server Edit permissions
*/
	function saveServerEditSettingsBttn()
	{
		var pk													=	escapeText($('#saveServerEditSettingsBttn').attr("pk")),
		instanz													=	escapeText($('#saveServerEditSettingsBttn').attr("instanz")),
		port													=	escapeText($('#saveServerEditSettingsBttn').attr("port"));
		
		$.ajax({
			type: "POST",
			url: "./php/functions/functionsSqlPost.php",
			data: {
				action:												'clientEditServerEdit',
				pk:													pk,
				port:												port,
				instanz:											instanz,
				adminCheckboxRightServerEditPort:					$("#adminCheckboxRightServerEditPort").is(':checked'),
				adminCheckboxRightServerEditSlots:					$("#adminCheckboxRightServerEditSlots").is(':checked'),
				adminCheckboxRightServerEditAutostart:				$("#adminCheckboxRightServerEditAutostart").is(':checked'),
				adminCheckboxRightServerEditMinClientVersion:		$("#adminCheckboxRightServerEditMinClientVersion").is(':checked'),
				adminCheckboxRightServerEditMainSettings:			$("#adminCheckboxRightServerEditMainSettings").is(':checked'),
				adminCheckboxRightServerEditDefaultServerGroups:	$("#adminCheckboxRightServerEditDefaultServerGroups").is(':checked'),
				adminCheckboxRightServerEditHostSettings:			$("#adminCheckboxRightServerEditHostSettings").is(':checked'),
				adminCheckboxRightServerEditComplaintSettings:		$("#adminCheckboxRightServerEditComplaintSettings").is(':checked'),
				adminCheckboxRightServerEditAntiFloodSettings:		$("#adminCheckboxRightServerEditAntiFloodSettings").is(':checked'),
				adminCheckboxRightServerEditTransferSettings:		$("#adminCheckboxRightServerEditTransferSettings").is(':checked'),
				adminCheckboxRightServerEditProtokollSettings:		$("#adminCheckboxRightServerEditProtokollSettings").is(':checked')
			},
			success: function(data)
			{
				if(data == "done")
				{
					setNotifySuccess(lang.user_edit_done);
				}
				else
				{
					setNotifyFailed(lang.user_edit_failed);
				};
			}
		});
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
				body		=	CodeEditor.createRequest.getValue();
				break;
			case "requestNo":
				body		=	CodeEditor.requestNo.getValue();
				break;
			case "requestYes":
				body		=	CodeEditor.requestYes.getValue();
				break;
			case "createTicket":
				body		=	CodeEditor.createTicket.getValue();
				break;
			case "answerTicket":
				body		=	CodeEditor.answerTicket.getValue();
				break;
			case "closeTicket":
				body		=	CodeEditor.closeTicket.getValue();
				break;
			case "forgotPassword":
				body		=	CodeEditor.forgotPassword.getValue();
				break;
		};
		
		$.ajax({
			type: "POST",
			url: "./php/functions/functionsMailPost.php",
			data: {
				action:		'saveMail',
				request:	id,
				headline:	encodeURIComponent(headline),
				title:		encodeURIComponent(title),
				body:		encodeURIComponent(body)
			},
			success: function(data){
				if(data == 'done')
				{
					setNotifySuccess(lang.email_saved);
					
					$('#'+id+'CodeBttn').css("display", "inline");
					$('#'+id+'Code').css("display", "none");
				}
				else
				{
					setNotifyFailed(data);
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
		regex_check				= 	emailRegex.test(mailContent);
		
		if(regex_check)
		{
			var headline		=	$('#'+id+'Headline').val(),
				title			=	$('#'+id+'Title').val(),
				body;
			
			switch(id)
			{
				case "createRequest":
					body		=	(typeof(CodeEditor.createRequest) == "undefined") ? document.getElementById("createRequestBody").value : CodeEditor.createRequest.getValue();
					break;
				case "requestNo":
					body		=	(typeof(CodeEditor.requestNo) == "undefined") ? document.getElementById("requestNoBody").value : CodeEditor.requestNo.getValue();
					break;
				case "requestYes":
					body		=	(typeof(CodeEditor.requestYes) == "undefined") ? document.getElementById("requestYesBody").value : CodeEditor.requestYes.getValue();
					break;
				case "createTicket":
					body		=	(typeof(CodeEditor.createTicket) == "undefined") ? document.getElementById("createTicketBody").value : CodeEditor.createTicket.getValue();
					break;
				case "answerTicket":
					body		=	(typeof(CodeEditor.answerTicket) == "undefined") ? document.getElementById("answerTicketBody").value : CodeEditor.answerTicket.getValue();
					break;
				case "closeTicket":
					body		=	(typeof(CodeEditor.closeTicket) == "undefined") ? document.getElementById("closeTicketBody").value : CodeEditor.closeTicket.getValue();
					break;
				case "forgotPassword":
					body		=	(typeof(CodeEditor.forgotPassword) == "undefined") ? document.getElementById("forgotPasswordBody").value : CodeEditor.forgotPassword.getValue();
					break;
			};
			
			$.ajax({
				type: "POST",
				url: "./php/functions/functionsMailPost.php",
				data: {
					action:		'writeMail',
					mail:		mailContent,
					headline:	encodeURIComponent(headline),
					title:		encodeURIComponent(title),
					body:		encodeURIComponent(body)
				},
				success: function(data){
					if(data == 'done')
					{
						setNotifySuccess(lang.email_sended);
					}
					else
					{
						setNotifyFailed(data);
					};
				}
			});
		}
		else
		{
			setNotifyFailed(lang.username_needs);
		};
	};