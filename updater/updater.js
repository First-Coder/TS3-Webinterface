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
	Choose a new Version
*/
function ShowChangelog(VersionNumber, possibleUpdate = true)
{
	$(".step-1").slideUp("slow", function()
	{
		firstFix		=	true;
		firstRemove		=	true;
		
		$.ajax({
			type: "POST",
			url: "functions.php",
			data: {
				action:			'getChangelog',
				versionnumber:	VersionNumber
			},
			success: function(data){
				var informations 	=	JSON.parse(data);
				
				if(informations[0].includes("ver"))
				{
					$(".changelogHeadline").text(informations[0]);
					$(".changelogTime").text(informations[1]);
					for(var i = 2;i < informations.length;i++)
					{
						if(informations[i][0] == "-" && informations[i][1] == "-")
						{
							if(firstFix)
							{
								document.getElementById("changelogContent").innerHTML += "<hr/><p>"+informations[i]+"</p>";
								firstFix				=	false;
							}
							else
							{
								document.getElementById("changelogContent").innerHTML += "<p>"+informations[i]+"</p>";
							};
						}
						else
						{
							switch(informations[i][0])
							{
								case "+":
									document.getElementById("changelogContent").innerHTML += "<p class=\"text-success\">"+informations[i]+"</p>";
									break;
								case "-":
									if(firstRemove)
									{
										document.getElementById("changelogContent").innerHTML += "<hr/><p class=\"text-danger-no-cursor\">"+informations[i]+"</p>";
										firstRemove		=	false;
									}
									else
									{
										document.getElementById("changelogContent").innerHTML += "<p class=\"text-danger-no-cursor\">"+informations[i]+"</p>";
									};
									break;
							};
						};
					};
				}
				else
				{
					$(".changelogHeadline").text(informations);
				};
				
				if(possibleUpdate)
				{
					$("#updateAction").attr("onClick", "updateNow('"+VersionNumber+"')");
					$("#updateAction").css("display", "inline");
				}
				else
				{
					$("#updateAction").css("display", "none");
				};
				
				$(".step-2").slideDown("slow");
			}
		});
	});
};

/*
	Update the Webinterface
*/
function updateNow(VersionNumber)
{
	$(".step-2").slideUp("slow", function()
	{
		$(".step-3").slideDown("slow", function()
		{
			var dataString 		= 	'action=updateWi&versionnumber='+VersionNumber;
			$.ajax({
				type: "POST",
				url: "functions.php",
				data: dataString,
				success: function(data){
					$(".step-3").slideUp("slow", function()
					{
						document.getElementById("changeOutput").innerHTML = data;
						$(".step-4").slideDown("slow");
					});
				}
			});
		});
	});
};

/*
	Back to Versionlist
*/
function backToMainMenu()
{
	parent.window.location.reload();
};