<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
?>
<?php
class Plugincompany_Groupswitch_Model_Rule_Condition_Orderhistory_Allorders extends Mage_Rule_Model_Condition_Abstract
{

    /**
     * Load attribute options
     *
     */
    public function loadAttributeOptions()
    {
        $attributes = array(
            'base_subtotal_incl_tax' => Mage::helper('groupswitch')->__('Subtotal (incl. Tax)'),
            'base_subtotal' => Mage::helper('groupswitch')->__('Subtotal (excl. Tax)'),
            'base_grand_total' => Mage::helper('groupswitch')->__('Grand Total (incl. Tax)'),
            'base_shipping_incl_tax' => Mage::helper('groupswitch')->__('Shipping Costs (incl. Tax)'),
            'base_shipping_amount' => Mage::helper('groupswitch')->__('Shipping Costs (excl. Tax)'),
            'base_discount_amount' => Mage::helper('groupswitch')->__('Discount Amount'),
            'base_shipping_discount_amount' => Mage::helper('groupswitch')->__('Shipping Discount Amount'),
            'base_tax_amount' => Mage::helper('groupswitch')->__('Tax Amount'),
            'tax_percent' => Mage::helper('groupswitch')->__('Tax Percentage'),
            'base_total_invoiced' => Mage::helper('groupswitch')->__('Total Invoiced (incl. Tax)'), //al
            'base_total_invoiced_ex_tax' => Mage::helper('groupswitch')->__('Total Invoiced (excl. Tax)'), //all
            'base_total_invoiced_minus_refunded' => Mage::helper('groupswitch')->__('Total Invoiced - Total Refunded (incl. Tax)'),
            'base_total_invoiced_minus_refunded_ex_tax' => Mage::helper('groupswitch')->__('Total Invoiced - Total Refunded (excl. Tax)'),
            'base_total_paid' => Mage::helper('groupswitch')->__('Total Paid'),
            'base_total_refunded' => Mage::helper('groupswitch')->__('Total Refunded'),
            'base_total_paid_minus_refunded' => Mage::helper('groupswitch')->__('Total Paid - Total Refunded'),
            'total_qty_ordered' => Mage::helper('groupswitch')->__('Total Items'),
            'weight' => Mage::helper('groupswitch')->__('Total Weight'),
            'coupon_code' => Mage::helper('groupswitch')->__('Coupon Code'),
            'coupon_rule_name' => Mage::helper('groupswitch')->__('Coupon Rule Name'),
            'state' => Mage::helper('groupswitch')->__('Order State'),
            'status' => Mage::helper('groupswitch')->__('Order Status'),
//            'is_virtual' => Mage::helper('groupswitch')->__('Is Virtual'),
            'entity_id' => Mage::helper('groupswitch')->__('Order ID'),
            'increment_id' => Mage::helper('groupswitch')->__('Order Increment ID'),
            'customer_taxvat' => Mage::helper('groupswitch')->__('VAT Number'),
            'order_currency_code' => Mage::helper('groupswitch')->__('Currency Code'),
            'shipping_method' => Mage::helper('groupswitch')->__('Shipping Method'),
            'payment_method' => Mage::helper('groupswitch')->__('Payment Method'),
//            'created_at' => Mage::helper('groupswitch')->__('Order Date'),
        );

        $bAttributes =
            array (
                'billingattr_region_id' => Mage::helper('groupswitch')->__('Region (select)'),
                'billingattr_region' => Mage::helper('groupswitch')->__('Region (text)'),
                'billingattr_postcode' => Mage::helper('groupswitch')->__('Postal Code'),
                'billingattr_street' => Mage::helper('groupswitch')->__('Street'),
                'billingattr_city' => Mage::helper('groupswitch')->__('City'),
                'billingattr_country_id' => Mage::helper('groupswitch')->__('Country'),
                'billingattr_vat_id' => Mage::helper('groupswitch')->__('VAT Number'),
                'billingattr_vat_is_valid' => Mage::helper('groupswitch')->__('VAT Number Valid'),
            );

        $sAttributes =
            array (
                'shippingattr_region_id' => Mage::helper('groupswitch')->__('Region (select)'),
                'shippingattr_region' => Mage::helper('groupswitch')->__('Region (text)'),
                'shippingattr_postcode' => Mage::helper('groupswitch')->__('Postal Code'),
                'shippingattr_street' => Mage::helper('groupswitch')->__('Street'),
                'shippingattr_city' => Mage::helper('groupswitch')->__('City'),
                'shippingattr_country_id' => Mage::helper('groupswitch')->__('Country'),
                'shippingattr_vat_id' => Mage::helper('groupswitch')->__('VAT Number'),
                'shippingattr_vat_is_valid' => Mage::helper('groupswitch')->__('VAT Number Valid'),
            );

        $attributes = array_merge($attributes, $bAttributes, $sAttributes);

        $this->setAttributeOption($attributes);

        return $this;
    }

