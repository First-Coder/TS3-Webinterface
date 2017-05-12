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
	$user_right			=	getUserRights('pk', $_SESSION['user']['id']);
	
	/*
		Get Link information
	*/
	$LinkInformations	=	getLinkInformations();
	
	if(empty($LinkInformations) || $mysql_modul['webinterface'] != 'true')
	{
		reloadSite(RELOAD_TO_MAIN);
	};
	
	/*
		Teamspeak Stuff
	*/
	$tsAdmin 			= 	new ts3admin($ts3_server[$LinkInformations['instanz']]['ip'], $ts3_server[$LinkInformations['instanz']]['queryport']);
	
	if($tsAdmin->getElement('success', $tsAdmin->connect()))
	{
		$tsAdmin->login($ts3_server[$LinkInformations['instanz']]['user'], $ts3_server[$LinkInformations['instanz']]['pw']);
		$tsAdmin->selectServer($LinkInformations['sid'], 'serverId', true);
		
		$server				= 	$tsAdmin->serverInfo();
		$clientList			=	$tsAdmin->clientDbList(0, GET_DB_CLIENTS);
		$clientOnlineList	=	$tsAdmin->clientList();
		
		if(((!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_view') || !isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_clients'))
				&& $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server']) || $user_right['right_web']['key'] != $mysql_keys['right_web'])
		{
			reloadSite(RELOAD_TO_SERVERVIEW);
		};
	}
	else
	{
		reloadSite(RELOAD_TO_MAIN);
	};
	
	/*
		Set language for the Datapicker
	*/
	if(LANGUAGE == 'german')
	{
		$languageDataPicker	=	'de';
	}
	else
	{
		$languageDataPicker	=	'en';
	};
?>

<!-- Benutzerliste -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-list"></i> <?php echo $language['all_registred_user']; ?>
		</h4>
	</div>
	<div class="card-block">
		<div class="form-group">
			<label><?php echo $language['delete_inactive_clients']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-clock-o" aria-hidden="true"></i>
				</span>
				<input type="text" id="datapickerClients" class="form-control datetimepicker">
				<button onClick="AreYouSure('<?php echo $language['user_delete']; ?>', 'deleteDBClientTime();')" class="btn btn-danger button-transition"><i class="fa fa-trash" aria-hidden="true"></i> <?php echo $language['user_delete']; ?></button>
			</div>
			<small class="form-text text-muted"><?php echo $language['delete_inactive_clients_info']; ?></small>
		</div>
		
		<table id="clientsTable" data-card-view="true" data-classes="table-no-bordered table-hover table"
			data-striped="true" data-pagination="true" data-search="true" data-row-style="loglevelColor" data-click-to-select="true">
			<thead>
				<tr>
					<th data-field="client"><?php echo $language['client']; ?></th>
					<th data-field="status"><?php echo $language['status']; ?></th>
					<th data-field="first_con"><?php echo $language['first_con']; ?></th>
					<th data-field="last_con"><?php echo $language['last_con']; ?></th>
					<th data-field="ip"><?php echo $language['ip_adress']; ?></th>
					<th data-field="connection"><?php echo $language['connections']; ?></th>
					<th data-field="description"><?php echo $language['description']; ?></th>
					<th data-field="client_id"><?php echo $language['client']; ?> ID</th>
					<th data-field="id"><?php echo $language['client']; ?> DB ID</th>
					<th data-field="actions"><?php echo $language['actions']; ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($clientList['data'] AS $client)
				{
					if($client['client_unique_identifier'] != 'ServerQuery') { ?>
						<tr>
							<td><?php xssEcho($client['client_nickname']); ?></td>
							<td>
								<?php
									$isClientOnline				=	false;
									
									foreach($clientOnlineList['data'] AS $onlineClient)
									{
										if($onlineClient['client_database_id'] == $client['cldbid'])
										{
											$isClientOnline		=	true;
											break;
										};
									};
									
									if($isClientOnline)
									{
										echo '<span class="label label-success">'.$language['online'].'</span>';
									}
									else
									{
										echo '<span class="label label-danger">'.$language['offline'].'</span>';
									};
								?>
							</td>
							<td><?php echo date('Y-m-d H:i:s', $client['client_created']); ?></td>
							<td><?php echo date('Y-m-d H:i:s', $client['client_lastconnected']); ?></td>
							<td><?php echo $client['client_lastip']; ?></td>
							<td><?php echo $client['client_totalconnections']; ?></td>
							<td><?php xssEcho($client['client_description']); ?></td>
							<td><?php echo $client['client_unique_identifier']; ?></td>
							<td><?php echo $client['cldbid']; ?></td>
							<td>
								<button style="margin-bottom: 0px;padding: .0rem .75rem;" class="btn btn-sm btn-danger" onClick="deleteDBClient('<?php echo $client['cldbid']; ?>');">
									<i class="fa fa-fw fa-trash"></i> <?php echo $language['user_delete']; ?>
								</button>
							</td>
						</tr>
					<?php };
				}; ?>
			</tbody>
		</table>
	</div>
</div>

<script src="js/bootstrap/bootstrap-table.js"></script>
<script src="js/bootstrap/moment-with-locales.js"></script>
<script src="js/bootstrap/bootstrap-datetimepicker.min.js"></script>
<script language="JavaScript">
	var port 						= 	'<?php echo $server['data']['virtualserver_port']; ?>',
		instanz						=	'<?php echo $LinkInformations['instanz']; ?>';
	
	$('#clientsTable').bootstrapTable({
		formatNoMatches: function ()
		{
			return '<?php echo $language['no_entrys']; ?>';
		}
	});
	
	$('.datetimepicker').datetimepicker({
		locale: '<?php echo $languageDataPicker; ?>',
		calendarWeeks: false,
		showClear: true,
		showClose: true,
		maxDate: "moment",
		icons: {
			time: "fa fa-clock-o",
			date: "fa fa-calendar",
			up: "fa fa-arrow-up",
			down: "fa fa-arrow-down",
			clear: 'fa fa-trash',
			close: 'fa fa-close',
			previous: 'fa fa-arrow-left',
			next: 'fa fa-arrow-right'
		}
	});
</script>
<script src="js/sonstige/preloader.js"></script>