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
	require_once("./functions.php");
	require_once("./functionsSql.php");
	require_once("./functionsTeamspeak.php");
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys		=	getKeys();
	
	/*
		Check Session
	*/
	$LoggedIn		=	(checkSession($mysql_keys['login_key'])) ? true : false;
	
	/*
		Get Client Permissions
	*/
	$user_right		=	getUserRights('pk', $_SESSION['user']['id']);
	
	/*
		Instanz shell
	*/
	if($_POST['action'] == 'instanzShell' && $LoggedIn && $user_right['right_hp_ts3']['key'] == $mysql_keys['right_hp_ts3'])
	{
		$instanzShell	= 	new sshConnect($_POST['ip'], $_POST['port'], null, $_POST['username'], urldecode($_POST['password']), (isSet($_POST['file'])) ? "../../files/shell/".$_POST['file'].".ppk" : null, (isSet($_POST['file'])) ? "../../files/shell/".$_POST['file'] : null);
		if($instanzShell->connect())
		{
			echo $instanzShell->exec('./'.urldecode($_POST['path']).'/ts3server_startscript.sh '.$_POST['command']).'<br/>';
		};
	};
	
	/*
		SSH Connect class
	*/
	class sshConnect
	{ 
		private $ssh_host, $ssh_port, $ssh_server_fp, $ssh_auth_user, $ssh_auth_pub, $ssh_auth_priv, $ssh_auth_pass, $connection;
		private $returnString;
		
		public function __construct($ssh_host, $ssh_port, $ssh_server_fp, $ssh_auth_user, $ssh_auth_pass, $ssh_auth_pub, $ssh_auth_priv)
		{
			$this->ssh_host			=	$ssh_host;
			$this->ssh_port			=	$ssh_port;
			$this->ssh_server_fp	=	$ssh_server_fp;
			$this->ssh_auth_user	=	$ssh_auth_user;
			$this->ssh_auth_pass	=	$ssh_auth_pass;
			$this->ssh_auth_pub		=	$ssh_auth_pub;
			$this->ssh_auth_priv	=	$ssh_auth_priv;
		}
		
		public function __destruct()
		{ 
			$this->disconnect(); 
		}
		
		private function setTxtColor($color, $txt)
		{
			switch($color)
			{
				case "red":
					$returnString	=	"<font class=\"text-danger-no-cursor\">".$txt."</font>";
					break;
				case "green":
					$returnString	=	"<font class=\"text-success\">".$txt."</font>";
					break;
				default:
					$returnString	=	$txt;
					break;
			};
			
			return $returnString;
		}
		
		public function connect()
		{
			if (!($this->connection = ssh2_connect($this->ssh_host, $this->ssh_port)))
			{
				die($this->setTxtColor('red', 'Cannot connect to server'));
				return false;
			}
			else
			{
				echo $this->setTxtColor('green', 'Connection to: '.$this->ssh_host.':'.$this->ssh_port.' successfull!<br/>');
			};
			
			if($fingerprint != null)
			{
				$fingerprint = ssh2_fingerprint($this->connection, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX); 
				if (strcmp($this->ssh_server_fp, $fingerprint) !== 0)
				{
					die($this->setTxtColor('red', 'Unable to verify server identity!'));
					return false;
				}
				else
				{
					echo $this->setTxtColor('green', 'Fingerprint successfull<br/>');
				};
			}
			else
			{
				echo 'Fingerprint not used!<br/>';
			};
			
			if($this->ssh_auth_pub == null)
			{
				if (!ssh2_auth_password($this->connection, $this->ssh_auth_user, $this->ssh_auth_pass))
				{
					die($this->setTxtColor('red', 'Autentication rejected by server'));
					return false;
				};
			}
			else
			{
				if (!ssh2_auth_pubkey_file($this->connection, $this->ssh_auth_user, $this->ssh_auth_pub, $this->ssh_auth_priv, $this->ssh_auth_pass))
				{
					die($this->setTxtColor('red', 'Autentication rejected by server'));
					return false;
				};
			};
			
			echo $this->setTxtColor('green', 'Connection successfull...<br/><br/>');
			return true;
		}
		
		public function exec($cmd, $withReturnValue = true)
		{
			echo ($withReturnValue) ? 'Command \''.$cmd.'\' will be now executed...' : '';
			if (!($stream = ssh2_exec($this->connection, $cmd)))
			{ 
				die(($withReturnValue) ? $this->setTxtColor('red', ' failed') : '');
			}
			else
			{
				echo ($withReturnValue) ? $this->setTxtColor('green', ' success<br/>') : '';
			};
			
			if($withReturnValue)
			{
				$error 			=	ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
				$data 			= 	ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
				
				stream_set_blocking($error, true);
				stream_set_blocking($data, true);
				
				$dataContent	=	stream_get_contents($data);
				return (!empty($dataContent)) ? $this->setTxtColor('green', $dataContent) : $this->setTxtColor('red', stream_get_contents($error));
			};
		}
		
		public function disconnect()
		{ 
			$this->exec('exit', false); 
			$this->connection = null; 
		}
	};
?>