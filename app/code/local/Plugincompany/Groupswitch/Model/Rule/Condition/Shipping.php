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
 * Class Plugincompany_Groupswitch_Model_Rule_Condition_Shipping
 */
class Plugincompany_Groupswitch_Model_Rule_Condition_Shipping extends Mage_Rule_Model_Condition_Abstract
{
    /**
     * Retrieve attribute options for rule creation
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = array(
            'shipping_city' => Mage::helper('groupswitch')->__('City'),
            'shipping_postcode' => Mage::helper('groupswitch')->__('Postcode'),
            'shipping_region' => Mage::helper('groupswitch')->__('State/Province (text)'),
            'shipping_region_id' => Mage::helper('groupswitch')->__('State/Province (dropdown)'),
            'shipping_country_id' => Mage::helper('groupswitch')->__('Country'),
        );

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * HTML for rule creation
     *
     * @return string
     */
    public function asHtml()
    {
        $html = 'Default shipping address'
            .$this->getTypeElementHtml()
            .$this->getAttributeElementHtml()
            .$this->getOperatorElementHtml()
            .$this->getValueElementHtml()
            .$this->getRemoveLinkHtml()
            .$this->getChooserContainerHtml();
        return $html;
    }

    /**
     * return element for attribute rendering
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
     * Return input type by attribute
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'shipping_country_id': case 'shipping_region_id':
            return 'select';
        }
        return 'string';
    }

    /**
     * Get attribute element for value input
     *
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'shipping_country_id': case 'shipping_region_id':
            return 'select';
        }
        return 'text';
    }

    /**
     * Get value select options for rule generation
     *
     * @return mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'shipping_country_id':
                    $options = Mage::getModel('adminhtml/system_config_source_country')
                        ->toOptionArray();
                    break;

                case 'shipping_region_id':
                    $options = Mage::getModel('adminhtml/system_config_source_allregion')
                        ->toOptionArray();
                    break;

                default:
                    $options = array();
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
