<?php


use Glpi\Toolbox\PluginMigration;


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

