# First-Coder Teamspeak 3 Webinterface #
###### written by L.Gmann ######

## About us

First-Coder is a study project, which is specialized to providing you various developments for free, 
what would cost normally a lot of money. Realized were a couple of different projects in completely 
different programming languages. A view projects in completely different programming languages would 
realize.

## Getting Started

The Interface is able to run on Windows and Linux platforms. This Interface need following configured packages:
* Webserver (apache, nginx, xampp)
* Database (MySQL or PostgreSQL)
* PHP 5.6+
* Basic knowledge of the linux shell (if you´re using a linux server)

### Supported languages

* German
* English
* Italian
* Spain
* French

### Prerequisites

Before we start we need to be sure that all packages are installed. We need the php SOAP extension. If you are using Linux pleaes type the following command in you shell. It will install the SOAP extension and restart the webservice.

PHP 5.6
```
sudo apt-get install php-soap
sudo systemctl restart apache2.service
```

PHP 7.0+
```
sudo apt-get install php7.0-soap
sudo systemctl restart apache2.service
```

### Installing

1. Download the Interface from our [Download Homepage](https://first-coder.de/index.php#download)
2. Upload all files into your webserver /var/www/MY-INTERFACE
3. Give the webuser full permissions to the files
4. Open the Link and following the instructions

### After installation

After you have installed the Interface you can login and add your instance. You will find it at the right side of you site. Be sure that your Interface is already added in your Teamspeak whitelist. Otherwise the Interface will get banned.

## Built With

* [Bootstrap v4.0.0](http://getbootstrap.com) - The web framework used
* [ts3admin.class.php](http://ts3admin.info) - Teamspeak framework
* [PHPMailer](https://github.com/PHPMailer/PHPMailer) - Mail lib

## More information

If you want more information above this Interface please visit our [Homepage](https://first-coder.de). If you have still questions or problems with it please write in our [Forum](https://forum.first-coder.de).

## Authors

* **L. Gmann** - *Developer and Manager*
* **SoulofSorrow** - *Sponsor and Manager*
* **@ndy** - *Forum Manager*

## License

This project is licensed under the GNU GPLv3 License - see the [LICENSE.md](LICENSE.md) file for details

## Changelog
You can find [here](https://first-coder.de/index.php#changelog) always the up to date changelog

## Screenshots
Login Area:
![Login Area](https://first-coder.de/images/1.3.10/login.png)

Dashboard:
![Profile](https://first-coder.de/images/1.3.10/profil_dashboard.png)

Profile:
![Profile](https://first-coder.de/images/1.3.10/profil_edit.png)

Profile permissions:
![Profile Permissions](https://first-coder.de/images/1.3.10/profil_permissions.png)

Main Settings:
![Main Settings](https://first-coder.de/images/1.3.10/admin_settings.png)

Instance Settings:
![Instance Settings](https://first-coder.de/images/1.3.10/admin_instance.png)

User Settings:
![User Settings 1](https://first-coder.de/images/1.3.10/admin_client.png)

TeamSpeak Main Site:
![TeamSpeak Main Site](https://first-coder.de/images/1.3.10/teamspeak_serverlist.png)

TeamSpeak Info:
![TeamSpeak Info](https://first-coder.de/images/1.3.10/teamspeak_serverview.png)

TeamSpeak server requests:
![TeamSpeak Server Requests](https://first-coder.de/images/1.3.10/teamspeak_server_requests.png)

TeamSpeak Server Create:
![TeamSpeak Server Create](https://first-coder.de/images/1.3.10/teamspeak_create_server.png)
