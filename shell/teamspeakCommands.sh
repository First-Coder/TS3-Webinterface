#!/bin/sh

############################################################################################
# teamspeakCommands.sh for the First-Coder Werbinterface
############################################################################################
# Autor: L.Gmann
############################################################################################
# Last edit: 29.11.2016
############################################################################################
#	This program is free software: you can redistribute it and/or modify
#	it under the terms of the GNU General Public License as published by
#	the Free Software Foundation, either version 3 of the License, or
#	any later version.
#
#	This program is distributed in the hope that it will be useful,
#	but WITHOUT ANY WARRANTY; without even the implied warranty of
#	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#	GNU General Public License for more details.
#
#	You should have received a copy of the GNU General Public License
#	along with this program.  If not, see <http://www.gnu.org/licenses/>.
#	
#	for help look http://first-coder.de/
############################################################################################
#	Exit Errors:
#		0:	No Errors
#		1:	Parameter Error
#		2:	Folder in Parameter 1 is not found
#		3:	Folder isn't a Teamspeakfolder
#		4:	Server tried to start as root
#		5:	Server could not connect external Server
#		6:	Server remote key was not found
############################################################################################

############################################################################################
# Define Colors
############################################################################################
COLOR_RED="\033[33;31m"
COLOR_GREEN="\033[33;32m"
COLOR_ORANGE="\033[33;38m"
COLOR_RESET="\033[33;36m"
COLOR_DEFAULT="\033[33;39m"

############################################################################################
# Welcome Echo && Check Connection
############################################################################################
clear
echo "${COLOR_RESET}#################################################################"
echo "Welcome to the Teamspeak Commandsscript from First-Coder"
echo "@autor L.Gmann"
echo "#################################################################"
echo ""

############################################################################################
# Check Key
############################################################################################
if find "shell/$1" > /dev/null 2>&1; then
	echo "${COLOR_GREEN}#################################################################"
	echo "Private key was found ;)"
	echo "#################################################################${COLOR_RESET}"
	echo ""
else
	echo "${COLOR_RED}#################################################################"
	echo "ERROR ! Private key not found. Upload the Private key under shell/$1"
	echo "#################################################################${COLOR_RESET}"
	echo "${COLOR_DEFAULT}"
	exit 6
fi

############################################################################################
# Check Connection
############################################################################################
if [ $# -eq 5 ]; then
	echo "#################################################################"
	echo "Connect to external Server..."
	if ssh -i "shell/$1" -p $3 $1@$2 exit; then
		echo "${COLOR_GREEN}Connection successfull to Server $2:$3${COLOR_RESET}"
		echo "#################################################################"
		echo ""
		echo "#################################################################"
		echo "Loading the Path..."
	else
		echo "${COLOR_RED}Can not connect to Server $2:$3${COLOR_RESET}"
		echo "#################################################################"
		echo "${COLOR_DEFAULT}"
		exit 5
	fi
else
	echo "${COLOR_RED}Usage: ${0} {user} {server-ip} {port} {path-to-teamspeakfolder} {start|stop|restart}"
	echo "${COLOR_DEFAULT}"
	exit 1
fi

############################################################################################
# Check User
############################################################################################
if [ "$1" = "root" ]; then
	echo "${COLOR_RED}#################################################################"
	echo "ERROR ! For security reasons we can not start the Server"
	echo "with the user ROOT"
	echo "#################################################################${COLOR_RESET}"
	echo "${COLOR_DEFAULT}"
	exit 4
fi

############################################################################################
# Go to Teamspeak Path
############################################################################################
BINARYNAME="ts3server"
if ! ssh -i "shell/$1" -p $3 $1@$2 cd "${4}";
	then
		echo "${COLOR_RED}Could not find the Teamspeak folder path :/${COLOR_RESET}"
		echo "#################################################################"
		echo "${COLOR_DEFAULT}"
		exit 2
	else
		echo "${COLOR_GREEN}Path $4 found :)${COLOR_RESET}"
		echo "#################################################################"
		echo ""
		echo "#################################################################"
		echo "Checking Teamspeakfolder..."
fi

############################################################################################
# Check Teamspeak folder
############################################################################################
if ssh -i "shell/$1" -p $3 $1@$2 stat $4"/ts3server" \> /dev/null 2\>\&1;
	then
		echo "${COLOR_GREEN}Teamspeakfolder is correct :)${COLOR_RESET}"
		echo "#################################################################"
		echo ""
	else
		echo "${COLOR_RED}This is not a Teamspeakfolder :/!${COLOR_RESET}"
		echo "#################################################################"
		echo "${COLOR_DEFAULT}"
		exit 3
fi

############################################################################################
# Parameter Check
############################################################################################
case "$5" in
	start)
		echo "#################################################################"
		echo "Starting the Teamspeakserver...${COLOR_ORANGE}"
		ssh -i "shell/$1" -p $3 $1@$2 $4"/ts3server_startscript.sh start"
		echo "${COLOR_RESET}#################################################################"
	;;
	stop)
		echo "#################################################################"
		echo "Stopping the Teamspeakserver...${COLOR_ORANGE}"
		ssh -i "shell/$1" -p $3 $1@$2 $4"/ts3server_startscript.sh stop"
		echo "${COLOR_RESET}#################################################################"
	;;
	restart)
		echo "#################################################################"
		echo "Restarting the Teamspeakserver...${COLOR_ORANGE}"
		ssh -i "shell/$1" -p $3 $1@$2 $4"/ts3server_startscript.sh restart"
		echo "${COLOR_RESET}#################################################################"
	;;
esac

echo "${COLOR_DEFAULT}"
exit 0

