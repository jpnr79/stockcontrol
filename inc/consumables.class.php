<?php

class consumables {
    private $fromDate;
    private $toDate;
    private $consumables = array();
    private $markupPercent = 5;

    function __construct( $id = null  ) {
        if ( $id <> NULL ) {
            $SQL = "SELECT * from `glpi_consumableitems` WHERE `id` = " . $db->real_escape_string($id) ." LIMIT 1";
            $res = $db-Aquery( $SQL );
            if ($res ->num_rows ==1 ) {
                $item=$res->fetch_assoc();
                var_dump($item);
                die();
            }
        }
    }


    function __destruct() {
    }


    function getIssueToDate() {
        return $this->toDate;
    }

    function getIssueFromDate() {
        return $this->fromDate;
    }

    function getConsumableIssues($fromDate=null, $toDate = null) {
        global $logger,$tickets,$parts,$db;
        
        if ($fromDate == null ) {
            if (! isset($_REQUEST['issueFrom']) ) {
                $this->fromDate = getFirstDayOfMonth(date('Y-m-d' ));   /* getFirstDayOfMonth is a function in datefunctions.inc.php */
            } else {
                $this->fromDate = $_REQUEST['issueFrom'];
            }
        } else {
            $this->fromDate = $fromDate;
        }

        if ($toDate == null) {
            if (! isset($_REQUEST['issueTo']) ) {
                $this->toDate = getLastDayOfMonth(date('Y-m-d'));  /* getLastDayOfMonth is a function in datefunctions.inc.php */
            } else {
                $this->toDate = $_REQUEST['issueTo'];
            }
        } else {
            $this->toDate = $toDate;
        }

        if ($toDate < $fromDate) {
           $toDate = getLastDayOfMonth($fromDate);    /* getLastDayOfMonth is a function in datefunctions.inc.php */
           }


        $SQL = "SELECT `glpi_consumableitemtypes`.`name` as `consumableitemtypesName`, 
                       `glpi_consumableitemtypes`.`id` as `itemTypeId`, 
                       `glpi_consumableitems`.`name` as `consumableitemsName`, 
                       `glpi_consumableitems`.`alarm_threshold`, 
                        count(*) as `currentStock` , 
                       `glpi_consumableitems`.`id` as `consumableitemsId`, 
                       `glpi_consumableitemtypes`.`id` as `consumableitemtypesId`, 
                       `glpi_consumableitemtypes`.`comment` as `consumableitemtypesComment`, 
                       `glpi_consumableitems`.`ref` as `consumableitemsRef`, 
                       `glpi_consumableitems`.`locations_id`, 
                       `glpi_consumableitems`.`manufacturers_id`, 
                       `glpi_consumableitems`.`comment` as `consumableitemsComment`, 
                       `glpi_consumables`.`id` as `consumablesId`, 
                       `glpi_consumables`.`date_in`, 
                       `glpi_consumables`.`date_out`, 
                       `glpi_consumables`.`itemtype`, 
                       `glpi_locations`.`completename`, 
                       `glpi_infocoms`.`order_number`, 
                       `glpi_infocoms`.`bill`, 
                       `glpi_infocoms`.`value`,
                       `glpi_infocoms`.`immo_number`,
                        COALESCE (
                                CONCAT('[user] ', `glpi_users`.`firstname`,' ',`glpi_users`.`realname`),
                               CONCAT('[Group] ',`glpi_groups`.`name`)
                        )  as `issuedTo`,
                       `glpi_budgets`.`comment` as `teamCode`,
                       `glpi_infocoms`.`comment`
                  FROM `glpi_consumables` 
             LEFT JOIN `glpi_consumableitems` ON (`glpi_consumables`.`consumableitems_id` = `glpi_consumableitems`.`id`) 
             LEFT JOIN `glpi_consumableitemtypes` ON (`glpi_consumableitems`.`consumableitemtypes_id` = `glpi_consumableitemtypes`.`id`) 
             LEFT JOIN `glpi_infocoms` ON ( `glpi_infocoms`.`items_id` = `glpi_consumables`.`id` AND `glpi_infocoms`.`itemtype` = 'consumable') 
             LEFT JOIN `glpi_locations` ON (`glpi_locations`.`id` = `glpi_consumableitems`.`locations_id`) 
             LEFT JOIN `glpi_users` ON (`glpi_consumables`.`items_id` = `glpi_users`.`id` AND `glpi_consumables`.`itemtype` = 'user')
             LEFT JOIN `glpi_groups` ON (`glpi_consumables`.`items_id` = `glpi_groups`.`id` AND `glpi_consumables`.`itemtype` = 'group')
             LEFT JOIN `glpi_budgets` ON (`glpi_budgets`.`id` = `glpi_infocoms`.`budgets_id`)
                 WHERE `glpi_consumableitems`.`is_deleted` = 0 
                   AND DATE(`glpi_consumables`.`date_out`) >= '" . $db->real_escape_string($this->fromDate) . "' 
                   AND DATE(`glpi_consumables`.`date_out`) <= '" . $db->real_escape_string($this->toDate)   . "' 
                   AND `glpi_consumableitems`.`alarm_threshold` <> 0 
              GROUP BY `glpi_consumables`.`id` 
              ORDER BY `glpi_locations`.`completename`,  
                       `glpi_consumableitemtypes`.`name`, 
                       `glpi_consumableitems`.`name` ";

#print "<pre>$SQL</pre>";
        try {
            $result = $db->query($SQL);
            while ( $consumable = $result->fetch_assoc() ) {
                $this->consumables[$consumable['consumablesId']] = $consumable;
                $this->consumables[$consumable['consumablesId']]['chargeOut']= round($consumable['value'] * (1 + ($this->markupPercent/100)),2) ;
                $this->consumables[$consumable['consumablesId']]['profit']=  $this->consumables[$consumable['consumablesId']]['chargeOut'] - $consumable['value'];
            }
        } catch (Exception $e) {
            die($e);
            return FALSE;
        } finally {
            if ( isset($result) && gettype($result)=='object' && get_class($result) == 'mysqli_result') $result->close();
        }
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
   function selectAllConsumableItems($selectedItem=0) {
       GLOBAL $db;
       $SQL = "select `glpi_consumableitems`.`id`, 
                       CONCAT(`glpi_consumableitemtypes`.`name`, ' -> ' ,`glpi_consumableitems`.`name`) as `name`
                 from `glpi_consumableitems`
            LEFT JOIN `glpi_consumableitemtypes` ON (`glpi_consumableitemtypes`.`id` = `glpi_consumableitems`.`consumableitemtypes_id`)
             order by `glpi_consumableitemtypes`.`name`, `glpi_consumableitems`.`name`";

       $retVal = "<select name=\"consumableItemsId\">";
       try {
           $result = $db->query($SQL);
           while ( $consumable = $result->fetch_assoc() ) {
               $retVal .= "<option value=\"" . $consumable['id'] . "\"";
               if ($consumable['id'] == $selectedItem) $retVal .= " SELECTED=\"SELECTED\" ";
               $retVal .= ">" . htmlentities($consumable['name']) . "</option>";
           }
       } catch (Exception $e) {
           die($e);
           return FALSE;
       } finally {
           if ( isset($result) && gettype($result)=='object' && get_class($result) == 'mysqli_result') $result->close();
       }
       return $retVal . "</select>";
   }


   /*
    * list all suppliers Items in a select
    */
   function selectAllSuppliers($selectedItem=0) {
       GLOBAL $db;
       $SQL = "select `id`,`name` 
                 from `glpi_suppliers`
                WHERE `is_deleted` = 0
             order by `name`";

       $retval = "<select name=\"supplierId\">";
       try {

           $result = $db->query($SQL);
           while ( $consumable = $result->fetch_assoc() ) {
               $retval .= "<option value=\"" . $consumable['id'] . "\">" . htmlentities($consumable['name']) . "</option>";
           }
       } catch (Exception $e) {
           die($e);
           return FALSE;
       } finally {
           if ( isset($result) && gettype($result)=='object' && get_class($result) == 'mysqli_result') $result->close();
       }
       return $retval . "</select>";
   }






   function printReceiptForm() {
       print "
         <form>
           <table>
             <tr>
               <td>Item</td>
               <td>" . $this->selectAllConsumableItems($_REQUEST['consumableItemisId']) . "</td>
             </tr>
             <tr>
               <td>Supplier</td>
               <td>" . $this->selectAllSuppliers($_REQUEST['supplierId']) . "</td>
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
   function insertConsumables($quantity,$data) {
      GLOBAL $db, $logger;
      for ($x = 1; $x <= $quantity; $x++) {
         /*
          * Let's insert the consumable itself
          */
         $SQL1 = sprintf("INSERT INTO `glpi_consumables` VALUES (NULL,0,%d,'%s',NULL,NULL,0,NOW(),NOW())",
                         $data['consumableItemsId'],
                         $db->real_escape_string($data['deliveryDate']));
         $db->query($SQL1);
         $insertId =  $db->insert_id;
         $logger->log(__FILE__,__LINE__, SOURCE_SYSTEM,'New reord ID is '.$insertId,'inserted the ' . $x . ' Consumable item',$SQL1);


         /*
          * now let's insert the infocoms data,
          * using the ID created by the previous nsert statement
          */
         $SQL2 = sprintf("INSERT INTO `glpi_infocoms` (`items_id`,`itemtype`, `buy_date`,`suppliers_id`,`order_number`,`value`,`order_date`,`delivery_date`)
                               VALUES (%d,'Consumable','%s',%d,'%s',%.2f,'%s','%s')",
                         $insertId,
                         $data['orderDate'],
                         $data['supplierId'],
                         strtoupper($data['orderNumber']),
                         $data['unitPrice'],
                         $data['orderDate'],
                         $data['deliveryDate']);
         $db->query($SQL2);
         $logger->log(__FILE__,__LINE__, SOURCE_SYSTEM,'New reord ID is '.$db->insert_id,'inserted financial information for consumable ' . $data['consumableItemsId'] ,$SQL2);
      }
   }
}

