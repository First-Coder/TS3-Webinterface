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
	
	require_once(__dir__."/../config/config.php");
	
	$updater 			= 	new SoapClient(null, array(
		'location' => 'http://wiki.first-coder.de/soap/soap_server.php',
		'uri' => 'https://wiki.first-coder.de/soap/soap_server.php'
	));
	
	/*
		Returns the Changelog of a spezific Version
	*/
	if($_POST['action'] == 'getChangelog')
	{
		echo json_encode($updater->getChangelog($_POST['versionnumber']));
	};
	
	/*
		Update the Webinterface
	*/
	if($_POST['action'] == 'updateWi')
	{
		$filename		=	'First-Coder-TS3-Webinterface-'.$_POST['versionnumber'].'-update.zip';
		
		// Download the files
		if (!is_file('First-Coder-TS3-Webinterface-'.$_POST['versionnumber'].'.zip'))
		{
			echo '<p>Downloading New Update</p>';
			
			$newUpdate = file_get_contents('https://teamspeak.first-coder.de/getUpdate.php?mail='.DONATOR_MAIL.'&version='.$_POST['versionnumber']);
			$dlHandler = fopen($filename, 'w');
			
			if(!fwrite($dlHandler, $newUpdate))
			{
				unlink($filename);
				echo '<p class="text-danger">&raquo; Could not save new update. Operation aborted.</p>';
				exit();
			};
			fclose($dlHandler);
			
			echo '<p class="text-success">&raquo; Update Downloaded And Saved</p>';
		}
		else
		{
			echo '<p class="text-success">&raquo; Update already downloaded.</p>';
		};
		
		// Rewrite the files
		echo '<ul>';
		$permissionCheck		=	true;
		$updateSuccess			=	true;
		$zipHandle 				= 	zip_open($filename);
		
		while($aF = zip_read($zipHandle))
		{
			$thisFileName 		= 	zip_entry_name($aF);
			$thisFileDir 		= 	dirname($thisFileName);
			
			//Continue if its not a file
			if(substr($thisFileName,-1,1) == '/')
			{
				continue;
			};
			
			$tmpFolder			=	"";
			foreach(explode("/", $thisFileDir) AS $folder)
			{
				$tmpFolder		.=	$folder;
				
				//Make the directory if we need to...
				if(!is_dir('../'.$tmpFolder))
				{
					if(mkdir ('../'.$tmpFolder))
					{
						echo '<li>Created Directory '.$tmpFolder.'</li>';
					}
					else
					{
						echo '<li>Created Directory '.$tmpFolder.'..... failed</li>';
					};
				};
				
				$tmpFolder		.=	"/";
			};
			
			if(!is_dir('../'.$thisFileName) && file_exists('../'.$thisFileName))
			{
				if($thisFileName != 'upgrade.php' && !is_writable("../".$thisFileName))
				{
					if(!chmod("../".$thisFileName, 0666))
					{
						echo '<li>'.$thisFileName.'........... FAILED (CAN NOT SET PERMISSION TO 0666)</li>';
						$permissionCheck	=	false;
					};
				};
			};
		};
		
		// Check local config
		if(!is_writable(__dir__."/../config/config.php"))
		{
			if(!chmod(__dir__."/../config/config.php", 0666))
			{
				echo '<li>config/config.php........... FAILED (CAN NOT SET PERMISSION TO 0666)</li>';
				$permissionCheck	=	false;
			};
		};
		
		$zipHandle 				= 	zip_open($filename);
		if($permissionCheck)
		{
			while($aF = zip_read($zipHandle))
			{
				$thisFileName 		= 	zip_entry_name($aF);
				
				//Continue if its not a file
				if(substr($thisFileName,-1,1) == '/')
				{
					continue;
				};
			    
				//Overwrite the file
				if(!is_dir('../'.$thisFileName))
				{
					echo '<li>'.$thisFileName.'...........';
					$contents 			= 	zip_entry_read($aF, zip_entry_filesize($aF));
					$updateThis 		= 	'';
				   
					//If we need to run commands, then do it.
					if($thisFileName == 'upgrade.php')
					{
						$upgradeExec = fopen ('upgrade.php','w');
						fwrite($upgradeExec, $contents);
						fclose($upgradeExec);
						include('upgrade.php');
						unlink('upgrade.php');
						echo ' EXECUTED</li>';
					}
					else
					{
						$updateThis 	= 	fopen('../'.$thisFileName, 'w');
						fwrite($updateThis, $contents);
						fclose($updateThis);
						unset($contents);
						echo ' UPDATED</li>';
					};
				};
			};
		}
		else
		{
			$updateSuccess				=	false;
		};
		echo '</ul>';
		unlink($filename);
		if($updateSuccess)
		{
			echo '<p class="text-success">&raquo; CMS Updated to '.json_decode($updater->getVersionList(DONATOR_MAIL))[$_POST['versionnumber']].'</p>';
		}
		else
		{
			echo '<p class="text-danger">&raquo; CMS Update to '.json_decode($updater->getVersionList(DONATOR_MAIL))[$_POST['versionnumber']].' failed</p>';
		};
	};
?>