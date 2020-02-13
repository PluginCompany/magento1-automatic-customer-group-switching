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
 * Class Plugincompany_Groupswitch_Model_Rule_Condition_Combine
 */
class Plugincompany_Groupswitch_Model_Rule_Condition_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('groupswitch/rule_condition_combine');
    }

    /**
     * Get select options for rule generation
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        //get order attributes
        $orderCondition = Mage::getModel('groupswitch/rule_condition_order');
        $orderAttributes = $orderCondition->loadAttributeOptions()->getAttributeOption();
        $orderAttributesArray = array();
        foreach ($orderAttributes as $code=>$label) {
            $orderAttributesArray[] = array('value'=>'groupswitch/rule_condition_order|'.$code, 'label'=>$label);
        }

        //get invoice attributes
        $invoiceCondition = Mage::getModel('groupswitch/rule_condition_invoice');
        $invoiceAttributes = $invoiceCondition->loadAttributeOptions()->getAttributeOption();
        $invoiceAttributesArray = array();
        foreach ($invoiceAttributes as $code=>$label) {
            $invoiceAttributesArray[] = array('value'=>'groupswitch/rule_condition_invoice|'.$code, 'label'=>$label);
        }

        //get shipping address attributes
        $shippingCondition = Mage::getModel('groupswitch/rule_condition_shipping');
        $shippingAttributes = $shippingCondition->loadAttributeOptions()->getAttributeOption();
        $shippingAttributesArray = array();
        foreach ($shippingAttributes as $code=>$label) {
            $shippingAttributesArray[] = array('value'=>'groupswitch/rule_condition_shipping|'.$code, 'label'=>$label);
        }

        //get billing address attributes
        $billingCondition = Mage::getModel('groupswitch/rule_condition_billing');
        $billingAttributes = $billingCondition->loadAttributeOptions()->getAttributeOption();
        $billingAttributesArray = array();
        foreach ($billingAttributes as $code=>$label) {
            $billingAttributesArray[] = array('value'=>'groupswitch/rule_condition_billing|'.$code, 'label'=>$label);
        }

        //get customer attributes
        $customerCondition = Mage::getModel('groupswitch/rule_condition_customer');
        $customerAttributes = $customerCondition->loadAttributeOptions()->getAttributeOption();
        $customerAttributesArray = array();
        foreach ($customerAttributes as $code=>$label) {
            $customerAttributesArray[] = array('value'=>'groupswitch/rule_condition_customer|'.$code, 'label'=>$label);
        }

        //get order history product attributes
        $productAllordersCondition = array(
            'label' => 'Ordered Products (order history)',
            'value' => array(
                array('value' => 'groupswitch/rule_condition_product_allorders_found', 'label' => Mage::helper('groupswitch')->__('Product attribute combination')),
                array('value' => 'groupswitch/rule_condition_product_allorders_subselect', 'label' => Mage::helper('groupswitch')->__('Products subselection')),
            )
        );

        //get current cart product attributes
        $productNeworderCondtion = array(
            'label' => 'Ordered Products (new orders)',
            'value' => array(
                array('value' => 'groupswitch/rule_condition_product_neworder_found', 'label' => Mage::helper('groupswitch')->__('Product attribute combination')),
                array('value' => 'groupswitch/rule_condition_product_neworder_subselect', 'label' => Mage::helper('groupswitch')->__('Products subselection')),
            )
        );

        //get order history order attributes
        $orderhistoryAllordersCondition = array(
            'label' => 'Single Order Totals (entire order history)',
            'value' => array(
                array('value' => 'groupswitch/rule_condition_orderhistory_allorders_found', 'label' => Mage::helper('groupswitch')->__('Order attribute combination')),
                array('value' => 'groupswitch/rule_condition_orderhistory_allorders_subselect', 'label' => Mage::helper('groupswitch')->__('Order subselection')),
            )
        );

        //get current cart product attributes
        $recurringProfilesCondition = array(
            'label' => 'Recurring Profiles',
            'value' => array(
                array('value' => 'groupswitch/rule_condition_recurring_found', 'label' => Mage::helper('groupswitch')->__('Attribute combination')),
            )
        );

        //set attributes
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('value' => 'groupswitch/rule_condition_combine', 'label' => Mage::helper('groupswitch')->__('Conditions combination')),
            array('label' => Mage::helper('groupswitch')->__('Customer Info'), 'value' => $customerAttributesArray),
            array('label' => Mage::helper('groupswitch')->__('Default Billing Address'), 'value' => $billingAttributesArray),
            array('label' => Mage::helper('groupswitch')->__('Default Shipping Address'), 'value' => $shippingAttributesArray),
            array('label' => Mage::helper('groupswitch')->__('Sum of Invoice Totals'), 'value' => $invoiceAttributesArray),
            array('label' => Mage::helper('groupswitch')->__('Sum of Order Totals'), 'value' => $orderAttributesArray),
            $orderhistoryAllordersCondition,
            $productAllordersCondition,
            $productNeworderCondtion,
            $recurringProfilesCondition
        ));

        return $conditions;
    }
}
