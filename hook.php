<?php

use Glpi\Toolbox\PluginMigration;

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_init_stockcontrol() {
   // No specific hook registration needed for now, but the function should exist.
   return true;
}

/**
 * Install hook
 *
 * @return boolean
 */
function plugin_stockcontrol_install()
{
   // Ensure the core PluginMigration class is loaded.
   if (!class_exists(PluginMigration::class)) {
      return false;
   }

   include_once __DIR__ . '/inc/migration.class.php';
   PluginMigration::makeMigration('stockcontrol', PluginStockcontrolMigration::class);
   return true;
}

/**
 * Uninstall hook
 *
 * @return boolean
 */
function plugin_stockcontrol_uninstall()
{
   include_once __DIR__ . '/inc/migration.class.php';

   $migration = new PluginStockcontrolMigration(false); // Pass false to skip parent constructor
   $migration->uninstall();

   return true;
}

/**
 * Get the plugin version.
 *
 * @return array
 */
function plugin_version_stockcontrol()
{
    return [
        'version'      => '1.0.0',
        'requirements' => [
            'glpi' => ['min' => '11.0', 'max' => '12.0'],
        ],
    ];
}
