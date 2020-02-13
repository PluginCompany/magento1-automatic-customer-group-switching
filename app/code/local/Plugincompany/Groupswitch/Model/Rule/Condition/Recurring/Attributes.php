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
 * Class Plugincompany_Groupswitch_Model_Rule_Condition_Customer
 */
class Plugincompany_Groupswitch_Model_Rule_Condition_Recurring_Attributes extends Mage_Rule_Model_Condition_Abstract
{
    /**
     * Set attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = array(
            'state' => Mage::helper('groupswitch')->__('Profile state'),
            'sku' => Mage::helper('groupswitch')->__('Product SKU'),
            'qty' => Mage::helper('groupswitch')->__('Product quantity'),
        );

        $this->setAttributeOption($attributes);

        return $this;
    }


    /**
     * Get attribute element renderer
     *
     * @return mixed
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * Get input type for comparison operator
     *
     * @return string
     */
    public function getInputType()
    {
        if ($this->getAttribute() == 'sku') {
            return 'string';
        }
        if ($this->getAttribute() == 'qty') {
            return 'numeric';
        }
        return 'select';
    }

    /**
     * Get value input renderer
     *
     * @return string
     */
    public function getValueElementType()
    {
        if (in_array($this->getAttribute(),array('sku','qty'))) {
            return 'text';
        }
        return 'select';
    }

    /**
     * Get value select options for attributes
     *
     * @return mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'state':
                     $options = array(
                         array('value' => 'unknown','label' => 'Not Initialized'),
                         array('value' => 'pending','label' => 'Pending'),
                         array('value' => 'active','label' => 'Active'),
                         array('value' => 'suspended','label' => 'Suspended'),
                         array('value' => 'canceled','label' => 'Canceled'),
                         array('value' => 'expired','label' => 'Expired'),
                     );
                    break;
                default:
                    $options = array();
                    break;
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    /**
     * Validate Address Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        return parent::validate($object);
    }
}
