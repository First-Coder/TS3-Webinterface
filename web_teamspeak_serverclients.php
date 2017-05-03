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
	$urlData				=	split("\?", $_SERVER['HTTP_REFERER'], -1);
	$serverInstanz			=	$urlData[2];
	$serverId				=	$urlData[3];
	
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
		Has the Client the Permission
	*/
	if($serverInstanz == '' || $serverId == '' || $mysql_modul['webinterface'] != 'true')
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	/*
		Teamspeak Stuff
	*/
	$tsAdmin = new ts3admin($ts3_server[$serverInstanz]['ip'], $ts3_server[$serverInstanz]['queryport']);
	
	if($tsAdmin->getElement('success', $tsAdmin->connect()))
	{
		// Im Teamspeak Einloggen
		$tsAdmin->login($ts3_server[$serverInstanz]['user'], $ts3_server[$serverInstanz]['pw']);
		
		// Server Select
		$tsAdmin->selectServer($serverId, 'serverId', true);
		
		// Server Info Daten abfragen
		$server				= 	$tsAdmin->serverInfo();
		
		$clientList			=	$tsAdmin->clientDbList();
		$clientOnlineList	=	$tsAdmin->clientList();
		
		// Keine Rechte
		if(((strpos($user_right['ports']['right_web_server_view'][$serverInstanz], $server['data']['virtualserver_port']) === false || strpos($user_right['ports']['right_web_server_clients'][$serverInstanz], $server['data']['virtualserver_port']) === false)
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

<!-- Benutzerliste -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-list"></i> <?php echo $language['all_registred_user']; ?>
		</h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<div class="gw-sidebar">
					<div id="gw-sidebar" class="gw-sidebar">
						<div class="nano-content">
							<ul class="gw-nav gw-nav-list">
								<?php foreach($clientList['data'] AS $client)
								{
									if($client['client_unique_identifier'] != 'ServerQuery') { ?>
										<li id="dbClient_<?php echo $client['cldbid']; ?>" class="init-arrow-down">
											<!-- Links -->
											<a href="javascript:void(0)"> <span class="gw-menu-text"><i class="fa fa-fw fa-user"></i> <?php echo htmlspecialchars($client['client_nickname']); ?></span>
											
											<!-- Rechts -->
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
													echo '<span class="label label-success gw-actions">'.$language['online'].'</span>';
												}
												else
												{
													echo '<span class="label label-danger gw-actions">'.$language['offline'].'</span>';
												};
											?>
											<b class="gw-arrow"></b> </a>
											
											<ul class="gw-submenu">
												<li><i class="fa fa-fw fa-info"></i> <?php echo $language['first_con']; ?>: <?php echo date('Y-m-d H:i:s', $client['client_created']); ?></li>
												<li><i class="fa fa-fw fa-info"></i> <?php echo $language['last_con']; ?>: <?php echo date('Y-m-d H:i:s', $client['client_lastconnected']); ?></a></li>
												<li><i class="fa fa-fw fa-info"></i> <?php echo $language['ip_adress']; ?>: <?php echo $client['client_lastip']; ?></li>
												<li><i class="fa fa-fw fa-info"></i> <?php echo $language['connection']; ?>: <?php echo $client['client_totalconnections']; ?></li>
												<?php if($client['client_description'] != '') { ?>
													<li><i class="fa fa-fw fa-info"></i> <?php echo $language['desc']; ?>: <?php echo htmlspecialchars($client['client_description']); ?></li>
												<?php } ?>
												<li><i class="fa fa-fw fa-info"></i> <?php echo $language['client']; ?> ID: <?php echo $client['client_unique_identifier']; ?></li>
												
												<!--<li class="gw-link"><i class="fa fa-fw fa-eye"></i> Rechte anschauen</li>-->
												<li onClick="deleteDBClient('<?php echo $client['cldbid']; ?>')" class="gw-link"><i class="fa fa-fw fa-trash"></i> <?php echo $language['deluser']; ?></li>
											</ul>
										</li>
									<?php }
								} ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Sprachdatein laden -->
<script language="JavaScript">
	var success						=	'<?php echo $language['success']; ?>';
	var failed						=	'<?php echo $language['failed']; ?>';
	
	var port 						= 	'<?php echo $server['data']['virtualserver_port']; ?>';
	//var serverId					=	'<?php echo $serverId; ?>';
	var instanz						=	'<?php echo $serverInstanz; ?>';
	
	var client_successfull_deleted	=	'<?php echo $language['client_successfull_deleted']; ?>';
</script>

<script>
	$(document).ready(function ()
	{
		var nav = function ()
		{
			$('.gw-nav > li > a').click(function ()
			{
				var gw_nav = $('.gw-nav');
				gw_nav.find('li').removeClass('active');
				$('.gw-nav > li > ul > li').removeClass('active');

				var checkElement = $(this).parent();
				var ulDom = checkElement.find('.gw-submenu')[0];

				if (ulDom == undefined)
				{
					checkElement.addClass('active');
					$('.gw-nav').find('li').find('ul:visible').slideUp();
					return;
				};
				
				if (ulDom.style.display != 'block')
				{
					gw_nav.find('li').find('ul:visible').slideUp();
					gw_nav.find('li.init-arrow-up').removeClass('init-arrow-up').addClass('arrow-down');
					gw_nav.find('li.arrow-up').removeClass('arrow-up').addClass('arrow-down');
					checkElement.removeClass('init-arrow-down');
					checkElement.removeClass('arrow-down');
					checkElement.addClass('arrow-up');
					checkElement.addClass('active');
					checkElement.find('ul').slideDown(300);
				}
				else
				{
					checkElement.removeClass('init-arrow-up');
					checkElement.removeClass('arrow-up');
					checkElement.removeClass('active');
					checkElement.addClass('arrow-down');
					checkElement.find('ul').slideUp(300);
				};
			});
		};
		nav();
	});
</script>
<script src="js/sonstige/preloader.js"></script>