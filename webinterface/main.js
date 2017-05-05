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
	Navigation / Load content
*/
	$(document).ready(function()
	{
		var dropdownMenu;
		$(window).on({
			"shown.bs.dropdown": function(e) {
				if(typeof(dropdownMenu) != "undefined" && dropdownMenu.isOpen == true)
				{
					$(e.target).append(dropdownMenu.detach());
					dropdownMenu.hide();
					dropdownMenu.isOpen	=	false;
				}
				else
				{
					dropdownMenu 		= 	$(e.target).find('.dropdown-menu');
					dropdownMenu.isOpen	=	true;
					
					$('body').append(dropdownMenu.detach());
					
					var eOffset 		= 	$(e.target).offset();
					dropdownMenu.css({
						'display': 'block',
						'top': eOffset.top + $(e.target).outerHeight(),
						'left': eOffset.left
					});
				};
			},
			"hide.bs.dropdown":  function(e) {
				if(typeof(dropdownMenu) != "undefined" && dropdownMenu.isOpen == true)
				{
					$(e.target).append(dropdownMenu.detach());
					dropdownMenu.hide();
					dropdownMenu.isOpen		=	false;
				}
				else
				{
					dropdownMenu 	= 	$(e.target).find('.dropdown-menu');
					dropdownMenu.isOpen	=	true;
					$('body').append(dropdownMenu.detach());
					
					var eOffset 	= 	$(e.target).offset();
					dropdownMenu.css({
						'display': 'block',
						'top': eOffset.top + $(e.target).outerHeight(),
						'left': eOffset.left
					});
					return false;
				};
			}
		});
		
		if(logged == "false")
		{
			var doc				=	window.location.search.split("?");
			switch(doc[1])
			{
				case "web_main_main":
					changeToMainMain();
					break;
				case "web_main_apply_server":
					changeToMainApplyForServer();
					break;
				case "web_main_masterserver":
					changeToMainMasterserver();
					break;
				default:
					changeToMainMain();
					break;
			};
		}
		else
		{
			var doc				=	window.location.search.split("?");
			
			if(typeof(doc[1]) == 'undefined')
			{
				profilDashboardInit();
			}
			else if(typeof(doc[2]) != 'undefined' && typeof(doc[3]) != 'undefined')
			{
				switch(doc[1])
				{
					case "web_teamspeak_serverview":
						teamspeakServerView(doc[2], doc[3]);
						break;
					case "web_teamspeak_servertoken":
						teamspeakTokenInit();
						break;
					case "web_teamspeak_serverbans":
						teamspeakBansInit();
						break;
					case "web_teamspeak_serverclients":
						teamspeakClientsInit();
						break;
					case "web_teamspeak_servericons":
						teamspeakIconsInit();
						break;
					case "web_teamspeak_servermassactions":
						teamspeakMassActionsInit();
						break;
					case "web_teamspeak_serverprotokol":
						teamspeakProtokolInit();
						break;
					case "web_teamspeak_serverbackups":
						teamspeakBackupsInit();
						break;
					case "web_teamspeak_serverfilelist":
						teamspeakFilelistInit();
						break;
					default:
						teamspeakServerView(doc[2], doc[3]);
						break;
				};
			}
			else
			{
				switch(doc[1])
				{
					case "web_main_main":
						changeToMainMain();
						break;
					case "web_main_apply_server":
						changeToMainApplyForServer();
						break;
					case "web_profil_dashboard":
						profilDashboardInit();
						break;
					case "web_profil_edit":
						profilEditInit();
						break;
					case "web_profil_rights":
						profilPermissionInit();
						break;
					case "web_admin_settings":
						adminSettingsInit();
						break;
					case "web_admin_instanz":
						adminInstanzInit();
						break;
					case "web_admin_user":
						adminUserInit();
						break;
					case "web_admin_mail":
						adminMailInit();
						break;
					case "web_admin_logs":
						adminLogsInit();
						break;
					case "web_teamspeak_server":
						teamspeakServerInit();
						break;
					case "web_teamspeak_server_create":
						teamspeakServerCreateInit();
						break;
					case "web_teamspeak_server_requests":
						teamspeakServerRequestsInit();
						break;
					case "web_ticket":
						ticketInit();
						break;
					default:
						profilDashboardInit();
						break;
				};
			};
		};
		
		checkIfUserIsBlocked();
	});

