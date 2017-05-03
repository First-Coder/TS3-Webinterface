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
	require_once("ts3admin.class.php");
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys		=	getKeys();
	$mysql_modul	=	getModuls();
	
	/*
		Start the PHP Session
	*/
	session_start();
	
	/*
		Is Client logged in?
	*/
	$urlData				=	explode("?", $_SERVER['HTTP_REFERER']);
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
	if($user_right['right_hp_user_edit'] != $mysql_keys['right_hp_user_edit'] && $user_right['right_hp_user_create'] != $mysql_keys['right_hp_user_create']
		&& $user_right['right_hp_user_delete'] != $mysql_keys['right_hp_user_delete'])
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	/*
		Get all Clients
	*/
	$users					=	getUsers();
?>

<div id="adminContent">
	<!-- Modal: Benutzer erstellen -->
	<div id="modalCreateUser" class="modal fade" data-backdrop="false" tabindex="-1" role="dialog" aria-labelledby="modalCreateUserLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header alert-success">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="modalCreateUserLabel"><?php echo $language['admin_user_add']; ?></h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12 modal-info">
							<p><?php echo $language['admin_user_add_info']; ?></p>
						</div>
					</div>
					<div class="row user-einrueckung">
						<div class="col-lg-3 col-md-12 input-padding">
							<?php echo $language['mail']; ?>:
						</div>
						<div class="col-lg-9 col-md-12" style="text-align:center;">
							<input id="adminCreateUser" type="text" class="form-control" placeholder="">
						</div>
					</div>
					<div class="row user-einrueckung">
						<div class="col-lg-3 col-md-12 input-padding">
							<?php echo $language['password']; ?>:
						</div>
						<div class="col-lg-9 col-md-12" style="text-align:center;">
							<input id="adminCreatePassword" type="password" class="form-control" placeholder="">
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-fw fa-close"></i> <?php echo $language['abort']; ?></button>
					<button onClick="createUser()" type="button" class="btn btn-success"><i class="fa fa-fw fa-edit"></i> <?php echo $language['create']; ?></button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal: Alle Benutzer löschen -->
	<div id="modalDeleteAllUser" class="modal fade" data-backdrop="false" tabindex="-1" role="dialog" aria-labelledby="modalDeleteAllUserLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header alert-danger">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="modalDeleteAllUserLabel"><?php echo $language['admin_user_del_all']; ?></h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12 modal-info">
							<p><?php echo $language['admin_user_del_info']; ?></p>
						</div>
					</div>
					<div class="row user-einrueckung">
						<div class="col-lg-3 col-md-12 input-padding">
							<?php echo $language['mail']; ?>:
						</div>
						<div class="col-lg-9 col-md-12" style="text-align:center;">
							<input id="adminDeleteAllUsersUser" type="text" class="form-control" placeholder="">
						</div>
					</div>
					<div class="row user-einrueckung">
						<div class="col-lg-3 col-md-12 input-padding">
							<?php echo $language['password']; ?>:
						</div>
						<div class="col-lg-9 col-md-12" style="text-align:center;">
							<input id="adminDeleteAllUsersPassword" type="password" class="form-control" placeholder="">
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-fw fa-close"></i> <?php echo $language['abort']; ?></button>
					<button onClick="deleteAllUsers()" type="button" class="btn btn-danger"><i class="fa fa-fw fa-trash"></i> <?php echo $language['delete']; ?></button>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Benutzerübersicht -->
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-list"></i> <?php echo $language['userlist']; ?>
				</div>
				<div class="pull-xs-right">
					<?php if($user_right['right_hp_user_delete'] == $mysql_keys['right_hp_user_delete']) { ?>
						<div data-toggle="modal" data-target="#modalDeleteAllUser" data-tooltip="tooltip" data-placement="top" title="<?php echo $lamguage['admin_title_delete_all_user']; ?>" class="pull-xs-right btn btn-secondary user-header-icons">
							<i class="fa fa-fw fa-close"></i>
						</div>
					<?php } ?>
					<?php if($user_right['right_hp_user_create'] == $mysql_keys['right_hp_user_create']) { ?>
						<div data-toggle="modal" data-target="#modalCreateUser" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['admin_title_user_add']; ?>" class="pull-xs-right btn btn-secondary user-header-icons">
							<i id="createUser" class="fa fa-fw fa-user-plus"></i>
						</div>
					<?php } ?>
				</div>
				<div style="clear:both;"></div>
			</h4>
		</div>
		<div class="card-block">
			<table class="table table-condensed table-hover">
				<tbody>
					<?php foreach($users AS $user) { ?>
						<tr onClick="showUser('<?php echo $user['pk_client']; ?>', '<?php echo htmlspecialchars($user['benutzer']); ?>', '<?php echo $user['last_login']; ?>');" style="cursor: pointer;">
							<td><?php echo htmlspecialchars($user['benutzer']); ?></td>
							<td class="hidden-sm-down"><?php echo $language['admin_user_last_login'].": ".$user['last_login']; ?></td>
							<td><div style="font-size: 15px;" class="label label-<?php echo ($user['benutzer_blocked'] == "true") ? "danger" : "success"; ?>"><?php echo ($user['benutzer_blocked'] == "true") ? $language['deactive'] : $language['active']; ?></div></td>
						</tr>
					<?php }; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<!-- Sprachdatein laden -->
<script>
	var success						=	'<?php echo $language['success']; ?>';
	var failed						=	'<?php echo $language['failed']; ?>';
	
	var username_needs				=	'<?php echo $language['username_needs']; ?>';
	var password_needs				=	'<?php echo $language['password_needs']; ?>';
</script>

<!-- Javascripte Laden -->
<script src="js/webinterface/admin.js"></script>
<script>
	$(function () {
		$('[data-tooltip="tooltip"]').tooltip();
	});
</script>

<script src="js/sonstige/preloader.js"></script>