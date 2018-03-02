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
		Info:
		Advanced: This settings are just in this config editable.
	*/
	
	/*
		Advanced
		
		If you have sponsored this project, you can add here your paypal mail. If you have
		sponsored more then 20 euro, you will get the new Version 2 weeks earlier. This a
		thank you for all supporter and not a payment. You will also get the new version
		without donations.
	*/
	define("DONATOR_MAIL", "");
	
	/*
		SQL Login Information
	*/
	define("SQL_Hostname", "");
	define("SQL_Datenbank", "");
	define("SQL_Username", "");
	define("SQL_Password", "");
	define("SQL_Port", "3306");
	define("SQL_Mode", "mysql");
	define("SQL_SSL", "0");
	
	/*
		Mailsettings
	*/
	define("USE_MAILS", "true");
	define("MAILADRESS", "mail@my-domain.de");
	define("MAIL_SMTP", "false");
	define("MAIL_SMTP_HOST", "");
	define("MAIL_SMTP_PORT", "");
	define("MAIL_SMTP_USERNAME", "");
	define("MAIL_SMTP_PASSWORD", "");
	define("MAIL_SMTP_ENCRYPTION", "off");				// Possible values: off, tls, ssl
	define("MAIL_SMTP_DEBUG", "1");						// SMTP Debug options 1 = Errors & Messages 2 = Only Messages
	
	/*
		Teamspeak 3 Heading (Dont take a Word that is to long)
	*/
	define("HEADING", "First-Coder");
	
	/*
		With this name will the Webinterface & Botinterface connect to your Teamspeakserver
	*/
	define("TS3_CHATNAME", "TS3-Servewache");
	
	/*
		Language from your Webinterface (default: english)
	*/
	define("LANGUAGE", "english");
	
	/*
		Insert here your Stylesheet you want to use (it must be in /css/themes/)
		Leave blank to use the normal Style
	*/
	define("STYLE", "");
	
	/*
		Masterserver, to show your Main / Supportserver on your Webpage
		Note: If you want use this feature make sure its on the Webpagesettings set!
	*/
	define("MASTERSERVER_INSTANZ", "");
	define("MASTERSERVER_PORT", "");
	
	/*
		Change the Newspage to a custompage. You can find the custom page under /config/custompages/custom_news.php
		
		Default is "false"
	*/
	define("CUSTOM_NEWS_PAGE", "false");
	
	/*
		Change the Loginpage to a custompage. You can find the custom page under /config/custompages/custom_dashboard.php
		
		Default is "false"
	*/
	define("CUSTOM_DASHBOARD_PAGE", "false");
	
	/*
		Advanced
		Change the Interval for all Teamspeakviewers in milliseconds. If you want to
		deaktivate the interval and just want one build (like normal viewers), write -1.
		
		Warning
		Please go under 2000 with the value, cause if your internet connection is not that
		good, the site will crash your Browser!
		
		Examples
			2000	=	2 seconds (default)
			4000	=	4 seconds
			-1		=	just load once
	*/
	define("TEAMSPEAKTREE_INTERVAL", "2000");
	
	/*
		Advanced
		Hide specific servergroups in your teamspeakviewer. Default is empty.
		
		Examples
			2,3,4 	=	Servergroups 2, 3 and 4 will be hide
			234,123	=	servergroups 234 and 123 will be hide
	*/
	define("TEAMSPEAKTREE_HIDE_SGROUPS", "");
	
	/*
		Advanced
		Change the Interval of Permissionscheck for every Client he is connected. If you want to
		deaktivate the interval, write -1.
		
		Warning
		Please go under 2000 with the value, cause if your internet connection is not that
		good, the site will crash your Browser!
		
		Examples
			10000	=	10 seconds (default)
			2000	=	2 seconds
			4000	=	4 seconds
			-1		=	deaktivated
	*/
	define("CHECK_CLIENT_PERMS", "10000");
	
	/*
		Advanced
		Change if you can delete a Ticket (complete deleted in the database)
		
		Default is "true"
		
		false		=>	Can be closed but not deleted
		true		=>	Can be closed and after that deleted
	*/
	define("TICKET_CAN_BE_DELETED", "true");
	
	/*
		Advanced
		Set the mount of max Clients they will be searched on a Teamspeakserver.
		https://github.com/par0noid/ts3admin.class/issues/27#event-1061704498
		
		Default is "9000000"
	*/
	define("GET_DB_CLIENTS", "9000000");
	
	/*
		Advanced
		Here you can edit, how long will be the created teamspeak banner image used.
		
		Examples
			60		=	1 minute (default)
			120		=	2 minutes
			300		=	5 minutes
	*/
	define("TEAMSPEAK_BANNER_REFRESH_INTERVALL", "60");
	
	/*
		Advanced
		Default Teamspeak "Server Create" Configuration
	*/
	$ts3_server_create_default['servername']						=	'First Coder Teamspeak 3 Server';
	$ts3_server_create_default['slots']								=	'32';
	$ts3_server_create_default['welcome_message']					=	'Willkommen auf unserem Teamspeak 3 Server';
	$ts3_server_create_default['reserved_slots']					=	'0';
	$ts3_server_create_default['password']							=	'';
	
	$ts3_server_create_default['host_message']						=	'';
	$ts3_server_create_default['host_message_show']					=	'0';				// Host message show 0=No message 1=Show message in Log 2=Show modal message 3=Modal message and exit
	$ts3_server_create_default['host_url']							=	'';
	$ts3_server_create_default['host_banner_url']					=	'';
	$ts3_server_create_default['host_banner_int']					=	'';
	$ts3_server_create_default['host_button_gfx']					=	'';
	$ts3_server_create_default['host_button_tip']					=	'';
	$ts3_server_create_default['host_button_url']					=	'';
	
	$ts3_server_create_default['auto_ban_count']					=	'';
	$ts3_server_create_default['auto_ban_time']						=	'';
	$ts3_server_create_default['remove_time']						=	'';
	
	$ts3_server_create_default['points_tick_reduce']				=	'';
	$ts3_server_create_default['points_needed_block_cmd']			=	'';
	$ts3_server_create_default['points_needed_block_ip']			=	'';
	
	$ts3_server_create_default['upload_bandwidth_limit']			=	'';
	$ts3_server_create_default['upload_quota']						=	'';
	$ts3_server_create_default['download_bandwidth_limit']			=	'';
	$ts3_server_create_default['download_quota']					=	'';
	
	$ts3_server_create_default['virtualserver_log_client']			=	'1';				// Log Clients no=0 yes=1
	$ts3_server_create_default['virtualserver_log_query']			=	'0';				// Log Query no=0 yes=1
	$ts3_server_create_default['virtualserver_log_channel']			=	'0';				// Log Channel no=0 yes=1
	$ts3_server_create_default['virtualserver_log_permissions']		=	'1';				// Log Permissions no=0 yes=1
	$ts3_server_create_default['virtualserver_log_server']			=	'1';				// Log Server no=0 yes=1
	$ts3_server_create_default['virtualserver_log_filetransfer']	=	'0';				// Log Filetransfer no=0 yes=1
?>