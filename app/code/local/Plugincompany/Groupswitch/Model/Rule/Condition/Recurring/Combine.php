<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
?>
<?php

/**
 * Class Plugincompany_Groupswitch_Model_Rule_Condition_Product_Neworder_Combine
 */
class Plugincompany_Groupswitch_Model_Rule_Condition_Recurring_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('groupswitch/rule_condition_recurring_combine');
    }

    /**
     * Get select options for condtion combination
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        //get cart conditions
        $condition = Mage::getModel('groupswitch/rule_condition_recurring_attributes');
        $recurringAttributes = $condition->loadAttributeOptions()->getAttributeOption();
        $attributes = array();
        foreach ($recurringAttributes as $code=>$label) {
            $attributes[] = array('value'=>'groupswitch/rule_condition_recurring_attributes|'.$code, 'label'=>$label);
        }

        //set condtions
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('label'=>Mage::helper('catalog')->__('Attributes'), 'value'=>$attributes)
        ));
        return $conditions;
    }

}