    protected function _prepareValueOptions()
    {
        // Check that both keys exist. Maybe somehow only one was set not in this routine, but externally.
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');
        if ($selectReady && $hashedReady) {
            return $this;
        }

        // Get array of select options. It will be used as source for hashed options
        $selectOptions = null;
        if(is_object($this->getAttributeObject())) {
            $attributeObject = $this->getAttributeObject();
            if ($attributeObject->usesSource()) {
                if ($attributeObject->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }
                $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
            }
        }

        if($this->getAttribute() == 'payment_method'){
            $selectOptions = Mage::getModel('adminhtml/system_config_source_payment_allmethods')->toOptionArray();
        }

        if($this->getAttribute() == 'shipping_method'){
            $selectOptions = Mage::getModel('adminhtml/system_config_source_shipping_allowedmethods')->toOptionArray();
        }

        // Set new values only if we really got them
        if ($selectOptions !== null) {
            // Overwrite only not already existing values
            if (!$selectReady) {
                $this->setData('value_select_options', $selectOptions);
            }
            if (!$hashedReady) {
                $hashedOptions = array();
                foreach ($selectOptions as $o) {
                    if (is_array($o['value'])) {
                        continue; // We cannot use array as index
                    }
                    $hashedOptions[$o['value']] = $o['label'];
                }
                $this->setData('value_option', $hashedOptions);
            }
        }

        return $this;
    }

    /**
     * Retrieve attribute object
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getAttributeObject()
    {
        try {
            $attrCode = $this->getAttribute();
            if(stristr($attrCode,'billingattr_')){
                $attrCode = str_replace('billingattr_','',$attrCode);
                $entityCode = 'order_address';
            }elseif(stristr($attrCode,'shippingattr_')){
                $attrCode = str_replace('shippingattr_','',$attrCode);
                $entityCode = 'order_address';
            }else{
                $entityCode = 'order';
            }

            if(in_array($attrCode,array('country_id','region_id'))){
                $entityCode = 'customer_address';
            }
            $obj = Mage::getModel('eav/entity_attribute')->loadByCode($entityCode,$attrCode);
        }
        catch (Exception $e) {
            $obj = new Varien_Object();
            $obj->setEntity(Mage::getResourceSingleton('sales/order'))
                ->setFrontendInput('text');
        }
        if($entityCode == 'region_id'){
            $obj->setFrontendInput('select');
        }
        return $obj;
    }

    /**
     * Retrieve value by option
     *
     * @param mixed $option
     * @return string
     */
    public function getValueOption($option=null)
    {
        $this->_prepareValueOptions();
        return $this->getData('value_option'.(!is_null($option) ? '/'.$option : ''));
    }

    /**
     * Retrieve select option values
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        $this->_prepareValueOptions();
        return $this->getData('value_select_options');
    }

    /**
     * Retrieve attribute element
     *
     * @return Varien_Form_Element_Abstract
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * Retrieve input type
     *
     * @return string
     */
    public function getInputType()
    {
        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }
        if(in_array($this->getAttribute(), array('shipping_method', 'payment_method'))){
            return 'select';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
                return 'date';

            case 'boolean':
                return 'boolean';

            default:
                return 'string';
        }
    }

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        if ($this->getAttribute()==='attribute_set_id') {
            return 'select';
        }
        if(in_array($this->getAttribute(), array('shipping_method', 'payment_method'))){
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'text';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
            case 'boolean':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
                return 'date';

            default:
                return 'text';
        }
    }

    /**
     * Validate Orderhistory Rule Condition
     *
     * @param Varien_Object $object
     *
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $valid = parent::validate($object);
        return $valid;
    }
}
