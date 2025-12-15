<?php
/*if (!defined('GLPI_ROOT')) { define('GLPI_ROOT', realpath(__DIR__ . '/../..')); }
/*
 * @version $Id: HEADER 1 2015-12-14 15:06 kartnico $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
// ----------------------------------------------------------------------
// Original Author of file: Nicolas BOURGES
// Purpose of file: plugin winadminpassword v1.1.2 - GLPI 0.90
// ----------------------------------------------------------------------
 */

// Installation function
function plugin_winadminpassword_install(): bool {
	return plugin_winadminpassword_update();
}

// Update function (called during install and upgrade)
function plugin_winadminpassword_update(): bool {
	global $DB;

	$migration = new Migration('2.0.0');

	if (!$DB->tableExists("glpi_plugin_winadminpassword_profiles")) {
		$migration->addPreQuery(
			"CREATE TABLE IF NOT EXISTS `glpi_plugin_winadminpassword_profiles` (
				`id` int unsigned NOT NULL,
				`profile` varchar(255) DEFAULT NULL,
				`use` tinyint(1) DEFAULT 0,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
		);
	}

	if (!$DB->tableExists("glpi_plugin_winadminpassword_configs")) {
		$migration->addPreQuery(
			"CREATE TABLE IF NOT EXISTS `glpi_plugin_winadminpassword_configs` (
				`id` int unsigned auto_increment NOT NULL,
				`key` varchar(255) DEFAULT NULL,
				`length` int(11) DEFAULT 12,
				`algo` int(11) DEFAULT 1,
				`size` int(11) DEFAULT 14,
				`color` varchar(255) DEFAULT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
		);
	}

	$migration->executeMigration();

	// Now add initial profile entry after tables are created
	if (!$DB->tableExists("glpi_plugin_winadminpassword_profiles")) {
		return false;
	}

	$prof = new PluginWinadminpasswordProfile();
	if (!$prof->getFromDB($_SESSION['glpiactiveprofile']['id'])) {
		include_once (GLPI_ROOT . '/plugins/winadminpassword/inc/profile.class.php');
		$prof->add([
			'id'      => $_SESSION['glpiactiveprofile']['id'],
			'profile' => $_SESSION['glpiactiveprofile']['name'],
			'use'     => 1
		]);
	}

	return true;
}

// Uninstall function
function plugin_winadminpassword_uninstall(): bool {
	global $DB;

	$migration = new Migration('2.0.0');

	$tables = ["glpi_plugin_winadminpassword_profiles", "glpi_plugin_winadminpassword_configs"];
	
	foreach($tables as $table) {
		if ($DB->tableExists($table)) {
			$migration->dropTable($table);
		}
	}

	$migration->executeMigration();

	return true;
}
