<?php
declare(strict_types=1);

namespace GlpiPlugin\Stockcontrol;

use \CommonDBTM;
use \Plugin;
use \Html;
use \Session;
use \Dropdown;
use \Request;



class PluginStockcontrolConsumables extends \CommonDBTM {
    private $fromDate;
    private $toDate;
    private array $consumables = [];
    private int $markupPercent = 5;

    public function __construct(?int $id = null) {
        global $DB;
        parent::__construct();
        if ($id !== null) {
            $result = $DB->request([
                'FROM' => 'glpi_consumableitems',
                'WHERE' => ['id' => $id],
                'LIMIT' => 1
            ]);
            if ($result->count() > 0) {
                $item = $result->current();
                // Handle $item as needed
            }
        }
    }

    public function __destruct() {
        parent::__destruct();
    }


    public function getIssueToDate(): ?string {
        return $this->toDate;
    }

    public function getIssueFromDate(): ?string {
        return $this->fromDate;
    }

    public function getConsumableIssues(?string $fromDate = null, ?string $toDate = null): array {
        global $DB;
        if ($fromDate === null) {
            $this->fromDate = isset($_REQUEST['issueFrom']) ? $_REQUEST['issueFrom'] : getFirstDayOfMonth(date('Y-m-d'));
        } else {
            $this->fromDate = $fromDate;
        }

        if ($toDate === null) {
            $this->toDate = isset($_REQUEST['issueTo']) ? $_REQUEST['issueTo'] : getLastDayOfMonth(date('Y-m-d'));
        } else {
            $this->toDate = $toDate;
        }

        if ($toDate < $fromDate) {
           $toDate = getLastDayOfMonth($fromDate);    /* getLastDayOfMonth is a function in datefunctions.inc.php */
        }

        $result = $DB->request([
            'SELECT' => [
                'glpi_consumableitemtypes.name' => 'consumableitemtypesName',
                new \QueryExpression('glpi_consumableitemtypes.id AS itemTypeId'),
                'glpi_consumableitems.name' => 'consumableitemsName',
                'glpi_consumableitems.alarm_threshold',
                new \QueryExpression('count(*) as currentStock'),
                'glpi_consumableitems.id' => 'consumableitemsId',
                'glpi_consumableitemtypes.id' => 'consumableitemtypesId',
                'glpi_consumableitemtypes.comment' => 'consumableitemtypesComment',
                'glpi_consumableitems.ref' => 'consumableitemsRef',
                'glpi_consumableitems.locations_id',
                'glpi_consumableitems.manufacturers_id',
                'glpi_consumableitems.comment' => 'consumableitemsComment',
                'glpi_consumables.id' => 'consumablesId',
                'glpi_consumables.date_in',
                'glpi_consumables.date_out',
                'glpi_consumables.itemtype',
                'glpi_locations.completename',
                'glpi_infocoms.order_number',
                'glpi_infocoms.bill',
                'glpi_infocoms.value',
                'glpi_infocoms.immo_number',
                'glpi_budgets.comment' => 'teamCode',
                'glpi_infocoms.comment'
            ],
            'FROM' => 'glpi_consumables',
            'LEFT JOIN' => [
                'glpi_consumableitems' => [
                    'ON' => [
                        'glpi_consumables' => 'consumableitems_id',
                        'glpi_consumableitems' => 'id'
                    ]
                ],
                'glpi_consumableitemtypes' => [
                    'ON' => [
                        'glpi_consumableitems' => 'consumableitemtypes_id',
                        'glpi_consumableitemtypes' => 'id'
                    ]
                ],
                'glpi_infocoms' => [
                    'ON' => [
                        new \QueryExpression('glpi_infocoms.items_id = glpi_consumables.id AND glpi_infocoms.itemtype = "consumable"')
                    ]
                ],
                'glpi_locations' => [
                    'ON' => [
                        'glpi_locations' => 'id',
                        'glpi_consumableitems' => 'locations_id'
                    ]
                ],
                'glpi_users' => [
                    'ON' => [
                        new \QueryExpression('glpi_consumables.items_id = glpi_users.id AND glpi_consumables.itemtype = "user"')
                    ]
                ],
                'glpi_groups' => [
                    'ON' => [
                        new \QueryExpression('glpi_consumables.items_id = glpi_groups.id AND glpi_consumables.itemtype = "group"')
                    ]
                ],
                'glpi_budgets' => [
                    'ON' => [
                        'glpi_budgets' => 'id',
                        'glpi_infocoms' => 'budgets_id'
                    ]
                ]
            ],
            'WHERE' => [
                'glpi_consumableitems.is_deleted' => 0,
                new \QueryExpression('DATE(glpi_consumables.date_out) >= "' . $DB->escape($this->fromDate) . '"'),
                new \QueryExpression('DATE(glpi_consumables.date_out) <= "' . $DB->escape($this->toDate) . '"'),
                ['!=', 'glpi_consumableitems.alarm_threshold', 0]
            ],
            'GROUP BY' => 'glpi_consumables.id',
            'ORDER' => ['glpi_locations.completename', 'glpi_consumableitemtypes.name', 'glpi_consumableitems.name']
        ]);

        try {
            foreach ($result as $consumable) {
                $this->consumables[$consumable['consumablesId']] = $consumable;
                $this->consumables[$consumable['consumablesId']]['chargeOut'] = round($consumable['value'] * (1 + ($this->markupPercent/100)), 2);
                $this->consumables[$consumable['consumablesId']]['profit'] = $this->consumables[$consumable['consumablesId']]['chargeOut'] - $consumable['value'];
            }
        } catch (Exception $e) {
            die($e);
            return false;
        }
        
        return $this->consumables;
    }



