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

(function($){$.fn.contextMenu=function(settings){return this.each(function(){$(this).on("contextmenu",function(e){if(e.ctrlKey)return;var $menu=$(settings.menuSelector).data("invokedOn",$(e.target)).show(function(){var $invokedOn=$(e.target);settings.menuShow.call(this,$invokedOn)}).css({position:"absolute",left:getMenuPosition(e.clientX-80,'width','scrollLeft'),top:getMenuPosition(e.clientY-390,'height','scrollTop')}).off('click').on('click','a',function(e){$menu.hide();var $invokedOn=$menu.data("invokedOn");var $selectedMenu=$(e.target);settings.menuSelected.call(this,$invokedOn,$selectedMenu)});return!1});$('body').click(function(event){$(settings.menuSelector).hide()})});function getMenuPosition(mouse,direction,scrollDir){var win=$(window)[direction](),scroll=$(window)[scrollDir](),menu=$(settings.menuSelector)[direction](),position=mouse+scroll;if(mouse+menu>win&&menu<mouse) position-=menu;return position}}})(jQuery,window)