<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
class Plugincompany_Groupswitch_Block_Adminhtml_Groupswitch_Renderer_Oldgroup
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $value = explode(',', $row->getOldCustomergroup());
        $cGroups = $this->getCustomerGroupArray();
        $result = array();
        foreach ($value as $val) {
            $result[] = $cGroups[$val];
        }
        $result = implode('<br />', $result);
        return $result;
    }

    public function getCustomerGroupArray(){
        $customerGroups = array();
        $collection = Mage::getModel("customer/group")->getCollection();
        foreach ($collection as $group){
            $customerGroups[$group->getCustomerGroupId()] = $group->getCustomerGroupCode();
        }
        return $customerGroups;
    }
}