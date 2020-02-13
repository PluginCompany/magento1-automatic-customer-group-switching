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
class Plugincompany_Groupswitch_Model_Rule_Condition_Product_Neworder_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('groupswitch/rule_condition_product_neworder_combine');
    }

    /**
     * Get select options for condtion combination
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        //get cart conditions
        $productCondition = Mage::getModel('groupswitch/rule_condition_product_neworder');
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $pAttributes = array();
        $iAttributes = array();
        foreach ($productAttributes as $code=>$label) {
            if (strpos($code, 'quote_item_')===0) {
                $iAttributes[] = array('value'=>'groupswitch/rule_condition_product_neworder|'.$code, 'label'=>$label);
            } else {
                $pAttributes[] = array('value'=>'groupswitch/rule_condition_product_neworder|'.$code, 'label'=>$label);
            }
        }

        //set condtions
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('value'=>'groupswitch/rule_condition_product_neworder_combine', 'label'=>Mage::helper('catalog')->__('Conditions Combination')),
            array('label'=>Mage::helper('catalog')->__('Cart Item Attribute'), 'value'=>$iAttributes),
            array('label'=>Mage::helper('catalog')->__('Product Attribute'), 'value'=>$pAttributes),
        ));
        return $conditions;
    }

    /**
     * @param $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}
