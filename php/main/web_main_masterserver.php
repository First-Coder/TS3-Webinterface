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
	require_once("../../config/config.php");
	require_once("../../config/instance.php");
	require_once("../../lang/lang.php");
	require_once("../functions/functions.php");
	require_once("../functions/functionsSql.php");
	require_once("../classes/ts3admin.class.php");
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_modul	=	getModuls();
	
	/*
		Modul aktiviert?
	*/
	if($mysql_modul['masterserver'] != "true" && MASTERSERVER_INSTANZ != "" && MASTERSERVER_PORT != "")
	{
		reloadSite();
	};
	
	/*
		Masterserver abfragen
	*/
	if($mysql_modul['masterserver'] == "true" && MASTERSERVER_INSTANZ != "" && MASTERSERVER_PORT != "")
	{
		$tsAdmin = new ts3admin($ts3_server[MASTERSERVER_INSTANZ]['ip'], $ts3_server[MASTERSERVER_INSTANZ]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[MASTERSERVER_INSTANZ]['user'], $ts3_server[MASTERSERVER_INSTANZ]['pw']);
			$tsAdmin->selectServer(MASTERSERVER_PORT, 'port', true);
			
			$server		= 	$tsAdmin->serverInfo();
		};
	};
?>

<!-- Teamspeakviewer -->
<div id="otherContent">
	<div class="card">
		<div class="card-block card-block-header">
			<div style="float:left;">
				<h4 class="card-title"><i class="fa fa-eye"></i> Teamspeakviewer</h4>
			</div>
			<div style="float:right;">
				<a class="btn btn-secondary btn-sm" href="ts3server://<?php echo $ts3_server[MASTERSERVER_INSTANZ]['ip']; ?>?port=<?php echo MASTERSERVER_PORT; ?>"><i class="fa fa-sign-in" aria-hidden="true"></i> <?php echo $language['connect']; ?></a>
			</div>
			<div style="clear:both;"></div>
		</div>
		<div class="card-block">
			<div class="row" style="margin:0;">
				<div class="col-lg-12 col-md-12" id="tree_loading" style="margin-top:20px;margin-bottom:20px;text-align:center;">
					<h3><?php echo $language['tree_loading']; ?></h3><br /><i style="font-size:100px;" class="fa fa-cogs fa-spin"></i>
				</div>
				<div class="col-lg-12 col-md-12 tree" id="tree" style="padding:20px;display:none;<?php if($server['data']['virtualserver_status'] == 'online') { echo 'background-color:rgba(0,199,0,0.2);'; } else { echo 'background-color:rgba(199, 0,0,0.2);'; } ?>">
					<div id="header_tree">
						<div style="float:left;">
							<div id="server_name" class="servername">&nbsp;&nbsp;<?php xssEcho($server['data']['virtualserver_name']); ?></div>
						</div>
						<div style="float:right;text-align:right;" id="server_icon"></div>
						<div style="clear:both;"></div>
					</div>
					<div id="tree_content"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- AB HIER AN DARF NICHT GEAENDERT WERDEN!!!! -->
<!-- Mitwirkende -->
<?php include("./web_main_support.php"); ?>

<script>
	var instanz						=	'<?php echo MASTERSERVER_INSTANZ; ?>';
	var port 						= 	'<?php echo MASTERSERVER_PORT; ?>';
	var treeInterval				= 	<?php echo TEAMSPEAKTREE_INTERVAL; ?>;
</script>
<script src="js/webinterface/teamspeakTree.js"></script>
<script src="js/sonstige/preloader.js"></script>