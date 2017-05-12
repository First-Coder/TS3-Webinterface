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
	require_once(__DIR__."/../../lang/lang.php");
	require_once(__DIR__."/../../php/functions/functions.php");
	require_once(__DIR__."/../../php/functions/functionsSql.php");
	
	/*
		Variables
	*/
	$LoggedIn			=	(checkSession()) ? true : false;
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys			=	getKeys();
	$mysql_modul		=	getModuls();
	
	/*
		Is Client logged in?
	*/
	if($_SESSION['login'] != $mysql_keys['login_key'] || $mysql_modul['webinterface'] != 'true')
	{
		reloadSite();
	};
	
	/*
		Get Client Permissions
	*/
	$user_right		=	getUserRights('pk', $_SESSION['user']['id']);
	
	/*
		Has the Client the Permission
	*/
	if($user_right['right_web']['key'] != $mysql_keys['right_web'] || $user_right['right_web_server_create']['key'] != $mysql_keys['right_web_server_create'] || $mysql_modul['webinterface'] != 'true')
	{
		reloadSite();
	};
	
	/*
		Search files in Folder wantserver/
	*/
	$wantServer = scandir(__DIR__."/../../files/wantServer/");
?>

<!-- Show list with Server Requests -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-list"></i> <?php echo $language['server_requests']; ?>
		</h4>
	</div>
	<div class="card-block">
		<table id="requestsTable" data-card-view="false" data-classes="table-no-bordered table-hover table"
			data-striped="true" data-pagination="true" data-search="true">
			<thead>
				<tr>
					<th data-field="client"><?php echo $language['client']; ?></th>
					<th data-field="port" data-align="center"><?php echo $language['port']; ?></th>
					<th data-field="actions" data-align="center" data-halign="left"><?php echo $language['actions']; ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if(!empty($wantServer))
				{
					foreach ($wantServer as $datei)
					{
						if($datei != "." && $datei != "..")
						{
							$information		=	explode("_", $datei);
							$information[1]		=	str_replace(".txt", "", $information[1]);
							echo '<tr>
									<td>'.xssSafe($information[0]).'</td>
									<td>'.xssSafe($information[1]).'</td>
									<td>
										<button class="btn btn-success btn-sm mini-left-right-margin" onClick="showServerRequest(\''.$datei.'\');"><i class="fa fa-edit"></i> <font class="hidden-md-down">'.$language['edit'].'</font>
										<button class="btn btn-danger btn-sm mini-left-right-margin" onClick="deleteWantServer(\''.$datei.'\');"><i class="fa fa-trash"></i> <font class="hidden-md-down">'.strtolower($language['delete']).'</font>
									</td>
								</tr>';
						};
					};
				}; ?>
			</tbody>
		</table>
	</div>
</div>

<!-- Javascripte Laden -->
<script src="js/bootstrap/bootstrap-table.js"></script>
<script src="js/webinterface/teamspeak.js"></script>
<script>
	$('#requestsTable').bootstrapTable({
		formatNoMatches: function ()
		{
			return lang.filelist_none;
		}
	});
</script>
<script src="js/sonstige/preloader.js"></script>