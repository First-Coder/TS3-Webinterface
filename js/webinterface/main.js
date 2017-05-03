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
					case "web_teamspeak_server":
						teamspeakServerInit();
						break;
					case "web_teamspeak_server_create":
						teamspeakServerCreateInit();
						break;
					case "web_teamspeak_server_requests":
						teamspeakServerRequestsInit();
						break;
					case "web_ticket_user":
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
		
		$('#mainContent').fadeOut("slow", function()
		{
			$('#mainContent').load("web_main_main.php", function()
			{
				$('#mainContent').fadeIn("slow");
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
		
		$('#mainContent').fadeOut("slow", function()
		{
			$('#mainContent').load("web_main_apply_server.php", function()
			{
				if(typeof(wantServer['0']) != 'undefined')
				{
					$('#wantServerStep1').remove();
					$('#wantServerStep2').show();
				};
				$('#mainContent').fadeIn("slow");
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
		
		$('#mainContent').fadeOut("slow", function()
		{
			$('#mainContent').load("web_main_masterserver.php", function()
			{
				$('#mainContent').fadeIn("slow");
			});
		});
	};

/*
	Slide something
*/
	function slideMe(myId, id)
	{
		var object 	= 	$("#"+id);
		if($('#'+myId).hasClass("toggleArrow"))
		{
			object.slideDown("slow");
		}
		else
		{
			object.slideUp("slow");
		};
		
	};
	
/*
	Function Select Profil Dashboard
*/
	function profilDashboardInit()
	{
		navigationInit("profilDashboard", "web_profil_dashboard");
	};

/*
	Function Select Profil Edit
*/
	function profilEditInit()
	{
		navigationInit("profilEdit", "web_profil_edit");
	};
	
/*
	Function Select Profil Permissions
*/
	function profilPermissionInit()
	{
		navigationInit("profilPermission", "web_profil_rights");
	};
	
/*
	Function Select Admin settings
*/
	function adminSettingsInit()
	{
		navigationInit("adminSettings", "web_admin_settings");
	};
	
/*
	Function Select Admin instanz
*/
	function adminInstanzInit()
	{
		navigationInit("adminInstanz", "web_admin_instanz");
	};
	
/*
	Function Select Admin users
*/
	function adminUserInit()
	{
		navigationInit("adminUser", "web_admin_user");
	};
	
/*
	Function Select Admin users
*/
	function adminMailInit()
	{
		navigationInit("adminMail", "web_admin_mail");
	};
	
/*
	Function Select Teamspeak Server
*/
	function teamspeakServerInit()
	{
		navigationInit("teamspeakServer", "web_teamspeak_server");
	};
	
/*
	Function Select Teamspeak Server Create
*/
	function teamspeakServerCreateInit()
	{
		navigationInit("teamspeakServerCreate", "web_teamspeak_server_create");
	};
	
/*
	Function Select Teamspeak Server Requests
*/
	function teamspeakServerRequestsInit()
	{
		navigationInit("teamspeakServerRequests", "web_teamspeak_server_requests");
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
		
		$("#mainContent").fadeOut("slow", function()
		{
			$("#mainContent").load('web_teamspeak_serverview.php', function()
			{
				$("#mainContent").fadeIn("slow");
			});
		});
	};
	
/*
	Function Select Ticket
*/
	function ticketInit()
	{
		navigationInit("ticketMain", "web_ticket_user");
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
			
			$("#mainContent").load("logout.php", function()
			{
				sessionStorage.clear();
				parent.window.location.reload();
			});
		});
	};
	
/*
	Function Change Navigation
*/
	function navigationInit(activeClass, link)
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
		
		$("#mainContent").fadeOut("slow", function()
		{
			$("#mainContent").load(link+'.php', function()
			{
				$("#mainContent").fadeIn("slow");
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
			var dataString		=	'action=checkUserBlocked&id='+sessionID;
			$.ajax({
				type: "POST",
				url: "functionsPost.php",
				data: dataString,
				cache: true,
				async: false,
				success: function(data){
					var informations		= 	JSON.parse(data);
					
					if(informations['blocked'] == 'true')
					{
						ausloggenInit();
						return;
					};
				}
			});
		};
		
		setTimeout(checkIfPermissionModulIsBlocked, 5000);
	};
	
	function checkIfPermissionModulIsBlocked()
	{
		var webinterface	=	new Array();
		
		var dataString		=	'action=getModuls';
		$.ajax({
			type: "POST",
			url: "functionsPost.php",
			data: dataString,
			cache: true,
			async: false,
			success: function(data){
				var informations		= 	JSON.parse(data);
				
				webinterface			=	informations;
			}
		});
		
		if(logged == "true")
		{
			dataString 			= 	'action=refreshRights&id='+sessionID;
			$.ajax({
				type: "POST",
				url: "functionsPost.php",
				data: dataString,
				cache: true,
				async: false,
				success: function(data){
					var informations		= 	JSON.parse(data);
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
						if(typeof(informations['ports']['right_web_server_view']) == 'undefined')
						{
							goBackToMain();
						}
						else
						{
							if(!informations['ports']['right_web_server_view'][instanz].includes(port))
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
							
							for(info in informations['ports'])
							{
								if(typeof(informations['ports'][info]) != "undefined")
								{
									if(typeof(informations['ports'][info][instanz]) != "undefined")
									{
										if(informations['ports'][info][instanz].includes(port))
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
		
		setTimeout(checkIfUserIsBlocked, 5000);
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
		var title			=	$('#newsTitle').val();
		var subtitle		=	$('#newsSubTitle').val();
		var content			=	document.getElementById("editor").innerHTML;
		
		var dataString 		= 	'action=createNews&title='+title+'&subtitle='+subtitle+'&content='+content;
		$.ajax({
			type: "POST",
			url: "functionsPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data){
				if(data == "done")
				{
					$('html').animate({scrollTop:0}, 'slow');	//IE, FF
					$('body').animate({scrollTop:0}, 'slow');	//chrome, don't know if Safari works
					
					changeToMainMain();
					
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: news_created,
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
						message: "News could not be created :/!",
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
	Delete News
*/
	function deleteNews(time)
	{
		var dataString 		= 	'action=deleteNews&file='+time+".json";
		$.ajax({
			type: "POST",
			url: "functionsPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data){
				if(data == "done")
				{
					$('html').animate({scrollTop:0}, 'slow');	//IE, FF
					$('body').animate({scrollTop:0}, 'slow');	//chrome, don't know if Safari works
					
					$('#'+time).remove();
					
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: news_deleted,
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
						message: "News could not be deleted :/!",
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
	Are you sure?
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
			
			// Benutzer pruefen
			var regex 				=	/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i;
			regex_check_mail		= 	regex.test(wantServerUser);
			
			if(!regex_check_mail)
			{
				$.notify({
					title: '<strong>'+failed+'</strong><br />',
					message: hp_user_change_user_failed,
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
			
			// Password pruefen
			if(!$('#radioAccount').prop("checked"))
			{
				var regex 				=	/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{4,100}$/;
				regex_check_pw			= 	regex.test(wantServerPw);
				
				if(!regex_check_pw)
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: hp_user_change_pw1_failed,
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
			
			if(regex_check_pw && regex_check_mail)
			{
				var dataString 		= 	'action=checkUser&name='+wantServerUser;
					
				$.ajax({
					type: "POST",
					url: "functionsPost.php",
					data: dataString,
					cache: true,
					async: true,
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
							$.notify({
								title: '<strong>'+failed+'</strong><br />',
								message: "User does not exist!",
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
								message: "User already exist!",
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
				$.notify({
					title: '<strong>'+failed+'</strong><br />',
					message: "Bitte alle Felder ausf&uuml;llen!",
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
				$.notify({
					title: '<strong>'+failed+'</strong><br />',
					message: ts_server_create_wrong_port,
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
			else if($('#serverCreateServername').val() == "")
			{
				$.notify({
					title: '<strong>'+failed+'</strong><br />',
					message: "Servername muss gesetzt sein!",
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
				$('#wantServerBttn').prop("disabled", true);
				
				wantServerValue					=	5;
				checkValues						=	true;
				
				setWantServerValues(wantServerValue++, "serverCreateServername", false, "servername");			// Servername wird gespeichert
				setWantServerValues(wantServerValue++, "serverCreatePort", false, "");							// Der Teamspeakport wird gespeichert
				setWantServerValues(wantServerValue++, "serverCreateSlots", true, "slots");						// Die Max. Teamspeakclients werden gespeichert
				setWantServerValues(wantServerValue++, "serverCreateReservedSlots", true, "reserved_slots");	// Anzahl reservierter Slots wird gespeichert
				setWantServerValues(wantServerValue++, "serverCreatePassword", false, "password");				// Das Teamspeakpassword wird gespeichert
				setWantServerValues(wantServerValue++, "serverCreateWelcomeMessage", false, "welcome_message");	// Die Willkommensnachricht wird gespeichert
				
				wantServer['11'] 				= 	'wantServer/';
				
				var jsonString = JSON.stringify(wantServer);
				$.ajax({
					url: "create_file_post.php",
					type: "post",
					data: {wantServerPost : jsonString},
					success: function(data){
						if(data == 'done')
						{
							$.notify({
								title: '<strong>'+success+'</strong><br />',
								message: server_request_success,
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