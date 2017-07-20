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
		var lang	=	"english";
		if($('#checkGerman').prop("checked"))
		{
			lang	=	"german";
		};
		
		if(setConfig("language", lang))
		{
			$('#installBttn').slideUp("fast", function() {
				$('#installHistory').slideDown("fast");
			});
			
			changeRightContent("./install/guidelines.php");
		};
	};
	
/*
	Go to Databaseconnection
*/
	function goToDatabaseConnection()
	{
		if(!document.getElementById("check_datenschutz").checked)
		{
			$('.checkBoxes1').addClass("background-danger");
		}
		else if(!document.getElementById("check_haftungsausschluss").checked)
		{
			$('.checkBoxes1').removeClass("background-danger");
			
			$('.checkBoxes1').addClass("background-success");
			$('.checkBoxes2').addClass("background-danger");
		}
		else if(!document.getElementById("check_ts_damage").checked)
		{
			$('.checkBoxes1').removeClass("background-danger");
			$('.checkBoxes2').removeClass("background-danger");
			
			$('.checkBoxes1').addClass("background-success");
			$('.checkBoxes2').addClass("background-success");
			$('.checkBoxes3').addClass("background-danger");
		}
		else
		{
			$('#guidelinesIcon').removeClass("fa-eye");
			$('#databaseconnectionIcon').removeClass("fa-ban");
			$('#databaseconnectionBg').removeClass("text-danger-no-cursor");
			$('#databaseconnectionBg').removeClass("background-danger");
			
			$('#guidelinesIcon').addClass("fa-check");
			$('#databaseconnectionIcon').addClass("fa-eye");
			$('#databaseconnectionBg').addClass("text-success");
			$('#databaseconnectionBg').addClass("background-success");
			
			$("body").animate({ scrollTop: 0 }, "slow", function() {
				changeRightContent("./install/database.php");
			});
		};
	};
	
/*
	Go to Settings
*/
	function gotToSettings()
	{
		$('#databaseconnectionIcon').removeClass("fa-eye");
		$('#settingsIcon').removeClass("fa-ban");
		$('#settingsBg').removeClass("text-danger-no-cursor");
		$('#settingsBg').removeClass("background-danger");
		
		$('#databaseconnectionIcon').addClass("fa-check");
		$('#settingsIcon').addClass("fa-eye");
		$('#settingsBg').addClass("text-success");
		$('#settingsBg').addClass("background-success");
		
		$("body").animate({ scrollTop: 0 }, "slow", function() {
			changeRightContent("./install/settings.php");
		});
	};
	
