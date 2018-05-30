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
		Check Requirements
	*/
	$requireSoap				=	extension_loaded("soap");
	$requireZip					=	extension_loaded("zip");
	$requirePhp					=	(float)phpversion() >= 5.3;
	$requireFilePerm			=	true;
	
	/*
		Get donwloaded version
	*/
	$downloadVersion = "Unknown";
	if($requireZip)
	{
		$fileData = function() {
			$file = fopen('zip://interface.zip#php/functions/functions.php', 'r');
			if (!$file)
			{
				die('Can not read zip file! Please be sure, that we have in this folder the interface.zip file!');
			};
			
			while (($line = fgets($file)) !== false)
			{
				yield $line;
			};

			fclose($file);
		};

		foreach ($fileData() as $line) {
			if(strpos($line, "define(\"INTERFACE_VERSION\"") !== false)
			{
				$lineparts = explode("\"", $line);
				$downloadVersion = $lineparts[3];
			};
		};
	};
	
	/*
		Get Newest Interface Version
	*/
	$latestVersion = "Unknown";
	$supporters[] = array('name' => 'L.Gmann', 'job' => 'Manager and Developer');
	$supporters[] = array('name' => 'SoulofSorrow', 'job' => 'Manager and Sponsor');
	
	if($requireSoap)
	{
		try
		{
			$client = new SoapClient(null, array(
				'location' => 'http://wiki.first-coder.de/soap/soap_server_version_two.php',
				'uri' => 'https://wiki.first-coder.de/soap/soap_server_version_two.php'
			));
			
			$latestVersion = $client->getNewestVersion(true, "");
			$supporters = $client->getTeam();
		}
		catch(Exception $e)
		{
			$latestVersion = "Connection failed";
		};
	};
	
	/*
		Check permissions
	*/
	$arrayFiles	=	array(
						"bg.png",
						"fcLogo.png",
						"interface.zip",
						"helper.php",
						"index.php"
					);
	$handle = @opendir("./");
	while ($file = @readdir ($handle))
    {
		if(in_array($file, $arrayFiles) || (strpos($file, "temp") !== false && strpos($file, ".php") !== false))
		{
			if($requireFilePerm && !is_writable($file))
			{
				$requireFilePerm	=	false;
			};
		};
	};
?>

