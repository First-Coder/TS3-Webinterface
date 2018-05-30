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
		Masterserver abfragen
	*/
	if($Moduls['masterserver'] == "true" && MASTERSERVER_INSTANZ != "" && MASTERSERVER_PORT != "")
	{
		require_once("./config/instance.php");
		require_once("./php/classes/ts3admin.class.php");
		
		$tsAdmin = new ts3admin($ts3_server[MASTERSERVER_INSTANZ]['ip'], $ts3_server[MASTERSERVER_INSTANZ]['queryport']);
	
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[MASTERSERVER_INSTANZ]['user'], $ts3_server[MASTERSERVER_INSTANZ]['pw']);
			$tsAdmin->selectServer(MASTERSERVER_PORT, 'port', true);
			
			$server		= 	$tsAdmin->serverInfo();
		};
	};
?>

<!-- Login -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title" id="loginTitle"><i class="fa fa-key"></i> <?php echo $language['login']; ?></h4>
		<h6 class="card-subtitle text-muted">Teamspeak 3 Interface</h6>
	</div>
	<div class="card-block">
		<input class="form-control" id="loginUser" type="email" placeholder="<?php echo $language['mail']; ?>">
		<input class="form-control" id="loginPw" type="password" placeholder="<?php echo $language['password']; ?>">
		<button style="width:100%;margin-bottom:10px;" onClick="loginUser();" id="loginBtn" class="btn btn-success"><i class="fa fa-paper-plane"></i> <?php echo $language['login']; ?></button>
		<button style="width:100%;margin-bottom:10px;" onClick="goToForgot();" id="forgotBtn" class="btn btn-warning btn-sm"><i class="fa fa-question"></i> <?php echo $language['forgot_access']; ?></button>
		<button style="width:100%;<?php echo ($Moduls['free_register'] != "true") ? "display: none;" : ""; ?>" onClick="goToRegister();" id="registerBtn" class="btn btn-secondary btn-sm"><i class="fa fa-plus"></i> <?php echo $language['register']; ?></button>
	</div>
</div>

<!-- Masterserver -->
<?php if($Moduls['masterserver'] == "true" && !empty($server['data']) && MASTERSERVER_INSTANZ != "" && MASTERSERVER_PORT != "") { ?>
	<div class="card hidden-md-down">
		<div class="card-block card-block-header">
			<h4 class="card-title"><i class="fa fa-eye"></i> <?php xssEcho($server['data']['virtualserver_name']); ?></h4>
			<h6 class="card-subtitle text-muted"><?php echo $language['masterserver']; ?></h6>
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
				<?php echo $language['online_since']; ?>
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
				<?php echo $language['ts3_query_user']; ?>
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
			
			<!-- Button -->
			<a href="ts3server://<?php echo $ts3_server[MASTERSERVER_INSTANZ]['ip']; ?>?port=<?php echo MASTERSERVER_PORT; ?>">
				<button class="btn btn-custom btn-sm" style="width: 100%;margin-top: 10px;"><i class="fa fa-sign-in" aria-hidden="true"></i> <?php echo $language['connect']; ?></button>
			</a>
		</div>
	</div>
<?php }; ?>

<!-- Thanks to all Supporters, do not remove -->
<button id="showSupport" style="width: 100%;" class="btn btn-secondary"><i class="fa fa-thumbs-up"></i> <?php echo $language['mitwirkende']; ?> & Disclaimer</button>

<!-- Javascripte Laden -->
<script src="js/webinterface/login.js"></script>
<script>
	var	bttnRegister					=	document.getElementById('registerBtn'),
		bttnLogin						=	document.getElementById('loginBtn'),
		bttnForgot						=	document.getElementById('forgotBtn'),
		loginTitle						=	document.getElementById('loginTitle'),
		inputPw							=	document.getElementById('loginPw'),
		regBlocked						=	"<?php echo $Moduls['free_register']; ?>";
	
	function goToForgot()
	{
		bttnForgot.style.display		=	"none";
		inputPw.style.display			=	"none";
		bttnRegister.style.display		=	"inline";
		
		loginTitle.innerHTML			=	"<i class=\"fa fa-question\"></i> "+lang.forgot_access;
		
		bttnLogin.onclick				=	forgotPassword;
		bttnLogin.innerHTML 			= 	"<i class=\"fa fa-reply\"></i> "+lang.reset;
		
		bttnRegister.onclick			=	goBackToMainLogin;
		bttnRegister.innerHTML 			= 	"<i class=\"fa fa-arrow-left\"></i> "+lang.back;
	};
	
	function goToRegister()
	{
		bttnForgot.style.display		=	"none";
		
		loginTitle.innerHTML			=	"<i class=\"fa fa-question\"></i> "+lang.register;
		
		bttnLogin.onclick				=	createUser;
		bttnLogin.innerHTML 			= 	"<i class=\"fa fa-check\"></i> "+lang.register;
		
		bttnRegister.onclick			=	goBackToMainLogin;
		bttnRegister.innerHTML 			= 	"<i class=\"fa fa-arrow-left\"></i> "+lang.back;
	};
	
	function goBackToMainLogin()
	{
		bttnForgot.style.display		=	"inline";
		inputPw.style.display			=	"inline";
		
		if(regBlocked != "true")
		{
			bttnRegister.style.display	=	"none";
		};
		
		loginTitle.innerHTML			=	"<i class=\"fa fa-key\"></i> "+lang.login;
		
		bttnLogin.onclick				=	loginUser;
		bttnLogin.innerHTML 			= 	"<i class=\"fa fa-paper-plane\"></i> "+lang.login;
		
		bttnRegister.onclick			=	goToRegister;
		bttnRegister.innerHTML			=	"<i class=\"fa fa-plus\"></i> "+lang.register;
	};
</script>