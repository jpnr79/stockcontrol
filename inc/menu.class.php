<?php
declare(strict_types=1);

namespace GlpiPlugin\Stockcontrol;

use \CommonDBTM;
use \Plugin;
use \Html;
use \Session;
use \Dropdown;
use \Request;

class PluginStockcontrolMenu extends \CommonGLPI {
   public static string $rightname = 'plugin_stockcontrol';

   public static function getMenuName(): string {
      return __('Stock Control System', 'stockcontrol');
   }

   public static function getMenuContent(): array {
      $menu = [];
      $menu['title'] = self::getMenuName();
      $menu['page'] = "/plugins/stockcontrol/front/stockcontrol.php";
      $menu['links']['search'] = "/plugins/stockcontrol/front/stockcontrol.php";
      return $menu;
   }

   public static function removeRightsFromSession(): void {
      if (isset($_SESSION['glpimenu']['tools']['types']['PluginStockcontrolMenu'])) {
         unset($_SESSION['glpimenu']['tools']['types']['PluginStockcontrolMenu']);
      }
      if (isset($_SESSION['glpimenu']['tools']['content']['pluginStockcontrolmenu'])) {
         unset($_SESSION['glpimenu']['tools']['content']['pluginStockcontrolmenu']);
      }
   }

   /**
    * @param CommonGLPI $item
    * @param int        $withtemplate
    * @return array|string
    */
   public static function getTabNameForItem(\CommonGLPI $item, int $withtemplate = 0): array|string {
      switch ($item->getType()) {
         case __CLASS__:
            return [__('Server Setup', 'stockcontrol'), __('Inventory Import', 'stockcontrol')];
         default:
            return parent::getTabNameForItem($item, $withtemplate);
      }
   }

            // ...existing code...

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
