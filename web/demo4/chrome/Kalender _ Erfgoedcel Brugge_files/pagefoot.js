$(document).ready(function()
{
	//-- Mobiel: menu links laten open-/dichtklappen
	$('nav.navigation-mobile ul li.submenu a.first-level-a, nav.navigation-mobile-small ul li.submenu a.first-level-a').click(function()
	{
		var objUlSubmenu = $(this).parent().find('ul.second-level');
		if (objUlSubmenu.css('display') == "none")
		{
			objUlSubmenu.slideDown('fast');
		}
		else
		{
			objUlSubmenu.slideUp('fast');
		}
	});
});
