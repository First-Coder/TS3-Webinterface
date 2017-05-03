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
	require_once("../config.php");
	
	/*
		Installed Languages
	*/
	$language												=	array();
	
	/*
		Default / English language
	*/
	if(LANGUAGE == '' || LANGUAGE == 'english')
	{
		$language['webinterfaceinstallation']					=	"Installation of Webinterface";
		
		// Buttons
		$language['weiter']										=	"Next";
		$language['erneut_versuchen']							=	"Try again";
		
		// Fortschrittsanzeige
		$language['datenrichtlinien']							=	"Privacy Policy";
		$language['datenbankverbindung']						=	"Databaseconnection";
		$language['einstellungen']								=	"Settings";
		$language['rechte_setzen']								=	"Set Permissions";
		
		// Step 0
		$language['choose_language']							=	"Choose Language";
		
		// Step 1
		$language['allgemeiner_datenschutzhinweis']				=	"General Privacy Polica";
		$language['allgemeiner_datenschutzhinweis_1']			=	"If you either connect or download files from servers owned and used by the domain \"first-coder.de\", there will be data saved in our logfiles. There is no possibility of recognition or following to you as person.";
		$language['allgemeiner_datenschutzhinweis_2']			=	"Saved are: Your username, date and time of your access as well as the page you accessed, the quota of your access and the state of your connection (means if the connection was successful. Further your IP-Adress is logge for security purposes..";
		$language['allgemeiner_datenschutzhinweis_3']			=	"That data will not be used commercially. It is possible to decline this saving by mailing us at \"info@first-coder.de\"";
		$language['allgemeiner_datenschutzhinweis_4']			=	"Your data will only be used by us - either statistically or for security scans.";
		$language['haftungsausschluss']							=	"Disclaimer";
		$language['haftungsausschluss_1']						=	"1. Content";
		$language['haftungsausschluss_1_1']						=	"The author reserves the right not to be responsible for the topicality, correctness, completeness or quality of the information provided. Liability claims regarding damage caused by the use of any information provided, including any kind of 
																	information which is incomplete or incorrect,will therefore be rejected. All offers are not-binding and without obligation. Parts of the pages or the complete publication including all offers and information might be extended, changed or partly 
																	or completely deleted by the author without separate announcement.";
		$language['haftungsausschluss_2']						=	"2. Referrals and links";
		$language['haftungsausschluss_2_1']						=	"The author is not responsible for any contents linked or referred to from his pages - unless he has full knowledge of illegal contents and would be able to prevent the visitors of his site fromviewing those pages. If any damage occurs by the use 
																	of information presented there, only the author of the respective pages might be liable, not the one who has linked to these pages. Furthermore the author is not liable for any postings or messages published by users of discussion boards, guestbooks 
																	or mailinglists provided on his page.";
		$language['haftungsausschluss_3']						=	"3. Copyright";
		$language['haftungsausschluss_3_1']						=	"The author intended not to use any copyrighted material for the publication or, if not possible, to indicate the copyright of the respective object. 
																	The copyright for any material created by the author is reserved. Any duplication or use of objects such as images, diagrams, sounds or texts in other electronic or printed publications is not permitted without the author's agreement.";
		$language['haftungsausschluss_4']						=	"4. Privacy policy";
		$language['haftungsausschluss_4_1']						=	"If the opportunity for the input of personal or business data (email addresses, name, addresses) is given, the input of these data takes place voluntarily. The use and payment of all offered services are permitted - if and so far technically possible 
																	and reasonable - without specification of any personal data or under specification of anonymized data or an alias. The use of published postal addresses, telephone or fax numbers and email addresses for marketing purposes is prohibited, offenders sending 
																	unwanted spam messages will be punished.";
		$language['haftungsausschluss_5']						=	"5. Legal validity of this disclaimer";
		$language['haftungsausschluss_5_1']						=	"This disclaimer is to be regarded as part of the internet publication which you were referred from. If sections or individual terms of this statement are not legal or correct, the content or validity of the other parts remain uninfluenced by this fact.";
		$language['accept_1']									=	"I have read this privacy policy and agree to it.";
		$language['accept_2']									=	"I have read the disclaimer and agree to it.";
		$language['accept_3']									=	"I am aware of the state of this page and the possibility that this could damage my own software, if I use it (BETA).";
		
		// Step 2
		$language['datenbankdaten_angeben']						=	"Fill in your databaseinformations";
		$language['hostname']									=	"Hostname";
		$language['datenbank']									=	"Database";
		$language['benutzername']								=	"Username";
		$language['passwort']									=	"Password";
		$language['datenbank_pruefen']							=	"Check Database";
		$language['datenbank_erstellen']						=	"Create Database";
		$language['datenbank_wird_erstellt']					=	"Database is being created.";
		$language['ja']											=	"Yes";
		$language['konsole']									=	"Console";
		
		// Step 3
		$language['benutzerdaten_angeben']						=	"Fill in your userinformation";
		$language['benutzerdaten_angeben_1']					=	"The user will be created using above data. He will be a super user with global assigned permissions.";
		$language['benutzerdaten_angeben_2']					=	"Caution: Your username has to be an email-address and the password must contain at least one capital letter, one letter and one number using at least 6 characters";
		$language['benutzer_wird_erstellt']						=	"User is being created";
		$language['homepagetitel']								=	"Homepagetitle";
		$language['teamspeakname']								=	"Teamspeakname";
		$language['uebernehmen']								=	"Submit";
		$language['falsches_passwort']							=	"That password is wrong!";
		$language['falscher_benutzer']							=	"That user is wrong!";
		
		// Step 4
		$language['ordnername']									=	"Directoryname";
		$language['rechte']										=	"Permissions";
		$language['rechte_1']									=	"Please check red marked folders for all writing permissions (777) by using FTP or  SSH.";
		$language['rechte_2']									=	"If you do not give the \"install\" folder permissions delete the folder manually!";
		$language['weiter_info']								=	"The next step will delete the \"install\" folder!";
		
		// Step 5
		$language['done_1']										=	"Congratulations!";
		$language['done_2']										=	"Please click the button below to end the installation. You will be redirected to the login!";
		$language['fertigstellen']								=	"Log in";
		$language['install_ordner_failed']						=	"\"install\" folder could not be deleted!";
		$language['install_ordner_loeschen']					=	"Please delete the \"install\" folder manually!";
	}
	/*
		German language
	*/
	else if(LANGUAGE == 'german')
	{
		$language['webinterfaceinstallation']					=	"Webinterfaceinstallation";
		
		// Buttons
		$language['weiter']										=	"Weiter";
		$language['erneut_versuchen']							=	"Erneut versuchen";
		
		// Fortschrittsanzeige
		$language['datenrichtlinien']							=	"Datenrichtlinien";
		$language['datenbankverbindung']						=	"Datenbankverbindung";
		$language['einstellungen']								=	"Einstellungen";
		$language['rechte_setzen']								=	"Rechte setzen";
		
		// Step 0
		$language['choose_language']							=	"Sprache w&auml;hlen";
		
		// Step 1
		$language['allgemeiner_datenschutzhinweis']				=	"Allgemeiner Datenschutzhinweis";
		$language['allgemeiner_datenschutzhinweis_1']			=	"Wenn Sie auf den Internet-Auftritt des Teamspeak 3 Webinterfaces von First-Coder aufrufen oder Daten von diesen Seiten herunterladen, werden hier&uuml;ber
																	Informationen von uns in einer Protokolldatei gespeichert und verarbeitet. Dieser Vorgang erfolgt anonymisiert. R&uuml;ckschl&uuml;sse auf Ihre Person sind nicht m&ouml;glich.";
		$language['allgemeiner_datenschutzhinweis_2']			=	"Gespeichert werden: Ihr Benutzername, das Datum und die Uhrzeit des Seitenaufrufs, die aufgerufene Seite bzw. der Name der abgerufenen Datei, die &uuml;bertragene Datenmenge
																	und die Meldung, ob der Zugriff/Abruf erfolgreich war. Au&beta;erdem wird einmalig die IP-Adresse des Webinterfaces an First-Coder.de gesendet.";
		$language['allgemeiner_datenschutzhinweis_3']			=	"Diese Daten werden nicht zu kommerziellen Zwecken genutzt. Die Auswertung dient allein der statistischen Aufbereitung und Verbesserung unseres Teamspeak Webinterfaces. 
																	Sie k&ouml;nnen dieser Datenerhebung, -speicherung und -verarbeitung jederzeit per E-Mail an info@first-coder.de widersprechen.";
		$language['allgemeiner_datenschutzhinweis_4']			=	"Ihre Daten werden ausschlie&beta;lich von uns verwendet. Eine Weitergabe an Dritte erfolgt nicht.";
		$language['haftungsausschluss']							=	"Haftungsausschluss";
		$language['haftungsausschluss_1']						=	"1. Inhalt des Onlineangebotes";
		$language['haftungsausschluss_1_1']						=	"Der Autor &uuml;bernimmt keinerlei Gew&auml;hr f&uuml;r die Aktualit&auml;t, Korrektheit, Vollst&auml;ndigkeit oder Qualit&auml;t der bereitgestellten Informationen. Haftungsanspr&uuml;che gegen den 
																	Autor, welche sich auf Sch&auml;den materieller oder ideeller Art beziehen, die durch die Nutzung oder Nichtnutzung der dargebotenen Informationen bzw. durch die 
																	Nutzung fehlerhafter und unvollst&auml;ndiger Informationen verursacht wurden, sind grunds&auml;tzlich ausgeschlossen, sofern seitens des Autors kein nachweislich 
																	vors&auml;tzliches oder grob fahrl&auml;ssiges Verschulden vorliegt. 
																	Alle Angebote sind freibleibend und unverbindlich. Der Autor beh&auml;lt es sich ausdr&uuml;cklich vor, Teile der Seiten oder das gesamte Angebot ohne gesonderte Ank&uuml;ndigung 
																	zu ver&auml;ndern, zu erg&auml;nzen, zu l&ouml;chen oder die Ver&ouml;ffentlichung zeitweise oder endg&uuml;ltig einzustellen.";
		$language['haftungsausschluss_2']						=	"2. Verweise und Links";
		$language['haftungsausschluss_2_1']						=	"Bei direkten oder indirekten Verweisen auf fremde Webseiten (\"Hyperlinks\"), die au&beta;erhalb des Verantwortungsbereiches des Autors liegen, w&uuml;rde eine 
																	Haftungsverpflichtung ausschlie&beta;lich in dem Fall in Kraft treten, in dem der Autor von den Inhalten Kenntnis hat und es ihm technisch m&ouml;glich und zumutbar w&auml;re, 
																	die Nutzung im Falle rechtswidriger Inhalte zu verhindern. 
																	Der Autor erkl&auml;rt hiermit ausdr&uuml;cklich, dass zum Zeitpunkt der Linksetzung keine illegalen Inhalte auf den zu verlinkenden Seiten erkennbar waren. Auf die aktuelle 
																	und zuk&uuml;nftige Gestaltung, die Inhalte oder die Urheberschaft der verlinkten/verkn&uuml;pften Seiten hat der Autor keinerlei Einfluss. Deshalb distanziert er sich 
																	hiermit ausdr&uuml;cklich von allen Inhalten aller verlinkten /verkn&uuml;pften Seiten, die nach der Linksetzung ver&auml;ndert wurden. Diese Feststellung gilt f&uuml;r alle innerhalb 
																	des eigenen Internetangebotes gesetzten Links und Verweise sowie f&uuml;r Fremdeintr&auml;ge in vom Autor eingerichteten G&auml;steb&uuml;chern, Diskussionsforen, Linkverzeichnissen, 
																	Mailinglisten und in allen anderen Formen von Datenbanken, auf deren Inhalt externe Schreibzugriffe m&ouml;glich sind. F&uuml;r illegale, fehlerhafte oder unvollst&auml;ndige 
																	Inhalte und insbesondere f&uuml;r Sch&auml;den, die aus der Nutzung oder Nichtnutzung solcherart dargebotener Informationen entstehen, haftet allein der Anbieter der Seite, 
																	auf welche verwiesen wurde, nicht derjenige, der &uuml;ber Links auf die jeweilige Ver&ouml;ffentlichung lediglich verweist.";
		$language['haftungsausschluss_3']						=	"3. Urheber- und Kennzeichenrecht";
		$language['haftungsausschluss_3_1']						=	"Der Autor ist bestrebt, in allen Publikationen die Urheberrechte der verwendeten Bilder, Grafiken, Tondokumente, Videosequenzen und Texte zu beachten, von ihm 
																	selbst erstellte Bilder, Grafiken, Tondokumente, Videosequenzen und Texte zu nutzen oder auf lizenzfreie Grafiken, Tondokumente, Videosequenzen und Texte 
																	zur&uuml;ckzugreifen. 
																	Alle innerhalb des Internetangebotes genannten und ggf. durch Dritte gesch&uuml;tzten Marken- und Warenzeichen unterliegen uneingeschr&auml;nkt den Bestimmungen des jeweils 
																	g&uuml;ltigen Kennzeichenrechts und den Besitzrechten der jeweiligen eingetragenen Eigent&uuml;mer. Allein aufgrund der blo&beta;en Nennung ist nicht der Schluss zu ziehen, dass 
																	Markenzeichen nicht durch Rechte Dritter gesch&uuml;tzt sind! 
																	Das Copyright f&uuml;r ver&ouml;ffentlichte, vom Autor selbst erstellte Objekte bleibt allein beim Autor der Seiten. Eine Vervielf&auml;ltigung oder Verwendung solcher Grafiken, 
																	Tondokumente, Videosequenzen und Texte in anderen elektronischen oder gedruckten Publikationen ist ohne ausdr&uuml;ckliche Zustimmung des Autors nicht gestattet.";
		$language['haftungsausschluss_4']						=	"4. Datenschutz";
		$language['haftungsausschluss_4_1']						=	"Sofern innerhalb des Internetangebotes die M&ouml;glichkeit zur Eingabe pers&ouml;nlicher oder gesch&auml;ftlicher Daten (Emailadressen, Namen, Anschriften) besteht, so erfolgt 
																	die Preisgabe dieser Daten seitens des Nutzers auf ausdr&uuml;cklich freiwilliger Basis. Die Inanspruchnahme und Bezahlung aller angebotenen Dienste ist - soweit 
																	technisch m&ouml;glich und zumutbar - auch ohne Angabe solcher Daten bzw. unter Angabe anonymisierter Daten oder eines Pseudonyms gestattet. Die Nutzung der im Rahmen 
																	des Impressums oder vergleichbarer Angaben ver&ouml;ffentlichten Kontaktdaten wie Postanschriften, Telefon- und Faxnummern sowie Emailadressen durch Dritte zur 
																	&Uuml;bersendung von nicht ausdr&uuml;cklich angeforderten Informationen ist nicht gestattet. Rechtliche Schritte gegen die Versender von sogenannten Spam-Mails bei 
																	Verst&ouml;ssen gegen dieses Verbot sind ausdr&uuml;cklich vorbehalten.";
		$language['haftungsausschluss_5']						=	"5. Rechtswirksamkeit dieses Haftungsausschlusses";
		$language['haftungsausschluss_5_1']						=	"Dieser Haftungsausschluss ist als Teil des Internetangebotes zu betrachten, von dem aus auf diese Seite verwiesen wurde. Sofern Teile oder einzelne Formulierungen 
																	dieses Textes der geltenden Rechtslage nicht, nicht mehr oder nicht vollst&auml;ndig entsprechen sollten, bleiben die &uuml;brigen Teile des Dokumentes in ihrem Inhalt und 
																	ihrer G&uuml;ltigkeit davon unber&uuml;hrt.";
		$language['accept_1']									=	"Ich habe die Allgemeinen Datenschutzhinweise gelesen und erkl&auml;re mich einverstanden.";
		$language['accept_2']									=	"Ich habe den Haftungsausschluss gelesen und erkl&auml;re mich einverstanden.";
		$language['accept_3']									=	"Mir ist bewusst, dass diese Seite aufgrund ihres Entwicklungsstandes Fehler beinhalten k&ouml;nnte, die bleibende Sch&auml;den auf meinem Teamspeakserver hinterlassen k&ouml;nnten.";
		
		// Step 2
		$language['datenbankdaten_angeben']						=	"Datenbankdaten angeben";
		$language['hostname']									=	"Hostname";
		$language['datenbank']									=	"Datenbank";
		$language['benutzername']								=	"Benutzername";
		$language['passwort']									=	"Passwort";
		$language['datenbank_pruefen']							=	"Datenbank pr&uuml;fen";
		$language['datenbank_erstellen']						=	"Datenbank erstellen";
		$language['datenbank_wird_erstellt']					=	"Datenbank wird erstellt";
		$language['ja']											=	"Ja";
		$language['konsole']									=	"Konsole";
		
		// Step 3
		$language['benutzerdaten_angeben']						=	"Benutzerdaten angeben";
		$language['benutzerdaten_angeben_1']					=	"Es wird der obenstehende Benutzer erstellt. Dieser bekommt alle Globale Rechte f&uuml;r das Webinterface. Alle weiteren Einstellung bez&uuml;glich der Instanzen werden 
																	dann im Webinterface vorgenommen.";
		$language['benutzerdaten_angeben_2']					=	"Bitte beachtet, dass der Benutzername eine E-Mail Adresse sein muss und das Passwort mindestens einen Gro&szlig;buchstaben, einen Kleinbuchstaben und eine Zahl besitzen muss!";
		$language['benutzer_wird_erstellt']						=	"Benutzer wird erstellt";
		$language['homepagetitel']								=	"Homepagetitel";
		$language['teamspeakname']								=	"Teamspeakname";
		$language['uebernehmen']								=	"&Uuml;bernehmen";
		$language['falsches_passwort']							=	"Das Passwort ist falsch!";
		$language['falscher_benutzer']							=	"Der Benutzer ist falsch!";
		
		// Step 4
		$language['ordnername']									=	"Ordnername";
		$language['rechte']										=	"Rechte";
		$language['rechte_1']									=	"Bitte geben Sie nun manuell folgenden Ordnern \"0777\" Rechte, die Rot makiert sind.";
		$language['rechte_2']									=	"Falls Sie dies nicht tun, l&iuml;schen sie bitte den \"install\" Ordner manuell!";
		$language['weiter_info']								=	"Beim n&auml;chsten Schritt wird der \"install\" Ordner gel&ouml;scht!";
		
		// Step 5
		$language['done_1']										=	"Herzlichen Gl&uuml;ckwunsch!";
		$language['done_2']										=	"Bitte klicken sie auf fertigstellen, um die Installation abzuschlie&beta;en. Sie werden dabei zum Webinterface weitergeleitet.";
		$language['fertigstellen']								=	"Fertigstellen";
		$language['install_ordner_failed']						=	"\"install\" Ordner konnte nicht gel&ouml;scht werden!";
		$language['install_ordner_loeschen']					=	"Bitte l&ouml;schen Sie den \"install\" Ordner manuell!";
	};
?>