/*
	Load Login Main Page
*/
	$('.mainMain')
		.click(function()
		{
			changeToMainMain();
			
			return false;
		});
		
	function changeToMainMain()
	{
		if ('replaceState' in history)
		{
			history.replaceState(null, document.title, "index.php?web_main_main");
		};
		
		$('.navbar-default .active').each(function()
		{
			$(this).removeClass("active");
		});
		
		$('.navigationitem').each(function()
		{
			$(this).removeClass("active");
		});
		
		$('.mainMain').each(function()
		{
			$(this).addClass("active");
		});
		
		$('#mainContent').fadeOut("fast", function()
		{
			$('#mainContent').load("./php/main/web_main_main.php", function()
			{
				if(document.getElementById('showSupport'))
				{
					document.getElementById('showSupport').innerHTML = '<i class="fa fa-thumbs-up"></i> '+lang.mitwirkende;
				};
				$('#mainContent').fadeIn("fast");
			});
		});
	};
		
/*
	Load Login Apply for Server Page
*/
	$('.mainApplyForServer')
		.click(function()
		{
			changeToMainApplyForServer();
			
			return false;
		});
	
	function changeToMainApplyForServer()
	{
		if ('replaceState' in history)
		{
			history.replaceState(null, document.title, "index.php?web_main_apply_server");
		};
		
		$('.navbar-default .active').each(function()
		{
			$(this).removeClass("active");
		});
		
		$('.navigationitem').each(function()
		{
			$(this).removeClass("active");
		});
		
		$('.mainApplyForServer').each(function()
		{
			$(this).addClass("active");
		});
		
		$('#mainContent').fadeOut("fast", function()
		{
			$('#mainContent').load("./php/main/web_main_apply_server.php", function()
			{
				if(document.getElementById('showSupport'))
				{
					document.getElementById('showSupport').innerHTML = '<i class="fa fa-thumbs-up"></i> '+lang.mitwirkende;
				};
				if(typeof(wantServer['4']) != 'undefined')
				{
					$('#wantServerStep1').remove();
					$('#wantServerStep2').remove();
					$('#wantServerStep3').show();
				}
				else if(typeof(wantServer['0']) != 'undefined')
				{
					$('#wantServerStep1').remove();
					$('#wantServerStep2').show();
				};
				$('#mainContent').fadeIn("fast");
			});
		});
	};
	
/*
	Load Login Masterserver Page
*/
	$('.mainMasterserver')
		.click(function()
		{
			changeToMainMasterserver();
			
			return false;
		});
	
	function changeToMainMasterserver()
	{
		if ('replaceState' in history)
		{
			history.replaceState(null, document.title, "index.php?web_main_masterserver");
		};
		
		$('.navbar-default .active').each(function()
		{
			$(this).removeClass("active");
		});
		
		$('.navigationitem').each(function()
		{
			$(this).removeClass("active");
		});
		
		$('.mainMasterserver').each(function()
		{
			$(this).addClass("active");
		});
		
		$('#mainContent').fadeOut("fast", function()
		{
			$('#mainContent').load("./php/main/web_main_masterserver.php", function()
			{
				if(document.getElementById('showSupport'))
				{
					document.getElementById('showSupport').innerHTML = '<i class="fa fa-thumbs-up"></i> '+lang.mitwirkende;
				};
				$('#mainContent').fadeIn("fast");
			});
		});
	};

