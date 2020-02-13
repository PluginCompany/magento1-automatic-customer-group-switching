<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
class Plugincompany_Groupswitch_Block_Adminhtml_Groupswitch_Edit_Tab_Email_Variables extends Varien_Data_Form_Element_Abstract {

    public function getHtml(){
        return Mage::app()->getLayout()
            ->createBlock('core/template')
            ->setTemplate('plugincompany/groupswitch/variables.phtml')
            ->toHtml()
            ;
    }

}