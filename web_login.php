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
		Get the Modul Keys
	*/
	$moduls		=	getModuls();
	
	/*
		Masterserver abfragen
	*/
	if($moduls['masterserver'] == "true" && MASTERSERVER_INSTANZ != "" && MASTERSERVER_PORT != "")
	{
		require_once("functions.php");
		
		$tsAdmin = new ts3admin($ts3_server[MASTERSERVER_INSTANZ]['ip'], $ts3_server[MASTERSERVER_INSTANZ]['queryport']);
	
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[MASTERSERVER_INSTANZ]['user'], $ts3_server[MASTERSERVER_INSTANZ]['pw']);
			
			// Server Select
			$tsAdmin->selectServer(MASTERSERVER_PORT, 'port', true);
			
			// Server Info Daten abfragen
			$server		= 	$tsAdmin->serverInfo();
		};
	};
?>

<!-- Login -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-key"></i> <?php echo $language['login']; ?></h4>
		<h6 class="card-subtitle text-muted">Teamspeak 3 Interface</h6>
	</div>
	<div class="card-block">
		<input class="form-control" id="loginUser" type="text" placeholder="<?php echo $language['username']; ?>">
		<input class="form-control" id="loginPw" type="password" placeholder="<?php echo $language['password']; ?>">
		
		<button style="width:100%;" id="loginBtn" class="btn btn-success"><i class="fa fa-paper-plane"></i> <?php echo $language['login']; ?></button>
		<?php echo ($moduls['free_register'] == "true") ? "<button style=\"width:100%;\" id=\"registerBtn\" class=\"btn btn-secondary\"><i class=\"fa fa-plus\"></i> ".$language['register']."</button>" : ""; ?>
	</div>
</div>

<!-- Masterserver -->
<?php if($moduls['masterserver'] == "true" && MASTERSERVER_INSTANZ != "" && MASTERSERVER_PORT != "") { ?>
	<div class="card hidden-md-down">
		<div class="card-block card-block-header">
			<h4 class="card-title"><i class="fa fa-eye"></i> <?php echo htmlspecialchars($server['data']['virtualserver_name']); ?></h4>
			<h6 class="card-subtitle text-muted">Masterserver</h6>
		</div>
		<div class="card-block">
			<!-- Status -->
			<div style="float:left;">
				<?php echo $language['ts3_serverstatus']; ?>
			</div>
			<div style="float:right;<?php echo ($server['data']['virtualserver_status'] == "online") ? "color:green;" : "color:red;"; ?>">
				<?php echo $server['data']['virtualserver_status']; ?>
			</div>
			<div style="clear:both;"></div>
			
			<!-- Online seid -->
			<div style="float:left;">
				<?php echo $language['ts3_online_since']; ?>
			</div>
			<div style="float:right;">
				<?php 
					$Tage			= 	$server['data']['virtualserver_uptime'] / 86400;
					$Stunden		=	($server['data']['virtualserver_uptime'] - (floor($Tage) * 86400)) / 3600;
					$Minuten		=	($server['data']['virtualserver_uptime'] - (floor($Tage) * 86400) - (floor($Stunden) * 3600)) / 60;

					echo floor($Tage) . "d " . floor($Stunden) . "h " . floor($Minuten) . "m";
				?>
			</div>
			<div style="clear:both;"></div>
			
			<!-- Benutzer -->
			<div style="float:left;">
				<?php echo $language['client']; ?>
			</div>
			<div style="float:right;">
				<?php echo ($server['data']['virtualserver_clientsonline'] - $server['data']['virtualserver_queryclientsonline'])." / ".$server['data']['virtualserver_maxclients']; ?>
			</div>
			<div style="clear:both;"></div>
			
			<!-- Querbenutzer -->
			<div style="float:left;">
				<?php echo $language['ts_main_query_user']; ?>
			</div>
			<div style="float:right;">
				<?php echo $server['data']['virtualserver_queryclientsonline']; ?>
			</div>
			<div style="clear:both;"></div>
			
			<!-- Verbindungen -->
			<div style="float:left;">
				<?php echo $language['connections']; ?>
			</div>
			<div style="float:right;">
				<?php echo $server['data']['virtualserver_client_connections']; ?>
			</div>
			<div style="clear:both;"></div>
		</div>
	</div>
<?php }; ?>

<!-- Javascripte Laden -->
<script src="js/webinterface/login.js"></script>