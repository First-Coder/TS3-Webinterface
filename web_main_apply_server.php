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
	$mysql_modul	=	getModuls();
	
	/*
		Get Link information
	*/
	$urlData				=	split("\?", $_SERVER['HTTP_REFERER'], -1);
	
	/*
		Modul aktiviert?
	*/
	if($mysql_modul['free_ts3_server_application'] != "true")
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
?>

<!-- Server beantragen Information -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-info"></i> <?php echo $language['apply_for_server']; ?></h4>
	</div>
	<div class="card-block">
		<p style="text-align:center;">
			<?php echo $language['server_application_info_1']; ?><br />
			<?php echo $language['server_application_info_2']; ?><br /><br />
		</p>
		<p>
			<?php echo $language['server_application_info_3']; ?>:<br />
			<ul>
				<li>
					<?php echo $language['server_application_info_4']; ?>
				</li>
				<li>
					<?php echo $language['server_application_info_5']; ?>
				</li>
				<li>
					<?php echo $language['server_application_info_6']; ?>
				</li>
				<li>
					<?php echo $language['server_application_info_7']; ?>
				</li>
			</ul>
		</p>
	</div>
</div>

<!-- Server beantragen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-edit"></i> <?php echo $language['apply_for_server']; ?></h4>
	</div>
	<div class="card-block">
		<div id="wantServerStep1">
			<div class="row">
				<div class="col-md-12">
					<label class="c-input c-radio">
						<input id="radioAccount" name="radioAccount" type="radio" checked>
						<span class="c-indicator"></span>
						<?php echo $language['i_have_an_acc_here']; ?>
					</label>
				</div>
			</div>
			<div class="row">
				<div class="col-md-1"></div>
				<div class="col-md-10">
					<div class="form-group row">
						<input type="text" class="form-control" id="wantServerLoginUser" placeholder="<?php echo strtoupper($language['username']); ?>">
					</div>
				</div>
				<div class="col-md-1"></div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<label class="c-input c-radio">
						<input name="radioAccount" type="radio">
						<span class="c-indicator"></span>
						<?php echo $language['i_have_no_acc_here']; ?>
					</label>
				</div>
			</div>
			<div class="row">
				<div class="col-md-1"></div>
				<div class="col-md-10">
					<div class="form-group row">
						<input type="text" class="form-control" id="wantServerLoginCreateUser" placeholder="<?php echo strtoupper($language['mail']); ?>">
					</div>
					<div class="form-group row">
						<input type="password" class="form-control" id="wantServerLoginCreatePw" placeholder="<?php echo strtoupper($language['password']); ?>">
					</div>
				</div>
				<div class="col-md-1"></div>
			</div>
		</div>
		<div id="wantServerStep2" style="display:none;">
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<!-- Fragen -->
					<div class="row server-create-einrueckung">
						<div class="col-lg-6 col-md-12">
							<?php echo $language['server_request_cause']; ?>
						</div>
						<div class="col-lg-6 col-md-12" style="text-align:center;">
							<textarea id="serverCreateCause" class="form-control" rows="3"></textarea> 
						</div>
					</div>
					<div class="row server-create-einrueckung">
						<div class="col-lg-6 col-md-12">
							<?php echo $language['server_request_why']; ?>
						</div>
						<div class="col-lg-6 col-md-12" style="text-align:center;">
							<textarea id="serverCreateWhy" class="form-control" rows="3"></textarea> 
						</div>
					</div>
					<div class="row server-create-einrueckung">
						<div class="col-lg-6 col-md-12 input-padding">
							<?php echo $language['server_request_clients']; ?>
						</div>
						<div class="col-lg-6 col-md-12" style="text-align:center;">
							<input id="serverCreateNeededSlots" maxlength="4" type="number" class="form-control" placeholder="">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="wantServerStep3" style="display:none;">
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<!-- Teamspeak Haupteinstellungen -->
					<div class="row server-create-einrueckung">
						<div class="col-lg-6 col-md-12 input-padding">
							<?php echo $language['ts3_servername']; ?>:
						</div>
						<div class="col-lg-6 col-md-12" style="text-align:center;">
							<input id="serverCreateServername" type="text" class="form-control" value="<?php echo $ts3_server_create_default['servername']; ?>">
						</div>
					</div>
					<div class="row server-create-einrueckung">
						<div class="col-lg-6 col-md-12 input-padding">
							<?php echo $language['ts3_choose_port']; ?>:
						</div>
						<div class="col-lg-6 col-md-12" style="text-align:center;">
							<input id="serverCreatePort" maxlength="5" type="number" class="form-control" placeholder="XXXXX">
						</div>
					</div>
					<div class="row server-create-einrueckung">
						<div class="col-lg-6 col-md-12 input-padding">
							<?php echo $language['ts3_max_clients']; ?>:
						</div>
						<div class="col-lg-6 col-md-12" style="text-align:center;">
							<input id="serverCreateSlots" maxlength="4" type="number" class="form-control" placeholder="<?php echo $ts3_server_create_default['slots']; ?>">
						</div>
					</div>
					<div class="row server-create-einrueckung">
						<div class="col-lg-6 col-md-12 input-padding">
							<?php echo $language['ts3_reservierte_slots']; ?>:
						</div>
						<div class="col-lg-6 col-md-12" style="text-align:center;">
							<input id="serverCreateReservedSlots" maxlength="4" type="number" class="form-control" placeholder="<?php echo $ts3_server_create_default['reserved_slots']; ?>">
						</div>
					</div>
					<div class="row server-create-einrueckung">
						<div class="col-lg-6 col-md-12 input-padding">
							<?php echo $language['password']; ?>:
						</div>
						<div class="col-lg-6 col-md-12" style="text-align:center;">
							<input id="serverCreatePassword" type="password" class="form-control" placeholder="<?php echo $ts3_server_create_default['password']; ?>">
						</div>
					</div>
					<div class="row server-create-einrueckung">
						<div class="col-lg-6 col-md-12">
							<?php echo $language['ts3_welcome_message']; ?>:
						</div>
						<div class="col-lg-6 col-md-12" style="text-align:center;">
							<textarea id="serverCreateWelcomeMessage" class="form-control" rows="8"><?php echo $ts3_server_create_default['welcome_message']; ?></textarea> 
						</div>
					</div>
				</div>
			</div>
		</div>
		<button style="width:100%;" type="button" class="btn btn-success" onClick="checkWantServer()" id="wantServerBttn"><i class="fa fa-fw fa-check"></i> <?php echo $language['next']; ?></button>
	</div>
</div>

<script src="js/sonstige/preloader.js"></script>