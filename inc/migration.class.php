<?php
declare(strict_types=1);

if (!defined('GLPI_ROOT')) {
    die('Sorry. You can\'t access this file directly');
}

class PluginStockcontrolMigration
{
    private $db;

    /**
     * @param bool $do_db_checks
     */
    public function __construct($do_db_checks = true)
    {
        global $DB;
        $this->db = $DB;
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
        // Creating a table for stock items
        $table_name = 'glpi_plugin_stockcontrol_items';

        if ($this->db->tableExists($table_name)) {
            return;
        }

        $query = "CREATE TABLE `" . $table_name . "` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `quantity` BIGINT UNSIGNED NOT NULL DEFAULT 0,
            `locations_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
            `date_mod` TIMESTAMP NULL DEFAULT NULL,
            `date_creation` TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `locations_id` (`locations_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->doQuery($query);
    }

    /**
     * Uninstall logic.
     */
    public function uninstall(): void
    {
        $tables = [
            'glpi_plugin_stockcontrol_items',
        ];

        foreach ($tables as $table) {
            if ($this->db->tableExists($table)) {
                $this->db->doQuery("DROP TABLE IF EXISTS `" . $table . "`");
            }
        }
    }
}
