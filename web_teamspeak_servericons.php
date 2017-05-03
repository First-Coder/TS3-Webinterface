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
		
		// Keine Rechte
		if(((strpos($user_right['ports']['right_web_server_view'][$serverInstanz], $server['data']['virtualserver_port']) === false || strpos($user_right['ports']['right_web_server_icons'][$serverInstanz], $server['data']['virtualserver_port']) === false)
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
	
	/*
		Load the icons
	*/
	$handler2	=	@opendir('images/ts_icons/'.$ts3_server[$serverInstanz]['ip'].'-'.$server['data']['virtualserver_port'].'/');
	if($handler2)
	{
		while($datei = readdir($handler2))
		{
			if($datei!='.' AND $datei!='..') // Keine Ordner
			{
				$icon_id						=	str_replace("icon_", "", $datei);
				$allicons[$datei]['name']		=	$icon_id;
				$allicons[$datei]['id']			=	sprintf('%u', $icon_id & 0xffffffff);
				$allicons[$datei]['info']		=	getimagesize('images/ts_icons/'.$ts3_server[$serverInstanz]['ip'].'-'.$server['data']['virtualserver_port'].'/'.$datei);
			};
		};
	};
	
	/*
		Get the Icons
	*/
	function get_icon ($ip, $icon_id, $port)
	{
		$name 		= 	str_replace("\\","",$icon_id);
		$name 		= 	str_replace("/","",$name);
		if(str_replace('icon_', '', $name) == 100 OR str_replace('icon_', '', $name) == 200 OR str_replace('icon_', '', $name) == 300 OR str_replace('icon_', '', $name) == 500 OR str_replace('icon_', '', $name) == 600)
		{
			$path	=	'images/ts_icons/';
		}
		else
		{
			$path	=	'images/ts_icons/'.$ip.'-'.$port.'/';
		};
		
		return $path.$name;
	};
?>

<!-- Icon hochalden -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-upload"></i> <?php echo $language['icon_upload']; ?>
		</h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<form class="dropzone" drop-zone="" id="file-dropzone"></form>
			</div>
		</div>
	</div>
</div>

<!-- Iconliste -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-list"></i> <?php echo $language['icon_avalible']; ?>
		</h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<table class="table table-condensed table-hover">
					<thead>
						<th>
							<?php echo $language['picture']; ?>
						</th>
						<th>
							<?php echo $language['ts3_icon_id']; ?>
						</th>
						<th>
							<?php echo $language['actions']; ?>
						</th>
					</head>
					<tbody>
						<?php foreach($allicons AS $value) {
							$filename		=	'images/ts_icons/'.$ts3_server[$serverInstanz]['ip'].'-'.$server['data']['virtualserver_port'].'/icon_'.$value['name'];
							$imgbinary 		= 	fread(fopen($filename, "r"), filesize($filename));
							?>
							<tr id="<?php echo $value['name']; ?>">
								<td style="text-align:center;"><img src="<?php echo 'data:image/png;base64,' . base64_encode($imgbinary); ?>" width="16" height="16" alt="Icon Image"></td>
								<td style="text-align:center;"><?php echo $value['name']; ?></td>
								<td style="text-align:center;">
									<a href="images/ts_icons/<?php echo $ts3_server[$serverInstanz]['ip'].'-'.$server['data']['virtualserver_port'] ?>/icon_<?php echo $value['name']; ?>" download><i style="width:49%;" class="fa fa-download"></i></a>
									<a onClick="deleteIcon('<?php echo $value['name']; ?>')" href="#"><i style="width:49%;" class="delete-icon fa fa-close"></i></a>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<!-- Javascripte Laden -->
<script src="js/sonstige/dropzone.js"></script>

<!-- Sprachdatein laden -->
<script>
	var success						=	'<?php echo $language['success']; ?>';
	var failed						=	'<?php echo $language['failed']; ?>';
	
	var port 						= 	'<?php echo $server['data']['virtualserver_port']; ?>';
	var serverId					=	'<?php echo $serverId; ?>';
	var instanz						=	'<?php echo $serverInstanz; ?>';
	
	// Icon hochladen
	$('#file-dropzone').dropzone({
		url: "uploadIcon.php",
		method: "POST",
		acceptedFiles: 'image/*',
		destination: "/images/ts_icons",
		dictDefaultMessage: '<?php echo $language['icon_upload_info']; ?>',
		init: function() {
			this.on('success', function( file, resp ){
				//console.log( file );
				//console.log( resp );
				//alert("Files are Added to the Local Server and will be uploaded to the Teamspeakserver... This can take a while...");
			});

			this.on('addedfile', function(file) {
				
			});

			this.on('drop', function(file) {
				//alert('file');
			}); 
			
			this.on('thumbnail', function(file) {
				if ( file.width > 16 || file.height > 16 )
				{
					file.rejectDimensions();
				}
				else
				{
					file.acceptDimensions();
				};
			});
		},
		accept: function(file, done)
		{
			file.acceptDimensions = done;
			file.rejectDimensions = function() {
				done('<?php echo $language['icon_upload_size']; ?>')
			};
		}
	});
</script>
<script src="js/sonstige/preloader.js"></script>