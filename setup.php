<?php
 
function plugin_version_stockcontrol() {
    return array('name'           => "Stock Control System",
                 'version'        => '1.0.6',
                 'author'         => 'Paul Furnival',
                 'license'        => 'GPLv2+',
                 'homepage'       => '',
                 'requirements'   => [
                                        'glpi'   => [
                                             'min' => '9.1',
                                             'max' => '9.4.3'
                                        ]
                                     ]);
}



function plugin_stockcontrol_check_config($verbose = false) {
   return true;
}
       
function plugin_stockcontrol_check_prerequisites() {
    // Check that the GLPI version is compatible
    if (version_compare(GLPI_VERSION, '9.4.2', 'lt') || version_compare(GLPI_VERSION, '9.4.2', 'gt')) {
        echo "This plugin Requires GLPI version 9.4.2";
        return false;
    }
    return true;
}


function plugin_init_stockcontrol() {
     global $PLUGIN_HOOKS;

#    Plugin::registerClass('PluginStockcontrolStock',array('addtabon' => array('Computer')));
#    Plugin::registerClass('PluginStockcontrolMenu',array());
        /*
         * if (Session::haveRight("plugin_ocsinventoryng", READ)) {
         *     $PLUGIN_HOOKS['menu_toadd']['ocsinventoryng'] = ['tools' => 'PluginOcsinventoryngMenu'];
         * }
         */

    $PLUGIN_HOOKS['menu_toadd']['stockcontrol'] = ['assets' => 'PluginStockcontrolMenu',
                                                  'tools' => 'PluginStockcontrolMenu'];
    $PLUGIN_HOOKS['csrf_compliant']['stockcontrol'] = true;
#    $PLUGIN_HOOKS['helpdesk_menu_entry']['stockcontrol'] = true;
#    $PLUGIN_HOOKS['menu_entry']['stockcontrol'] = true;
#    $PLUGIN_HOOKS['submenu_entry']['stockcontrol']['add'] = '/front/index.php';

    // Display a menu entry ?
#    if (Session::haveRight("plugin_stockcontrol", READ)) {
#          $PLUGIN_HOOKS['menu_toadd']['stockcontrol'] = ['tools' => 'PluginstockcontrolMenu'];
#    }
}
?>
