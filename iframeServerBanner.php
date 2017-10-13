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
	require_once("./config/config.php");
	require_once("./config/instance.php");
	require_once("./lang/lang.php");
	require_once("./php/classes/ts3admin.class.php");
	
	/*
		Variables
	*/
	$instanz					=	htmlentities($_GET['instanz']);
	$port						=	htmlentities($_GET['port']);
	$replacer = [
        "ts3" => [
            "%status%" 			=> 	"virtualserver_status",
            "%sid%" 			=> 	"virtualserver_id",
            "%sport%" 			=> 	"virtualserver_port",
            "%platform%" 		=> 	"virtualserver_platform",
            "%servername%" 		=> 	"virtualserver_name",
            "%serverversion%" 	=> 	"virtualserver_version",
            "%maxclients%" 		=>	"virtualserver_maxclients",
            "%clientsonline%" 	=> 	"virtualserver_clientsonline",
            "%channelcount%" 	=> 	"virtualserver_channelsonline",
            "%packetloss%" 		=> 	"virtualserver_total_packetloss_total",
            "%ping%" 			=> 	"virtualserver_total_ping"
        ]
    ];
	
	/*
		Banner Content
	*/
	try
	{
		/*
			Requirements
		*/
		if (!function_exists('imagettftext'))
		{
            throw new Exception ('PHP-GD not installed >> http://php.net/manual/en/book.image.php');
        };
		
        if (!is_writable('./images/ts_banner/'))
		{
            throw new Exception ('No Write Permission for Folder \'images/ts_banner/\' in Root Directory of the Interface');
        };
		
		/*
			Get Informations
		*/
		$packetmanager 	= 	json_decode(file_get_contents('./images/ts_banner/'.$instanz.'_'.$port.'_settings.json'), 1);
		
		/*
			Use already existing files
		*/
		if ((file_exists('./images/ts_banner/'.$instanz.'_'.$port.'_cached_img') && filemtime('./images/ts_banner/'.$instanz.'_'.$port.'_cached_img') > (time() - TEAMSPEAK_BANNER_REFRESH_INTERVALL))
			|| (file_exists('./images/ts_banner/'.$instanz.'_'.$port.'_cache.lock')))
		{
            showOldImage();
        };
		
		/*
			Create a new picture
		*/
		fclose(fopen('./images/ts_banner/'.$instanz.'_'.$port.'_cache.lock', "w+"));
		$image 			= 	imagecreatefrompng($packetmanager['settings']['bgimage']);
		$serverinfo		=	array();
		$clients		=	array();
		
		/*
			Get Teamspeak informations
		*/
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			$tsAdmin->selectServer($port, "port");
			$serverinfo		=	$tsAdmin->serverInfo();
			$clients		=	$tsAdmin->clientList("-ip");
			$ts3clients		=	array();
			
			if(!empty($clients['data']))
			{
				foreach($clients['data'] AS $client)
				{
					if($client['client_type'] == 0)
					{
						$ts3clients[htmlentities($client['connection_client_ip'])] = htmlentities($client['client_nickname']);
					};
				};
				
				if(!empty($ts3clients))
				{
					$clientcache = fopen('./images/ts_banner/'.$instanz.'_'.$port.'_clients.php', 'w+');
					fwrite($clientcache, '<?php $nicklist = json_decode(\''.str_replace("'", "\'", json_encode($ts3clients, 1)).'\',1);');
					fclose($clientcache);
				};
			};
		};
		
		/*
			Place text to the picture
		*/
		foreach ($packetmanager['data'] AS $text=>$textInfos)
		{
            if (!file_exists($textInfos['fontfile']))
			{
				throw new Exception ('Font File not found! Searched at '.$textInfos['fontfile'].PHP_EOL.'You may need to set the absolute path (from root directory /var/www/...)');
			};
			
            if (strpos($text, '%nickname%') !== FALSE)
			{
				continue;
			};
			
			foreach ($replacer['ts3'] as $k => $v)
			{
				$text 		= 	str_replace($k, $serverinfo['data'][$v], $text);
			};
			$text 			= 	str_replace('%realclients%', $serverinfo['data']['virtualserver_clientsonline']-$serverinfo['data']['virtualserver_queryclientsonline'], $text);
			$text 			= 	str_replace('%ping_floored%', floor(htmlentities($serverinfo['data']['virtualserver_total_ping'])), $text);
			$text 			= 	str_replace('%packetloss_00%', round(htmlentities($serverinfo['data']['virtualserver_total_packetloss_total']), 2, PHP_ROUND_HALF_DOWN), $text);
			$text 			= 	str_replace('%packetloss_floored%', floor(htmlentities($serverinfo['data']['virtualserver_total_packetloss_total'])), $text);
            $text 			= 	str_replace('%timeHi%', date("H:i"), $text);
            $text 			= 	str_replace('%timeHis%', date("H:i:s"), $text);
            $text 			= 	str_replace('%date%', date("d.m.Y"), $text);
			
            paintText($image, $textInfos['fontsize'], $textInfos['x'], $textInfos['y'], $textInfos['color'], $textInfos['fontfile'], $text);
        };
		
		foreach ($packetmanager['custom'] AS $text=>$textInfos)
		{
            if (!file_exists($textInfos['fontfile']))
			{
				throw new Exception ('Font File not found! Searched at '.$textInfos['fontfile'].PHP_EOL.'You may need to set the absolute path (from root directory /var/www/...)');
			};
			
            if (strpos($text, '%nickname%') !== FALSE)
			{
				continue;
			};
			
			paintText($image, $textInfos['fontsize'], $textInfos['x'], $textInfos['y'], $textInfos['color'], $textInfos['fontfile'], $textInfos['text']);
		};
		
		/*
			Clearing
		*/
		imagepng($image, './images/ts_banner/'.$instanz.'_'.$port.'_cached_img');
        unlink('./images/ts_banner/'.$instanz.'_'.$port.'_cache.lock');
        header('Content-Type: image/png');
        imagepng(getImage());
        imagedestroy($image);
	}
	catch (Exception $e)
	{
        echo $e->getMessage();
        if(file_exists('./images/ts_banner/'.$instanz.'_'.$port.'_cache.lock'))
		{
			unlink('./images/ts_banner/'.$instanz.'_'.$port.'_cache.lock');
		};
    };
	
	function showOldImage()
	{
		global $instanz;
		global $port;
		
		$i = 0;
		while (file_exists('./images/ts_banner/'.$instanz.'_'.$port.'_cache.lock')) {
			if ($i >= 10) throw new Exception ('Cache Lock exists... Please Remove the File \''.$instanz.'_'.$port.'_cache.lock\' in Folder \'cache\' manually if it still exists after this Error!');
			$i++;
			sleep(1);
		}
		header('Content-Type: image/png');
		imagepng(getImage());
		die();
	};
	
	function getImage()
	{
		global $instanz, $port;
		
		if(file_exists('./images/ts_banner/'.$instanz.'_'.$port.'_clients.php'))
		{
			require_once('./images/ts_banner/'.$instanz.'_'.$port.'_clients.php');
		};
		
        $packetmanager 		= 	json_decode(file_get_contents('./images/ts_banner/'.$instanz.'_'.$port.'_settings.json'), 1);
        $image 				= 	imagecreatefrompng('./images/ts_banner/'.$instanz.'_'.$port.'_cached_img');
		
		
		if (!empty($nicklist[getIp()]))
		{
			$nickname 		= 	$nicklist[getIp()];
			foreach ($packetmanager['data'] AS $text=>$textInfos)
			{
				if (strpos($text, '%nickname%') !== FALSE)
				{
					paintText($image, $textInfos['fontsize'], $textInfos['x'], $textInfos['y'], $textInfos['color'], $textInfos['fontfile'], str_replace('%nickname%', $nickname, $text));
				};
			};
		};
		
        return $image;
    };
	
	function getIp()
	{
        if (isset($_SERVER['HTTP_CLIENT_IP']))
		{
            return $_SERVER['HTTP_CLIENT_IP'];
		}
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
		{
            return $_SERVER['HTTP_X_FORWARDED'];
		}
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
		{
            return $_SERVER['HTTP_FORWARDED_FOR'];
		}
        else if(isset($_SERVER['HTTP_FORWARDED']))
		{
            return $_SERVER['HTTP_FORWARDED'];
		}
        else if(isset($_SERVER['REMOTE_ADDR']))
		{
            return $_SERVER['REMOTE_ADDR'];
		}
        else
		{
            return NULL;
		};
    };
	
	function paintText($image, $fontsize, $xpos, $ypos, $color, $fontfile, $text)
	{
        $hex = str_replace("#", "", $color);
        if(strlen($hex) == 3)
		{
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        }
		else
		{
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        };
        imagettftext($image,$fontsize,0,$xpos,$ypos,imagecolorallocate($image, $r, $g, $b),$fontfile,$text);
        return;
    };
?>