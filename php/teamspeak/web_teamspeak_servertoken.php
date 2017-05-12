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
		
		$server 		= 	$tsAdmin->serverInfo();
		$tokens			=	$tsAdmin->getElement('data', $tsAdmin->tokenList());
		$channels		=	$tsAdmin->getElement('data', $tsAdmin->channelList("-topic -flags -voice -limits -icon"));
		$sgroups		=	$tsAdmin->getElement('data', $tsAdmin->serverGroupList());
		$cgroups		=	$tsAdmin->getElement('data', $tsAdmin->channelGroupList());
		
		$channelTree	=	getChannelTree($channels, false);
		$subChannelTree	=	getChannelTree($channels, true);
		
		$sgroup			=	getGroupTree($sgroups, false);
		$cgroup			=	getGroupTree($cgroups, true);
		
		if(((!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_view') || !isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_token'))
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

<!-- Token erstellen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-edit"></i> <?php echo $language['token_create']; ?>
		</h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-lg-4 col-md-12">
				<select id="tokenChooseKindOfGroup" class="form-control small-top-bottom-margin c-select" onChange="changeTokenSelectmenu()">
					<option value="0" selected><?php echo $language['sgroup']; ?></option>
					<option value="1"><?php echo $language['cgroup']; ?></option>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<select id="tokenChooseGroup" class="form-control small-top-bottom-margin c-select">
					<?php echo $sgroup; ?>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<select id="tokenChooseChannel" class="form-control small-top-bottom-margin c-select" disabled>
					<option value="" style="display:none;" selected><?php echo $language['select_channel']; ?></option>
					<optgroup label="<?php echo $language['channel']; ?>">
						<?php echo $channelTree; ?>
					</optgroup>
					<optgroup label="<?php echo $language['sub_channel']; ?>">
						<?php echo $subChannelTree; ?>
					</optgroup>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<input id="tokenChooseDescription" class="form-control small-top-bottom-margin" type="text" placeholder="<?php echo $language['description']; ?>">
			</div>
			<div class="col-lg-4 col-md-12">
				<input id="tokenChooseAnzahl" class="form-control small-top-bottom-margin" type="number" placeholder="<?php echo $language['how_much']; ?>">
			</div>
			<div class="col-lg-4 col-md-12">
				<button style="width: 100%;" onClick="createToken()" class="small-top-bottom-margin btn btn-success"><i class="fa fa-check"></i> <?php echo $language['create']; ?></button>
			</div>
		</div>
	</div>
</div>

<!-- Tokenliste -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-key"></i> <?php echo $language['token_list']; ?></font>
		</h4>
	</div>
	<div class="card-block">
		<table id="channelTokenTable" data-card-view="true" data-classes="table-no-bordered table-hover table"
			data-striped="true" data-pagination="true" data-search="true" data-row-style="loglevelColor" data-click-to-select="true">
			<thead>
				<tr>
					<th data-field="type"><?php echo $language['type']; ?></th>
					<th data-field="groupname"><?php echo $language['groupname']; ?></th>
					<th data-field="channel"><?php echo $language['channel']; ?></th>
					<th data-field="create_on"><?php echo $language['create_on']; ?></th>
					<th data-field="token"><?php echo $language['token']; ?></th>
					<th data-field="description"><?php echo $language['description']; ?></th>
					<th data-field="actions"><?php echo $language['actions']; ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
					if(!empty($tokens))
					{
						foreach($tokens AS $token)
						{
							$value							=	array();
							if ($token['token_type'] == 1)
							{
								$value['group']				=	$language['ts_cgroup'];
								foreach($cgroups AS $cgroup2)
								{
									if($cgroup2['cgid'] == $token['token_id1'])
									{
										$value['scgroup']	=	$cgroup2['name'];
									};
								};
								foreach($channels AS $channel)
								{
									if($channel['cid'] == $token['token_id2'])
									{
										$value['channel']	=	$channel['channel_name'];
									};
								};
								$value['token_created']		=	date('d.m.Y - H:i', $token['token_created']);
								$value['token_description']	=	$token['token_description'];
							}
							else
							{
								$value['group']				=	$language['sgroup'];
								foreach($sgroups AS $sgroup2)
								{
									if($sgroup2['sgid'] == $token['token_id1'])
									{
										$value['scgroup']	=	$sgroup2['name'];
									};
								};
								$value['channel']			=	'-';
								$value['token_created']		=	date('d.m.Y - H:i', $token['token_created']);
								$value['token_description']	=	$token['token_description'];
							};
							
							echo "<tr>
									<td>".$value["group"]."</td>
									<td>".xssSafe($value["scgroup"])."</td>
									<td>".xssSafe($value["channel"])."</td>
									<td>".$value["token_created"]."</td>
									<td>".$token["token"]."</td>
									<td>".xssSafe($value["token_description"])."</td>
									<td><button style=\"margin-bottom: 0px;padding: .0rem .75rem;\" class=\"btn btn-sm btn-danger\" onClick=\"deleteToken('".$token["token"]."')\"><i class=\"fa fa-fw fa-trash\"></i> ".$language['delete']."</button></td>
								</tr>";
						};
					};
				?>
			</tbody>
		</table>
	</div>
</div>

<script src="js/bootstrap/bootstrap-table.js"></script>
<script>
	var sgroup						=	'<?php echo str_replace ("'", "\'", $sgroup); ?>',
		cgroup						=	'<?php echo str_replace ("'", "\'", $cgroup); ?>',
		serverId					=	'<?php echo $LinkInformations['sid']; ?>',
		instanz						=	'<?php echo $LinkInformations['instanz']; ?>',
		port 						= 	'<?php echo $server['data']['virtualserver_port']; ?>';
	
	$('#channelTokenTable').bootstrapTable({
		formatNoMatches: function ()
		{
			return lang.token_none;
		}
	});
</script>
<script src="js/webinterface/teamspeak.js"></script>
<script src="js/sonstige/preloader.js"></script>