 <?php
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
		Includes
	*/
	require_once("lang.php");
	require_once("functions.php");
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys				=	getKeys();
	$mysql_modul			=	getModuls();
	
	/*
		Start the PHP Session
	*/
	session_start();
	
	/*
		Get Client Permissions
	*/
	$user_right				=	getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	
	/*
		Get Link information
	*/
	$urlData				=	explode("?", $_SERVER['HTTP_REFERER']);
	$serverInstanz			=	$urlData[2];
	$serverId				=	$urlData[3];
	
	/*
		Is Client logged in?
	*/
	if($_SESSION['login'] != $mysql_keys['login_key'])
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	if($serverInstanz == '' || $serverId == '' || $mysql_modul['webinterface'] != 'true')
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	/*
		Teamspeak Functions
	*/
	$tsAdmin = new ts3admin($ts3_server[$serverInstanz]['ip'], $ts3_server[$serverInstanz]['queryport']);
	
	if($tsAdmin->getElement('success', $tsAdmin->connect()))
	{
		// Im Teamspeak Einloggen
		$tsAdmin->login($ts3_server[$serverInstanz]['user'], $ts3_server[$serverInstanz]['pw']);
		
		// Server Select
		$tsAdmin->selectServer($serverId, 'serverId', true);
		
		// Server Info Daten abfragen
		$server		= 	$tsAdmin->serverInfo();
		
		// Keine Rechte
		if(((strpos($user_right['ports']['right_web_server_view'][$serverInstanz], $server['data']['virtualserver_port']) === false || strpos($user_right['ports']['right_web_server_protokoll'][$serverInstanz], $server['data']['virtualserver_port']) === false)
				&& $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server']) || $user_right['right_web'] != $mysql_keys['right_web'])
		{
			echo '<script type="text/javascript">';
			echo 	'window.location.href="'.$urlData[0].'";';
			echo '</script>';
		};
	}
	else
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
?>

<!-- Protokol -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-list"></i> <?php echo $language['ts_protokol']; ?></h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<table class="table" id="ts3_protokol_table">
					<thead>
						<tr>
							<th style="width:20%;text-align:center;"><?php echo $language['date']; ?></th>
							<th style="width:10%;text-align:center;" class="hidden-sm-down"><?php echo $language['level']; ?></th>
							<th style="width:20%;text-align:center;" class="hidden-lg-down"><?php echo $language['type']; ?></th>
							<th style="width:10%;text-align:center;" class="hidden-lg-down"><?php echo $language['server_id']; ?></th>
							<th style="width:40%;text-align:center;"><?php echo $language['message']; ?></th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<!-- Sprachdatein laden -->
<script>
	var success						=	'<?php echo $language['success']; ?>';
	var failed						=	'<?php echo $language['failed']; ?>';
	
	var port 						= 	'<?php echo $server['data']['virtualserver_port']; ?>';
	var instanz						=	'<?php echo $serverInstanz; ?>';
</script>

<script>
	$(function()
	{
		// Query Log abfragen lassen
		setTimeout(getQueryInformations, 500);
		var refresh_log = setInterval(function() {
			if (document.getElementById('ts3_protokol_table'))
			{
				var dataString 			= 	'action=portServerQuerylog&port='+port+'&instanz='+instanz;
				$.ajax({
					type: "POST",
					url: "functionsTeamspeakPost.php",
					data: dataString,
					dataTyp: "json",
					cache: false,
					success: function(data){
						// Inhalt anzeigen lassen
						$("#ts3_protokol_table").find("tr:gt(0)").remove();
						$('#ts3_protokol_table tr:last').after(data);
					}
				});
			}
			else
			{
				clearInterval(refresh_log);
			};
		}, 10000);
	});
	
	function getQueryInformations()
	{
		// Daten übergeben
		if (document.getElementById('ts3_protokol_table'))
		{
			var dataString 			= 	'action=portServerQuerylog&port='+port+'&instanz='+instanz;
			$.ajax({
				type: "POST",
				url: "functionsTeamspeakPost.php",
				data: dataString,
				dataTyp: "json",
				cache: false,
				success: function(data){
					// Inhalt anzeigen lassen
					$("#ts3_protokol_table").find("tr:gt(0)").remove();
					$('#ts3_protokol_table tr:last').after(data);
				}
			});
		}
		else
		{
			clearInterval(refresh_log);
		};
	};
</script>
<script src="js/sonstige/preloader.js"></script>