# First-Coder Teamspeak 3 Webinterface #
###### Be careful! This version is still in the Alpha / Beta. If you want to have a stable version please use the version 1 of this interface! ######

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

1. Download the Interface from our [Download Homepage](https://first-coder.de/teamspeak/download)
2. Upload all files into your webserver /var/www/MY-INTERFACE
3. Give the webuser full permissions to the files. You can also give them all 0777 permissions (chmod -R 777 /var/www/html) cause after the installation all files will be removed!
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

This project is licensed under the GNU GPLv3 License - see the [LICENSE.txt](LICENSE.txt) file for details

## Changelog
You can find [here](https://first-coder.de/index.php?download#changelog) always the up to date changelog

## Screenshots
Comming soon...