/*
	Navigation Teamspeakserver selected
*/
	function teamspeakViewInit()
	{
		navigationTeamspeakInit("teamspeakView", "web_teamspeak_serverview", instanz, serverId);
	};
	
	function teamspeakClientsInit()
	{
		navigationTeamspeakInit("teamspeakClients", "web_teamspeak_serverclients", instanz, serverId);
	};
	
	function teamspeakProtokolInit()
	{
		navigationTeamspeakInit("teamspeakProtokol", "web_teamspeak_serverprotokol", instanz, serverId);
	};
	
	function teamspeakMassActionsInit()
	{
		navigationTeamspeakInit("teamspeakMassActions", "web_teamspeak_servermassactions", instanz, serverId);
	};
	
	function teamspeakIconsInit()
	{
		navigationTeamspeakInit("teamspeakIcons", "web_teamspeak_servericons", instanz, serverId);
	};
	
	function teamspeakBansInit()
	{
		navigationTeamspeakInit("teamspeakBans", "web_teamspeak_serverbans", instanz, serverId);
	};
	
	function teamspeakTokenInit()
	{
		navigationTeamspeakInit("teamspeakToken", "web_teamspeak_servertoken", instanz, serverId);
	};
	
	function teamspeakFilelistInit()
	{
		navigationTeamspeakInit("teamspeakFilelist", "web_teamspeak_serverfilelist", instanz, serverId);
	};
	
	function teamspeakBackupsInit()
	{
		navigationTeamspeakInit("teamspeakBackup", "web_teamspeak_serverbackups", instanz, serverId);
	};
	
	function navigationTeamspeakInit(activeClass, link, instanz, serverId)
	{
		if ('replaceState' in history)
		{
			history.replaceState(null, document.title, "index.php?"+link+"?"+instanz+"?"+serverId);
		};
		
		$('.navigationitem').each(function()
		{
			$(this).removeClass("active");
		});
		
		$('.navbar-default .active').each(function()
		{
			$(this).removeClass("active");
		});
		
		$("."+activeClass).addClass("active");
		
		$("#mainContent").fadeOut("fast", function()
		{
			$("#mainContent").load('./php/teamspeak/'+link+'.php', function()
			{
				$("#mainContent").fadeIn("fast");
			});
		});
	};

/*
	Slide something
*/
	function slideMe(boxid, iconid, iconleft = false)
	{
		var box 		=	$('#'+boxid),
			icon		=	$('#'+iconid);
		
		if((icon.hasClass("fa-arrow-right") && !iconleft) || icon.hasClass("fa-arrow-left") && iconleft)
		{
			icon.removeClass((iconleft) ? "fa-arrow-left" : "fa-arrow-right");
			icon.addClass("fa-arrow-down");
			
			box.slideDown("slow");
		}
		else
		{
			icon.removeClass("fa-arrow-down");
			icon.addClass((iconleft) ? "fa-arrow-left" : "fa-arrow-right");
			
			box.slideUp("slow");
		};
	};
	
/*
	Converttime
*/
	function convertTime(seconds)
	{
		var returnArray			=	Array();
		returnArray['days']		=	Math.floor(seconds / 86400);
		returnArray['hours']	=	Math.floor((seconds - (returnArray['days'] * 86400)) / 3600);
		returnArray['minutes']	=	Math.floor((seconds - ((returnArray['days'] * 86400)+(returnArray['hours'] * 3600))) / 60);
		returnArray['seconds']	=	Math.floor(seconds - ((returnArray['days'] * 86400)+(returnArray['hours'] * 3600)+(returnArray['minutes'] * 60)));
		
		return returnArray;
	};
	
	
