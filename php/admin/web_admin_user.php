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
	$mysql_modul	=	getModuls();
	
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
	$user_right			=	getUserRights('pk', $_SESSION['user']['id']);
	
	/*
		Has the Client the Permission
	*/
	if($user_right['right_hp_user_edit']['key'] != $mysql_keys['right_hp_user_edit'] && $user_right['right_hp_user_create']['key'] != $mysql_keys['right_hp_user_create']
		&& $user_right['right_hp_user_delete']['key'] != $mysql_keys['right_hp_user_delete'])
	{
		reloadSite();
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
					<h4 class="modal-title" id="modalCreateUserLabel"><?php echo $language['title_user_add']; ?></h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12 modal-info">
							<p><?php echo $language['user_add_info2']; ?></p>
						</div>
					</div>
					<div class="form-group">
						<label for="adminCreateUser"><?php echo $language['mail']; ?></label>
						<div class="input-group">
							<span class="input-group-addon">
								@
							</span>
							<input type="email" class="form-control" id="adminCreateUser" aria-describedby="adminCreateUserHelp">
						</div>
						<small id="adminCreateUserHelp" class="form-text text-muted"><?php echo $language['mail_help']; ?></small>
					</div>
					<div class="form-group">
						<label for="adminCreatePassword"><?php echo $language['password']; ?></label>
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-key"></i>
							</span>
							<input type="password" class="form-control" id="adminCreatePassword" aria-describedby="adminCreatePasswordHelp">
						</div>
						<small id="adminCreatePasswordHelp" class="form-text text-muted"><?php echo $language['password_help']; ?></small>
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
					<h4 class="modal-title" id="modalDeleteAllUserLabel"><?php echo $language['title_delete_all_user']; ?></h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12 modal-info">
							<p><?php echo $language['user_del_info']; ?></p>
						</div>
					</div>
					<div class="form-group">
						<label for="adminDeleteAllUsersUser"><?php echo $language['mail']; ?></label>
						<div class="input-group">
							<span class="input-group-addon">
								@
							</span>
							<input type="email" class="form-control" id="adminDeleteAllUsersUser" aria-describedby="adminDeleteAllUsersUserHelp">
						</div>
						<small id="adminDeleteAllUsersUserHelp" class="form-text text-muted"><?php echo $language['mail_help']; ?></small>
					</div>
					<div class="form-group">
						<label for="adminDeleteAllUsersPassword"><?php echo $language['password']; ?></label>
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-key"></i>
							</span>
							<input type="password" class="form-control" id="adminDeleteAllUsersPassword" aria-describedby="adminDeleteAllUsersPasswordHelp">
						</div>
						<small id="adminDeleteAllUsersPasswordHelp" class="form-text text-muted"><?php echo $language['password_help']; ?></small>
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
			<h4 class="card-title"><i class="fa fa-list"></i> <?php echo $language['userlist']; ?></h4>
		</div>
		<div class="card-block">
			<div id="toolbar">
				<div class="form-inline">
					<?php if($user_right['right_hp_user_delete']['key'] == $mysql_keys['right_hp_user_delete']) { ?>
						<button data-toggle="modal" data-target="#modalDeleteAllUser" class="pull-xs-right btn btn-secondary">
							<i class="fa fa-fw fa-trash"></i> <?php echo $language['title_delete_all_user']; ?>
						</button>
					<?php }; ?>
					<?php if($user_right['right_hp_user_create']['key'] == $mysql_keys['right_hp_user_create']) { ?>
						<button data-toggle="modal" data-target="#modalCreateUser" class="pull-xs-right btn btn-secondary" style="margin-right: 10px;">
							<i id="createUser" class="fa fa-fw fa-user-plus"></i> <?php echo $language['title_user_add']; ?>
						</button>
					<?php }; ?>
				</div>
			</div>
			<table id="userList" data-toggle="table" data-card-view="true" data-classes="table-no-bordered table-hover table"
				data-striped="true" data-pagination="true" data-search="true" data-toolbar="#toolbar">
				<thead>
					<tr>
						<th data-field="client"><?php echo $language['client']; ?></th>
						<th data-field="active"><?php echo $language['userstatus']; ?></th>
						<th data-field="last_login"><?php echo $language['last_login']; ?></th>
						<th data-field="actions"><?php echo $language['actions']; ?></th>
						<th data-field="pk" data-visible="false"></th>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach($users AS $user)
						{
							$btnDelete		=	"";
							
							if($user['benutzer_blocked'] == "true")
							{
								$label		=	"danger";
								$content	=	$language['deactive'];
							}
							else
							{
								$label		=	"success";
								$content	=	$language['active'];
							};
							
							if(count($users > 1) && $user_right['right_hp_user_delete']['key'] == $mysql_keys['right_hp_user_delete'])
							{
								$btnDelete	=	"<button onClick=\"AreYouSure('".$language['user_delete']."', 'deleteUser(\'".$user['pk_client']."\')');\" class=\"btn btn-danger btn-sm\">".$language['delete']."</button>";
							};
							
							echo "<tr>
									<td>".xssSafe($user['benutzer'])."</td>
									<td><span style=\"font-size: 15px;\" class=\"label label-".$label."\">".$content."</span></td>
									<td>".$user['last_login']."</td>
									<td>
										<button onClick=\"showUser('".$user['pk_client']."', '".xssSafe($user['benutzer'])."', '".$user['last_login']."');\" class=\"btn btn-success btn-sm\">".ucfirst($language['edit'])."</button>
										<button onClick=\"showUserServerPermission('".$user['pk_client']."', '".xssSafe($user['benutzer'])."');\" class=\"btn btn-info btn-sm\">".$language['server_permission']."</button>
										".$btnDelete."
									</td>
									<td>".$user['pk_client']."</td>
								</tr>";
						};
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<!-- Javascripte Laden -->
<script src="js/bootstrap/bootstrap-table.js"></script>
<script src="js/webinterface/admin.js"></script>
<script src="js/sonstige/preloader.js"></script>