    function listConsumableIssues($asCSV = 0) {
        $this->getConsumableIssues();
        $cummulative = 0;
        if (! $asCSV ) {
            print "<table> <tr>
                   <th>Date </th> <th>PO No. </th> <th>Invoice </th> <th>Description </th>
                   <th>GLPI </th> <th>Unit<br />Cost </th> <th>Issued to </th> <th>Team Code </th>
                   <th>Charge Out </th> <th>Period<br />Costed </th> <th>Issue<br />Ref </th> <th>Profit </th>
                   <th>Cumulative </th> <th>Comments </th>
                 </tr>";

            foreach ($this->consumables as $item ) {
                $cumulative += $item['profit'];
                printf("<tr class=\"%s\">
                            <td>%s</td> <td>%s</td> <td>%s</td> <td><a href=\"https://helpdesk.cspencerltd.co.uk/front/consumableitem.form.php?id=%d\" target=\"_BLANK\">%s</a></td>
                            <td>%s</td> <td align=\"right\">&pound; %.2f</td> <td>%s</td> <td>%s</td>
                            <td align=\"right\">&pound; %.2f</td> <td>%s</td> <td>%d</td>
                            <td align=\"right\">&pound; %.2f</td> <td align=\"right\">&pound; %.2f</td> <td>%s</td>
                        </tr>",
                        ($item['alarm_threshold'] >= $item['currentStock'] ? "reportWarning" : "report"),
                        $item['date_out'],
                        $item['order_number'],
                        $item['bill'],
                        $item['consumableitemsId'],
                        $item['consumableitemsName'],
                       $item['immo_number'],
                        $item['value'],
                        $item['issuedTo'],
                        $item['teamCode'],
                        $item['chargeOut'],
                        date('Y-m'),
                        $item['consumablesId'],
                        $item['profit'],
                        $cumulative,
                        $item['comment']
                );
            }
            print "</table>";
        } else { /* this is downloading a CSV file */
            print '"Date","PO No.","Invoice","Description","GLPI","Unit Cost","Issued to","Team Code","Charge Out","Period Costed","Issue Ref","Profit","Cummulative","Comments"'."\r\n";
            foreach ($this->consumables as $item ) {
                $cumulative += $item['profit'];
                printf('"%s","%s","%s","%s [https://helpdesk.cspencerltd.co.uk/front/consumableitem.form.php?id=%d]",%s,%.2f,"%s",%.2f,"%s","%s",%.2f,"%s",%.2f,"%s"'."\r\n",
                       $item['date_out'],
                       $item['order_number'],
                       $item['bill'],
                       $item['consumableitemsName'],
                       $item['consumableitemsId'],
                       $item['immo_number'],
                       $item['value'],
                       $item['issuedTo'],
                       $item['teamCode'],
                       $item['chargeOut'],
                       date('Y-m'),
                       $item['consumablesId'],
                       $item['profit'],
                        $cumulative,
                       $item['comment']
                );
           }
       }
   }



   /*
    * This function takes care of upadting all the releant fields in GLPI when we submit an order into SOS
    * Note this isn't actually ordering something, it's raising the order.
    *
    * What we do is:
    *     1) add the required number of consumable records
    *     2) add the infocoms records with the relevant fields entered
    *          2a) Order Date
    *          2b) Order Number
    *          2c) Supplier
    */
   function orderConsumables($quantity,$orderNumber, $supplierIId) {
       GLOBAL $db;


   }





   function printOrderForm() {
        print "<form><label for=\"orderNumber\">Order Number</label><input tyep=\"text\" name=\"purchaseOrder\" /><input type=\"submit\" value=\"Order\"></form>";
   }






