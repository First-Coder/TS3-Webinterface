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
	$LoggedIn		=	(checkSession()) ? true : false;
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys		=	getKeys();
	
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
	$user_right				=	getUserRights('pk', $_SESSION['user']['id']);
	
	/*
		Has the Client the Permission
	*/
	if($user_right['right_hp_logs']['key'] != $mysql_keys['right_hp_logs'])
	{
		reloadSite();
	};
	
	/*
		Read logs
	*/
	$systemLogs				=	file(__DIR__."/../../logs/system.log");
	$userLogs				=	file(__DIR__."/../../logs/user.log");
?>

<div id="adminContent">
	<!-- System logs -->
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<i class="fa fa-archive"></i> <?php echo $language['system_logs']; ?>
			</h4>
		</div>
		<div class="card-block">
			<?php
				$logAvalible		=	explode("|", $systemLogs[0]);
				if(empty($systemLogs))
				{
					echo "<p style=\"text-align: center;\">".$language['no_entrys']."</p>";
				}
				else if(count($logAvalible) != 3)
				{
					echo "<p style=\"text-align: center;\">".$language['log_not_possible']."</p>";
				}
				else
				{ ?>
					<table data-toggle="table" data-card-view="true" data-classes="table-no-bordered table-hover table"
						data-striped="true" data-pagination="true" data-search="true" data-row-style="loglevelColor">
						<thead>
							<tr>
								<th data-field="date"><?php echo $language['date']; ?></th>
								<th data-field="logLevel"><?php echo $language['loglevel']; ?></th>
								<th data-field="description"><?php echo $language['description']; ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
								foreach($systemLogs AS $entry)
								{
									$entryParts		=	explode("|", $entry);
									echo "<tr>
											<td>".xssSafe(trim($entryParts[0]))."</td>
											<td>".xssSafe(trim($entryParts[1]))."</td>
											<td>".xssSafe(trim($entryParts[2]))."</td>
										</tr>";
								};
							?>
						</tbody>
					</table>
			<?php }; ?>
		</div>
	</div>
	
	<!-- User logs -->
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<i class="fa fa-archive"></i> <?php echo $language['client_logs']; ?>
			</h4>
		</div>
		<div class="card-block">
			<?php
				$logAvalible		=	explode("|", $userLogs[0]);
				if(empty($userLogs))
				{
					echo "<p style=\"text-align: center;\">".$language['no_entrys']."</p>";
				}
				else if(count($logAvalible) != 3)
				{
					echo "<p style=\"text-align: center;\">".$language['log_not_possible']."</p>";
				}
				else
				{ ?>
					<table data-toggle="table" data-card-view="true" data-classes="table-no-bordered table-hover table"
						data-striped="true" data-pagination="true" data-search="true">
						<thead>
							<tr>
								<th data-field="date"><?php echo $language['date']; ?></th>
								<th data-field="name"><?php echo $language['client']; ?></th>
								<th data-field="description"><?php echo $language['description']; ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
								foreach($userLogs AS $entry)
								{
									$entryParts		=	explode("|", $entry);
									echo "<tr>
											<td>".xssSafe(trim($entryParts[0]))."</td>
											<td>".xssSafe(trim($entryParts[1]))."</td>
											<td>".xssSafe(trim($entryParts[2]))."</td>
										</tr>";
								};
							?>
						</tbody>
					</table>
			<?php }; ?>
		</div>
	</div>
</div>

<!-- Javascripte Laden -->
<script src="js/bootstrap/bootstrap-table.js"></script>
<script src="js/webinterface/admin.js"></script>
<script>
	function loglevelColor(row, index)
	{
		if(row.logLevel == "WARNING")
		{
			return { classes: 'text-warning' };
		}
		else if(row.logLevel == "CRITICAL" || row.logLevel == "ERROR")
		{
			return { classes: 'text-danger' };
		}
		else
		{
			return {};
		};
	};
</script>
<script src="js/sonstige/preloader.js"></script>