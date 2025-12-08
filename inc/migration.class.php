<?php

if (!defined('GLPI_ROOT')) {
    die('Sorry. You can\'t access this file directly');
}

class PluginStockcontrolMigration extends \Glpi\Toolbox\PluginMigration
{
    /**
     * @param bool $do_db_checks
     */
    public function __construct($do_db_checks = true)
    {
        // Overload the constructor to allow instantiation without DB connection for uninstall.
        // The `uninstall` method will initialize its own DB connection.
    }

    public static function getMigrationSteps(): array
    {
        // Map your plugin versions to migration methods.
        // This is where you'll add steps for future updates.
        return [
            '1.0.0' => 'migrationTo100',
        ];
    }

    /**
     * Migration to version 1.0.0.
     * Creates the initial database tables.
     */
    public function migrationTo100(): void
    {
        // Example: creating a table for stock items.
        $table_name = 'glpi_plugin_stockcontrol_items';

        if (!$this->db->tableExists($table_name)) {
            $this->db->createTable(
                $table_name,
                "
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `quantity` INT(11) NOT NULL DEFAULT 0,
                `locations_id` INT(11) NOT NULL DEFAULT 0,
                `date_mod` TIMESTAMP NULL DEFAULT NULL,
                `date_creation` TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `name` (`name`),
                KEY `locations_id` (`locations_id`)
                ",
                [
                    'engine'  => 'InnoDB',
                    'charset' => 'utf8mb4',
                    'collate' => 'utf8mb4_unicode_ci',
                ]
            );
        }
    }

    /**
     * Uninstall logic.
     */
    public function uninstall(): void
    {
        global $DB; // Get DB connection for uninstall.

        $tables = [
            'glpi_plugin_stockcontrol_items',
            // Add other plugin tables here
        ];

        foreach ($tables as $table) {
            if ($DB->tableExists($table)) {
                $DB->dropTable($table);
            }
        }
    }
}