   /*
    * list all consumable Items in a select
    */
   function selectAllConsumableItems($selectedItem = 0) {
       global $DB;
       $result = $DB->request([
           'SELECT' => [
               'glpi_consumableitems.id',
               new \QueryExpression("CONCAT(glpi_consumableitemtypes.name, ' -> ', glpi_consumableitems.name) as name")
           ],
           'FROM' => 'glpi_consumableitems',
           'LEFT JOIN' => [
               'glpi_consumableitemtypes' => [
                   'ON' => [
                       'glpi_consumableitemtypes' => 'id',
                       'glpi_consumableitems' => 'consumableitemtypes_id'
                   ]
               ]
           ],
           'ORDER' => ['glpi_consumableitemtypes.name', 'glpi_consumableitems.name']
       ]);

       $retVal = "<select name=\"consumableItemsId\">";
       try {
           foreach ($result as $consumable) {
               $retVal .= "<option value=\"" . htmlspecialchars($consumable['id']) . "\"";
               if ($consumable['id'] == $selectedItem) {
                   $retVal .= " SELECTED=\"SELECTED\" ";
               }
               $retVal .= ">" . htmlentities($consumable['name']) . "</option>";
           }
       } catch (Exception $e) {
           die($e);
           return false;
       }
       return $retVal . "</select>";
   }


   /*
    * list all suppliers Items in a select
    */
   function selectAllSuppliers($selectedItem = 0) {
       global $DB;
       $result = $DB->request([
           'SELECT' => ['id', 'name'],
           'FROM' => 'glpi_suppliers',
           'WHERE' => ['is_deleted' => 0],
           'ORDER' => ['name']
       ]);

       $retval = "<select name=\"supplierId\">";
       try {
           foreach ($result as $consumable) {
               $retval .= "<option value=\"" . htmlspecialchars($consumable['id']) . "\">" . htmlentities($consumable['name']) . "</option>";
           }
       } catch (Exception $e) {
           die($e);
           return false;
       }
       return $retval . "</select>";
   }






   function printReceiptForm() {
       $consumableItemId = isset($_REQUEST['consumableItemisId']) ? $_REQUEST['consumableItemisId'] : 0;
       $supplierId = isset($_REQUEST['supplierId']) ? $_REQUEST['supplierId'] : 0;
       
       print "
         <form>
           <table>
             <tr>
               <td>Item</td>
               <td>" . $this->selectAllConsumableItems($consumableItemId) . "</td>
             </tr>
             <tr>
               <td>Supplier</td>
               <td>" . $this->selectAllSuppliers($supplierId) . "</td>
             </tr>
             <tr>
               <td>Quantity</td>
               <td><input type=\"text\" name=\"quantity\" value=\"\"></input></td>
             </tr>
             <tr>
               <td>Order Number</td>
               <td><input type=\"text\" name=\"orderNumber\" value=\"\"> </input></td>
             </tr>
             <tr>
               <td>Order Date</td>
               <td><input type=\"date\" name=\"orderDate\" value=\"" . date("Y-m-d") ."\"> </input></td>
             </tr>
             <tr>
               <td>Delivery Date</td>
               <td><input type=\"date\" name=\"deliveryDate\" value=\"" . date("Y-m-d") ."\"> </input></td>
             </tr>
             <tr>
               <td>Unit Price (excl VAT)</td>
               <td><input type=\"text\" name=\"unitPrice\"> </input></td>
             </tr>
             <tr>
               <td colspan=\"2\" align=\"right\"><input type=\"submit\" /></td>
             </tr>
           </table>
         </form>";
   }





   /*
    * insert records into the consumables table and the infocoms table
    *
    * parameters
    *    $quantity = how many to enter
    *    $data     = an associative array containing the data that will be replicated for each itme
    *                [
    *                   consumableItemsId,
    *                   supplierId,
    *                   orderNumber,
    *                   orderDate,
    *                   deliveryDate,
    *                   unitPrice
    *                ]
    */
   function insertConsumables($quantity, $data) {
      global $DB;
      for ($x = 1; $x <= $quantity; $x++) {
         /*
          * Let's insert the consumable itself
          */
         $DB->insert('glpi_consumables', [
             'consumableitems_id' => (int)$data['consumableItemsId'],
             'date_in' => $data['deliveryDate'],
             'date_creation' => date('Y-m-d H:i:s'),
             'date_mod' => date('Y-m-d H:i:s')
         ]);
         
         $insertId = $DB->getLastInsertId();

         /*
          * now let's insert the infocoms data,
          * using the ID created by the previous insert statement
          */
         $DB->insert('glpi_infocoms', [
             'items_id' => $insertId,
             'itemtype' => 'Consumable',
             'buy_date' => $data['orderDate'],
             'suppliers_id' => (int)$data['supplierId'],
             'order_number' => strtoupper($data['orderNumber']),
             'value' => (float)$data['unitPrice'],
             'order_date' => $data['orderDate'],
             'delivery_date' => $data['deliveryDate']
         ]);
      }
   }
}

