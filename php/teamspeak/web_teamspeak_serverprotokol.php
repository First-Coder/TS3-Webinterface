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
	require_once(__DIR__."/../../config/config.php");
	require_once(__DIR__."/../../config/instance.php");
	require_once(__DIR__."/../../lang/lang.php");
	require_once(__DIR__."/../../php/functions/functions.php");
	require_once(__DIR__."/../../php/functions/functionsSql.php");
	require_once(__DIR__."/../../php/functions/functionsTeamspeak.php");
	
	/*
		Variables
	*/
	$LoggedIn			=	(checkSession()) ? true : false;
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys				=	getKeys();
	$mysql_modul			=	getModuls();
	
	/*
		Is Client logged in?
	*/
	if($_SESSION['login'] != $mysql_keys['login_key'])
	{
		reloadSite();
	};
	
	/*
		Get Client Permissions
	*/
	$user_right				=	getUserRights('pk', $_SESSION['user']['id']);
	
	/*
		Get Link information
	*/
	$linkInformations	=	getLinkInformations();
	
	if(empty($linkInformations) || $mysql_modul['webinterface'] != 'true')
	{
		reloadSite(RELOAD_TO_MAIN);
	};
	
	/*
		Teamspeak Functions
	*/
	$tsAdmin = new ts3admin($ts3_server[$linkInformations['instanz']]['ip'], $ts3_server[$linkInformations['instanz']]['queryport']);
	
	if($tsAdmin->getElement('success', $tsAdmin->connect()))
	{
		$tsAdmin->login($ts3_server[$linkInformations['instanz']]['user'], $ts3_server[$linkInformations['instanz']]['pw']);
		$tsAdmin->selectServer($linkInformations['sid'], 'serverId', true);
		
		$server		= 	$tsAdmin->serverInfo();
		
		if(((!isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_view') || !isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_protokoll'))
				&& $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server']) || $user_right['right_web']['key'] != $mysql_keys['right_web'])
		{
			reloadSite(RELOAD_TO_SERVERVIEW);
		};
	}
	else
	{
		reloadSite(RELOAD_TO_MAIN);
	};
?>

<!-- Protokol -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-list"></i> <?php echo $language['protokoll']; ?></h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<table class="table" id="ts3_protokol_table">
					<thead>
						<th style="width:20%;"><?php echo $language['date']; ?></th>
						<th style="width:10%;" class="hidden-sm-down"><?php echo $language['level']; ?></th>
						<th style="width:20%;" class="hidden-lg-down"><?php echo $language['type']; ?></th>
						<th style="width:10%;" class="hidden-lg-down"><?php echo $language['server_id']; ?></th>
						<th style="width:40%;"><?php echo $language['message']; ?></th>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script>
	$(function()
	{
		var port 						= 	'<?php echo $server['data']['virtualserver_port']; ?>',
			instanz						=	'<?php echo $linkInformations['instanz']; ?>';
		
		setTimeout(getQueryInformations, 500, port, instanz);
		var refresh_log = setInterval(function() {
			if (document.getElementById('ts3_protokol_table'))
			{
				getQueryInformations(port, instanz);
			}
			else
			{
				clearInterval(refresh_log);
			};
		}, 10000);
	});
	
	function getQueryInformations(port, instanz)
	{
		if (document.getElementById('ts3_protokol_table'))
		{
			$.ajax({
				type: "POST",
				url: "./php/functions/functionsTeamspeakPost.php",
				data: {
					action:		'portServerQuerylog',
					port:		port,
					instanz:	instanz
				},
				success: function(data){
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