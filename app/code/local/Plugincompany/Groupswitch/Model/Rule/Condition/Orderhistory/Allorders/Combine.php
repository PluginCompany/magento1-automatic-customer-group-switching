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
 * Class Plugincompany_Groupswitch_Model_Rule_Condition_Orderhistory_Allorders_Combine
 */
class Plugincompany_Groupswitch_Model_Rule_Condition_Orderhistory_Allorders_Combine extends Mage_Rule_Model_Condition_Combine
{

    public function __construct()
    {
        parent::__construct();
        $this->setType('groupswitch/rule_condition_orderhistory_allorders_combine');
    }

    /**
     * Get attributes for cart history condtions
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        //Get orderhistory / cart attributes
        $orderhistoryCondition = Mage::getModel('groupswitch/rule_condition_orderhistory_allorders');
        $orderhistoryAttributes = $orderhistoryCondition->loadAttributeOptions()->getAttributeOption();
        $oAttributes = array();
        $bAttributes = array();
        $sAttributes = array();
        foreach ($orderhistoryAttributes as $code=>$label) {
            if (stristr($code, 'billingattr')) {
                $bAttributes[] = array('value'=>'groupswitch/rule_condition_orderhistory_allorders|'.$code, 'label'=>$label);
            }elseif(stristr($code,'shippingattr')){
                $sAttributes[] = array('value'=>'groupswitch/rule_condition_orderhistory_allorders|'.$code, 'label'=>$label);
            } else {
                $oAttributes[] = array('value'=>'groupswitch/rule_condition_orderhistory_allorders|'.$code, 'label'=>$label);
            }
        }

        //set attributes
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('value'=>'groupswitch/rule_condition_orderhistory_allorders_combine', 'label'=>Mage::helper('catalog')->__('Conditions Combination')),
            array('label'=>Mage::helper('catalog')->__('Order Attributes'), 'value'=>$oAttributes),
            array('label'=>Mage::helper('catalog')->__('Billing Address Attributes'), 'value'=>$bAttributes),
            array('label'=>Mage::helper('catalog')->__('Shipping Address Attributes'), 'value'=>$sAttributes),
        ));
        return $conditions;
    }

    /**
     * Conditions as Array
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
     * @param string $containerKey
     * @param string $itemKey
     * @return string
     */
    public function asXml($containerKey='conditions', $itemKey='condition')
    {
        $xml = "<aggregator>".$this->getAggregator()."</aggregator>"
            ."<periodtype>".$this->getPeriodtype()."</periodtype>"
            ."<recentdays>".$this->getRecentdays()."</recentdays>"
            ."<beforedays>".$this->getBeforedays()."</beforedays>"
            ."<fromdate>".$this->getFromdate()."</fromdate>"
            ."<todate>".$this->getTodate()."</todate>"
            ."<value>".$this->getValue()."</value>"
            ."<$containerKey>";
        foreach ($this->getConditions() as $condition) {
            $xml .= "<$itemKey>".$condition->asXml()."</$itemKey>";
        }
        $xml .= "</$containerKey>";
        return $xml;
    }

    /**
     * Load conditions array
     *
     * @param $arr
     * @param string $key
     * @return $this
     */
    public function loadArray($arr, $key='conditions')
    {
        $this->setAggregator(isset($arr['aggregator']) ? $arr['aggregator']
            : (isset($arr['attribute']) ? $arr['attribute'] : null))
            ->setValue(isset($arr['value']) ? $arr['value']
                : (isset($arr['operator']) ? $arr['operator'] : null));

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

        if (!empty($arr[$key]) && is_array($arr[$key])) {
            foreach ($arr[$key] as $condArr) {
                try {
                    $cond = $this->_getNewConditionModelInstance($condArr['type']);
                    if ($cond) {
                        $this->addCondition($cond);
                        $cond->loadArray($condArr, $key);
                    }
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }
        return $this;
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
     * @param $orderhistoryCollection
     * @return $this
     */
    public function collectValidatedAttributes($orderhistoryCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($orderhistoryCollection);
        }
        return $this;
    }

    protected function _addCustomOrderAttributesToOrder(Mage_Sales_Model_Order $order)
    {
        $order
            ->setBaseTotalInvoicedExTax($this->_getBaseTotalInvoicedExTaxForOrder($order))
            ->setBaseTotalInvoicedMinusRefunded($this->_getBaseTotalInvoicedMinusRefundedForOrder($order))
            ->setBaseTotalInvoicedMinusRefundedExTax($this->_getBaseTotalInvoicedMinusRefundedExTaxForOrder($order))
            ->setBaseTotalPaidMinusRefunded($this->_getBaseTotalPaidMinusRefundedForOrder($order))
        ;
        if($order->getPayment()){
            $order->setPaymentMethod(
                $order->getPayment()->getMethod()
            );
        }
    }

    protected function _getBaseTotalInvoicedExTaxForOrder($order)
    {
        return $order->getBaseTotalInvoiced() - $order->getBaseTaxInvoiced();
    }

    protected function _getBaseTotalInvoicedMinusRefundedForOrder($order)
    {
        return $order->getBaseTotalInvoiced() - $order->getBaseTotalRefunded();
    }

    protected function _getBaseTotalInvoicedMinusRefundedExTaxForOrder($order)
    {
        return $this->_getBaseTotalInvoicedMinusRefundedForOrder($order) + $order->getBaseTaxRefunded() - $order->getBaseTaxInvoiced();
    }

    protected function _getBaseTotalPaidMinusRefundedForOrder($order)
    {
        return $order->getBaseTotalPaid() - $order->getBaseTotalRefunded();
    }
}