/*
	Go to the Last Page
*/
	function gotToFinish()
	{
		$('#databaseconnectionIcon').removeClass("fa-eye");
		$('#databaseconnectionIcon').addClass("fa-check");
		
		$("body").animate({ scrollTop: 0 }, "slow", function() {
			changeRightContent("./install/finish.php");
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
	Check the Database and create all entrys
*/
	function checkDatabaseConnection()
	{
		var host		=	$('#check_hostname').val(),
			user		=	$('#check_username').val(),
			pw 			=	$('#check_password').val(),
			database	=	$('#check_database').val(),
			port		=	$('#check_port').val(),
			mode		=	$('#check_sqlmode').val(),
			ssl			=	$('#checkSslRequire').prop("checked");
		
		$.ajax({
			type: "POST",
			url: "./install/functionsPost.php",
			data: {
				action:		'check_database',
				host:		encodeURIComponent(host),
				user:		encodeURIComponent(user),
				pw:			encodeURIComponent(pw),
				database:	encodeURIComponent(database),
				port:		port,
				mode:		mode,
				ssl:		ssl
			},
			success: function(data)
			{
				if(data == 'done')
				{
					document.getElementById('database_errorbox').innerHTML			=	"<b>Databaseconnection Successful!</b>";
					$('#database_errorbox').removeClass("alert-info");
					$('#database_errorbox').removeClass("alert-danger");
					$('#database_errorbox').addClass("alert-success");
					
					$('#check_hostname').attr('disabled', 'disabled');
					$('#check_username').attr('disabled', 'disabled');
					$('#check_password').attr('disabled', 'disabled');
					$('#checkSslRequire').attr('disabled', 'disabled');
					$('#check_port').attr('disabled', 'disabled');
					$('#check_sqlmode').attr('disabled', 'disabled');
					$('#check_database').attr('disabled', 'disabled');
					
					$('#databaseConnectionBttn').removeClass("btn-custom");
					$('#databaseConnectionBttn').addClass("btn-success");
					$('#databaseConnectionBttn').attr("onclick", "createDatabase();");
					document.getElementById('databaseConnectionBttn').innerHTML		=	"<i class=\"fa fa-edit\" aria-hidden=\"true\"></i> "+datenbank_erstellen;
				}
				else
				{
					document.getElementById('database_errorbox').innerHTML	=	data;
					$('#database_errorbox').removeClass("alert-info");
					$('#database_errorbox').addClass("alert-danger");
				};
			}
		});
	};
	
	function createDatabase()
	{
		var host		=	$('#check_hostname').val(),
			user		=	$('#check_username').val(),
			pw 			=	$('#check_password').val(),
			database	=	$('#check_database').val(),
			port		=	$('#check_port').val(),
			mode		=	$('#check_sqlmode').val(),
			ssl			=	$('#checkSslRequire').prop("checked");
		
		document.getElementById('database_errorbox').innerHTML	=	"Creating...";
		$('#database_errorbox').removeClass("alert-success");
		$('#database_errorbox').addClass("alert-info");
		
		$.ajax({
			type: "POST",
			url: "./install/functionsPost.php",
			data: {
				action:		'create_database',
				host:		encodeURIComponent(host),
				user:		encodeURIComponent(user),
				pw:			encodeURIComponent(pw),
				database:	encodeURIComponent(database),
				port:		port,
				mode:		mode,
				ssl:		ssl
			},
			success: function(data)
			{
				var errorbox			=	document.getElementById('database_errorbox');
				errorbox.innerHTML		=	data;
				
				if(errorbox.getElementsByClassName("console_error").length <= 0)
				{
					$('#databaseConnectionBttn').removeClass("btn-success");
					$('#databaseConnectionBttn').addClass("btn-custom");
					$('#databaseConnectionBttn').attr("onclick", "gotToSettings();");
					document.getElementById('databaseConnectionBttn').innerHTML		=	weiter+" <i class=\"fa fa-arrow-right\" aria-hidden=\"true\"></i>";
				};
			}
		});
	};
	
/*
	Create the User / Settings
*/
	function submitSettings()
	{
		var username			=	$('#username').val(),
			password			=	$('#password').val(),
			heading				=	escapeText($('#heading').val()),
			ts3chatname			=	escapeText($('#ts3Chatname').val()),
			regex_check			=	true,
			errorbox			=	document.getElementById('database_errorbox');
		
		var regex 				=	/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{4,99}$/;
		regex_check				= 	regex.test(password);
		
		if(regex_check)
		{
			var regex 			=	/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i;
			regex_check			= 	regex.test(username);
		}
		else
		{
			$('#database_errorbox').removeClass("alert-info");
			$('#database_errorbox').addClass("alert-danger");
			
			errorbox.innerHTML	=	falsches_passwort+"<br/>"+passwort_info;
			return;
		};
		
		if(regex_check)
		{
			$('#database_errorbox').removeClass("alert-danger");
			$('#database_errorbox').addClass("alert-warning");
			
			errorbox.innerHTML	=	"Creating...";
			
			$.ajax({
				type: "POST",
				url: "./install/functionsPost.php",
				data: {
					action:		'create_user',
					user:		username,
					pw:			encodeURIComponent(password),
					heading:	encodeURIComponent(heading),
					tschatname:	encodeURIComponent(ts3chatname)
				},
				success: function(data){
					if(document.getElementById('console_block').getElementsByClassName("console_error").length <= 0)
					{
						$('#database_errorbox').removeClass("alert-danger");
						$('#database_errorbox').removeClass("alert-info");
						$('#database_errorbox').addClass("alert-success");
						
						errorbox.innerHTML 	= 	data;
						
						$('#submitBttn').attr("onclick", "gotToFinish();");
						document.getElementById('submitBttn').innerHTML		=	weiter+" <i class=\"fa fa-arrow-right\" aria-hidden=\"true\"></i>";
					}
					else
					{
						$('#database_errorbox').removeClass("alert-info");
						$('#database_errorbox').addClass("alert-danger");
						
						errorbox.innerHTML 	= 	data;
					};
				}
			});
		}
		else
		{
			$('#database_errorbox').removeClass("alert-info");
			$('#database_errorbox').addClass("alert-danger");
			
			errorbox.innerHTML		=	falscher_benutzer+"<br/>"+benutzer_info;
			return;
		};
	};
	
/*
	Change the Content on the rigth side
*/
	function changeRightContent(filename)
	{
		$('#installContentRight').fadeOut("fast", function() {
			$('#installContentRight').load(filename, function() {
				$('#installContentRight').fadeIn("fast");
			});
		});
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
	admin_settings: Change Config
*/
	function setConfig(which, data)
	{
		switch(which)
		{
			case "language":
				var postData	=	{
					LANGUAGE: 				escapeText(data)
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
			var returnVal;
			$.ajax({
				type: "POST",
				url: "./php/functions/functionsPost.php",
				data: {
					action:		'setConfig',
					data:		JSON.stringify(postData)
				},
				async: false,
				success: function(data){
					if(data == "done")
					{
						returnVal	=	true;
					}
					else
					{
						returnVal	=	false;
					}
				}
			});
			return returnVal;
		};
	};