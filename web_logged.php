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
?>

<!-- Profil -->
<div class="card hidden-md-down">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-user"></i> <?php echo $language['main_main_profil']; ?></h4>
	</div>
	<div class="card-block">
		<div class="navigationitem profilDashboard" onClick="profilDashboardInit();">
			<div style="float:left;">
				Dashboard
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-dashboard"></i>
			</div>
			<div style="clear:both;"></div>
		</div>
		
		<div class="navigationitem profilEdit" onClick="profilEditInit();">
			<div style="float:left;">
				<?php echo $language['profile']." ".$language['edit']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-edit"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<div class="navigationitem profilPermission" onClick="profilPermissionInit();">
			<div style="float:left;">
				<?php echo $language['profile']." ".$language['permission']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-university"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<div class="navigationitem" onClick="ausloggenInit();">
			<div style="float:left;">
				<?php echo $language['logout']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-sign-out"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
	</div>
</div>

<!-- Administration -->
<div class="card settingsarea hidden-md-down">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-wrench"></i> <?php echo $language['main_main_settings']; ?></h4>
	</div>
	<div class="card-block">
		<div class="navigationitem adminSettings" onClick="adminSettingsInit();">
			<div style="float:left;">
				<?php echo $language['settings']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-gears"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<div class="navigationitem adminInstanz" onClick="adminInstanzInit();">
			<div style="float:left;">
				<?php echo $language['instances']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-globe"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<div class="navigationitem adminUser" onClick="adminUserInit();">
			<div style="float:left;">
				<?php echo $language['client']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-users"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<div class="navigationitem adminMail" onClick="adminMailInit();">
			<div style="float:left;">
				Mail <?php echo $language['settings']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-inbox"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
	</div>
</div>

<!-- Teamspeak 3 -->
<div class="card webinterfacearea hidden-md-down">
	<div class="card-block card-block-header">
		<h4 class="card-title"><img src="images/tsLogo.png" width="28" style="margin-top: -3px;"/> <?php echo $language['main_main_interface']; ?></h4>
	</div>
	<div class="card-block">
		<div class="navigationitem teamspeakServer" onClick="teamspeakServerInit();">
			<div style="float:left;">
				Server
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-server"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<div class="navigationitem teamspeakServerCreate" onClick="teamspeakServerCreateInit();">
			<div style="float:left;">
				<?php echo $language['create_server']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-paint-brush"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php
			$wantServer = scandir('wantServer/');
			if(count($wantServer) > 2) { ?>
				<div class="navigationitem teamspeakServerRequests" onClick="teamspeakServerRequestsInit();">
					<div style="float:left;">
						<?php echo $language['server_requests']; ?>
					</div>
					<div style="float:right;padding-top:5px;">
						<i class="fa fa-list"></i>
					</div>
				</div>
				<div style="clear:both;"></div>
		<?php }; ?>
	</div>
</div>

<!-- Ticket -->
<div class="card hidden-md-down">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-ticket"></i> Ticket System</h4>
	</div>
	<div class="card-block">
		<div class="navigationitem ticketMain" onClick="ticketInit();">
			<div style="float:left;">
				Tickets
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-ticket"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
	</div>
</div>