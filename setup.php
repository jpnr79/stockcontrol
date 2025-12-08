<?php
declare(strict_types=1);

function plugin_init_stockcontrol() {
    // Registra classes (usando FQCN)
    Plugin::registerClass('GlpiPlugin\\Stockcontrol\\PluginStockcontrolStock', ['addtabon' => ['ConsumableItem','Computer']]);
    Plugin::registerClass('GlpiPlugin\\Stockcontrol\\PluginStockcontrolConsumables');
    Plugin::registerClass('GlpiPlugin\\Stockcontrol\\PluginStockcontrolMenu');
    // ...existing code...
}
// ...existing code...

function plugin_version_stockcontrol() {
    return [
        'name'           => 'Stock Control',
        'version'        => '2.0.0',
        'author'         => 'Seu Nome',
        'license'        => 'GPLv2+',
        'homepage'       => 'https://github.com/jpnr79/stockcontrol',
        'requirements'   => [
            'glpi' => [
                'min' => '11.0.0',
                'max' => '12.0.0'
            ],
            'php' => [
                'min' => '8.4.0'
            ]
        ]
    ];
}

function plugin_stockcontrol_check_prerequisites() {
    // Verifique se a versão do GLPI é compatível
    if (version_compare(GLPI_VERSION, '11.0.0', '<')) {
        echo "This plugin requires GLPI >= 11.0.0";
        return false;
    }
    if (version_compare(PHP_VERSION, '8.4', '<')) {
        echo "This plugin requires PHP 8.4 or above.";
        return false;
    }
    return true;
}
function plugin_stockcontrol_check_config() {
    return true;
}
