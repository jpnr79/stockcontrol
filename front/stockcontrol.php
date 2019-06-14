<?php
include ("../../../inc/includes.php");

Html::header('Stock Control', '', "tools", "pluginstockcontrolmenu", "stockcontrol");

$menu = new PluginStockcontrolMenu();
$menu->display();
Html::footer();

?>
