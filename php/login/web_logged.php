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
?>

<!-- Profil -->
<div class="card hidden-md-down">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-user"></i> <?php echo $language['your_profile']; ?></h4>
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
				<?php echo $language['edit_profile']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-edit"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<div class="navigationitem profilPermission" onClick="profilPermissionInit();">
			<div style="float:left;">
				<?php echo $language['profile_permissions']; ?>
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
		<h4 class="card-title"><i class="fa fa-wrench"></i> <?php echo $language['global_settings']; ?></h4>
	</div>
	<div class="card-block">
		<?php $permission	=	$user_right["right_hp_main"]["key"] == $mysql_keys["right_hp_main"]; ?>
		<div class="navigationitem adminSettings <?php echo (!$permission) ? "text-danger" : ""; ?>" <?php echo ($permission) ? "onClick=\"adminSettingsInit();\"" : ""; ?>>
			<div style="float:left;">
				<?php echo $language['settings']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-gears"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php $permission	=	$user_right["right_hp_ts3"]["key"] == $mysql_keys["right_hp_ts3"]; ?>
		<div class="navigationitem adminInstanz <?php echo (!$permission) ? "text-danger" : ""; ?>" <?php echo ($permission) ? "onClick=\"adminInstanzInit();\"" : ""; ?>>
			<div style="float:left;">
				<?php echo $language['instances']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-globe"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php $permission	=	($user_right["right_hp_user_edit"]["key"] == $mysql_keys["right_hp_user_edit"] || $user_right["right_hp_user_create"]["key"] == $mysql_keys["right_hp_user_create"] || $user_right["right_hp_user_delete"]["key"] == $mysql_keys["right_hp_user_delete"]); ?>
		<div class="navigationitem adminUser <?php echo (!$permission) ? "text-danger" : ""; ?>" <?php echo ($permission) ? "onClick=\"adminUserInit();\"" : ""; ?>>
			<div style="float:left;">
				<?php echo $language['client']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-users"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php if(USE_MAILS == "true") { ?>
			<?php $permission	=	$user_right["right_hp_mails"]["key"] == $mysql_keys["right_hp_mails"]; ?>
			<div class="navigationitem adminMail <?php echo (!$permission) ? "text-danger" : ""; ?>" <?php echo ($permission) ? "onClick=\"adminMailInit();\"" : ""; ?>>
				<div style="float:left;">
					<?php echo $language['mail_settings']; ?>
				</div>
				<div style="float:right;padding-top:5px;">
					<i class="fa fa-inbox"></i>
				</div>
			</div>
			<div style="clear:both;"></div>
		<?php }; ?>
		
		<?php $permission	=	$user_right["right_hp_logs"]["key"] == $mysql_keys["right_hp_logs"]; ?>
		<div class="navigationitem adminLogs <?php echo (!$permission) ? "text-danger" : ""; ?>" <?php echo ($permission) ? "onClick=\"adminLogsInit();\"" : ""; ?>>
			<div style="float:left;">
				<?php echo $language['logs']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-archive"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
	</div>
</div>

<!-- Teamspeak 3 -->
<?php if($Moduls['webinterface'] == "true" && ($user_right['right_web']['key'] == $mysql_keys['right_web'] || $user_right['right_web_server_create']['key'] == $mysql_keys['right_web_server_create'])) { ?>
	<div class="card webinterfacearea hidden-md-down">
		<div class="card-block card-block-header">
			<h4 class="card-title"><img src="images/tsLogo.png" width="28" style="margin-top: -3px;"/> <?php echo $language['interface']; ?></h4>
		</div>
		<div class="card-block">
			<?php $permission	=	$user_right["right_web"]["key"] == $mysql_keys["right_web"]; ?>
			<div class="navigationitem teamspeakServer <?php echo (!$permission) ? "text-danger" : ""; ?>" <?php echo ($permission) ? "onClick=\"teamspeakServerInit();\"" : ""; ?>>
				<div style="float:left;">
					<?php echo $language['server']; ?>
				</div>
				<div style="float:right;padding-top:5px;">
					<i class="fa fa-server"></i>
				</div>
			</div>
			<div style="clear:both;"></div>
			
			<?php $permission	=	$user_right["right_web_server_create"]["key"] == $mysql_keys["right_web_server_create"]; ?>
			<div class="navigationitem teamspeakServerCreate <?php echo (!$permission) ? "text-danger" : ""; ?>" <?php echo ($permission) ? "onClick=\"teamspeakServerCreateInit();\"" : ""; ?>>
				<div style="float:left;">
					<?php echo $language['create_server']; ?>
				</div>
				<div style="float:right;padding-top:5px;">
					<i class="fa fa-paint-brush"></i>
				</div>
			</div>
			<div style="clear:both;"></div>
			
			<?php
				$wantServer = scandir(__DIR__."/../../files/wantServer/");
				if(count($wantServer) > 2) { ?>
					<div class="navigationitem teamspeakServerRequests <?php echo (!$permission) ? "text-danger" : ""; ?>" <?php echo ($permission) ? "onClick=\"teamspeakServerRequestsInit();\"" : ""; ?>>
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
<?php }; ?>

<!-- Teamspeak Bot -->
<div class="card botarea hidden-md-down">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-music"></i>  <?php echo "Botinterface"; ?></h4>
	</div>
	<div class="card-block">
		<div class="navigationitem" style="text-align: center;">
			Comming soon <i class="fa fa-smile-o"></i>
		</div>
	</div>
</div>

<!-- Ticket -->
<div class="card hidden-md-down">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-ticket"></i> <?php echo $language['ticket_system']; ?></h4>
	</div>
	<div class="card-block">
		<div class="navigationitem ticketMain" onClick="ticketInit();">
			<div style="float:left;">
				<?php echo $language['tickets']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-ticket"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
	</div>
</div>