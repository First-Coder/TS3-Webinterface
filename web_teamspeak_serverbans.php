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
		Is Client logged in?
	*/
	if($_SESSION['login'] != $mysql_keys['login_key'])
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
		Get Link information
	*/
	$urlData				=	split("\?", $_SERVER['HTTP_REFERER'], -1);
	$serverInstanz			=	$urlData[2];
	$serverId				=	$urlData[3];
	
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
		
		$banlist	=	$tsAdmin->banList();
		
		// Keine Rechte
		if(((strpos($user_right['ports']['right_web_server_view'][$serverInstanz], $server['data']['virtualserver_port']) === false || strpos($user_right['ports']['right_web_server_bans'][$serverInstanz], $server['data']['virtualserver_port']) === false)
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

<!-- Bann hinzufügen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-edit"></i> <?php echo $language['ts_bans_create']; ?>
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
				<input id="banInput" class="form-control" style="margin-top:3px;" placeholder="<?php echo $language['ban_name_uid_ip']; ?>">
			</div>
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<input id="banReason" class="form-control" style="margin-top:3px;" placeholder="<?php echo $language['reason']; ?>">
			</div>
			<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
				<input id="banTime" type="number" class="form-control" style="margin-top:3px;" placeholder="<?php echo $language['ban_time']; ?>">
			</div>
			<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
				<button onClick="addBan();" class="btn btn-danger" style="width:100%;"><i class="fa fa-ban" aria-hidden="true"></i> <?php echo $language['ban']; ?></button>
			</div>
		</div>
	</div>
</div>

<!-- Banliste -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-list"></i> <?php echo $language['ts_bans_list']; ?>
		</h4>
	</div>
	<div class="card-block">
		<table class="table table-condensed">
			<tbody>
				<?php if(!empty($banlist['data']))
				{
					$banBackground						=	false;
					foreach($banlist['data'] AS $bancount=>$ban)
					{ ?>
						<tr id="banid_<?php echo $ban["banid"]; ?>" style="<?php echo ($banBackground) ? 'background-color:rgba(0,0,0,0.06);' : ''; ?>">
							<td>
								<div class="hover">
									<span class="banlist-headline">
										<?php echo $language['name']; ?>
									</span>
									<span class="banlist-subline">
										<?php echo htmlspecialchars($ban["name"]); ?>
									</span>
								</div>
								<div class="hover">
									<span class="banlist-headline">
										Ban ID
									</span>
									<span class="banlist-subline">
										<?php echo $ban["banid"]; ?>
									</span>
								</div>
								<div class="hover">
									<span class="banlist-headline">
										<?php echo $language['ip_adress']; ?>
									</span>
									<span class="banlist-subline">
										<?php echo $ban["ip"]; ?>
									</span>
								</div>
								<div class="hover">
									<span class="banlist-headline">
										<?php echo $language['ts3_uniquie_id']; ?>
									</span>
									<span class="banlist-subline">
										<?php echo $ban["uid"]; ?>
									</span>
								</div>
								<div class="hover">
									<span class="banlist-headline">
										<?php echo $language['ts_banlist_admin']; ?>
									</span>
									<span class="banlist-subline">
										<?php echo htmlspecialchars($ban["invokername"]); ?>
									</span>
								</div>
								<div class="hover">
									<span class="banlist-headline">
										<?php echo $language['reason']; ?>
									</span>
									<span class="banlist-subline">
										<?php echo htmlspecialchars($ban["reason"]); ?>
									</span>
								</div>
								<div class="hover">
									<span class="banlist-headline">
										<?php echo $language['ts3_create_on']; ?>
									</span>
									<span class="banlist-subline">
										<?php 
											echo date('d.m.Y - H:i:s', $ban["created"]);
										?>
									</span>
								</div>
								<div class="hover">
									<span class="banlist-headline">
										<?php echo $language['duration']; ?>
									</span>
									<span class="banlist-subline">
										<?php 
											if($ban["duration"] == 0)
											{
												echo $language['unlimited'];
											}
											else
											{
												echo $ban["duration"]/60 . " " . $language['minutes'];
											};
										?>
									</span>
								</div>
								<div class="hover">
									<span class="banlist-headline">
										<?php echo $language['actions']; ?>
									</span>
									<span class="banlist-subline">
										<i onClick="deleteBan('<?php echo $ban["banid"]; ?>')" class="fa fa-fw fa-trash"></i>
									</span>
								</div>
							</td>
						</tr>
					<?php 	$banBackground	=	!$banBackground;
							$i++;
					}
				}
				else 
				{ ?>
					<tr style="text-align:center;" id="noBans">
						<td>
							<span>
								<?php echo $language['ts_bans_no_bans']; ?>
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
	
	var port 						= 	'<?php echo $server['data']['virtualserver_port']; ?>';
	//var serverId					=	'<?php echo $serverId; ?>';
	var instanz						=	'<?php echo $serverInstanz; ?>';
</script>
<script src="js/webinterface/teamspeak.js"></script>
<script src="js/sonstige/preloader.js"></script>