<html lang="en">
	<head>
		<title>First-Coder -- Teamspeak3 Control Panel -- Installer</title>
		
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=0.8, minimum-scale=0.8, maximum-scale=0.8, shrink-to-fit=no">
		<meta name="author" content="First Coder: L.Gmann">
		<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
		<meta http-equiv="Pragma" content="no-cache" />
		<meta http-equiv="Expires" content="0" />
		
		<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
		<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
		<link rel="stylesheet" type="text/css" href="https://first-coder.de/resource-installer/css/bootstrap-material-design.min.css" />
		<link rel="stylesheet" type="text/css" href="https://first-coder.de/resource-installer/css/material-bootstrap-wizard.css" />
		<link rel="stylesheet" type="text/css" href="https://first-coder.de/resource-installer/css/style.css" />
		
		<script src="https://first-coder.de/resource-installer/js/jquery.min.js"></script>
		<script src="https://first-coder.de/resource-installer/js/jquery.bootstrap.js"></script>
		<script src="https://first-coder.de/resource-installer/js/popper.js"></script>
		<script src="https://first-coder.de/resource-installer/js/arrive.js"></script>
		<script src="https://first-coder.de/resource-installer/js/bootstrap-material-design.min.js"></script>
		<script src="https://first-coder.de/resource-installer/js/material-bootstrap-wizard.js"></script>
		<script src="https://first-coder.de/resource-installer/js/installer.js"></script>
	</head>
	<body>
		<div class="image-container set-full-height" style="background-image: url('bg.png')">
			<div class="container">
				<div class="row">
					<div class="ml-auto mr-auto col-md-10">
						<div class="wizard-container">
							<div class="card wizard-card" data-color="green" id="wizard">
								<div class="wizard-header">
									<h3 class="wizard-title">
										First-Coder Teamspeak3 Control Panel
									</h3>
									<h5>Developments that make a difference</h5>
								</div>
								
								<div id="alert" class="alert alert-danger" role="alert" style="display: none;">
									<b>Databaseinformation incomplete</b><br/>
									Please fill out all fields! You can't leave a input field empty!
								</div>
								
								<div class="wizard-navigation">
									<ul>
										<li class="nav-item"><a class="nav-link" href="#Informations" data-toggle="tab">Informations</a></li>
										<li class="nav-item"><a class="nav-link" href="#Requirements" data-toggle="tab">Requirements</a></li>
										<li class="nav-item delete-step-2"><a class="nav-link <?php echo ($requireFilePerm && $requirePhp && $requireSoap) ? "" : "disabled"; ?>" href="#Guidelines" data-toggle="tab">Guidelines</a></li>
									</ul>
								</div>

								<div class="tab-content">
									<div class="tab-pane" id="Informations">
										<div class="row delete-step-2">
											<div class="mt-auto mb-auto col-sm-4">
												<img class="img-fluid" src="fcLogo.png">
											</div>
											<div class="col-sm-8">
												<h3><b>First-Coder Teamspeak Control Panel</b></h3>
												<p class="mb-0">Downloaded Version: <font class="text-<?php echo ($downloadVersion == $latestVersion || $latestVersion == "Connection failed") ? "success" : "danger"; ?>"><?php echo $downloadVersion; ?></font></p>
												<p>Newest Version: <font class="text-<?php echo ($latestVersion != "Connection failed") ? "success" : "danger"; ?>"><?php echo $latestVersion; ?></font></p>
												<h3 class="mt-2"><b>First-Coder Team</b></h3>
												<?php foreach($supporters as $supporter) { ?>
													<P class="mb-0"><?php echo $supporter['name']." as ".$supporter['job']; ?></p>
												<?php }; ?>
											</div>
										</div>
									</div>
									<div class="tab-pane" id="Requirements">
										<div class="row delete-step-2">
											<div class="col-sm-12">
												<div class="float-left ml-2">
													<h3><b>SOAP Modul</b></h3>
													<p>SOAP Modul is <?php echo ($requireSoap) ? "activated": "deactivated"; ?>!</p>
												</div>
												<div class="float-right mt-2 mr-2">
													<div class="icon icon-<?php echo ($requireSoap) ? "success": "danger"; ?>">
														<i class="material-icons"><?php echo ($requireSoap) ? "check": "close"; ?></i>
													</div>
												</div>
											</div>
											<div class="col-sm-12">
												<div class="float-left ml-2">
													<h3><b>Zip Modul</b></h3>
													<p>Zip Modul is <?php echo ($requireZip) ? "activated": "deactivated"; ?>!</p>
												</div>
												<div class="float-right mt-2 mr-2">
													<div class="icon icon-<?php echo ($requireZip) ? "success": "danger"; ?>">
														<i class="material-icons"><?php echo ($requireZip) ? "check": "close"; ?></i>
													</div>
												</div>
											</div>
											<div class="col-sm-12">
												<div class="float-left ml-2">
													<h3><b>PHP Version</b></h3>
													<p>You have installed PHP Version <?php echo (float)phpversion(); ?>!<br/>We need a higher PHP Version then 5.3.</p>
												</div>
												<div class="float-right mt-2 mr-2">
													<div class="icon icon-<?php echo ((float)phpversion() >= 5.3) ? "success": "danger"; ?>">
														<i class="material-icons"><?php echo ((float)phpversion() >= 5.3) ? "check": "close"; ?></i>
													</div>
												</div>
											</div>
											<div class="col-sm-12">
												<div class="float-left ml-2">
													<h3><b>File permissions</b></h3>
													<p>The installation files in the folder has <?php echo ($requireFilePerm) ? "" : "<b>not</b>"; ?> all permissions. <?php echo ($requireFilePerm) ? "" : "Please give them <i>write</i> permission!"; ?></p>
												</div>
												<div class="float-right mt-2 mr-2">
													<div class="icon icon-<?php echo ($requireFilePerm) ? "success": "danger"; ?>">
														<i class="material-icons"><?php echo ($requireFilePerm) ? "check": "close"; ?></i>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="tab-pane" id="Guidelines">
										<h3><b>General Privacy Polica</b></h3>
										<p class="mb-0">If you either connect or download files from servers owned and used by the domain "first-coder.de", there will be data saved in our logfiles. There is no possibility of recognition or following to you as person.</p>
										<p class="mb-0">Saved are: Your username, date and time of your access as well as the page you accessed, the quota of your access and the state of your connection (means if the connection was successful. Further your IP-Adress is logge for security purposes.</p>
										<p class="mb-0">That data will not be used commercially. It is possible to decline this saving by mailing us at "admin@first-coder.de". Your data will only be used by us - either statistically or for security scans.</p>
										
										<h3><b>Disclaimer</b></h3>
										<p class="mb-0"><b>1. Content</b></p>
										<p class="mb-0">The author reserves the right not to be responsible for the topicality, correctness, completeness or quality of the information provided. Liability claims regarding damage caused by the use of any information provided, including any kind of 
														information which is incomplete or incorrect,will therefore be rejected. All offers are not-binding and without obligation. Parts of the pages or the complete publication including all offers and information might be extended, changed or partly 
														or completely deleted by the author without separate announcement.</p>
										<p class="mb-0"><b>2. Referrals and links</b></p>
										<p class="mb-0">The author is not responsible for any contents linked or referred to from his pages - unless he has full knowledge of illegal contents and would be able to prevent the visitors of his site fromviewing those pages. If any damage occurs by the use 
														of information presented there, only the author of the respective pages might be liable, not the one who has linked to these pages. Furthermore the author is not liable for any postings or messages published by users of discussion boards, guestbooks 
														or mailinglists provided on his page.</p>
										<p class="mb-0"><b>3. Copyright</b></p>
										<p class="mb-0">The author intended not to use any copyrighted material for the publication or, if not possible, to indicate the copyright of the respective object. 
														The copyright for any material created by the author is reserved. Any duplication or use of objects such as images, diagrams, sounds or texts in other electronic or printed publications is not permitted without the author's agreement.</p>
										<p class="mb-0"><b>4. Privacy policy</b></p>
										<p class="mb-0">If the opportunity for the input of personal or business data (email addresses, name, addresses) is given, the input of these data takes place voluntarily. The use and payment of all offered services are permitted - if and so far technically possible 
														and reasonable - without specification of any personal data or under specification of anonymized data or an alias. The use of published postal addresses, telephone or fax numbers and email addresses for marketing purposes is prohibited, offenders sending 
														unwanted spam messages will be punished.</p>
										<p class="mb-0"><b>5. Legal validity of this disclaimer</b></p>
										<p class="mb-0">This disclaimer is to be regarded as part of the internet publication which you were referred from. If sections or individual terms of this statement are not legal or correct, the content or validity of the other parts remain uninfluenced by this fact.</p>
									</div>
								</div>
								<div class="wizard-footer">
									<div class="pull-right">
										<button type='button' class='btn btn-next btn-fill btn-success btn-wd'>Next</button>
										<button onClick="StartInstallation();" type='button' class='btn btn-finish btn-fill btn-success btn-wd'>I accept</button>
									</div>
									<div class="pull-left">
										<button type='button' class='btn btn-previous btn-fill btn-default btn-wd'>Previous</button>
									</div>
									<div class="clearfix"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>