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
	require_once("config.php");
	
	/*
		Installed Languages (add here your language if you have a new one)
	*/
	$language												=	array();
	$installedLanguages										=	array(
																	"german" => "First-Coder.de",
																	"english" => "lorixon.com",
																	"italian" => "Grafic404",
																	"turkish" => "sezerondr"
																);
	if(LANGUAGE == '' || LANGUAGE == 'english')
	{
		include("lang/en.php");	
	}
	else if(LANGUAGE == 'german')
	{
		include("lang/de.php");
	}
	else if(LANGUAGE == 'italian')
	{
		include("lang/it.php");
	}
	else if(LANGUAGE == 'turkish')
	{
		include("lang/tr.php");
	};
?>