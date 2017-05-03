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
	$urlData				=	explode("\?", $_SERVER['HTTP_REFERER'], -1);
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
		$server = $tsAdmin->serverInfo();
		
		// Token abfragen
		$tokens		=	$tsAdmin->getElement('data', $tsAdmin->tokenList());
		
		// Channel abfragen
		$channels	=	$tsAdmin->getElement('data', $tsAdmin->channelList("-topic -flags -voice -limits -icon"));
		
		// Server- und Channelgruppen abfragen
		$sgroups	=	$tsAdmin->getElement('data', $tsAdmin->serverGroupList());
		$cgroups	=	$tsAdmin->getElement('data', $tsAdmin->channelGroupList());
		
		// Keine Rechte
		if(((strpos($user_right['ports']['right_web_server_view'][$serverInstanz], $server['data']['virtualserver_port']) === false || strpos($user_right['ports']['right_web_server_token'][$serverInstanz], $server['data']['virtualserver_port']) === false)
				&& $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server']) || $user_right['right_web'] != $mysql_keys['right_web'])
		{
			echo '<script type="text/javascript">';
			echo 	'window.location.href="'.$urlData[0].'";';
			echo '</script>';
		};
		
		// Channels
		if(!empty($channels))
		{
			foreach($channels AS $key=>$value)
			{
				if ($value['pid'] == 0)
				{
					if(preg_match("^\[(.*)spacer([\w\p{L}\d]+)?\]^u", $value['channel_name'], $treffer) AND $value['pid']==0)
					{
						$getspacer=explode($treffer[0], $value['channel_name']);
						$checkspacer=$getspacer[1][0].$getspacer[1][0].$getspacer[1][0];
						if($treffer[1]=="*" or strlen($getspacer[1])==3 AND $checkspacer==$getspacer[1])
						{
							$spacer='';
							for($i=0; $i<=50; $i++)
							{
								if(strlen($spacer)<50)
								{
									$spacer.=$getspacer[1];
								}
								else
								{
									break;
								};
							};
							$channelTree .= "<option value='".$value['cid']."'>".htmlspecialchars($spacer)."</option>";
						}
						else
						{
							$spacer=explode($treffer[0], $value['channel_name']);
							$channelTree .= "<option value='".$value['cid']."'>".htmlspecialchars($spacer[1])."</option>";
						};
					}
					else
					{
						$channelTree .= "<option value='".$value['cid']."'>".htmlspecialchars($value['channel_name'])."</option>";
					};
				};
			};
		};
		
		// SubChannels
		if(!empty($channels))
		{
			foreach($channels AS $key=>$value)
			{
				if ($value['pid'] != 0)
				{
					if(preg_match("^\[(.*)spacer([\w\p{L}\d]+)?\]^u", $value['channel_name'], $treffer) AND $value['pid']==0)
					{
						$getspacer=explode($treffer[0], $value['channel_name']);
						$checkspacer=$getspacer[1][0].$getspacer[1][0].$getspacer[1][0];
						if($treffer[1]=="*" or strlen($getspacer[1])==3 AND $checkspacer==$getspacer[1])
						{
							$spacer='';
							for($i=0; $i<=50; $i++)
							{
								if(strlen($spacer)<50)
								{
									$spacer.=$getspacer[1];
								}
								else
								{
									break;
								};
							};
							$subChannelTree .= "<option value='".$value['cid']."'>".htmlspecialchars($spacer)."</option>";
						}
						else
						{
							$spacer=explode($treffer[0], $value['channel_name']);
							$subChannelTree .= "<option value='".$value['cid']."'>".htmlspecialchars($spacer[1])."</option>";
						};
					}
					else
					{
						$subChannelTree .= "<option value='".$value['cid']."'>".htmlspecialchars($value['channel_name'])."</option>";
					};
				};
			};
		};
		
		// Channelgruppen
		if(!empty($cgroups))
		{
			$cgroup			= "";
			foreach($cgroups AS $key => $value)
			{
				if ($value['type'] != '2' AND $value['type'] != '0')
				{
					$cgroup .= "<option group=\'cgroup\' value=\'ts3-channelgroup-id-" . $value['cgid'] . "\'>" . htmlspecialchars($value['name']) . "</option>";
				};
			};
		};
		
		// Servergruppen
		if(!empty($sgroups))
		{
			$sgroup			= "";
			foreach($sgroups AS $key => $value)
			{
				if ($value['type'] != '2' AND $value['type'] != '0')
				{
					$sgroup .= "<option group=\'sgroup\' value=\'ts3-servergroup-id-" . $value['sgid'] . "\'>" . htmlspecialchars($value['name']) . "</option>";
				};
			};
		};
	}
	else
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
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
				<select id="tokenChooseKindOfGroup" class="form-control token-select" onChange="changeTokenSelectmenu()">
					<option value="0" selected><?php echo $language['ts_sgroup']; ?></option>
					<option value="1"><?php echo $language['ts_cgroup']; ?></option>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<select id="tokenChooseGroup" class="form-control token-select">
					<?php echo $sgroup; ?>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<select id="tokenChooseChannel" class="form-control" disabled>
					<option value="" style="display:none;" selected><?php echo $language['ts_select_channel']; ?></option>
					<optgroup label="<?php echo $language['ts_channel']; ?>">
						<?php echo $channelTree; ?>
					</optgroup>
					<optgroup label="<?php echo $language['ts_sub_channel']; ?>">
						<?php echo $subChannelTree; ?>
					</optgroup>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-4 col-md-12">
				<input id="tokenChooseDescription" class="form-control" type="text" placeholder="<?php echo $language['description']; ?>">
			</div>
			<div class="col-lg-4 col-md-12">
				<input id="tokenChooseAnzahl" class="form-control" type="number" placeholder="<?php echo $language['how_much']; ?>">
			</div>
			<div class="col-lg-4 col-md-12">
				<button style="margin-top: 10px;margin-bottom: 10px;width: 100%;" onClick="createToken('<?php echo $server['data']['virtualserver_port']; ?>')" class="btn btn-success"><i class="fa fa-check"></i> <?php echo $language['create']; ?></button>
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
		<table class="table table-condensed">
			<tbody id="channelTokenTable">
				<?php if(!empty($tokens))
				{
					$tokenBackground					=	false;
					foreach($tokens AS $token)
					{
						// Variabeln gestalten
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
							$value['group']				=	$language['ts_sgroup'];
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
						}; ?>
						
						<tr id="<?php echo str_replace("+", "", $token["token"]); ?>" style="<?php echo ($tokenBackground) ? 'background-color:rgba(0,0,0,0.06);' : ''; ?>">
							<td>
								<div class="hover">
									<span class="tokenlist-headline">
										<?php echo $language['token_type']; ?>
									</span>
									<span class="tokenlist-subline">
										<?php echo $value["group"]; ?>
									</span>
								</div>
								<div class="hover">
									<span class="tokenlist-headline">
										<?php echo $language['token_groupname']; ?>
									</span>
									<span class="tokenlist-subline">
										<?php echo $value["scgroup"]; ?>
									</span>
								</div>
								<div class="hover">
									<span class="tokenlist-headline">
										<?php echo $language['ts_channel']; ?>
									</span>
									<span class="tokenlist-subline">
										<?php echo $value["channel"]; ?>
									</span>
								</div>
								<div class="hover">
									<span class="tokenlist-headline">
										<?php echo $language['ts3_create_on']; ?>
									</span>
									<span class="tokenlist-subline">
										<?php echo $value["token_created"]; ?>
									</span>
								</div>
								<div class="hover">
									<span class="tokenlist-headline">
										Token
									</span>
									<span class="tokenlist-subline">
										<?php echo $token["token"]; ?>
									</span>
								</div>
								<div class="hover">
									<span class="tokenlist-headline">
										<?php echo $language['description']; ?>
									</span>
									<span class="tokenlist-subline">
										<?php echo htmlspecialchars($value["token_description"]); ?>
									</span>
								</div>
								<div class="hover">
									<span class="tokenlist-headline">
										<?php echo $language['actions']; ?>
									</span>
									<span class="tokenlist-subline">
										<i onClick="deleteToken('<?php echo $token["token"]; ?>', '<?php echo $server['data']['virtualserver_port']; ?>')" class="fa fa-fw fa-trash"></i>
									</span>
								</div>
							</td>
						</tr>
					<?php $tokenBackground	=	!$tokenBackground;
					}
				}
				else 
				{ ?>
					<tr style="text-align:center;" id="noToken">
						<td>
							<span>
								<?php echo $language['token_none']; ?>
							</span>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>

<!-- Sprachdatein laden -->
<script>
	var success						=	'<?php echo $language['success']; ?>';
	var failed						=	'<?php echo $language['failed']; ?>';
	
	var sgroup						=	'<?php echo $sgroup; ?>';
	var cgroup						=	'<?php echo $cgroup; ?>';
	
	var serverId					=	'<?php echo $serverId; ?>';
	var instanz						=	'<?php echo $serverInstanz; ?>';
	var port 						= 	'<?php echo $server['data']['virtualserver_port']; ?>';
	
	var description					=	'<?php echo $language['description']; ?>';
	var actions						=	'<?php echo $language['actions']; ?>';
	var ts3_create_on				=	'<?php echo $language['ts3_create_on']; ?>';
	var ts_channel					=	'<?php echo $language['ts_channel']; ?>';
	var token_groupname				=	'<?php echo $language['token_groupname']; ?>';
	var token_type					=	'<?php echo $language['token_type']; ?>';
</script>
<script src="js/sonstige/preloader.js"></script>