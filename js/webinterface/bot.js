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
	Querybot: Save Botsettings
*/
	function saveBotSettings(tablename)
	{
		switch(tablename)
		{
			case "bot_settigns_afk":
				var postData	=	{
					isAfkMoving: 					($('#setAfkModul').is(':checked')) ? "1" : "0",
					isMovingMicMuted: 				($('#setAfkMicMuted').is(':checked')) ? "1" : "0",
					isMovingHeadsetMuted: 			($('#setAfkHeadsetMuted').is(':checked')) ? "1" : "0",
					isMovingAway: 					($('#setAfkAway').is(':checked')) ? "1" : "0",
					AfkMovingChannel: 				$('#afkChannel').val(),
					isMovingIdleTime: 				$('#setAfkIdleTime').val(),
					isMovingIdleWarningMessage: 	($('#setAfkIdleWarningMessage').is(':checked')) ? "1" : "0",
					MovingIdleWarningMessage:		$('#setAfkIdleMessage').val(),
					afkMoveingImmunSgroups:			JSON.stringify($('#setAfkImmunSgroups').tagsinput('items'))
				};
				break;
		};
		
		$.ajax({
			type: "POST",
			url: "./php/functions/functionsBotPost.php",
			data: {
				action:		'saveBotSettings',
				botid:		botid,
				table:		tablename,
				data:		JSON.stringify(postData)
			},
			success: function(data){
				/*if(data == "done" && which == "language")
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
				};*/
				console.log(data);
			}
		});
	};

/*
	Querybot: Show Botdetails
*/
	function showQueryBot(id, instanz, port)
	{
		$("#mainContent").fadeOut("fast", function()
		{
			$("#mainContent").load('./php/bot/web_bot_query_info.php', { "id" : id, "instanz" : instanz, "port" : port }, function()
			{
				$("#mainContent").fadeIn("fast");
			});
		});
	};

/*
	Querybot: Create Querybot
*/
	function createQueryBot()
	{
		$.ajax({
			type: "POST",
			url: "./php/functions/functionsBotPost.php",
			data: {
				action:		'createQueryBot',
				instanz:	$('#selectCreateQueryBotInstance').val(),
				port:		$('#selectCreateQueryBotPort').val(),
				name:		encodeURIComponent($('#createQueryBotName').val())
			},
			success: function(data){
				console.log(data);
			}
		});
	};
	
/*
	Querybot: Delete Querybot
*/
	function deleteQueryBot(id)
	{
		$.ajax({
			type: "POST",
			url: "./php/functions/functionsBotPost.php",
			data: {
				action:		'deleteQueryBot',
				id:			id
			},
			success: function(data){
				console.log(data);
			}
		});
	};

/*
	Querybot: Change Selectmenu on Querybot Create
*/
	function addQueryBotChangePort()
	{
		var instanz		=	$('#selectCreateQueryBotInstance').val();
		
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
					var element = document.getElementById('selectCreateQueryBotPort');
					
					while ( element.childNodes.length >= 1 )
					{
						element.removeChild(element.firstChild);
					};
					
					if(data != "[]")
					{
						var ports 	=	JSON.parse(data);
						for (i = 0; i < ports.length; i++)
						{
							port 					= 	document.createElement('option');
							port.value				=	ports[i];
							port.text				=	ports[i];
							element.appendChild(port);
						};
						
						$('#bttnCreateQueryBot').prop("disabled", false);
					}
					else
					{
						nope 						= 	document.createElement('option');
						nope.value					=	'nope';
						nope.text					=	unescape(lang.ts3_no_copy);
						element.appendChild(nope);
						
						$('#bttnCreateQueryBot').prop("disabled", true);
					};
				}
			});
		}
		else
		{
			var element = document.getElementById('selectCreateQueryBotSid');
			
			while ( element.childNodes.length >= 1 )
			{
				element.removeChild(element.firstChild);
			};
			
			nope 						= 	document.createElement('option');
			nope.value					=	'nope';
			nope.text					=	unescape(lang.ts3_no_copy);
			element.appendChild(nope);
		};
	};