<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.4 2015-05-18 07:45:15 vtouchard Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

switch($sub) {
	case 'origins':
		$admin_layout = str_replace('!!menu_sous_rub!!', $msg['origins'], $admin_layout);
		print $admin_layout;
		echo window_title($database_window_title.$msg['origins'].$msg[1003].$msg[1001]);
		include("./admin/authorities/origins.inc.php");
		break;
	case 'perso':
		$admin_layout = str_replace('!!menu_sous_rub!!', $msg['admin_menu_noti_perso'], $admin_layout);
		print $admin_layout;
		echo window_title($database_window_title.$msg['admin_menu_noti_perso'].$msg[1003].$msg[1001]);
		include("./admin/authorities/perso.inc.php");
		break;
	case 'authperso':
		$admin_layout = str_replace('!!menu_sous_rub!!', $msg['admin_menu_noti_perso'], $admin_layout);
		print $admin_layout;
		echo window_title($database_window_title.$msg['admin_menu_noti_perso'].$msg[1003].$msg[1001]);
		include("./admin/authorities/authperso.inc.php");
		break;
	case 'templates':
		$admin_layout = str_replace('!!menu_sous_rub!!', $msg['admin_menu_authperso_template'], $admin_layout);
		print $admin_layout;
		echo window_title($database_window_title.$msg['admin_menu_authperso_template'].$msg[1003].$msg[1001]);
		include("./admin/authorities/auth_templates.inc.php");
		break;
	default:
		$admin_layout = str_replace('!!menu_sous_rub!!', "", $admin_layout);
		print $admin_layout;
		echo window_title($database_window_title.$msg[131].$msg[1003].$msg[1001]);
		include("$include_path/messages/help/$lang/admin_authorities.txt");
		break;
}
