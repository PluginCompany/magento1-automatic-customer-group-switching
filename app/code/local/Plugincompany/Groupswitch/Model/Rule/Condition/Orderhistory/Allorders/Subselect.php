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
 * Class Plugincompany_Groupswitch_Model_Rule_Condition_Orderhistory_Allorders_Subselect
 */
class Plugincompany_Groupswitch_Model_Rule_Condition_Orderhistory_Allorders_Subselect
    extends Plugincompany_Groupswitch_Model_Rule_Condition_Orderhistory_Allorders_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('groupswitch/rule_condition_orderhistory_allorders_subselect')
            ->setValue(null);
    }

    /**
     * @param $arr
     * @param string $key
     * @return $this
     */
    public function loadArray($arr, $key='conditions')
    {
        $this->setAttribute($arr['attribute']);
        $this->setOperator($arr['operator']);
        parent::loadArray($arr, $key);
        return $this;
    }

    /**
     * @param string $containerKey
     * @param string $itemKey
     * @return string
     */
    public function asXml($containerKey='conditions', $itemKey='condition')
    {
        $xml = '<attribute>'.$this->getAttribute().'</attribute>'
            . '<operator>'.$this->getOperator().'</operator>'
            . parent::asXml($containerKey, $itemKey);
        return $xml;
    }

    /**
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(array(
            'orderhistory_qty'  => Mage::helper('groupswitch')->__('total quantity'),
        ));
        return $this;
    }

    /**
     * @return $this
     */
    public function loadValueOptions()
    {
        return $this;
    }

    /**
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array(
            '=='  => Mage::helper('rule')->__('is'),
            '!='  => Mage::helper('rule')->__('is not'),
            '>='  => Mage::helper('rule')->__('equals or greater than'),
            '<='  => Mage::helper('rule')->__('equals or less than'),
            '>'   => Mage::helper('rule')->__('greater than'),
            '<'   => Mage::helper('rule')->__('less than'),
            '()'  => Mage::helper('rule')->__('is one of'),
            '!()' => Mage::helper('rule')->__('is not one of'),
        ));
        return $this;
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }


    /**
     * HTML for rule creation
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

        $html = $this->getTypeElement()->getHtml().
        Mage::helper('groupswitch')->__(
            "If %s %s %s for a subselection of orders in the order history %s"
            . "<span style='$displayBetween' id='" . $identifier . "between'>%s and %s</span>"
            . "<span style='$displayLessdays' id='" . $identifier . "lessdaysago'>%s days </span>"
            . "<span style='$displayMoredays' id='" . $identifier . "moredaysago'>%s days </span>"
            . "matching %s of these conditions:",
            $this->getAttributeElement()->getHtml(),
            $this->getOperatorElement()->getHtml(),
            $this->getValueElement()->getHtml(),
            $this->getPeriodTypeElement()->toHtml(),
            $this->getFromDateElement()->getHtml(),
            $this->getToDateElement()->getHtml(),
            $this->getRecentDaysElement()->getHtml(),
            $this->getBeforeDaysElement()->getHtml(),
            $this->getAggregatorElement()->getHtml()
        );

        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        return $html;
    }


    /**
     * validate orderhistory history
     *
     * @param Varien_Object $object Quote
     * @return boolean
     */
    public function validate(Varien_Object $object)
    {
        if (!$this->getConditions()) {
            return false;
        }

        $period = $this->getPeriodRange();

        $orderhistory= $this->getRule()->getOrderCollection($period['from'], $period['to']);

        $total = 0;
        foreach ($orderhistory as $item) {

            //add billing attributes
            $bAddress = $item->getBillingAddress()->getData();
            foreach($bAddress as $k => $v){
                $item->setData('billingattr_' . $k, $v);
            }
            
            if(!$item->getIsVirtual()){
                //add shipping attributes
                $sAddress = $item->getShippingAddress()->getData();
                foreach($sAddress as $k => $v){
                    $item->setData('shippingattr_' . $k, $v);
                }
            }
            
            $this->_addCustomOrderAttributesToOrder($item);

            if (parent::validate($item)) {
                $total += 1;
            }
        }

        return $this->validateAttribute($total);
    }
}