/*
	Escape text
*/
	function escapeText(text)
	{
		return text.replace(/\\/g, '\\\\').
			replace(/\u0008/g, '\\b').
			replace(/\t/g, '\\t').
			replace(/\n/g, '\\n').
			replace(/\f/g, '\\f').
			replace(/\r/g, '\\r').
			replace(/'/g, '\\\'').
			replace(/"/g, '\\"');
	};
	
/*
	Notify Success
*/
	function setNotifySuccess(text)
	{
		$.notify({
			title: '<strong>'+lang.success+'</strong><br />',
			message: text,
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
	
/*
	Notify Failed
*/
	function setNotifyFailed(text)
	{
		$.notify({
			title: '<strong>'+lang.failed+'</strong><br />',
			message: text,
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
	
/*
	Function Select Profil Dashboard
*/
	function profilDashboardInit()
	{
		navigationInit("profilDashboard", "web_profil_dashboard", "./php/profile/");
	};

/*
	Function Select Profil Edit
*/
	function profilEditInit()
	{
		navigationInit("profilEdit", "web_profil_edit", "./php/profile/");
	};
	
/*
	Function Select Profil Permissions
*/
	function profilPermissionInit()
	{
		navigationInit("profilPermission", "web_profil_rights", "./php/profile/");
	};
	
/*
	Function Select Admin settings
*/
	function adminSettingsInit()
	{
		navigationInit("adminSettings", "web_admin_settings", "./php/admin/");
	};
	
/*
	Function Select Admin instanz
*/
	function adminInstanzInit()
	{
		navigationInit("adminInstanz", "web_admin_instanz", "./php/admin/");
	};
	
/*
	Function Select Admin users
*/
	function adminUserInit()
	{
		navigationInit("adminUser", "web_admin_user", "./php/admin/");
	};
	
/*
	Function Show Mailcontent
*/
	function adminMailInit()
	{
		navigationInit("adminMail", "web_admin_mail", "./php/admin/");
	};

/*
	Function see Logs
*/	
	function adminLogsInit()
	{
		navigationInit("adminLogs", "web_admin_logs", "./php/admin/");
	};
	
/*
	Function Select Teamspeak Server
*/
	function teamspeakServerInit()
	{
		navigationInit("teamspeakServer", "web_teamspeak_server", "./php/teamspeak/");
	};
	
/*
	Function Select Teamspeak Server Create
*/
	function teamspeakServerCreateInit()
	{
		navigationInit("teamspeakServerCreate", "web_teamspeak_server_create", "./php/teamspeak/");
	};
	
/*
	Function Select Teamspeak Server Requests
*/
	function teamspeakServerRequestsInit()
	{
		navigationInit("teamspeakServerRequests", "web_teamspeak_server_requests", "./php/teamspeak/");
	};
	
/*
	Function Select Teamspeakserverview
*/
	function teamspeakServerView(instanz, id)
	{
		if ('replaceState' in history)
		{
			history.replaceState(null, document.title, "index.php?web_teamspeak_serverview?"+instanz+"?"+id);
		};
		
		$('.navigationitem').each(function()
		{
			$(this).removeClass("active");
		});
		
		$('.navbar-default .active').each(function()
		{
			$(this).removeClass("active");
		});
		
		$(".teamspeakView").addClass("active");
		
		$("#mainContent").fadeOut("fast", function()
		{
			$("#mainContent").load('./php/teamspeak/web_teamspeak_serverview.php', function()
			{
				$("#mainContent").fadeIn("fast");
			});
		});
	};
	
/*
	Function Select Ticket
*/
	function ticketInit()
	{
		navigationInit("ticketMain", "web_ticket", "./php/ticket/");
	};
	
/*
	Function Logout
*/
	function ausloggenInit()
	{
		$(".preloader").fadeIn("slow", function()
		{
			if ('replaceState' in history)
			{
				history.replaceState(null, document.title, "index.php?web_main_main");
			};
			
			$("#mainContent").load("./php/login/logout.php", function()
			{
				sessionStorage.clear();
				parent.window.location.reload();
			});
		});
	};
	
/*
	Function Change Navigation
*/
	function navigationInit(activeClass, link, path)
	{
		if ('replaceState' in history)
		{
			history.replaceState(null, document.title, "index.php?"+link);
		};
		
		$('.navigationitem').each(function()
		{
			$(this).removeClass("active");
		});
		
		$('.navbar-default .active').each(function()
		{
			$(this).removeClass("active");
		});
		
		$("."+activeClass).addClass("active");
		
		$("#mainContent").fadeOut("fast", function()
		{
			$("#mainContent").load(path+link+'.php', function()
			{
				$("#mainContent").fadeIn("fast");
			});
		});
	};
	
/*
	Überprüfen ob Benutzer geblockt
*/
	function checkIfUserIsBlocked()
	{
		if(logged == "true")
		{
			$.ajax({
				type: "POST",
				url: "./php/functions/functionsSqlPost.php",
				data: {
					action:		'checkUserBlocked'
				},
				success: function(data){
					var informations		= 	JSON.parse(data);
					
					if(informations['blocked'] == 'true')
					{
						ausloggenInit();
					}
					else
					{
						if(checkClientInterval != -1)
						{
							setTimeout(checkIfPermissionModulIsBlocked, checkClientInterval);
						};
					};
				}
			});
		}
		else
		{
			if(checkClientInterval != -1)
			{
				checkIfPermissionModulIsBlocked();
			};
		};
	};
	
	function checkIfPermissionModulIsBlocked()
	{
		var webinterface	=	new Array();
		
		$.ajax({
			type: "POST",
			url: "./php/functions/functionsSqlPost.php",
			data: {
				action:		'getModuls'
			},
			success: function(data){
				var informations		= 	JSON.parse(data);
				webinterface			=	informations;
				
				if(logged == "true")
				{
					$.ajax({
						type: "POST",
						url: "./php/functions/functionsSqlPost.php",
						data: {
							action:		'refreshRights'
						},
						success: function(data) {
							informations			= 	JSON.parse(data);
							var link				=	String(window.location).split("?");
							
							if(webinterface['free_ts3_server_application'] == 'true')
							{
								$('.mainApplyForServer').fadeIn("slow");
							}
							else
							{
								$('.mainApplyForServer').fadeOut("slow");
								
								if(link[1].includes("web_main_apply_server"))
								{
									changeToMainMain();
								};
							};
							
							if(webinterface['masterserver'] == 'true')
							{
								$('.mainMasterserver').fadeIn("slow");
							}
							else
							{
								$('.mainMasterserver').fadeOut("slow");
								
								if(link[1].includes("web_main_masterserver"))
								{
									changeToMainMain();
								};
							};
							
							checkPermItem(typeof(informations['right_web']) != 'undefined' && webinterface['webinterface'] == 'true', "teamspeakServer", "web_teamspeak_server", link);
							checkPermItem(typeof(informations['right_web_server_create']) != 'undefined' && webinterface['webinterface'] == 'true', "teamspeakServerCreate", "web_teamspeak_server_create", link);
							checkPermItem(typeof(informations['right_web_server_create']) != 'undefined' && webinterface['webinterface'] == 'true', "teamspeakServerRequests", "web_teamspeak_server_requests", link);
							checkPermItem(typeof(informations['right_hp_main']) != 'undefined', "adminSettings", "web_admin_settings", link);
							checkPermItem(typeof(informations['right_hp_ts3']) != 'undefined', "adminInstanz", "web_admin_instanz", link);
							checkPermItem(typeof(informations['right_hp_user_create']) != 'undefined' || typeof(informations['right_hp_user_delete']) != 'undefined' || typeof(informations['right_hp_user_edit']) != 'undefined', "adminUser", "web_admin_user", link);
							checkPermItem(typeof(informations['right_hp_mails']) != 'undefined', "adminMail", "web_admin_mail", link);
							checkPermItem(typeof(informations['right_hp_logs']) != 'undefined', "adminLogs", "web_admin_logs", link);
							
							if(webinterface['webinterface'] != 'true' || (typeof(informations['right_web_server_create']) == 'undefined' && typeof(informations['right_web']) == 'undefined'))
							{
								$(".webinterfacearea").slideUp("slow");
								
								if(link[1].includes("web_teamspeak"))
								{
									profilDashboardInit();
								};
							}
							else
							{
								$(".webinterfacearea").slideDown("slow");
							};
							
							if(typeof(informations['right_hp_user_create']) == 'undefined' && typeof(informations['right_hp_user_delete']) == 'undefined' && typeof(informations['right_hp_user_edit']) == 'undefined'
								&& typeof(informations['right_hp_ts3']) == 'undefined' && typeof(informations['right_hp_main']) == 'undefined' && typeof(informations['right_hp_mails']) == 'undefined')
							{
								$(".settingsarea").slideUp("slow");
							}
							else
							{
								$(".settingsarea").slideDown("slow");
							};
							
							if(typeof(link[2]) != 'undefined' && typeof(link[3]) != 'undefined' && typeof(port) != "undefined" && typeof(informations['right_web_global_server']) == 'undefined')
							{
								if(typeof(informations['right_web_server_view']) == 'undefined')
								{
									goBackToMain();
								}
								else
								{
									if(!informations['right_web_server_view'][instanz].includes(port))
									{
										goBackToMain();
									};
									
									var permission									=	Array();
									permission["right_web_server_protokoll"]		=	false;
									permission["right_web_server_mass_actions"]		=	false;
									permission["right_web_server_icons"]			=	false;
									permission["right_web_server_clients"]			=	false;
									permission["right_web_server_bans"]				=	false;
									permission["right_web_server_token"]			=	false;
									permission["right_web_file_transfer"]			=	false;
									permission["right_web_server_backups"]			=	false;
									
									for(info in informations)
									{
										if(typeof(informations[info]) != "undefined")
										{
											if(typeof(informations[info][instanz]) != "undefined")
											{
												if(informations[info][instanz].includes(port))
												{
													permission[info]				=	true;
												};
											};
										};
									};
									
									changeClass(permission['right_web_server_protokoll'], "teamspeakProtokol", "teamspeakProtokolInit();", "web_teamspeak_serverprotokol", link[1]);
									changeClass(permission['right_web_server_mass_actions'], "teamspeakMassActions", "teamspeakMassActionsInit();", "web_teamspeak_servermassactions", link[1]);
									changeClass(permission['right_web_server_icons'], "teamspeakIcons", "teamspeakIconsInit();", "web_teamspeak_servericons", link[1]);
									changeClass(permission['right_web_server_clients'], "teamspeakClients", "teamspeakClientsInit();", "web_teamspeak_serverclients", link[1]);
									changeClass(permission['right_web_server_bans'], "teamspeakBans", "teamspeakBansInit();", "web_teamspeak_serverbans", link[1]);
									changeClass(permission['right_web_server_token'], "teamspeakToken", "teamspeakTokenInit();", "web_teamspeak_servertoken", link[1]);
									changeClass(permission['right_web_file_transfer'], "teamspeakFilelist", "teamspeakFilelistInit();", "web_teamspeak_serverfilelist", link[1]);
									changeClass(permission['right_web_server_backups'], "teamspeakBackup", "teamspeakBackupsInit();", "web_teamspeak_serverbackups", link[1]);
								};
							};
						}
					});
				}
				else
				{
					var link				=	String(window.location).split("?");
					
					if(webinterface['free_ts3_server_application'] == 'true')
					{
						$('.mainApplyForServer').fadeIn("slow");
					}
					else
					{
						$('.mainApplyForServer').fadeOut("slow");
						
						if(link[1].includes("web_main_apply_server"))
						{
							changeToMainMain();
						};
					};
					
					if(webinterface['masterserver'] == 'true')
					{
						$('.mainMasterserver').fadeIn("slow");
					}
					else
					{
						$('.mainMasterserver').fadeOut("slow");
						
						if(link[1].includes("web_main_masterserver"))
						{
							changeToMainMain();
						};
					};
				};
				
				setTimeout(checkIfPermissionModulIsBlocked, checkClientInterval);
			}
		});
	};
	
	function checkPermItem(hasPermission, classPermission, filePermission, link)
	{
		if(hasPermission)
		{
			if($('.'+classPermission).hasClass("text-danger"))
			{
				$('.'+classPermission).removeClass("text-danger");
				$('.'+classPermission).attr('onclick', classPermission+'Init();');
			};
		}
		else
		{
			if(!$('.'+classPermission).hasClass("text-danger"))
			{
				$('.'+classPermission).addClass("text-danger");
				$('.'+classPermission).attr('onclick','');
			};
			
			if(link[1].includes(filePermission))
			{
				profilDashboardInit();
			};
		};
	};
	
	function changeClass(add, changeClass, onClick, headerSite, link)
	{
		if(add)
		{
			if($('.'+changeClass).hasClass("text-danger"))
			{
				$('.'+changeClass).removeClass("text-danger");
				$('.'+changeClass).attr('onclick',onClick);
			};
		}
		else
		{
			if(!$('.'+changeClass).hasClass("text-danger"))
			{
				$('.'+changeClass).addClass("text-danger");
				$('.'+changeClass).attr('onclick',"");
			};
			
			if(link.includes(headerSite))
			{
				teamspeakViewInit();
			};
		};
	};
	
/*
	Function write News
*/
	function writeNews()
	{
		var title			=	escapeText($('#newsTitle').val()),
			subtitle		=	escapeText($('#newsSubTitle').val()),
			content			=	CKEDITOR.instances.editor.getData();
		
		$.ajax({
			type: "POST",
			url: "./php/functions/functionsPost.php",
			data: {
				action:		'createNews',
				title:		encodeURIComponent(title),
				subtitle:	encodeURIComponent(subtitle),
				content:	encodeURIComponent(content)
			},
			success: function(data){
				if(data == "done")
				{
					$('html').animate({scrollTop:0}, 'slow');	//IE, FF
					$('body').animate({scrollTop:0}, 'slow');	//chrome, don't know if Safari works
					
					changeToMainMain();
					setNotifySuccess(lang.news_created);
				}
				else
				{
					setNotifyFailed(lang.news_created_failed);
				};
			}
		});
	};
	
/*
	Delete News
*/
	function deleteNews(time)
	{
		$.ajax({
			type: "POST",
			url: "./php/functions/functionsPost.php",
			data: {
				action:		'deleteNews',
				file:		time+".json"
			},
			success: function(data){
				if(data == "done")
				{
					$('html').animate({scrollTop:0}, 'slow');	//IE, FF
					$('body').animate({scrollTop:0}, 'slow');	//chrome, don't know if Safari works
					
					$('#modalAreYouSure').modal('hide');
					$('#'+time).remove();
					
					setNotifySuccess(lang.news_deleted);
				}
				else
				{
					setNotifyFailed(lang.news_deleted_failed);
				};
			}
		});
	};
	
/*
	Are you sure?
	written for Benjamin Hoheisel
*/
	function AreYouSure(label, clickEvent)
	{
		if(document.getElementById("modalAreYouSure"))
		{
			$('#modalAreYouSureLabel').text(label);
			$('#areYouSureBttn').attr("onClick", clickEvent+";");
			
			$('#modalAreYouSure').modal('show');
		};
	};
	
/*
	Server Application
*/
	function checkWantServer()
	{
		if(typeof(wantServer['0']) == 'undefined')
		{
			var wantServerUser, wantServerPw;
			var regex_check_mail	=	true;
			var regex_check_pw		=	true;
			
			if($('#radioAccount').prop("checked"))
			{
				wantServerUser		=	$('#wantServerLoginUser').val();
				wantServerPw		=	'';
			}
			else
			{
				wantServerUser		=	$('#wantServerLoginCreateUser').val();
				wantServerPw		=	$('#wantServerLoginCreatePw').val();
			};
			
			var regex 				=	/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i;
			regex_check_mail		= 	regex.test(wantServerUser);
			
			if(!regex_check_mail)
			{
				setNotifyFailed(lang.change_user_failed);
			};
			
			if(!$('#radioAccount').prop("checked"))
			{
				var regex 				=	/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{4,100}$/;
				regex_check_pw			= 	regex.test(wantServerPw);
				
				if(!regex_check_pw)
				{
					setNotifyFailed(lang.change_pw1_failed);
				};
			};
			
			if(regex_check_pw && regex_check_mail)
			{
				$.ajax({
					type: "POST",
					url: "./php/functions/functionsSqlPost.php",
					data: {
						action	:	'checkUser',
						name	:	wantServerUser
					},
					success: function(data){
						if($('#radioAccount').prop("checked") && data == 'error' || !$('#radioAccount').prop("checked") && data == 'done')
						{
							wantServer['0']			=	wantServerUser;
							wantServer['1']			=	wantServerPw;
							
							$('#wantServerStep1').slideUp("slow", function ()
							{
								$('#wantServerStep2').slideDown("slow");
							});
						}
						else if($('#radioAccount').prop("checked") && data == 'done')
						{
							setNotifyFailed(lang.user_does_not_exist);
						}
						else
						{
							setNotifyFailed(lang.user_already_exists);
						};
					}
				});
			};
		}
		else if(typeof(wantServer['4']) == 'undefined')
		{
			wantServerValue					=	2;
			checkValues						=	true;
			
			if(!setWantServerValues(wantServerValue++, "serverCreateCause")) { checkValues = false; };
			if(!setWantServerValues(wantServerValue++, "serverCreateWhy")) { checkValues = false; };
			if(!setWantServerValues(wantServerValue++, "serverCreateNeededSlots", true)) { checkValues = false; };
			
			if(!checkValues)
			{
				setNotifyFailed(lang.field_cant_be_empty);
			}
			else
			{
				$('#wantServerStep2').slideUp("slow", function ()
				{
					$('#wantServerStep3').slideDown("slow");
				});
			};
		}
		else
		{
			var port		=	$('#serverCreatePort').val();
			var port_regex	=	 /^[0-9]{4}$/;
			var port_check	= 	port_regex.test(port);
			
			if(!port_check)
			{
				setNotifyFailed(lang.port_cant_be_used);
			}
			else if($('#serverCreateServername').val() == "")
			{
				setNotifyFailed(lang.servername_needed);
			}
			else
			{
				$('#wantServerBttn').prop("disabled", true);
				
				wantServerValue					=	5;
				checkValues						=	true;
				
				setWantServerValues(wantServerValue++, "serverCreateServername", false, "servername");			// Servername wird gespeichert
				setWantServerValues(wantServerValue++, "serverCreatePort", false, "");							// Der Teamspeakport wird gespeichert
				setWantServerValues(wantServerValue++, "serverCreateSlots", true, "slots");						// Die Max. Teamspeakclients werden gespeichert
				setWantServerValues(wantServerValue++, "serverCreateReservedSlots", true, "reserved_slots");	// Anzahl reservierter Slots wird gespeichert
				setWantServerValues(wantServerValue++, "serverCreatePassword", false, "password");				// Das Teamspeakpassword wird gespeichert
				setWantServerValues(wantServerValue++, "serverCreateWelcomeMessage", false, "welcome_message");	// Die Willkommensnachricht wird gespeichert
				
				wantServer['11'] 				= 	'../../files/wantServer/';
				
				var jsonString = JSON.stringify(wantServer);
				$.ajax({
					url: "./php/functions/funtionsCreateServerRequest.php",
					type: "post",
					data: {
						wantServerPost: jsonString
					},
					success: function(data){
						if(data == 'done')
						{
							setNotifySuccess(lang.server_request_requested);
						}
						else
						{
							setNotifyFailed(data);
							$('#wantServerBttn').prop("disabled", false);
						};
					}
				});
			};
		};
	};
	
	function setWantServerValues(arrayNumber, id, isNumber = false, defaultValue = "")
	{
		if(defaultValue == "")
		{
			if ($('#'+id).val() != '' && (isNumber && $('#'+id).val() > 0) || !isNumber)
			{
				wantServer[arrayNumber] 			= 		$('#'+id).val();
				return true;
			}
			else
			{
				return false;
			};
		}
		else
		{
			if ($('#'+id).val() != '' && (isNumber && $('#'+id).val() > 0) || !isNumber)
			{
				wantServer[arrayNumber] 			= 		$('#'+id).val();
			}
			else
			{
				wantServer[arrayNumber] 			= 		ts3_server_create_default[defaultValue];
			};
		};
	};