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
			$("#mainContent").load(link+'.php', function()
			{
				$("#mainContent").fadeIn("fast");
			});
		});
	};
	
/*
	web_teamspeakbackups: slide
*/
	function slideBackups(direction)
	{
		if(direction == "up")
		{
			$('#slideBackups').slideUp("slow");
		}
		else
		{
			$('#slideBackups').slideDown("slow");
		};
	};
	

/*
	Instanz Message / Poke
*/
	function instanzMessagePoke()
	{
		var instanz						=	$("input[name='instanzMsgPoke']:checked").val();
		var message 					=	$('#instanzMessagePokeContent').val();
		var mode						=	$('#instanzMode').hasClass("active");
		
		if(message != '')
		{
			var dataString 				= 	'action=instanzMsgPoke&message='+message+'&mode='+mode+'&instanz='+instanz;
			$.ajax({
				type: "POST",
				url: "functionsTeamspeakPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data)
				{
					if(data == "done")
					{
						$.notify({
							title: '<strong>'+success+'</strong><br />',
							message: ts_msg_poke_done,
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
		};
	};
	
/*
	Server Message / Poke
*/
	function serverMessage(id, instanz, serverPort)
	{
		var message 	=	$('#serverMessageContent_'+instanz+"_"+id).val();
		
		if(message != '')
		{
			var dataString = 'action=serverMessage&instanz='+instanz+'&port='+serverPort+'&mode=3&message='+message+'&serverid='+id;
			$.ajax({
				type: "POST",
				url: "functionsTeamspeakPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data)
				{
					if(data == "done")
					{
						$.notify({
							title: '<strong>'+success+'</strong><br />',
							message: ts_msg_done,
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
		};
	};
	
	function serverPoke(id, instanz, serverPort)
	{
		var message 	=	$('#serverMessageContent_'+instanz+"_"+id).val();
		
		if(message != '')
		{
			var dataString = 'action=serverPoke&instanz='+instanz+'&port='+serverPort+'&message='+message+'&serverid='+id;
			$.ajax({
				type: "POST",
				url: "functionsTeamspeakPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data)
				{
					if(data == "done")
					{
						$.notify({
							title: '<strong>'+success+'</strong><br />',
							message: ts_poke_done,
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
		};
	};

/*
	Server Start / Stop
*/
	function toggleStartStopTeamspeakserver(id, instanz, serverPort)
	{
		if(tsStatus == 'online')
		{
			stopTeamspeakserver(id, instanz, serverPort);
			tsStatus = "offline";
		}
		else
		{
			startTeamspeakserver(id, instanz, serverPort);
			tsStatus = "online";
		};
	};
	
	function stopTeamspeakserver(id, instanz, serverPort)
	{
		var dataString = 'action=serverStop&instanz='+instanz+'&port='+serverPort+'&serverid='+id;
		$.ajax({
			type: "POST",
			url: "functionsTeamspeakPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data)
			{
				if(data == 'done')
				{
					// Serverstatus ändern
					if(document.getElementById("clientsonline_"+instanz+"_"+id))
					{
						document.getElementById("clientsonline_"+instanz+"_"+id).innerHTML	=	'<strong>-</strong>';
						document.getElementById("onlinesince_"+instanz+"_"+id).innerHTML	=	'<strong>-</strong>';
						document.getElementById("queryclients_"+instanz+"_"+id).innerHTML	=	'<strong>-</strong>';
						document.getElementById("status_"+instanz+"_"+id).innerHTML			=	'<strong style="color:red;">Offline</strong>';
						
						$("#serverMessage_"+instanz+"_"+id).prop("disabled", true);
						$("#serverPoke_"+instanz+"_"+id).prop("disabled", true);
					};
					
					// Dashboard
					if(document.getElementById("clients-"+instanz+"-"+id))
					{
						document.getElementById("clients-"+instanz+"-"+id).innerHTML		=	'-';
						document.getElementById("status-"+instanz+"-"+id).innerHTML			=	'offline';
						
						$('#status-'+instanz+'-'+id).removeClass("text-success");
						$('#status-'+instanz+'-'+id).addClass("text-danger");
					};
					
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: ts_server_stoped,
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
	};
	
	function startTeamspeakserver(id, instanz, serverPort)
	{
		var dataString = 'action=serverStart&instanz='+instanz+'&port='+serverPort+'&serverid='+id;
		$.ajax({
			type: "POST",
			url: "functionsTeamspeakPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data)
			{
				if(data == 'done')
				{
					if(document.getElementById("clientsonline_"+instanz+"_"+id) || document.getElementById("clients-"+instanz+"-"+id))
					{
						var dataString = 'action=serverInfo&instanz='+instanz+'&port='+serverPort+'&serverid='+id;
						$.ajax({
							type: "POST",
							url: "functionsTeamspeakPost.php",
							data: dataString,
							cache: true,
							async: true,
							success: function(data)
							{
								var informations 	=	JSON.parse(data);
								
								// Serverstatus ändern
								if(document.getElementById("clientsonline_"+instanz+"_"+id))
								{
									document.getElementById("clientsonline_"+instanz+"_"+id).innerHTML	=	'<strong>'+(informations['data']['virtualserver_clientsonline'] - informations['data']['virtualserver_query_client_connections'])+'&nbsp;/&nbsp;'+informations['data']['virtualserver_maxclients']+'</strong>';
									document.getElementById("onlinesince_"+instanz+"_"+id).innerHTML	=	'<strong>0d 0h 0m 0s</strong>';
									document.getElementById("queryclients_"+instanz+"_"+id).innerHTML	=	'<strong>'+informations['data']['virtualserver_query_client_connections']+'</strong>';
									document.getElementById("status_"+instanz+"_"+id).innerHTML			=	'<strong style="color:green;">Online</strong>';
								};
								
								// Dashboard
								if(document.getElementById("clients-"+instanz+"-"+id))
								{
									document.getElementById("clients-"+instanz+"-"+id).innerHTML		=	(informations['data']['virtualserver_clientsonline'] - informations['data']['virtualserver_query_client_connections'])+'&nbsp;/&nbsp;'+informations['data']['virtualserver_maxclients'];
									document.getElementById("status-"+instanz+"-"+id).innerHTML			=	'online';
									
									$('#status-'+instanz+'-'+id).removeClass("text-danger");
									$('#status-'+instanz+'-'+id).addClass("text-success");
								};
							}
						});
						
						if(document.getElementById("serverMessage_"+instanz+"_"+id))
						{
							$("#serverMessage_"+instanz+"_"+id).prop("disabled", false);
							$("#serverPoke_"+instanz+"_"+id).prop("disabled", false);
						};
					};
					
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: ts_server_started,
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
	};
	
/*
	Delete a Teamspeakserver
*/
	function deleteTeamspeakserver(serverId, instanz, port)
	{
		var dataString = 'action=serverDelete&serverid='+serverId+'&instanz='+instanz+'&port='+port;
		$.ajax({
			type: "POST",
			url: "functionsTeamspeakPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data)
			{
				if(data == 'done')
				{
					$('#serverbox_'+instanz+'_'+port).remove();
					$('#modalAreYouSure').modal('hide');
					
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: ts_server_deleted,
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
	};
	
/*
	Go back to Main menu
*/
	function goBackToMain()
	{
		if ('replaceState' in history)
		{
			history.replaceState(null, document.title, "index.php?web_teamspeak_server");
		};
		
		$(".preloader").fadeIn("slow", function()
		{
			$("#hp").load("web_main.php");
		});
	};

/*
	Show Teamspeakinformations
*/
	function showTeamspeakserver(id, instanz)
	{
		if ('replaceState' in history)
		{
			history.replaceState(null, document.title, "index.php?web_teamspeak_serverview?"+instanz+"?"+id);
		};
		
		$(".preloader").fadeIn("slow", function()
		{
			$("#hp").load("web_main.php?temp?"+instanz+"?"+id);
		});
	};

/*
	Serverview: Show / Hide Table stuff
*/
	function showHideTableStuff(name)
	{
		$('#slideToggle'+name).slideToggle("slow");
		if($('#icon'+name).hasClass('fa-arrow-down'))
		{
			$('#icon'+name).removeClass('fa-arrow-down');
			$('#icon'+name).addClass('fa-arrow-left');
		}
		else
		{
			$('#icon'+name).removeClass('fa-arrow-left');
			$('#icon'+name).addClass('fa-arrow-down');
		};
	};

/*
	Serverview: Server Edit
*/
	function serverEdit(right, newValue, instanz, serverId, serverPort)
	{
		var dataString 				= 	'action=serverEdit&instanz='+instanz+'&port='+serverPort+'&right='+right+'&value='+newValue+'&instanz='+instanz+'&serverid='+serverId;
		$.ajax({
			type: "POST",
			url: "functionsTeamspeakPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data)
			{
				if(data == "done")
				{
					// Zurück zu Hauptseite springen
					if(right == 'virtualserver_port')
					{
						var infoMessage		=	ts_server_edit+'<br/>'+ts_server_edit_port;
						
						goBackToMain();
					}
					else
					{
						var infoMessage		=	ts_server_edit;
					}
					
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: infoMessage,
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
	};
	
/*
	Serverview: Create Channel
*/
	function createChannel()
	{
		// Informationen abfragen
		var ts3_information			= 	new Array();
		
		// Server wird gespeichert
		ts3_information['0']									=		instanz;
		
		// ServerIP
		/*ts3_information['1']									=		ts3_server[instanz]['ip'];
		
		// ServerQueryPort
		ts3_information['2']									=		ts3_server[instanz]['queryport'];
		
		// ServerBenutzer
		ts3_information['3']									=		ts3_server[instanz]['user'];
		
		// ServerPassword
		ts3_information['4']									=		ts3_server[instanz]['pw'];*/
		
		// ServerPort
		ts3_information['5']									=		port;
		
		// Channelposition
		ts3_information['6']									=		$('#cpid').val();
		
		// Channelname
		ts3_information['7']									=		$('#channel_name').val();
		
		// ChannelTopic
		ts3_information['8']									=		$('#channel_topic').val();
		
		// ChannelBeschreibung
		ts3_information['9']									=		$('#channel_description').val();
		
		// ChannelCodec
		ts3_information['10']									=		$('#channel_codec').val();
		
		// ChannelQualitaet
		ts3_information['11']									=		$('#channel_codec_quality').val();
		
		// MaximaleClienten
		ts3_information['12']									=		$('#channel_maxclients').val();
		
		// MaximaleClientenPerFamilie
		ts3_information['13']									=		$('#channel_maxfamilyclients').val();
		
		// ChannelTyp
		ts3_information['14']									=		$('#channel_typ').val();
		
		// MaximaleClientenGeerbt
		ts3_information['15']									=		$('#channel_flag_maxfamilyclients_inherited').val();
		
		// ChannelTalkPower
		ts3_information['16']									=		$('#channel_needed_talk_power').val();
		
		// PhonetischerName
		ts3_information['17']									=		$('#channel_name_phonetic').val();
		
		var jsonString = JSON.stringify(ts3_information);
		$.ajax({
			url: "ts3_create_channel_post.php",
			type: "post",
			data: {TS3_Information : jsonString},
			success: function(data){
				if(data == 'done')
				{
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: ts_server_created,
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
					
					setTimeout(teamspeakViewInit, 500);
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
	Serverview: Token: Selectmenu Change
*/
	function changeTokenSelectmenu()
	{
		var kindOfGroup		=	$('#tokenChooseKindOfGroup').val();
		
		$('#tokenChooseChannel').prop('disabled', function(i, v)
		{
			return !v;
		});
		
		if($('#tokenChooseChannel').hasClass("input-danger"))
		{
			$('#tokenChooseChannel').removeClass("input-danger");
		};
		
		if(kindOfGroup == 0)
		{
			$("#tokenChooseGroup").html(sgroup);
		}
		else
		{
			$("#tokenChooseGroup").html(cgroup);
		};
	};
	
/*
	Serverview: Token: Delete Token
*/
	function deleteToken(token, serverPort)
	{
		var dataString 	= 'action=deleteToken&token='+encodeURIComponent(token)+'&serverid='+serverId+'&instanz='+instanz+'&port='+serverPort;
		$.ajax({
			type: "POST",
			url: "functionsTeamspeakPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data)
			{
				if(data == "done")
				{
					$('#'+token.replace("+", "")).remove();
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
	Serverview: Token: Token Create
*/
	function createToken(serverPort)
	{
		var failState		=	false;
		var tokenAnzahl		=	$('#tokenChooseAnzahl').val();
		var tokenDesc		=	$('#tokenChooseDescription').val();
		var tokenKindGroup	=	$('#tokenChooseKindOfGroup').val();
		var tokenGroup		=	$('#tokenChooseGroup').val();
		var tokenChannel	=	0;
		if(tokenKindGroup == 1)
		{
			tokenChannel	=	$('#tokenChooseChannel').val();
		};
		
		if(tokenAnzahl == '' || tokenAnzahl <= 0)
		{
			failState		=	true;
			$('#tokenChooseAnzahl').addClass("input-danger");
		}
		else
		{
			if($('#tokenChooseAnzahl').hasClass("input-danger"))
			{
				$('#tokenChooseAnzahl').removeClass("input-danger");
			};
		};
		
		if(tokenKindGroup == 1 && tokenChannel == '')
		{
			failState		=	true;
			$('#tokenChooseChannel').addClass("input-danger");
		}
		else
		{
			if($('#tokenChooseChannel').hasClass("input-danger"))
			{
				$('#tokenChooseChannel').removeClass("input-danger");
			};
		};
		
		if(!failState)
		{
			var value_res 	= 	tokenGroup.split("-");	// value_res[3]
			var dataString 	= 'action=addToken&type='+tokenKindGroup+'&tokenid1='+value_res[3]+'&tokenid2='+tokenChannel+'&description='+tokenDesc+'&number='+tokenAnzahl;
			dataString		+=	'&serverid='+serverId+'&instanz='+instanz+'&port='+serverPort;
			$.ajax({
				type: "POST",
				url: "functionsTeamspeakPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data)
				{
					var informations 	=	JSON.parse(data);
					
					if(informations['done'] == 'true')
					{
						for(var k in informations)
						{
							if(k != 'done')
							{
								if(document.getElementById('noToken'))
								{
									$('#noToken').remove();
								};
								
								
								var table 			= 	document.getElementById("channelTokenTable");

								var row 			= 	table.insertRow(0);
								row.id 				= 	informations[k]['token'].replace("+", "");
								
								var dataString		=	'<div class="hover">';
								dataString			+=		'<span class="tokenlist-headline">';
								dataString			+=			token_type;
								dataString			+=		'</span>';
								dataString			+=		'<span class="tokenlist-subline">';
								dataString			+=			informations[k]['type'];
								dataString			+=		'</span>';
								dataString			+=	'</div>';
								dataString			+=	'<div class="hover">';
								dataString			+=		'<span class="tokenlist-headline">';
								dataString			+=			token_groupname;
								dataString			+=		'</span>';
								dataString			+=		'<span class="tokenlist-subline">';
								dataString			+=			$('#tokenChooseGroup :selected').text();
								dataString			+=		'</span>';
								dataString			+=	'</div>';
								dataString			+=	'<div class="hover">';
								dataString			+=		'<span class="tokenlist-headline">';
								dataString			+=			ts_channel;
								dataString			+=		'</span>';
								dataString			+=		'<span class="tokenlist-subline">';
								dataString			+=			(tokenChannel != 0) ? $('#tokenChooseChannel :selected').text() : "-";
								dataString			+=		'</span>';
								dataString			+=	'</div>';
								dataString			+=	'<div class="hover">';
								dataString			+=		'<span class="tokenlist-headline">';
								dataString			+=			ts3_create_on;
								dataString			+=		'</span>';
								dataString			+=		'<span class="tokenlist-subline">';
								dataString			+=			'Now';
								dataString			+=		'</span>';
								dataString			+=	'</div>';
								dataString			+=	'<div class="hover">';
								dataString			+=		'<span class="tokenlist-headline">';
								dataString			+=			'Token';
								dataString			+=		'</span>';
								dataString			+=		'<span class="tokenlist-subline">';
								dataString			+=			informations[k]['token'];
								dataString			+=		'</span>';
								dataString			+=	'</div>';
								dataString			+=	'<div class="hover">';
								dataString			+=		'<span class="tokenlist-headline">';
								dataString			+=			description;
								dataString			+=		'</span>';
								dataString			+=		'<span class="tokenlist-subline">';
								dataString			+=			informations[k]['description'];
								dataString			+=		'</span>';
								dataString			+=	'</div>';
								dataString			+=	'<div class="hover">';
								dataString			+=		'<span class="tokenlist-headline">';
								dataString			+=			actions;
								dataString			+=		'</span>';
								dataString			+=		'<span class="tokenlist-subline">';
								dataString			+=			'<i onClick="deleteToken(\''+informations[k]['token']+'\', \''+serverPort+'\')" class="fa fa-fw fa-trash"></i>';
								dataString			+=		'</span>';
								dataString			+=	'</div>';
								var cell1 			= 	row.insertCell(0);
								cell1.innerHTML 	= 	dataString;
							};
						};
					}
					else
					{
						$.notify({
							title: '<strong>'+failed+'</strong><br />',
							message: informations['error'],
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
	Serverview: Backup: Reset Server
*/
	function resetServer(port, instanz)
	{
		var dataString			=	'action=resetServer&instanz='+instanz+'&port='+port;
		
		$.ajax({
			type: "POST",
			url: "functionsTeamspeakPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data)
			{
				if(data == "")
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: "There was something wrong in the reset progress :/",
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
						title: '<strong>'+success+'</strong><br />',
						message: reset_server_success+"<br />Server Token:<i> "+data+"</i>",
						icon: 'fa fa-check'
					},{
						type: 'info',
						allow_dismiss: true,
						delay: 0,
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
	Serverview: Backup: Create Channelbackup
*/
	function createBackup(port, instanz)
	{
		var backupKind			=	$('#backupChannel').prop("checked");
		var backupChannelKind	=	$('#backupChannelName').prop("checked");
		var dataString			=	'action=createBackup&instanz='+instanz+'&port='+port;
		
		if(backupKind)
		{
			dataString			+=	"&kind=channel";
			if(backupChannelKind)
			{
				dataString			+=	"&kindChannel=name";
			}
			else
			{
				dataString			+=	"&kindChannel=all";
			};
		}
		else
		{
			dataString			+=	"&kind=server";
		};
		
		$.ajax({
			type: "POST",
			url: "functionsTeamspeakPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data)
			{
				if(data == "false" || data == "")
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: "Backup fehlgeschlafen! Vielleicht hat der Ordner \"/backup\" keine 0777 Rechte!",
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
					if(document.getElementById(data.replace(".json", "")) == null)
					{
						if(backupKind)
						{
							var table 			= 	document.getElementById("channelBackupTable");
							
							var row 			= 	table.insertRow(0);
							row.id 				= 	data.replace(".json", "");
							
							var dataString		=	"<a href=\"#\"><i style=\"text-align:center;width:33%;\" class=\"fa fa-check\"></i></a>";
							dataString			+=	"<a download href=\"./backup/channel/"+data+"\"><i style=\"text-align:center;width:33%;\" class=\"fa fa-download\"></i></a>";
							dataString			+=	"<a onClick=\"deleteBackup('"+data+"', 'channel')\" href=\"#\"><i style=\"text-align:center;width:33%;\" class=\"fa fa-trash\"></i></a>";
							
							var cell1 			= 	row.insertCell(0);
							var cell2 			= 	row.insertCell(1);
							
							cell1.innerHTML 	= 	data;
							cell2.innerHTML 	= 	dataString;
						}
						else
						{
							var table 			= 	document.getElementById("serverBackupTable");
							
							var row 			= 	table.insertRow(0);
							row.id 				= 	data.replace(".json", "");
							
							var dataString		=	"<a href=\"#\"><i style=\"text-align:center;width:33%;\" class=\"fa fa-check\"></i></a>";
							dataString			+=	"<a download href=\"./backup/server/"+data+"\"><i style=\"text-align:center;width:33%;\" class=\"fa fa-download\"></i></a>";
							dataString			+=	"<a onClick=\"deleteBackup('"+data+"', 'server')\" href=\"#\"><i style=\"text-align:center;width:33%;\" class=\"fa fa-trash\"></i></a>";
							
							var cell1 			= 	row.insertCell(0);
							var cell2 			= 	row.insertCell(1);
							
							cell1.innerHTML 	= 	data;
							cell2.innerHTML 	= 	dataString;
						};
					};
				};
			}
		});
	};
	
	/*function createBackupChannel(port, instanz)
	{
		var dataString 		= 	'action=getTeamspeakChannels&instanz='+instanz+'&port='+port;
		$.ajax({
			type: "POST",
			url: "functionsTeamspeakPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data)
			{
				var informations 	=	JSON.parse(data);
				
				channels			=	new Array();
				channels[0]			=	'write_teamspeak_channel_backup';
				channels[1]			=	port;
				channels[2]			=	instanz;
				for(var k in informations)
				{
					channels[parseInt(k)+3]		=	informations[k]['channel_name'];
				}
				
				var jsonString = JSON.stringify(channels);
				$.ajax({
					url: "ts3_create_backup_post.php",
					type: "post",
					data: {TS3_Information : jsonString},
					success: function(data){
						if(data != '' && document.getElementById(data.replace(".txt", "")) == null)
						{
							var table 			= 	document.getElementById("channelBackupTable");

							var row 			= 	table.insertRow(0);
							row.id 				= 	data.replace(".txt", "");
							
							var dataString		=	"<a href=\"#\"><i style=\"text-align:center;width:33%;\" class=\"fa fa-check\"></i></a>";
							dataString			+=	"<a download href=\"./backup/channel/"+data+"\"><i style=\"text-align:center;width:33%;\" class=\"fa fa-download\"></i></a>";
							dataString			+=	"<a onClick=\"deleteBackupChannel('"+data+"')\" href=\"#\"><i style=\"text-align:center;width:33%;\" class=\"fa fa-trash\"></i></a>";
							var cell1 			= 	row.insertCell(0);
							var cell2 			= 	row.insertCell(1);
							cell1.innerHTML 	= 	data;
							cell2.innerHTML 	= 	dataString;
						};
					}
				});
			}
		});
	};*/
	
/*
	Serverview: Backup: Delete Channelbackup
*/
	function deleteBackup(path, kind)
	{
		infos			=	new Array();
		infos[0]		=	'delete_backup';
		infos[1]		=	'./backup/'+kind+'/'+path;
		
		var jsonString 	= 	JSON.stringify(infos);
		$.ajax({
			url: "ts3_create_backup_post.php",
			type: "post",
			data: {TS3_Information : jsonString},
			success: function(data){
				if(data == "done")
				{
					$('#'+path.replace(".json", "")).remove();
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
	Serverview: Backup: Activate Channelbackup
*/
	function activateBackupChannel(path, info_0)
	{
		// Info
		alert("This can take a while.... Dont get panic ;)");
		
		infos			=	new Array();
		infos[0]		=	info_0;
		infos[1]		=	'backup/channel/'+path;
		infos[2]		=	port;
		infos[3]		=	instanz;
		
		var jsonString 	= 	JSON.stringify(infos);
		$.ajax({
			url: "ts3_create_backup_post.php",
			type: "post",
			data: {TS3_Information : jsonString},
			success: function(data){
				if(data == "done")
				{
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: ts_backup_restored,
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
	};
	
/*
	Serverview: Backup: Activate Serverbackup
*/
	function activateBackupServer(path)
	{
		// Info
		alert("This can take a while.... Dont get panic ;)");
		
		infos			=	new Array();
		infos[0]		=	"activate_backup_server";
		infos[1]		=	'backup/server/'+path;
		infos[2]		=	port;
		infos[3]		=	instanz;
		
		var jsonString 	= 	JSON.stringify(infos);
		$.ajax({
			url: "ts3_create_backup_post.php",
			type: "post",
			data: {TS3_Information : jsonString},
			success: function(data){
				alert(data);
				/*$.notify({
					title: '<strong>'+success+'</strong><br />',
					message: ts_backup_restored,
					icon: 'fa fa-check'
				},{
					type: 'success',
					allow_dismiss: true,
					placement:
					{
						from: 'bottom',
						align: 'right'
					}
				});*/
			}
		});
	};

/*
	Serverview: Icons: Delete Icons
*/
	function deleteIcon(id)
	{
		var dataString 				= 	'action=deleteIcon&id='+id+'&instanz='+instanz+'&serverId='+serverId+'&port='+port;
		$.ajax({
			type: "POST",
			url: "functionsTeamspeakPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data)
			{
				if(data == "done")
				{
					$('#'+id).remove();
				}
				else
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: "There was something wrong in the delete process :/",
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
	Serverview: Serverhome: Client Message / Poke 
*/
	function clientMsg(clid)
	{
		var message		=	$('#inputMessagePoke').val();
		var mode		=	$('#selectMessagePoke').val();
		if(message != '')
		{
			if(mode == 1)
			{
				var dataString	=	'action=clientMsg&message='+message+'&instanz='+instanz+'&port='+port+'&clid='+clid;
				$.ajax({
					type: "POST",
					url: "functionsTeamspeakPost.php",
					data: dataString,
					cache: true,
					async: true,
					success: function(data)
					{
						if(data == 'done')
						{
							$.notify({
								title: '<strong>'+success+'</strong><br />',
								message: langClientMsg,
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
				var dataString	=	'action=clientPoke&message='+message+'&instanz='+instanz+'&port='+port+'&clid='+clid;
				$.ajax({
					type: "POST",
					url: "functionsTeamspeakPost.php",
					data: dataString,
					cache: true,
					async: true,
					success: function(data)
					{
						if(data == 'done')
						{
							$.notify({
								title: '<strong>'+success+'</strong><br />',
								message: langClientPoke,
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
			};
		};
	};
	
/*
	Serverview: Serverhome: Client Move
*/
	function clientMove(clid)
	{
		var cid			=	$('#selectMoveInChannel').val();
		if(cid != '')
		{
			var dataString	=	'action=clientMove&cid='+cid+'&instanz='+instanz+'&port='+port+'&clid='+clid;
			$.ajax({
				type: "POST",
				url: "functionsTeamspeakPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data)
				{
					if(data == 'done')
					{
						$.notify({
							title: '<strong>'+success+'</strong><br />',
							message: langClientMove,
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
		};
	};
	
/*
	Serverview: Serverhome: Client Kick
*/
	function clientKick(clid)
	{
		var mode		=	$('#selectKickStyle').val();
		var message		=	$('#inputMessageKick').val();
		var dataString	=	'action=clientKick&message='+message+'&mode='+mode+'&instanz='+instanz+'&port='+port+'&clid='+clid;
		$.ajax({
			type: "POST",
			url: "functionsTeamspeakPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data)
			{
				if(data == 'done')
				{
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: langClientKick,
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
	};

/*
	Serverview: Serverhome: Client Ban
*/
	function clientBan(clid)
	{
		var time		=	$('#inputBanTime').val();
		var message		=	$('#inputMessageBan').val();
		if(time == '')
			time 		=	0;
		var dataString	=	'action=clientBan&message='+message+'&time='+time+'&instanz='+instanz+'&port='+port+'&clid='+clid;
		$.ajax({
			type: "POST",
			url: "functionsTeamspeakPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data)
			{
				if(data == 'done')
				{
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: langClientBan,
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
	};
	
/*
	Serverview: Serverhome: Add / Remove Serverpermissions
*/
	function addRemoveSRights(clid, sgid, id)
	{
		var hasRight	=	$('#'+id).hasClass("table-success");
		if(hasRight)
		{
			var dataString	=	'action=clientRemoveServerGroup&sgid='+sgid+'&instanz='+instanz+'&port='+port+'&clid='+clid;
			$.ajax({
				type: "POST",
				url: "functionsTeamspeakPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data)
				{
					if(data == 'done')
					{
						$('#'+id).removeClass("table-success");
						$('#'+id).removeClass("text-success");
						$('#'+id).addClass("table-danger");
						$('#'+id).addClass("text-danger");
					};
				}
			});
		}
		else
		{
			var dataString	=	'action=clientAddServerGroup&sgid='+sgid+'&instanz='+instanz+'&port='+port+'&clid='+clid;
			$.ajax({
				type: "POST",
				url: "functionsTeamspeakPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data)
				{
					if(data == 'done')
					{
						$('#'+id).removeClass("table-danger");
						$('#'+id).removeClass("text-danger");
						$('#'+id).addClass("table-success");
						$('#'+id).addClass("text-success");
					};
				}
			});
		};
	};
	
/*
	Serverview: Serverhome Add / Remove Channelgroup
*/
	function addRemoveCRights(cid, oldCgid, clid, cgid, id)
	{
		if(clientChannelGroupId != -1)
		{
			oldCgid		=	clientChannelGroupId;
		};
		
		var hasRight	=	$('#'+id).hasClass("table-success");
		
		if(!hasRight)
		{
			var dataString	=	'action=clientChangeChannelGroup&cid='+cid+'&cgid='+cgid+'&instanz='+instanz+'&port='+port+'&clid='+clid;
			$.ajax({
				type: "POST",
				url: "functionsTeamspeakPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data)
				{
					if(data == 'done')
					{
						clientChannelGroupId	=	cgid;
						
						$('#'+id).removeClass("table-danger");
						$('#'+id).removeClass("text-danger");
						$('#'+id).addClass("table-success");
						$('#'+id).addClass("text-success");
						
						$('#cgroup_'+oldCgid).removeClass("table-success");
						$('#cgroup_'+oldCgid).removeClass("text-success");
						$('#cgroup_'+oldCgid).addClass("table-danger");
						$('#cgroup_'+oldCgid).addClass("text-danger");
					};
				}
			});
		};
	};
	
/*
	Serverview: Massenactions: Message / Poke
*/
	function massactionsChangeMessagePoke()
	{
		// Daten sammeln
		var group_id 			= 	$('#selectMessagePokeGroup').val();
		var who					=	$("#selectMessagePokeGroup").find(':selected').attr('group');
		var channel				=	$('#selectMessagePokeChannel').val();
		var group				=	'none';
		var res 				= 	group_id.split("-");	// res[3]
		
		if(who != 'all')
		{
			group		=	res[3];
		};
		
		// Daten in Funktion übergeben
		massactionsMassInfo(who, group, channel, 'msg');
	};
	
	function actionMessagePoke()
	{
		// Daten sammeln
		var group_id 			= 	$('#selectMessagePokeGroup').val();
		var who					=	$("#selectMessagePokeGroup").find(':selected').attr('group');
		var channel				=	$('#selectMessagePokeChannel').val();
		var group				=	'none';
		var res 				= 	group_id.split("-");	// res[3]
		
		if(who != 'all')
		{
			group		=	res[3];
		};
		
		// Daten in Funktion übergeben
		massactionsMassAction(who, group, channel, 'msg');
	};

/*
	Serverview: Massenactions: Move
*/
	function massactionsChangeMove()
	{
		var whichChannel	= 	$('#selectMoveFromChannel').val();
		var toChannel		= 	$('#selectMoveInChannel').val();
		
		if(whichChannel != '' && toChannel != '')
		{
			// Daten sammeln
			var group_id 			= 	$('#selectMoveGroup').val();
			var who					=	$("#selectMoveGroup").find(':selected').attr('group');
			var group				=	'none';
			var res 				= 	group_id.split("-");	// res[3]
			
			if(who != 'all')
			{
				group		=	res[3];
			};
			
			// Daten in Funktion übergeben
			massactionsMassInfo(who, group, whichChannel, 'move', false);
		};
	};
	
	function actionMove()
	{
		var whichChannel	= 	$('#selectMoveFromChannel').val();
		var toChannel		= 	$('#selectMoveInChannel').val();
		
		if(whichChannel != '' && toChannel != '')
		{
			// Daten sammeln
			var group_id 			= 	$('#selectMoveGroup').val();
			var who					=	$("#selectMoveGroup").find(':selected').attr('group');
			var group				=	'none';
			var res 				= 	group_id.split("-");	// res[3]
			
			if(who != 'all')
			{
				group		=	res[3];
			};
			
			// Daten in Funktion übergeben
			massactionsMassAction(who, group, whichChannel, 'move', false);
		};
	};

/*
	Serverview: Massenactions: Kick
*/
	function massactionsChangeKick()
	{
		// Daten sammeln
		var group_id 			= 	$('#selectKickGroup').val();
		var who					=	$("#selectKickGroup").find(':selected').attr('group');
		var channel				=	$('#selectKickChannel').val();
		var group				=	'none';
		var res 				= 	group_id.split("-");	// res[3]
		
		if(who != 'all')
		{
			group		=	res[3];
		};
		
		// Daten in Funktion übergeben
		massactionsMassInfo(who, group, channel, 'kick');
	};
	
	function actionKick()
	{
		// Daten sammeln
		var group_id 			= 	$('#selectKickGroup').val();
		var who					=	$("#selectKickGroup").find(':selected').attr('group');
		var channel				=	$('#selectKickChannel').val();
		var group				=	'none';
		var res 				= 	group_id.split("-");	// res[3]
		
		if(who != 'all')
		{
			group		=	res[3];
		};
		
		// Daten in Funktion übergeben
		massactionsMassAction(who, group, channel, 'kick');
	};

/*
	Serverview: Massenactions: Ban
*/
	function massactionsChangeBan()
	{
		// Daten sammeln
		var group_id 			= 	$('#selectBanGroup').val();
		var who					=	$("#selectBanGroup").find(':selected').attr('group');
		var channel				=	$('#selectBanChannel').val();
		var group				=	'none';
		var res 				= 	group_id.split("-");	// res[3]
		
		if(who != 'all')
		{
			group		=	res[3];
		};
		
		// Daten in Funktion übergeben
		massactionsMassInfo(who, group, channel, 'ban');
	};
	
	function actionBan()
	{
		// Daten sammeln
		var group_id 			= 	$('#selectBanGroup').val();
		var who					=	$("#selectBanGroup").find(':selected').attr('group');
		var channel				=	$('#selectBanChannel').val();
		var group				=	'none';
		var res 				= 	group_id.split("-");	// res[3]
		
		if(who != 'all')
		{
			group		=	res[3];
		};
		
		// Daten in Funktion übergeben
		massactionsMassAction(who, group, channel, 'ban');
	};

/*
	Serverview: Massenactions: MassenACTION
*/
	function massactionsMassAction(who, group, channel, action)
	{
		// Art der Clienten ermitteln
		var getUsers =	'ID=1';
		switch (who)
		{
			case 'all':
				getUsers 				+= 	'&action=getUsers&ts3_server='+instanz+'&ts3_port='+port+'&who=all&channel='+channel+'&mass_action='+action;
				break;
			case 'sgroup':
				if(channel != 'none')
				{
					getUsers 			+= 	'&action=getUsers&ts3_server='+instanz+'&ts3_port='+port+'&who=sgroup&group='+group+'&channel='+channel+'&mass_action='+action;
				}
				else
				{
					getUsers 			+= 	'&action=getUsers&ts3_server='+instanz+'&ts3_port='+port+'&who=sgroup&group='+group+'&mass_action='+action;
				};
				break;
			case 'cgroup':
				if(channel != 'none')
				{
					getUsers 			+= 	'&action=getUsers&ts3_server='+instanz+'&ts3_port='+port+'&who=cgroup&group='+group+'&channel='+channel+'&mass_action='+action;
				}
				else
				{
					getUsers 			+= 	'&action=getUsers&ts3_server='+instanz+'&ts3_port='+port+'&who=cgroup&group='+group+'&mass_action='+action;
				};
				break;
		};
		
		// Daten abschicken und auswerten
		$.ajax({
			type: "POST",
			url: "functionsTeamspeakPost.php",
			data: getUsers,
			cache: true,
			async: true,
			success: function(data)
			{
				var informations		= 	JSON.parse(data);
				if(informations.length > 1)
				{
					for($i=1; $i<informations.length; $i++)
					{
						if(informations[0] == 'move')
						{
							var cid			=	$('#selectMoveInChannel').val();
							if(cid != '')
							{
								var dataString	=	'action=clientMove&cid='+cid+'&instanz='+instanz+'&port='+port+'&clid='+informations[$i];
								$.ajax({
									type: "POST",
									url: "functionsTeamspeakPost.php",
									data: dataString,
									cache: true,
									async: true,
									success: function(data)
									{
										// Hier noch die Abfrage beenden
										//alert(data);
									}
								});
							};
						};
						
						if(informations[0] == 'kick')
						{
							var mode		=	$('#selectKickStyle').val();
							var message		=	$('#inputMessageKick').val();
							var dataString	=	'action=clientKick&message='+message+'&mode='+mode+'&instanz='+instanz+'&port='+port+'&clid='+informations[$i];
							$.ajax({
								type: "POST",
								url: "functionsTeamspeakPost.php",
								data: dataString,
								cache: true,
								async: true,
								success: function(data)
								{
									// Hier noch die Abfrage beenden
									//alert(data);
								}
							});
						};
						
						if(informations[0] == 'ban')
						{
							var time		=	$('#inputBanTime').val();
							var message		=	$('#inputMessageBan').val();
							if(time == '')
								time 		=	0;
							var dataString	=	'action=clientBan&message='+message+'&time='+time+'&instanz='+instanz+'&port='+port+'&clid='+informations[$i];
							$.ajax({
								type: "POST",
								url: "functionsTeamspeakPost.php",
								data: dataString,
								cache: true,
								async: true,
								success: function(data)
								{
									// Hier noch die Abfrage beenden
									//alert(data);
								}
							});
						};
						
						if(informations[0] == 'msg')
						{
							var message		=	$('#inputMessagePoke').val();
							var mode		=	$('#selectMessagePoke').val();
							if(message != '')
							{
								if(mode == 1)
								{
									var dataString	=	'action=clientMsg&message='+message+'&instanz='+instanz+'&port='+port+'&clid='+informations[$i];
									$.ajax({
										type: "POST",
										url: "functionsTeamspeakPost.php",
										data: dataString,
										cache: true,
										async: true,
										success: function(data)
										{
											// Hier noch die Abfrage beenden
											//alert(data);
										}
									});
								}
								else
								{
									var dataString	=	'action=clientPoke&message='+message+'&instanz='+instanz+'&port='+port+'&clid='+informations[$i];
									$.ajax({
										type: "POST",
										url: "functionsTeamspeakPost.php",
										data: dataString,
										cache: true,
										async: true,
										success: function(data)
										{
											// Hier noch die Abfrage beenden
											//alert(data);
										}
									});
								};
							};
						};
					};
				};
			}
		});
	};
	
/*
	Serverview: Massenactions: MassINFO
*/
	function massactionsMassInfo(who, group, channel, action)
	{
		// Art der Clienten ermitteln
		var getUsers =	'';
		switch (who)
		{
			case 'all':
				getUsers 				+= 	'&action=getUsers&ts3_server='+instanz+'&ts3_port='+port+'&who=all&channel='+channel+'&mass_action='+action;
				break;
			case 'sgroup':
				if(channel != 'none')
				{
					getUsers 			+= 	'&action=getUsers&ts3_server='+instanz+'&ts3_port='+port+'&who=sgroup&group='+group+'&channel='+channel+'&mass_action='+action;
				}
				else
				{
					getUsers 			+= 	'&action=getUsers&ts3_server='+instanz+'&ts3_port='+port+'&who=sgroup&group='+group+'&mass_action='+action;
				};
				break;
			case 'cgroup':
				if(channel != 'none')
				{
					getUsers 			+= 	'&action=getUsers&ts3_server='+instanz+'&ts3_port='+port+'&who=cgroup&group='+group+'&channel='+channel+'&mass_action='+action;
				}
				else
				{
					getUsers 			+= 	'&action=getUsers&ts3_server='+instanz+'&ts3_port='+port+'&who=cgroup&group='+group+'&mass_action='+action;
				};
				break;
		};
		
		// Daten abschicken und auswerten
		$.ajax({
			type: "POST",
			url: "functionsTeamspeakPost.php",
			data: getUsers,
			cache: true,
			async: true,
			success: function(data)
			{
				var informations		= 	JSON.parse(data);
				
				if(informations.length <= 1)
				{
					if(informations[0] == 'move' && document.getElementById("infoMove"))
					{
						document.getElementById("infoMove").innerHTML = ts3_mass_no_affected_user;
					};
					
					if(informations[0] == 'kick' && document.getElementById("infoKick"))
					{
						document.getElementById("infoKick").innerHTML = ts3_mass_no_affected_user;
					};
					
					if(informations[0] == 'ban' && document.getElementById("infoBan"))
					{
						document.getElementById("infoBan").innerHTML = ts3_mass_no_affected_user;
					};
					
					if(informations[0] == 'msg' && document.getElementById("infoMessagePoke"))
					{
						document.getElementById("infoMessagePoke").innerHTML = ts3_mass_no_affected_user;
					};
				}
				else
				{
					var clients		=	'';
					for($i=1; $i<informations.length; $i++)
					{
						if($i == 0 || $i == informations.length -1)
						{
							clients	+=	informations[$i];
						}
						else 
						{
							clients	+=	informations[$i]+', ';
						};
					};
					
					if(informations[0] == 'move' && document.getElementById("infoMove"))
					{
						document.getElementById("infoMove").innerHTML = clients;
					};
					
					if(informations[0] == 'kick' && document.getElementById("infoKick"))
					{
						document.getElementById("infoKick").innerHTML = clients;
					};
					
					if(informations[0] == 'ban' && document.getElementById("infoBan"))
					{
						document.getElementById("infoBan").innerHTML = clients;
					};
					
					if(informations[0] == 'msg' && document.getElementById("infoMessagePoke"))
					{
						document.getElementById("infoMessagePoke").innerHTML = clients;
					};
				};
			}
		});
	};
	
/*
	Serverview: Banlist: Add Ban
*/
	function addBan()
	{
		var input		=	$('#banInput').val();
		var banReason	=	$('#banReason').val();
		var banTime		=	$('#banTime').val();
		var bantype		=	"uid";
		
		if($('#banName').prop( "checked" ))
		{
			var bantype	=	"name";
		};
		if($('#banIp').prop( "checked" ))
		{
			var bantype	=	"ip";
		};
		
		if(input != "" && banTime != "")
		{
			var dataString	=	'action=clientBanManuell&port='+port+'&bantype='+bantype+'&instanz='+instanz+'&input='+input+'&time='+banTime+'&reason='+banReason;
			$.ajax({
				type: "POST",
				url: "functionsTeamspeakPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data)
				{
					if(data == "done")
					{
						$.notify({
							title: '<strong>'+success+'</strong><br />',
							message: "Ban erfolgreich hinzugef&uuml;gt!",
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
						teamspeakBansInit();
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
				message: "Banzeit und Name/UID/IP angeben!",
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
	Serverview: Banlist: Delete Ban
*/
	function deleteBan(banid)
	{
		var dataString	=	'action=clientUnban&port='+port+'&banid='+banid+'&instanz='+instanz;
		$.ajax({
			type: "POST",
			url: "functionsTeamspeakPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data)
			{
				if(data == "done")
				{
					$('#banid_'+banid).remove();
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
	Serverview: Server Create: Slide Optionalsettings
*/
function slideSettings(id)
{
	var icon		=	$('#'+id+"Icon");
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
	Serverview: Server Create: Checkport
*/
	$(function() {
		$('#serverCreatePort').focusout(function() {
			if($(this).val() == "")
			{
				$(this).removeClass("text-danger");
				$(this).removeClass("text-success");
			}
			else
			{
				var dataString								=	'action=checkTeamspeakPort&port='+$(this).val()+'&instanz='+$("#serverCreateWhichInstanz").val();
				$.ajax({
					type: "POST",
					url: "functionsTeamspeakPost.php",
					data: dataString,
					cache: true,
					async: true,
					success: function(data){
						if(data == "done")
						{
							$("#serverCreatePort").addClass("text-danger");
							$("#serverCreatePort").removeClass("text-success");
						}
						else
						{
							$("#serverCreatePort").addClass("text-success");
							$("#serverCreatePort").removeClass("text-danger");
						};
					}
				});
			};
		});
	});
	
/*
	Serverview: Server Create: Server Create
*/
	function createServer(requestName = "", requestPw = "", filename)
	{
		var port		=	$('#serverCreatePort').val();
		var port_regex	=	 /^[0-9]{4}$/;
		var port_check	= 	port_regex.test(port);
		var requestPk	=	'';
		var isRequest	=	false;
		
		if(requestName != "")
		{
			if(requestPw != "")
			{
				var dataString 		= 	'action=createUser&username='+requestName+'&password='+requestPw+'&withPk=true&crypted=true';
			}
			else
			{
				var dataString 		= 	'action=getPk&name='+requestName;
			};
			
			$.ajax({
				type: "POST",
				url: "functionsPost.php",
				data: dataString,
				cache: true,
				async: false,
				success: function(data){
					if(data != 'User already exists!' && data != 'No Username or no Password!' && data != "" && data != "User does not exist!")
					{
						requestPk		=	data;
						isRequest		=	true;
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
		
		$('#createServer').addClass("disabled");
		
		// Informationen abfragen
		var ts3_information = new Array();
		
		// Server wird gespeichert
		ts3_information['0']									=		$('#serverCreateWhichInstanz').val();
		
		// Gibt ein true zurück, falls es sich um ein Request handelt...
		if(isRequest)
		{
			ts3_information['1']								=		"true";
			ts3_information['2']								=		requestName;
			ts3_information['3']								=		filename;
		}
		else
		{
			ts3_information['1']								=		"false";
		};
		
		// ServerIP
		/*ts3_information['1']									=		ts3_server[$('#serverCreateWhichInstanz').val()]['ip'];
		
		// ServerQueryPort
		ts3_information['2']									=		ts3_server[$('#serverCreateWhichInstanz').val()]['queryport'];
		
		// ServerBenutzer
		ts3_information['3']									=		ts3_server[$('#serverCreateWhichInstanz').val()]['user'];
		
		// ServerPassword
		ts3_information['4']									=		ts3_server[$('#serverCreateWhichInstanz').val()]['pw'];*/
		
		// Anzahl reservierter Slots wird gespeichert
		if ($('#serverCreateReservedSlots').val() != '')
		{
			ts3_information['5'] 								= 		$('#serverCreateReservedSlots').val();
		}
		else
		{
			ts3_information['5'] 								= 		ts3_server_create_default['reserved_slots'];
		};
		
		// Der Teamspeakhostmessage wird gespeichert
		if($('#serverCreateHostMessage').val() != '' && !isRequest)
		{
			ts3_information['6'] 								= 		$('#serverCreateHostMessage').val();
		}
		else
		{
			ts3_information['6'] 								= 		ts3_server_create_default['host_message'];
		};
		
		// Der Teamspeakmessagemode wird gespeichert
		if(!isRequest)
		{
			ts3_information['7']								=		$('#serverCreateHosttype').val();
		};
		
		if($('#serverCreateHostUrl').val() != '' && !isRequest)
		{
			ts3_information['8'] 								= 		$('#serverCreateHostUrl').val();
		}
		else
		{
			ts3_information['8'] 								= 		ts3_server_create_default['host_url'];
		};
		
		if($('#serverCreateHostBannerUrl').val() != '' && !isRequest)
		{
			ts3_information['9'] 								= 		$('#serverCreateHostBannerUrl').val();
		}
		else
		{
			ts3_information['9'] 								= 		ts3_server_create_default['host_banner_url'];
		};
		
		if($('#serverCreateHostBannerInterval').val() != '' && !isRequest)
		{
			ts3_information['10']								= 		$('#serverCreateHostBannerInterval').val();
		}
		else
		{
			ts3_information['10'] 								= 		ts3_server_create_default['host_banner_int'];
		};
		
		if($('#serverCreateHostButtonGfxUrl').val() != '' && !isRequest)
		{
			ts3_information['11']								= 		$('#serverCreateHostButtonGfxUrl').val();
		}
		else
		{
			ts3_information['11'] 								= 		ts3_server_create_default['host_button_gfx'];
		};
		
		if($('#serverCreateHostButtonTooltip').val() != '' && !isRequest)
		{
			ts3_information['12']								= 		$('#serverCreateHostButtonTooltip').val();
		}
		else
		{
			ts3_information['12'] 								= 		ts3_server_create_default['host_button_tip'];
		};
		
		if($('#serverCreateHostButtonUrl').val() != '' && !isRequest)
		{
			ts3_information['13']								= 		$('#serverCreateHostButtonUrl').val();
		}
		else
		{
			ts3_information['13'] 								= 		ts3_server_create_default['host_button_url'];
		};
		
		if($('#serverCreateAutobanCount').val() != '' && !isRequest)
		{
			ts3_information['14'] 								= 		$('#serverCreateAutobanCount').val();
		}
		else
		{
			ts3_information['14'] 								= 		ts3_server_create_default['auto_ban_count'];
		};
		
		if($('#serverCreateAutobanDuration').val() != '' && !isRequest)
		{
			ts3_information['15'] 								= 		$('#serverCreateAutobanDuration').val();
		}
		else
		{
			ts3_information['15'] 								= 		ts3_server_create_default['auto_ban_time'];
		};
		
		if($('#serverCreateAutobanDeleteAfter').val() != '' && !isRequest)
		{
			ts3_information['16'] 								= 		$('#serverCreateAutobanDeleteAfter').val();
		}
		else
		{
			ts3_information['16'] 								= 		ts3_server_create_default['remove_time'];
		};
		
		if($('#serverCreateReducePoints').val() != '' && !isRequest)
		{
			ts3_information['17'] 								= 		$('#serverCreateReducePoints').val();
		}
		else
		{
			ts3_information['17'] 								= 		ts3_server_create_default['points_tick_reduce'];
		};
		
		if($('#serverCreatePointsBlock').val() != '' && !isRequest)
		{
			ts3_information['18'] 								= 		$('#serverCreatePointsBlock').val();
		}
		else
		{
			ts3_information['18'] 								= 		ts3_server_create_default['points_needed_block_cmd'];
		};
		
		if($('#serverCreatePointsBlockIp').val() != '' && !isRequest)
		{
			ts3_information['19'] 								= 		$('#serverCreatePointsBlockIp').val();
		}
		else
		{
			ts3_information['19'] 								= 		ts3_server_create_default['needed_block_ip'];
		};
		
		if($('#serverCreateUploadLimit').val() != '' && !isRequest)
		{
			ts3_information['20'] 								= 		$('#serverCreateUploadLimit').val();
		}
		else
		{
			ts3_information['20'] 								= 		ts3_server_create_default['upload_bandwidth_limit'];
		};
		
		if($('#serverCreateUploadKontigent').val() != '' && !isRequest)
		{
			ts3_information['21'] 								= 		$('#serverCreateUploadKontigent').val();
		}
		else
		{
			ts3_information['21'] 								= 		ts3_server_create_default['upload_quota'];
		};
		
		if($('#serverCreateDownloadLimit').val() != '' && !isRequest)
		{
			ts3_information['22'] 								= 		$('#serverCreateDownloadLimit').val();
		}
		else
		{
			ts3_information['22'] 								= 		ts3_server_create_default['download_bandwidth_limit'];
		};
		
		if($('#serverCreateDownloadKontigent').val() != '' && !isRequest)
		{
			ts3_information['23'] 								= 		$('#serverCreateDownloadKontigent').val();
		}
		else
		{
			ts3_information['23'] 								= 		ts3_server_create_default['download_quota'];
		};
		
		// Protokolinformationen speichern
		ts3_information['24']									=		$('#serverCreateProtokolClient').val();
		
		ts3_information['25']									=		$('#serverCreateProtokolQuery').val();
		
		ts3_information['26']									=		$('#serverCreateProtokolChannel').val();
		
		ts3_information['27']									=		$('#serverCreateProtokolRights').val();
		
		ts3_information['28']									=		$('#serverCreateProtokolServer').val();
		
		ts3_information['29']									=		$('#serverCreateProtokolTransfer').val();
		
		// Servername wird gespeichert
		if($('#serverCreateServername').val() != '')
		{
			ts3_information['30'] 								= 		$('#serverCreateServername').val();
		}
		else
		{
			ts3_information['30'] 								= 		ts3_server_create_default['servername'];
		};
		
		if(!isRequest)
		{
			// Der Copy Port (falls vorhanden) wird gespeichert
			ts3_information['31']								=		$('#serverCreateServerCopyPort').val();
		};
		
		// Der Teamspeakport wird gespeichert
		ts3_information['32'] 									= 		$('#serverCreatePort').val();
		
		// Die Max. Teamspeakclients werden gespeichert
		if ($('#serverCreateSlots').val() != '')
		{
			ts3_information['33'] 								= 		$('#serverCreateSlots').val();
		}
		else
		{
			ts3_information['33'] 								= 		ts3_server_create_default['slots'];
		};
		
		// Das Teamspeakpassword wird gespeichert
		if($('#teamspeak_password').val() != '')
		{
			ts3_information['34'] 								= 		$('#serverCreatePassword').val();
		}
		else
		{
			ts3_information['34'] 								= 		ts3_server_create_default['password'];
		};
		
		// Die Willkommensnachricht wird gespeichert
		ts3_information['35'] 									= 		$('#serverCreateWelcomeMessage').val();
		
		if(!isRequest)
		{
			// Server Copy
			ts3_information['36']								=		$('#serverCreateServerCopy').val();
		};
		
		/*if($('#serverCreateServerCopy').val() != 'nope')
		{
			// Server Copy IP
			ts3_information['37']								=		ts3_server[$('#serverCreateServerCopy').val()]['ip'];
			
			// Server Copy Query
			ts3_information['38']								=		ts3_server[$('#serverCreateServerCopy').val()]['queryport'];
			
			// Server Copy User
			ts3_information['39']								=		ts3_server[$('#serverCreateServerCopy').val()]['user'];
			
			// Server Copy Pass
			ts3_information['40']								=		ts3_server[$('#serverCreateServerCopy').val()]['pw'];
		}*/
		
		var jsonString = JSON.stringify(ts3_information);
		$.ajax({
			url: "ts3_create_server_post.php",
			type: "post",
			data: {TS3_Information : jsonString},
			success: function(data){
				$('#createServer').removeClass("disabled");
				
				var informations 	=	JSON.parse(data);
				
				if(informations['success'] == '1')
				{
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: "Server ID:<i> "+informations['serverid']+"</i><br />Server Port:<i> "+informations['port']+"</i><br />Server Token:<i> "+informations['token']+"</i>",
						icon: 'fa fa-check'
					},{
						type: 'info',
						allow_dismiss: true,
						delay: 0,
						placement:
						{
							from: 'bottom',
							align: 'right'
						}
					});
					
					// Falls ServerRequest ist
					if(isRequest)
					{
						// Rechte geben
						var hp_server_view_switch					=	"true";
						var ts_rights_server_edit_switch			=	"false";
						var ts_rights_server_start_stop_switch		=	"true";
						var ts_rights_server_msg_poke_switch		=	"true";
						var ts_rights_server_mass_actions_switch	=	"true";
						var ts_rights_server_protokoll_switch		=	"true";
						var ts_rights_server_icons_switch			=	"true";
						var ts_rights_server_bans_switch			=	"true";
						var ts_rights_server_token_switch			=	"true";
						var ts_rights_server_filelist_switch		=	"true";
						var ts_rights_server_backups_switch			=	"true";
						var ts_rights_server_clients_switch			=	"true";
						var ts_rights_client_actions_switch			=	"true";
						var ts_rights_client_rights_switch			=	"true";
						var ts_rights_channel_actions_switch		=	"true";
						
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
						dataString									+=	'&pk='+requestPk;
						
						dataString									+=	'&teamspeak_port='+informations['port'];
						dataString									+=	'&teamspeak_instanz='+$('#serverCreateWhichInstanz').val();
						
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
							success: function(data){
								if(data == "done")
								{
									teamspeakServerRequestsInit();
								};
							}
						});
					};
				}
				else
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: informations['error'],
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
	Serverview: Server Create: Delete Server Application
*/
	function deleteWantServer(file)
	{
		var dataString	=	'action=deleteData&link='+file;
		$.ajax({
			type: "POST",
			url: "functionsPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data)
			{
				if(data == 'error')
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: "File do not exist anymore!",
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
						title: '<strong>'+success+'</strong><br />',
						message: file_delete_success,
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
					
					// Seite neu laden
					teamspeakServerRequestsInit();
				};
			}
		});
	};
	
/*
	Serverview: Server Create: Abort Server Application
*/
	function abortServerRequest()
	{
		teamspeakServerRequestsInit();
	};

/*
	Serverview: Server Create: Copy Port (Server Create)
*/
	function serverCreateChangePort()
	{
		var instanz		=	$('#serverCreateServerCopy').val();
		
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
					var element = document.getElementById('serverCreateServerCopyPort');
					
					// Element leeren
					while ( element.childNodes.length >= 1 )
					{
						element.removeChild(element.firstChild);
					};
					
					// Ports erstellen
					for (i = 0; i < ports.length; i++)
					{
						port 					= 	document.createElement('option');
						port.value				=	ports[i];
						port.text				=	ports[i];
						element.appendChild(port);
					};
				}
			});
		}
		else
		{
			var element = document.getElementById('serverCreateServerCopyPort');
			
			// Element leeren
			while ( element.childNodes.length >= 1 )
			{
				element.removeChild(element.firstChild);
			};
			
			// Kein Port einschreiben
			nope 						= 	document.createElement('option');
			nope.value					=	'nope';
			nope.text					=	unescape(ts3_no_copy);
			element.appendChild(nope);
		};
	};
	
/*
	Serverview: Serverhome: Delete Teamspeakchannel
*/
	function deleteTeamspeakChannel(cid, sid)
	{
		var dataString 		= 	'action=deleteChannel&cid='+cid+'&instanz='+instanz+'&port='+port+'&serverid='+sid;
		$.ajax({
			type: "POST",
			url: "functionsTeamspeakPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data){
				if(data == 'done')
				{
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: channel_deleted,
						icon: 'fa fa-warning'
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
	};
	
/*
	Serverview: Serverclients: Delete Databaseclient
*/
	function deleteDBClient(cldbid)
	{
		var dataString 		= 	'action=deleteDBClient&cldbid='+cldbid+'&instanz='+instanz+'&port='+port;
		$.ajax({
			type: "POST",
			url: "functionsTeamspeakPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data){
				if(data == 'done')
				{
					$('#dbClient_'+cldbid).remove();
					
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: client_successfull_deleted,
						icon: 'fa fa-warning'
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
	};
	
/*
	web_teamspeak_server_requests: Show Server Request informations
*/
	function showServerRequest(file)
	{
		$("#mainContent").fadeOut("slow", function()
		{
			$("#mainContent").load('web_teamspeak_server_requests_info.php', { "file" : file }, function()
			{
				$("#mainContent").fadeIn("slow");
			});
		});
	};
	
/*
	web_teamspeak_serverfilelist: Delete a file
*/
	function deleteFile(path, cid, time)
	{
		var dataString 		= 	'action=deleteFileFromFilelist&cid='+cid+"&path="+path+'&instanz='+instanz+'&port='+port;
		$.ajax({
			type: "POST",
			url: "functionsTeamspeakPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data){
				if(data == 'done')
				{
					$('#'+time+"_"+cid).remove();
					
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: file_delete_success,
						icon: 'fa fa-warning'
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
	};