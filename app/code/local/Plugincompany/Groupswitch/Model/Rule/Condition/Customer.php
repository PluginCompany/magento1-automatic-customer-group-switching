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
class Plugincompany_Groupswitch_Model_Rule_Condition_Customer extends Mage_Rule_Model_Condition_Abstract
{

    /**
     * Check if attribute is allowed for rule creation
     * @param $attribute
     * 
     */
    protected function _canUseAttribute($attribute)
    {
        $excluded = array(
            'store_id',
            'website_id',
            'created_in',
            'entity_type_id' ,
            'attribute_set_id' ,
            'updated_at' ,
            'group_id' ,
            'password_hash' ,
            'default_billing' ,
            'default_shipping' ,
            'rp_token',
            'rp_token_created_at' ,
            'reward_update_notification' ,
            'reward_warning_notification' ,
        );
        if(!in_array($attribute,$excluded)){
            return true;
        }
        return false;
    }

    /**
     * Set attribute options
     *
     * @return $this
     */
    protected function _getSpecialAttributes()
    {
        $attributes = array(
            'newsletter' => Mage::helper('groupswitch')->__('Subscribed to newsletter'),
            'age' => Mage::helper('groupswitch')->__('Age in years'),
            'account_age' => Mage::helper('groupswitch')->__('Account age in days'),
            'last_groupswitch_days_ago' => Mage::helper('groupswitch')->__('Last automatic group switch days ago')
        );

        return $attributes;
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
        if (is_object($this->getAttributeObject())) {
            $attributeObject = $this->getAttributeObject();
            if ($attributeObject->usesSource()) {
                if ($attributeObject->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }
                $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
            }
            if(in_array($attributeObject->getAttributeCode(), array("disable_auto_group_change","newsletter","confirmation"))){
                $selectOptions = array(
                    array('label' => 'Yes', 'value' => 1),
                    array('label' => 'No', 'value' => 0)
                );
            }
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
            $obj = Mage::getSingleton('eav/config')
                ->getAttribute('customer', $this->getAttribute());
        }
        catch (Exception $e) {
            $obj = new Varien_Object();
            $obj->setEntity(Mage::getResourceSingleton('customer/customer'))
                ->setFrontendInput('text');
        }
        return $obj;
    }
    
    /**
     * Set attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $customerAttributes = Mage::getResourceSingleton('customer/customer')
            ->loadAllAttributes()
            ->getAttributesByCode();

        $attributes = array();
        foreach ($customerAttributes as $attribute) {
            if(!$this->_canUseAttribute($attribute->getAttributeCode()) || !$attribute->getFrontendLabel()){
                continue;
            }
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $specialAttr = $this->_getSpecialAttributes();
        $attributes = array_merge($attributes,$specialAttr);

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
     * Retrieve input type
     *
     * @return string
     */
    public function getInputType()
    {
        if (in_array($this->getAttribute(),array('account_age','age', 'last_groupswitch_days_ago'))) {
            return 'numeric';
        }
        if(in_array($this->getAttribute(), array("disable_auto_group_change","newsletter","confirmation"))){
            return 'boolean';
        }
        
        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
            case 'datetime':
                return 'date';

            case 'boolean':
                return 'boolean';

            default:
                return 'string';
        }
    }
    
    /**
     * Retrieve value element
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getValueElement()
    {
        $element = parent::getValueElement();
        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    $element->setInputFormat(Varien_Date::DATE_INTERNAL_FORMAT);
                    $element->setFormat(Varien_Date::DATE_INTERNAL_FORMAT);
                    $element->setExplicitApply(true);
                    $element->setImage(Mage::getDesign()->getSkinUrl('images/grid-cal.gif'));
                    break;
            }
        }

        return $element;
    }

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        if(in_array($this->getAttribute(), array("disable_auto_group_change","newsletter","confirmation"))){
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'text';
        }
        $attr = $this->getAttributeObject();
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
            case 'boolean':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
            case 'datetime':
                return 'date';
            
            default:
                return 'text';
        }
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
