<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
class Plugincompany_Groupswitch_Model_Rule_Condition_Totals extends Mage_Rule_Model_Condition_Abstract
{
    protected $_typeName;

    /**
     * Generate condition rhtml
     *
     * @return string
     */
    public function asHtml()
    {
        $identifier = $this->getPrefix() . '_' . $this->getId() . '_';

        $displayBetween = $displayLessdays = $displayMoredays = 'display:none';
        switch($this->getPeriodtype()){
            case 'between':
                $displayBetween = '';
                break;
            case 'lessdaysago':
                $displayLessdays = '';
                break;
            case 'moredaysago':
                $displayMoredays = '';
                break;
        }

        $html = 'Sum of ' . $this->_typeName
            . $this->getTypeElementHtml()
            . $this->getAttributeElementHtml()
            . "in the order history"
            . $this->getPeriodTypeElement()->toHtml()
            . "<span style='$displayBetween' id='"  . $identifier  . "between'>"
            . $this->getFromDateElement()->getHtml()  . " and " . $this->getToDateElement()->getHtml()
            . "</span>"
            . "<span style='$displayLessdays' id='" . $identifier . "lessdaysago'>"
            . $this->getRecentDaysElement()->getHtml() . " days"
            . "</span>"
            . "<span style='$displayMoredays' id='" . $identifier . "moredaysago'>"
            . $this->getBeforeDaysElement()->getHtml() . "days"
            . "</span>"
            . $this->getOperatorElementHtml()
            . $this->getValueElementHtml()
            . $this->getRemoveLinkHtml()
            . $this->getChooserContainerHtml();
        return $html;
    }

    /**
     * The period options.
     * Complete history, between dates, last xx days, before xx days
     *
     * @return array
     */
    public function getPeriodOptions()
    {
        $options = array(
            'all' => 'since day one',
            'between' => 'on and between dates',
            'lessdaysago' => 'of the last',
            'moredaysago' => 'older than',
        );
        return $options;
    }

    /**
     * get period type dropdown
     *
     * @return mixed
     */
    public function getPeriodTypeElement()
    {
        $identifier = $this->getPrefix() . '_' . $this->getId() . '_';
        if (is_null($this->getPeriodtype())) {
            $this->setPeriodtype('all');
        }

        $periodOptions = $this->getPeriodOptions();
        return $this->getForm()->addField($this->getPrefix().'__'.$this->getId().'__periodtype', 'select', array(
            'name'           =>'rule['.$this->getPrefix().']['.$this->getId().'][periodtype]',
            'value'          => $this->getPeriodtype(),
            'value_name'     => $periodOptions[$this->getPeriodtype()],
            'options'        => $periodOptions,
            'required'       => true,
            'onchange'       => "hidePeriodValues('" . $identifier . "',this)"
        ))->setRenderer(Mage::getBlockSingleton('rule/editable'));
    }

    /**
     * get last xx days element
     *
     * @return mixed
     */
    public function getRecentDaysElement()
    {
        if (is_null($this->getRecentdays())) {
            $this->setRecentdays(30);
        }

        return $this->getForm()->addField($this->getPrefix().'__'.$this->getId().'__recentdays', 'text', array(
            'name'           =>'rule['.$this->getPrefix().']['.$this->getId().'][recentdays]',
            'value'          => $this->getRecentdays(),
            'value_name'     => $this->getRecentdays(),
            'required'       => true
        ))->setRenderer(Mage::getBlockSingleton('rule/editable'));
    }

    /**
     * get xx days before input element
     *
     * @return mixed
     */
    public function getBeforeDaysElement()
    {
        if (is_null($this->getBeforedays())) {
            $this->setBeforedays(30);
        }

        return $this->getForm()->addField($this->getPrefix().'__'.$this->getId().'__beforedays', 'text', array(
            'name'           =>'rule['.$this->getPrefix().']['.$this->getId().'][beforedays]',
            'value'          => $this->getBeforedays(),
            'value_name'     => $this->getBeforedays(),
            'required'       => true,
            'class'     => 'validate-number'
        ))->setRenderer(Mage::getBlockSingleton('rule/editable'));
    }

