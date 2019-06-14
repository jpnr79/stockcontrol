<?php
class PluginStockcontrolMenu extends CommonGLPI {
   static $rightname = 'plugin_stockcontrol';

   static function getMenuName() {
      return 'Stock Control System';
   }

   static function getMenuContent() {

      $menu                    = [];
      $menu['title']           = self::getMenuName();
      $menu['page']            = "/plugins/stockcontrol/front/stockcontrol.php";
      $menu['links']['search'] = "/plugins/stockcontrol/front/stockcontrol.php";

      return $menu;
   }

   /**
    *
    * /
   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['tools']['types']['PluginStockcontrolMenu'])) {
         unset($_SESSION['glpimenu']['tools']['types']['PluginStockcontrolMenu']);
      }
      if (isset($_SESSION['glpimenu']['tools']['content']['pluginStockcontrolgmenu'])) {
         unset($_SESSION['glpimenu']['tools']['content']['pluginStockincontrolmenu']);
      }
   }

   /**
    * @param CommonGLPI $item
    * @param int        $withtemplate
    *
    * @return array|string
    * /
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      global $CFG_GLPI;

      switch ($item->getType()) {
         case __CLASS__ :
            $dbu = new DbUtils();
            $ocsServers = $dbu->getAllDataFromTable('glpi_plugin_ocsinventoryng_ocsservers',
                                               ["is_active" => 1]);
            if (!empty($ocsServers)) {

               $ong[0] = __('Server Setup', 'ocsinventoryng');

               $ong[1] = __('Inventory Import', 'ocsinventoryng');

               $ong[2] = __('IPDiscover Import', 'ocsinventoryng');

               //if (isset($_POST["plugin_ocsinventoryng_ocsservers_id"])) {
               //   $_SESSION["plugin_ocsinventoryng_ocsservers_id"] = $_POST["plugin_ocsinventoryng_ocsservers_id"];
               //} else {
               //   $_SESSION["plugin_ocsinventoryng_ocsservers_id"] = PluginOcsinventoryngOcsServer::getFirstServer();
               //}

               if (isset($_SESSION["plugin_ocsinventoryng_ocsservers_id"])
                   && $_SESSION["plugin_ocsinventoryng_ocsservers_id"] > 0) {
                  if (PluginOcsinventoryngOcsServer::checkOCSconnection($_SESSION["plugin_ocsinventoryng_ocsservers_id"])) {

                     $ocsClient = new PluginOcsinventoryngOcsServer();
                     $client    = $ocsClient->getDBocs($_SESSION["plugin_ocsinventoryng_ocsservers_id"]);
                     $version   = $client->getTextConfig('GUI_VERSION');
                     $snmp      = $client->getIntConfig('SNMP');
                     if ($version > $ocsClient::OCS2_1_VERSION_LIMIT && $snmp) {
                        $ong[3] = __('SNMP Import', 'ocsinventoryng');
                     }
                  }
               }
            } else {
               $ong = [];
               echo "<div align='center'>";
               echo "<i class='fas fa-exclamation-triangle fa-4x' style='color:orange'></i>";
               echo "<br>";
               echo "<div class='red b'>";
               echo __('No OCSNG server defined', 'ocsinventoryng');
               echo "<br>";
               echo __('You must to configure a OCSNG server', 'ocsinventoryng');
               echo " : <a href='" . $CFG_GLPI["root_doc"] . "/plugins/ocsinventoryng/front/ocsserver.form.php'>";
               echo __('Add a OCSNG server', 'ocsinventoryng');
               echo "</a>";
               echo "</div></div>";
            }
            return $ong;

         default :
            return '';
      }
   }

   /**
    * @param CommonGLPI $item
    * @param int        $tabnum
    * @param int        $withtemplate
    *
    * @return bool
    * /
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 0, $withtemplate = 0) {

      if ($item->getType() == __CLASS__) {
         $ocs    = new PluginOcsinventoryngOcsServer();
         $ipdisc = new PluginOcsinventoryngIpdiscoverOcslink();
         $snmp   = new PluginOcsinventoryngSnmpOcslink();
         switch ($tabnum) {
            case 0 :
               $ocs->setupMenu($_SESSION["plugin_ocsinventoryng_ocsservers_id"]);
               break;

            case 1 :
               $ocs->importMenu($_SESSION["plugin_ocsinventoryng_ocsservers_id"]);
               break;

            case 2 :
               $ipdisc->ipDiscoverMenu();
               break;

            case 3 :
               $snmp->snmpMenu($_SESSION["plugin_ocsinventoryng_ocsservers_id"]);
               break;

         }
      }
      return true;
   }

   /**
    * @param array $options
    *
    * @return array
    * /
   function defineTabs($options = []) {

      $ong = [];

      $this->addStandardTab(__CLASS__, $ong, $options);

      return $ong;
   }
   */
}
