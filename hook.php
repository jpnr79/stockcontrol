<?php


use Glpi\Toolbox\PluginMigration;


/**
 * Install hook
 *
 * @return boolean
 */
function plugin_stockcontrol_install()
{
   try {
      include_once __DIR__ . '/inc/migration.class.php';
      $migration = new PluginStockcontrolMigration(true);
      
      // Execute migration steps
      $steps = PluginStockcontrolMigration::getMigrationSteps();
      foreach ($steps as $version => $method) {
          if (method_exists($migration, $method)) {
              $migration->$method();
          }
      }
      
      return true;
   } catch (Exception $e) {
      error_log("Stockcontrol install error: " . $e->getMessage());
      return false;
   }
}

/**
 * Uninstall hook
 *
 * @return boolean
 */
function plugin_stockcontrol_uninstall()
{
   // Ensure the class is loaded by the autoloader before we include a file that extends it.
   if (!class_exists(PluginMigration::class)) {
      return false;
   }
   include_once __DIR__ . '/inc/migration.class.php';
   $migration = new PluginStockcontrolMigration();
   $migration->uninstall();
   return true;
}