    /**
     * get the 'from date' element
     *
     * @return mixed
     */
    public function getFromDateElement()
    {
        if (is_null($this->getFromdate())) {
            $this->setFromdate(date('Y-m-d'));
        }

        return $this->getForm()->addField($this->getPrefix().'__'.$this->getId().'__fromdate', 'date', array(
            'name'           =>'rule['.$this->getPrefix().']['.$this->getId().'][fromdate]',
            'value'          => $this->getFromdate(),
            'value_name'     => Mage::helper('groupswitch')->formatDate($this->getFromdate()),
            'image'          => Mage::getDesign()->getSkinUrl('images/grid-cal.gif'),
            'format'         => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT) ,
            'explicit_apply' => true,
            'required'       => true
        ))->setRenderer(Mage::getBlockSingleton('rule/editable'));
    }

    /**
     * get the 'to date' element
     *
     * @return mixed
     */
    public function getToDateElement()
    {
        if (is_null($this->getTodate())) {
            $this->setTodate(date('Y-m-d'));
        }

        return $this->getForm()->addField($this->getPrefix().'__'.$this->getId().'__todate', 'date', array(
            'name'           =>'rule['.$this->getPrefix().']['.$this->getId().'][todate]',
            'value'          => $this->getTodate(),
            'value_name'     => Mage::helper('groupswitch')->formatDate($this->getTodate()),
            'image'          => Mage::getDesign()->getSkinUrl('images/grid-cal.gif'),
            'format'         => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT) ,
            'explicit_apply' => true,
            'required'       => true
        ))->setRenderer(Mage::getBlockSingleton('rule/editable'));
    }

    /**
     * Get date range for current condition
     *
     * @return array
     */
    public function getPeriodRange()
    {
        switch($this->getPeriodtype()){
            case 'all':
                $from = false;
                $to = false;
                break;
            case 'between':
                $from = $this->getFromdate();
                $to = date('Y-m-d',strtotime($this->getTodate() . ' +1 day'));
                break;
            case 'lessdaysago':
                $to = false;
                $days = $this->getRecentdays();
                if (!$days) {
                    $days = 0;
                }
                $from = date('Y-m-d', strtotime("-$days day"));
                break;
            case 'moredaysago':
                $from = false;
                $days = $this->getBeforedays();
                if (!$days) {
                    $days = 0;
                }
                $to = date('Y-m-d', strtotime("-$days day"));
                break;
        }
        return array('from' => $from, 'to' => $to);
    }

    /**
     * Conditions as array
     *
     * @param array $arrAttributes
     * @return array
     */
    public function asArray(array $arrAttributes = array())
    {
        $out = parent::asArray();
        $out['periodtype'] = $this->getPeriodtype();
        $out['beforedays'] = $this->getBeforedays();
        $out['recentdays'] = $this->getRecentdays();
        $out['todate'] = date('Y-m-d',strtotime($this->getTodate()));
        $out['fromdate'] = date('Y-m-d',strtotime($this->getFromdate()));

        return $out;
    }

    /**
     * Conditions as XML
     *
     * @return string
     */
    public function asXml()
    {
        $xml = "<type>".$this->getType()."</type>"
            ."<periodtype>".$this->getPeriodtype()."</periodtype>"
            ."<recentdays>".$this->getRecentdays()."</recentdays>"
            ."<beforedays>".$this->getBeforedays()."</beforedays>"
            ."<fromdate>".$this->getFromdate()."</fromdate>"
            ."<todate>".$this->getTodate()."</todate>"
            ."<attribute>".$this->getAttribute()."</attribute>"
            ."<operator>".$this->getOperator()."</operator>"
            ."<value>".$this->getValue()."</value>";
        return $xml;
    }

    /**
     * Load conditions array
     *
     * @param $arr
     * @param string $key
     * @return $this
     */
    public function loadArray($arr, $key='conditions'){
        parent::loadArray($arr);
        if (isset($arr['periodtype'])) {
            $this->setPeriodtype($arr['periodtype']);
        }
        if (isset($arr['recentdays'])) {
            $this->setRecentdays($arr['recentdays']);
        }
        if (isset($arr['beforedays'])) {
            $this->setBeforedays($arr['beforedays']);
        }
        if (isset($arr['fromdate'])) {
            $this->setFromdate($arr['fromdate']);
        }
        if (isset($arr['todate'])) {
            $this->setTodate($arr['todate']);
        }
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
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            $this->_defaultOperatorInputByType = array(
                'string'      => array('==', '!=', '>=', '>', '<=', '<', '{}', '!{}', '()', '!()'),
                'numeric'     => array('==', '!=', '>=', '>', '<=', '<'),
                'date'        => array('==', '>=', '<='),
                'select'      => array('==', '!='),
                'boolean'     => array('==', '!='),
                'multiselect' => array('{}', '!{}', '()', '!()'),
                'grid'        => array('()', '!()'),
            );
            $this->_arrayInputTypes = array('multiselect', 'grid');
        }
        return $this->_defaultOperatorInputByType;
    }

    /**
     * Get input type for comparison operator
     *
     * @return string
     */
    public function getInputType()
    {
        return 'numeric';
    }

    /**
     * Get value input renderer
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

}
