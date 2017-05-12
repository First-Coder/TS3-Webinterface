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
		Teamspeak Functions
	*/
	$tsAdmin = new ts3admin($ts3_server[$LinkInformations['instanz']]['ip'], $ts3_server[$LinkInformations['instanz']]['queryport']);
	
	if($tsAdmin->getElement('success', $tsAdmin->connect()))
	{
		$tsAdmin->login($ts3_server[$LinkInformations['instanz']]['user'], $ts3_server[$LinkInformations['instanz']]['pw']);
		$tsAdmin->selectServer($LinkInformations['sid'], 'serverId', true);
		
		$server		= 	$tsAdmin->serverInfo();
		$banlist	=	$tsAdmin->banList();
		
		if(((!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_view') || !isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_bans'))
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

<!-- Bann hinzufügen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-edit"></i> <?php echo $language['ban_create']; ?>
		</h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 margin-row">
				<label class="c-input c-radio">
					<input id="banName" name="banRadio" type="radio" checked>
					<span class="c-indicator"></span>
					<?php echo $language['ban_name']; ?>
				</label>
			</div>
			<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 margin-row">
				<label class="c-input c-radio">
					<input id="banIp" name="banRadio" type="radio">
					<span class="c-indicator"></span>
					<?php echo $language['ban_ip']; ?>
				</label>
			</div>
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin-row">
				<label class="c-input c-radio">
					<input name="banRadio" type="radio">
					<span class="c-indicator"></span>
					<?php echo $language['ban_unique_id']; ?>
				</label>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<input id="banInput" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['ban_name_uid_ip']; ?>">
			</div>
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<input id="banReason" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['reason']; ?>">
			</div>
			<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
				<input id="banTime" type="number" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['ban_time']; ?>">
			</div>
			<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
				<button onClick="addBan();" class="btn btn-danger small-top-bottom-margin" style="width:100%;"><i class="fa fa-ban" aria-hidden="true"></i> <?php echo $language['ban']; ?></button>
			</div>
		</div>
	</div>
</div>

<!-- Banliste -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-list"></i> <?php echo $language['bans_list']; ?>
		</h4>
	</div>
	<div class="card-block">
		<table id="banTable" data-card-view="true" data-classes="table-no-bordered table-hover table"
			data-striped="true" data-pagination="true" data-search="true" data-row-style="loglevelColor" data-click-to-select="true">
			<thead>
				<tr>
					<th data-field="name"><?php echo $language['name']; ?></th>
					<th data-field="id"><?php echo $language['ban_id']; ?></th>
					<th data-field="ip"><?php echo $language['ip_adress']; ?></th>
					<th data-field="uniquie_id"><?php echo $language['uniquie_id']; ?></th>
					<th data-field="admin"><?php echo $language['banlist_admin']; ?></th>
					<th data-field="reason"><?php echo $language['reason']; ?></th>
					<th data-field="create_on"><?php echo $language['create_on']; ?></th>
					<th data-field="duration"><?php echo $language['duration']; ?></th>
					<th data-field="actions"><?php echo $language['actions']; ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if(!empty($banlist['data']))
				{
					foreach($banlist['data'] AS $bancount=>$ban)
					{ ?>
						<tr>
							<td><?php xssEcho($ban["name"]); ?></td>
							<td><?php echo $ban["banid"]; ?></td>
							<td><?php xssEcho($ban["ip"]); ?></td>
							<td><?php xssEcho($ban["uid"]); ?></td>
							<td><?php xssEcho($ban["invokername"]); ?></td>
							<td><?php xssEcho($ban["reason"]); ?></td>
							<td><?php echo date('d.m.Y - H:i:s', $ban["created"]); ?></td>
							<td>
								<?php echo ($ban["duration"] == 0) ? $language['unlimited'] : $ban["duration"]/60 . " " . $language['minutes']; ?>
							</td>
							<td>
								<button style="margin-bottom: 0px;padding: .0rem .75rem;" class="btn btn-sm btn-danger" onClick="deleteBan('<?php echo $ban["banid"]; ?>');">
									<i class="fa fa-fw fa-trash"></i> <?php echo $language['delete']; ?>
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
<script>
	var port 						= 	'<?php echo $server['data']['virtualserver_port']; ?>',
		instanz						=	'<?php echo $LinkInformations['instanz']; ?>';
	
	$('#banTable').bootstrapTable({
		formatNoMatches: function ()
		{
			return lang.ts_bans_no_bans;
		}
	});
</script>
<script src="js/webinterface/teamspeak.js"></script>
<script src="js/sonstige/preloader.js"></script>