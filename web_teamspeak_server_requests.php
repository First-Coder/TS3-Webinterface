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
		Start the PHP Session
	*/
	session_start();
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys			=	getKeys();
	$mysql_modul		=	getModuls();
	
	/*
		Is Client logged in?
	*/
	$urlData				=	explode("?", $_SERVER['HTTP_REFERER']);
	if($_SESSION['login'] != $mysql_keys['login_key'] || $mysql_modul['webinterface'] != 'true')
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	/*
		Get Client Permissions
	*/
	$user_right				=	getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	
	/*
		Has the Client the Permission
	*/
	if($user_right['right_web'] != $mysql_keys['right_web'] || $user_right['right_web_server_create'] != $mysql_keys['right_web_server_create'])
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	/*
		Search files in Folder wantserver/
	*/
	$wantServer = scandir('wantServer/');
?>

<!-- Show list with Server Requests -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<div class="pull-xs-left">
				<i class="fa fa-list"></i> <?php echo $language['server_requests']; ?>
			</div>
			<div style="clear:both;"></div>
		</h4>
	</div>
	<div class="card-block">
		<table class="table table-condensed table-hover">
			<tbody>
				<?php foreach ($wantServer as $datei)
				{
					if($datei != "." && $datei != "..")
					{
						$information		=	explode("_", $datei);
						$information[1]		=	str_replace(".txt", "", $information[1]); ?>
						<tr onClick="showServerRequest('<?php echo $datei; ?>');" style="cursor: pointer;">
							<td>Account: <?php echo htmlspecialchars($information[0]); ?></td>
							<td>Port: <?php echo htmlspecialchars($information[1]); ?></td>
						</tr>
					<?php };
				}; ?>
			</tbody>
		</table>
	</div>
</div>

<!-- Javascripte Laden -->
<script src="js/webinterface/teamspeak.js"></script>
<script src="js/sonstige/preloader.js"></script>