<?php
include ("../../../inc/includes.php");

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isInstalled('stockcontrol') || !$plugin->isActivated('stockcontrol')) {
   Html::displayNotFoundError();
}

//check for ACLs
if (PluginStockControlStock::canView()) {
   //View is granted: display the list.

   //Add page header
   Html::header(
            __('Stock Control plugin', 'stockcontrol'),
            $_SERVER['PHP_SELF'],
            'assets',
            'pluginstockcontrolstock',
            'stock'
   );
   Search::show('Pluginstockcontrolstock');
   Html::footer();
} else {
   //View is not granted.
   Html::displayRightError();
}
