<?php
declare(strict_types=1);

namespace GlpiPlugin\Stockcontrol;

use \CommonDBTM;
use \Plugin;
use \Html;
use \Session;
use \Dropdown;
use \Request;


class PluginStockcontrolStock extends \CommonDBTM {

    public function getTabNameForItem(\CommonGLPI $item, int $withtemplate = 0): array|string {
        return self::createTabEntry(__('Stock Control', 'stockcontrol'));
    }

    /*
    public static function displayTabContentForItem(\CommonGLPI $item, int $tabnum = 1, int $withtemplate = 0): bool {
        echo '<p>Hello, world</p>';
        return true;
    }
    */

    public function showForm(int $ID, array $options = []): void {
        global $CFG_GLPI;
        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        if (!isset($options['display'])) {
            $options['display'] = true;
        }
        $params = $options;
        $params['display'] = false;
        $out = '<tr>';
        $out .= '<th>' . __('My label', 'stockcontrol') . '</th>';
        $objectName = autoName(
            $this->fields['name'],
            'name',
            (isset($options['withtemplate']) && $options['withtemplate'] == 2),
            $this->getType(),
            $this->fields['entities_id']
        );
        $out .= '<td>';
        $out .= \Html::autocompletionTextField(
            $this,
            'name',
            [
                'value'   => $objectName,
                'display' => false
                                                  ]
                                             );
        $out .= '</td>';
        $out .= $this->showFormButtons($params);
        if ($options['display'] == true) {
            echo $out;
        } else {
                // Removed return statement
        }
    }

}
