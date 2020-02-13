<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */

/**
 * Class Plugincompany_Groupswitch_Model_Rule
 */
class Plugincompany_Groupswitch_Model_Rule extends Mage_Rule_Model_Abstract
{

    protected $_orders;
    protected $_invoices;
    protected $_customerData;
    protected $_recurringProfileData;
    private $_lastProcessedRule = null;
    private $customerGroupCodes = array();

    /**
     * Set resource model and Id field name
     * Initialize product history object
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('groupswitch/rule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Prepare order status string for SQL IN clause
     *
     * @return array|string
     */
    protected function _getOrderStatusSqlString()
    {
        $statusArray = explode(',', $this->getOrderStatus());

        //generate status string for IN clause
        $sqlStatus = array();
        foreach ($statusArray as $item) {
            $sqlStatus[] = "'" . $item . "'";
        }
        $sqlStatus = implode(',', $sqlStatus);
        return $sqlStatus;
    }


    /**
     * Initialize rule model data from array.
     * Set store labels if applicable.
     *
     * @param array $data
     *
     * @return Plugincompany_Groupswitch_Model_Rule
     */
    public function loadPost(array $data)
    {
        parent::loadPost($data);

        if (isset($data['store_labels'])) {
            $this->setStoreLabels($data['store_labels']);
        }

        return $this;
    }

    /**
     * Get rule conditions combination instance
     *
     * @return Plugincompany_Groupswitch_Model_Rule_Condition_Combine
     */
    public function getConditionsInstance()
    {
        return Mage::getModel('groupswitch/rule_condition_combine');
    }

    /**
     * Get rule action instance
     *
     * @return Plugincompany_Groupswitch_Model_Rule_Condition_Combine
     */
    public function getActionsInstance()
    {
        return Mage::getModel('groupswitch/rule_condition_combine');
    }

    /**
     * Process rules by customer and optionally by order
     *
     * @param Varien_Object $customer
     * @param $event
     * @param null $order
     * @param bool $save
     * @param bool $allowedRuleIds
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function processRules(
        Varien_Object $customer,
        $event,
        $order = null,
        $save = false,
        $allowedRuleIds = false
    ) {
        $this
            ->_initCustomerData($customer)
            ->_initRecurringProfileData($customer)
        ;

        $storeId = $customer->getWebsiteId();

        $rules = $this->getRulesCollection($storeId);

        foreach ($rules as $rule) {
            if (!in_array($customer->getGroupId(), explode(',', $rule->getOldCustomergroup()))) {
                continue;
            }
            //if customer is already in destination group, no need to run rule
            if (in_array($customer->getGroupId(),explode(',',$rule->getNewCustomergroup()))) {
                continue;
            }

            //if rule not enabled for event, continue
            if(!in_array($event,explode(',',$rule->getEvents())) && $event != 'bulk_group_switch'){
                continue;
            }
            
            //if ruleId not in allowed rule Ids, skip it
            if($allowedRuleIds && !in_array($rule->getId(),$allowedRuleIds)) continue;

            //set customer ID, used later in validation
            $rule->setCustomerId($customer->getId());

            //build rule-specific validation data object
            $obj = new Varien_Object();
            $obj->setData($this->_getCustomerData());

            //if order is passed, add current order data
            $statusArray = explode(',', $rule->getOrderStatus());
            if ($order && in_array($order->getStatus(),$statusArray)) {
                $obj->setCurrentOrder($order);
            }

            //add recurring profile data to validation object
            $obj->setRecurringProfileData($this->_recurringProfileData);

            //execute validation
            $validated = $rule->validate($obj);

            if ($validated) {
                $this->applyRule($rule,$customer);
                if ($rule->getStopRulesProcessing()) {
                    break;
                }
            }
        }

        $switched = $customer->getOrigData('group_id') != $customer->getGroupId();
        if($this->_lastProcessedRule && $switched){
            $customer->setLastAutoGroupSwitch(date('Y-m-d'));
        }
        if ($save && $switched && $this->_lastProcessedRule) {
            /** @var $customer Mage_Customer_Model_Customer */
            $customer->save();
        }

        if($this->_lastProcessedRule && $switched){
            $this->sendNotifications($customer, $this->_lastProcessedRule);
            $this->_registerOrderGroupIdIfApplicable($customer->getGroupId());
        }
        return $this;
    }

    /**
     * Send Email Notification
     *
     * @param Varien_Object $customer
     * @param $rule
     * @return void
     * @throws Mage_Core_Exception
     */
    public function sendNotifications(Varien_Object $customer, $rule){

        //load customer store
        if($customer->getStoreId()){
            $store = Mage::getModel('core/store')->load($customer->getStoreId());
        }
        else{
            $store = Mage::app()->getWebsite($customer->getWebsiteId())->getDefaultStore();
        }

        //Template Variables
        $emailTemplateVariables = array(
            'from_group' => Mage::getModel('customer/group')->load($customer->getOrigData('group_id')),
            'to_group'   => Mage::getModel('customer/group')->load($customer->getGroupId()),
            'customer'   => $customer,
            'store'      => $store
        );

        if($rule->getCustomerNotification()){

            $toEmail = $customer->getEmail();
            $toName = $customer->getName();
            $subject = $rule->getCustomerNotificationSubject();
            $body = $rule->getCustomerNotificationContents();

            $this->_sendEmail($toEmail, $toName, $subject, $body, $emailTemplateVariables);
        }

        if($rule->getAdminNotification()){
        
            $toEmail = $rule->getAdminNotificationEmail() ? $rule->getAdminNotificationEmail() : Mage::getStoreConfig('trans_email/ident_general/email');
            $toName = Mage::getStoreConfig('trans_email/ident_general/name');
            $subject = $rule->getAdminNotificationSubject();
            $body = $rule->getAdminNotificationContents();

            $this->_sendEmail($toEmail, $toName, $subject, $body, $emailTemplateVariables);
        }

        $this;
    }

    /**
     * Send Email
     *
     * @param string $toEmail
     * @param string $toName
     * @param string $subject
     * @param string $body
     * @param array $emailTemplateVariables
     * @return $this
     */
    private function _sendEmail($toEmail, $toName, $subject, $body, $emailTemplateVariables){
        /* @var $translate Mage_Core_Model_Translate */
        //$translate = Mage::getSingleton('core/translate');
        //$translate->setTranslateInline(false);

        //Sending E-Mail.
        $emailTemplate  = Mage::getModel('core/email_template');
 
        $emailTemplate->setTemplateSubject($subject);
        $emailTemplate->setTemplateText($body);
       // $emailTemplate->setId('custom');
 
        // Get General email address (Admin->Configuration->General->Store Email Addresses)
        $fromData['email'] = Mage::getStoreConfig('trans_email/ident_general/email');
        $fromData['name'] = Mage::getStoreConfig('trans_email/ident_general/name');

        $emailTemplate->setReplyTo($fromData['email']);

        $emailTemplate->setSenderName($fromData['name']);
        $emailTemplate->setSenderEmail($fromData['email']);
 
        $emailTemplate->send($toEmail, $toName, $emailTemplateVariables);

        //$translate->setTranslateInline(true);

        return $this;
    }


    /**
     * Apply validated rule on customer
     *
     * @param Varien_Object $rule
     * @param Varien_Object $customer
     * @return $this
     */
    public function applyRule(Varien_Object $rule, Varien_Object $customer)
    {
        //get next group for round robin distribution
        $newGroupId = $this->getNextGroup($rule);
        
        $customer
            ->setGroupId($newGroupId);
        
        $this->_setGroupIdToSession($newGroupId);
        

        $this->_lastProcessedRule = $rule;

        $rule
            ->setExecutedCount($rule->getExecutedCount() + 1)
            ->save()
        ;

        return $this;
    }
    
    protected function _setGroupIdToSession($groupId){
        $this->addGroupIdToRegistry($groupId);

        if(Mage::app()->getStore()->isAdmin()){
            return $this;
        }

        $this
            ->registerGroupIdInCheckoutSession($groupId)
            ->registerGroupIdInCustomerSession($groupId)
            ;
        return $this;
    }

    private function addGroupIdToRegistry($groupId)
    {
        if(Mage::registry('force_customer_group_id')){
            Mage::unregister('force_customer_group_id');
        }
        Mage::register('force_customer_group_id', $groupId);
        return $this;
    }

    private function registerGroupIdInCheckoutSession($groupId)
    {
        Mage::getSingleton('checkout/session')
            ->setCustomerGroupId($groupId)
        ;
        $this->_getCustomerFromQuote()
            ->setGroupId($groupId)
        ;
        $this->_getQuote()
            ->setCustomerGroupId($groupId)
        ;
        return $this;
    }

    private function registerGroupIdInCustomerSession($groupId)
    {
        Mage::getSingleton('customer/session')->setCustomerGroupId($groupId);
        $sCust = Mage::getSingleton('customer/session')->getCustomer();
        if($sCust){
            $sCust->setGroupId($groupId);
        }
        return $this;
    }

    protected function _registerOrderGroupIdIfApplicable($newId)
    {
        if($this->_canRegisterOrderGroupId()){
            $this->_registerOrderGroupId($newId);
        }
        return $this;
    }
    
    protected function _canRegisterOrderGroupId()
    {
        if(Mage::app()->getStore()->isAdmin()){
            return false;
        }
        return $this->_lastProcessedRule && $this->_lastProcessedRule->getApplyNewGroupToOrder();
    }
    
    protected function _registerOrderGroupId($newId)
    {
        $quote = $this->_getQuote();
        $quote->setCustomerGroupId($newId);
        return $this;
    }

    /**
     * Get next group ID for round robin distribution
     *
     * @param $rule
     * @return mixed
     */
    public function getNextGroup($rule)
    {
        $toGroups = explode(',',$rule->getNewCustomergroup());
        $count = count($toGroups);
        $executed = $rule->getExecutedCount();

        if ($executed < $count) {
            $group = $toGroups[$executed];
        }else{
            $key = $executed % $count;
            $group = $toGroups[$key];
        }
        return $group;
    }

    /**
     * get customer data array
     *
     * @return array
     */
    protected function _getCustomerData()
    {
        if ($this->_customerData) {
            return $this->_customerData;
        }
        return array();
    }

    /**
     * Get complete product order history for given period
     * (for current customer and order statuses)
     *
     * @param $from
     * @param $to
     * @return bool|Mage_Sales_Model_Resource_Order_Item_Collection
     */
    public function getProductHistory($from,$to)
    {
        if (!$this->getCustomerId()) {
            return false;
        }

        $orderTable = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
        $collection = Mage::getModel('sales/order_item')->getCollection();
        $collection
            ->getSelect()
            ->joinLeft(array('orders' => $orderTable), 'orders.entity_id=main_table.order_id')
            ->where('orders.customer_id=?', $this->getCustomerId())
            ->where("orders.status IN ({$this->_getOrderStatusSqlString()})")
            ->group('product_id')
        ;

        if ($from) {
            $collection
                ->getSelect()
                ->where('orders.created_at > ?', $from);
        }

        if ($to) {
            $collection
                ->getSelect()
                ->where('orders.created_at < ?', $to);
        }

        $collection
            ->addExpressionFieldToSelect('product_qty', 'SUM(main_table.qty_ordered)',array())
            ->addExpressionFieldToSelect('product_turnover_incl_tax', 'SUM(main_table.base_row_total_incl_tax)',array())
            ->addExpressionFieldToSelect('product_turnover_excl_tax', 'SUM(main_table.base_row_total)',array())
        ;

        return $collection;
    }

    /**
     * Get order totals for customer and current rule statuses for given period
     *
     * @param $from
     * @param $to
     * @return bool|Varien_Object
     */
    public function getOrderTotals($from,$to)
    {
        if (!$this->getCustomerId()) {
            return false;
        }

        $collection = Mage::getModel('sales/order')->getCollection();
        $collection
            ->addFieldToFilter('customer_id', $this->getCustomerId())
            ->addExpressionFieldToSelect('order_base_subtotal_incl_tax', 'SUM(base_subtotal_incl_tax)', array())
            ->addExpressionFieldToSelect('order_base_subtotal', 'SUM(base_subtotal)', array())
            ->addExpressionFieldToSelect('order_base_grand_total', 'SUM(base_grand_total)', array())
            ->addExpressionFieldToSelect('order_base_total_invoiced', 'SUM(base_total_invoiced)', array())
            ->addExpressionFieldToSelect('order_base_total_invoiced_ex_tax', 'SUM(base_total_invoiced) - IFNULL(SUM(base_tax_invoiced),0)', array())
            ->addExpressionFieldToSelect('order_base_total_invoiced_minus_refunded', 'SUM(base_total_invoiced) - IFNULL(SUM(base_total_refunded),0)', array())
            ->addExpressionFieldToSelect(
                'order_base_total_invoiced_minus_refunded_ex_tax', 
                '(SUM(base_total_invoiced) - IFNULL(SUM(base_tax_invoiced),0)) - (IFNULL(SUM(base_total_refunded),0) - IFNULL(SUM(base_tax_refunded),0))',
                array())
            ->addExpressionFieldToSelect('order_base_total_paid', 'SUM(base_total_paid)', array())
            ->addExpressionFieldToSelect('order_base_total_refunded', 'SUM(base_total_refunded)', array())
            ->addExpressionFieldToSelect('order_base_total_paid_minus_refunded', 'SUM(base_total_paid) - IFNULL(SUM(base_total_refunded),0)', array())
            ->addExpressionFieldToSelect('order_base_shipping_incl_tax', 'SUM(base_shipping_incl_tax)', array())
            ->addExpressionFieldToSelect('order_base_shipping_amount', 'SUM(base_shipping_amount)', array())
            ->addExpressionFieldToSelect('order_total_qty_ordered', 'SUM(total_qty_ordered)', array())
            ->addExpressionFieldToSelect('order_weight', 'SUM(weight)', array())
            ->getSelect()
            ->where("status IN ({$this->_getOrderStatusSqlString()})")
            ->group('customer_id')
        ;

        if ($from) {
            $collection
                ->getSelect()
                ->where('created_at > ?', $from);
        }

        if ($to) {
            $collection
                ->getSelect()
                ->where('created_at < ?', $to);
        }

        return $collection->getFirstItem();
    }

    /**
     * Get order collection for customer and current rule statuses for given period
     *
     * @param $from
     * @param $to
     * @return bool|Varien_Object
     */
    public function getOrderCollection($from,$to)
    {
        if (!$this->getCustomerId()) {
            return false;
        }

        $collection = Mage::getModel('sales/order')->getCollection();
        $collection
            ->addFieldToFilter('customer_id', $this->getCustomerId())
            ->getSelect()
            ->where("status IN ({$this->_getOrderStatusSqlString()})")
        ;

        if ($from) {
            $collection
                ->getSelect()
                ->where('created_at > ?', $from);
        }

        if ($to) {
            $collection
                ->getSelect()
                ->where('created_at < ?', $to);
        }

        return $collection;
    }

    /**
     * Get invoice totals for customer and current rule statuses for given period
     *
     * @param $from
     * @param $to
     * @return bool|Varien_Object
     */
    public function getInvoiceTotals($from,$to)
    {
        if (!$this->getCustomerId()) {
            return false;
        }

        $collection = Mage::getModel('sales/order')->getCollection();
        $collection
            ->addFieldToFilter('customer_id', $this->getCustomerId())
            ->addExpressionFieldToSelect('invoice_base_subtotal_incl_tax', 'SUM(invoice.base_subtotal_incl_tax)', array())
            ->addExpressionFieldToSelect('invoice_base_subtotal', 'SUM(invoice.base_subtotal)', array())
            ->addExpressionFieldToSelect('invoice_base_grand_total', 'SUM(invoice.base_grand_total)', array())
            ->addExpressionFieldToSelect('invoice_base_shipping_incl_tax', 'SUM(invoice.base_shipping_incl_tax)', array())
            ->addExpressionFieldToSelect('invoice_base_shipping_amount', 'SUM(invoice.base_shipping_amount)', array())
            ->addExpressionFieldToSelect('invoice_total_qty', 'SUM(invoice.total_qty)', array())
            ->getSelect()
            ->where("invoice.state IN ({$this->getInvoiceStatus()})")
            ->joinInner(array('invoice'=> 'sales_flat_invoice'),'invoice.order_id=main_table.entity_id')
            ->group('main_table.customer_id')
        ;

        if ($from) {
            $collection
                ->getSelect()
                ->where('invoice.created_at > ?', $from);
        }

        if ($to) {
            $collection
                ->getSelect()
                ->where('invoice.created_at < ?', $to);
        }

        return $collection->getFirstItem();
    }

    /**
     * Get rule collection for current date
     *
     * @param $storeId
     * @param $date
     * @return Plugincompany_Groupswitch_Model_Mysql4_Rule_Collection
     */
    public function getRulesCollection($storeId, $date = false)
    {
        if(!$date) {
            $date = $this->getCurrentDate();
        }
        $rules = $this->getCollection();
        $rules
            ->addStoreIdFilter($storeId)
            ->addDateFilter($date)
            ->addIsActiveFilter()
            ->sortByPriority()
        ;
        return $rules;
    }

    /**
     * Init customer data
     *
     * @param Varien_Object $customer
     * @return $this
     */
    protected function _initCustomerData(Varien_Object $customer)
    {
        $billingData = $this->_getCustomerBilling($customer);
        $shippingData = $this->_getCustomerShipping($customer);
        $customerAttribtues = $this->_getCustomerAttributes($customer);

        $data = array_merge($billingData, $shippingData,$customerAttribtues);
        $this->_customerData = $data;

        return $this;
    }

    /**
     * @param Varien_Object $customer
     * @return $this
     * @throws Exception
     */
    protected function _initRecurringProfileData(Varien_Object $customer)
    {
        $collection = Mage::getModel('sales/recurring_profile')->getCollection();
        $collection
            ->addFieldToFilter('customer_id', $customer->getId());

        $data = new Varien_Data_Collection();
        foreach ($collection as $profile) {
            $orderItem = unserialize($profile->getOrderItemInfo());
            $itemData = array(
                'sku' => $orderItem['sku'],
                'state' => $profile->getState(),
                'qty' => $orderItem['qty']
            );
            $item = new Varien_Object();
            $item->setData($itemData);
            $data->addItem($item);
        }

        $this->_recurringProfileData = $data;

        return $this;

    }

    /**
     * Get customer attribute array for validation
     *
     * @param Varien_Object $customer
     * @return array
     */
    protected function _getCustomerAttributes(Varien_Object $customer)
    {
        //check if data is set by post on customer model
        if($customer->getIsSubscribed() === true){
            $subscribed = 1;
        }elseif($customer->getIsSubscribed() === false){
            $subscribed = 0;
        }else{
            //if not set retrieve from subscriber model
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
            if($subscriber->getId() && $subscriber->getStatus() === "1")
            {
                $subscribed = 1;
            }else{
                $subscribed = 0;
            }
        }

        //calculate account age
        $date1 = new DateTime(date('Y-m-d',strtotime($customer->getCreatedAt())));
        $date2 = new DateTime(date('Y-m-d'));
        $accountAge = $date2->diff($date1)->format("%a");

        //calculate customer age
        $bdate = $customer->getDob();
        if (!$bdate) {
            $bdate = date('Y-m-d');
        }
        $age = date_diff(date_create($bdate), date_create('now'))->y;

        //calculate last auto customer group switched days ago
        $lastGroupSwitchDate = $customer->getLastAutoGroupSwitch();
        if(!$lastGroupSwitchDate) {
            $lastGroupSwitchDate= date('Y-m-d');
        }
        $date1 = new DateTime(date('Y-m-d',strtotime($lastGroupSwitchDate)));
        $date2 = new DateTime(date('Y-m-d'));
        $lastGroupSwitchDaysAgo = $date2->diff($date1)->format("%a");

        $data = array_merge(array('age' => $age, 'account_age' => $accountAge, 'newsletter' => $subscribed, 'last_groupswitch_days_ago' => $lastGroupSwitchDaysAgo),$customer->getData());
        
        return $data;
    }

    /**
     * Get customer billing address for validation
     *
     * @param Varien_Object $customer
     * @return array
     */
    protected function _getCustomerBilling(Varien_Object $customer)
    {
        //try customer object from event
        $billing = $this->_getBilllingFromCustomerObject($customer);
        
        //try quote session customer
        if(!$billing){
            $billing = $this->_getDefaultBillingFromQuote();
        }

        $data = array();
        if ($billing) {
            foreach ($billing->getData() as $k => $v) {
                $data['billing_' . $k] = $v;
            }
        }
        return $data;
    }
    
    protected function _getBilllingFromCustomerObject($customer)
    {
        $billing = $customer->getDefaultBillingAddress();
        if(!$billing && $customer->getDefaultBilling()){
            $billing = Mage::getModel('customer/address')->load($customer->getDefaultBilling());
        }
        return $billing;
    }
    
    protected function _getDefaultBillingFromQuote()
    {
        foreach($this->_getCustomerAddressesFromQuote() as $address){
            if($address->getIsDefaultBilling()){
                return $address;
            }
        }
        return false;
    }
    
    
    protected function _getCustomerAddressesFromQuote()
    {
        return $this
            ->_getCustomerFromQuote()
            ->getAddresses()
            ;
    }
    
    protected function _getCustomerFromQuote()
    {
         return $this->_getQuote()->getCustomer();
    }
    
    protected function _getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * get customer shipping address for validation
     *
     * @param Varien_Object $customer
     * @return array
     */
    protected function _getCustomerShipping(Varien_Object $customer)
    {
        //try customer event object
        $shipping = $this->_getShippingFromCustomerObject($customer);
        
        //try customer quote session object
        if(!$shipping){
            $shipping = $this->_getDefaultShippingFromQuote();
        }
        
        $data = array();
        if ($shipping) {
            foreach ($shipping->getData() as $k => $v) {
                $data['shipping_' . $k] = $v;
            }
        }
        return $data;
    }
    
    protected function _getShippingFromCustomerObject($customer)
    {
        $shipping = $customer->getDefaultShippingAddress();
        if(!$shipping && $customer->getDefaultShipping()){
            $shipping = Mage::getModel('customer/address')->load($customer->getDefaultShipping());
        }
        return $shipping;
    }
    
    protected function _getDefaultShippingFromQuote()
    {
        foreach($this->_getCustomerAddressesFromQuote() as $address){
            if($address->getIsDefaultShipping()){
                return $address;
            }
        }
        return false;
    }
    
    public function applySelectedRulesForCustomerRange($lastCustomerId, $limit, $ruleIds)
    {
        if(!is_array($ruleIds)){
            $ruleIds = array($ruleIds);
        }
        
        //load customer collection
        $customerCollection = $this
            ->getCustomerCollectionForRulesByIds($ruleIds, false)
            ->addAttributeToFilter('entity_id', array('gt' => $lastCustomerId))
        ;
        $customerCollection
            ->getSelect()
            ->limit($limit);
        
        $result = array();
        //apply rule to each customer
        foreach($customerCollection as $customer){
            $error = false;
            //apply rule
            try {
                Mage::getModel('groupswitch/rule')
                    ->processRules($customer, 'bulk_group_switch', null, true, $ruleIds);
            }
            catch(\Exception $e) {
                Mage::log($e->getMessage(), null, 'plugincompany_groupswitch.log', true);
                $error = true;
            }

            //get from / to group
            $fromGroupId = $customer->getOrigData('group_id');
            $toGroupId = $customer->getGroupId();
            if($fromGroupId == $toGroupId){
                $toGroup = "No group change";
            }

            if($error) {
                $toGroup = "An error occured. Result: " . $this->getCustomerGroupCodeById($toGroupId);
            }

            if(empty($toGroup)) {
                $toGroup = $this->getCustomerGroupCodeById($toGroupId);
            }

            //add to results
            $result[] = array(
                'customer_id' => $customer->getId(),
                'customer_name' => $customer->getName(),
                'from_group' => $this->getCustomerGroupCodeById($fromGroupId),
                'to_group' => $toGroup,
                'date' => $this->getCurrentDate()
            );
        }
        return $result;
    }

    private function getCustomerGroupCodeById($id)
    {
        if(empty($this->customerGroupCodes[$id])) {
            $this->customerGroupCodes[$id] = Mage::getModel('customer/group')->load($id)->getCustomerGroupCode();
        }
        return $this->customerGroupCodes[$id];
    }

    /**
     * @param string $event
     * @param bool $onlyActiveRules
     * @return Mage_Customer_Model_Resource_Customer_Collection|object
     * @throws Mage_Core_Exception
     */
    public function getCustomerCollectionForEvent($event, $onlyActiveRules = true)
    {
        $collection = Mage::getModel('customer/customer')->getCollection();
        $this->_addApplicableRuleFilterToCustomerCollection($collection, null, $event, $onlyActiveRules);
        return $collection;
    }

    /**
     * @param array|string|int $ruleIds
     * @param bool $onlyActiveRules
     * @return Mage_Customer_Model_Resource_Customer_Collection|object
     * @throws Mage_Core_Exception
     */
    public function getCustomerCollectionForRulesByIds($ruleIds, $onlyActiveRules = true)
    {
        $collection = Mage::getModel('customer/customer')->getCollection();
        $this->_addApplicableRuleFilterToCustomerCollection($collection, $ruleIds, null, $onlyActiveRules);
        return $collection;
    }

    /**
     * @param Mage_Customer_Model_Resource_Customer_Collection $collection
     * @param array|null $ruleIds
     * @param array|null $event
     * @param bool $onlyActiveRules
     * @return Mage_Customer_Model_Resource_Customer_Collection
     * @throws Mage_Core_Exception
     */
    protected function _addApplicableRuleFilterToCustomerCollection(
        Mage_Customer_Model_Resource_Customer_Collection $collection,
        $ruleIds = null,
        $event = null,
        $onlyActiveRules = true
    ) {
        $rules = $this->getSortedRulesCollection();

        if($onlyActiveRules) {
            $rules
                ->addIsActiveFilter()
                ->addDateFilter();
        }
        if($ruleIds) {
            $rules->addIdFilter($ruleIds);
         }
        if($event) {
            $rules->addEventFilter($event);
        }

        $adapter = $collection->getSelect()->getAdapter();
        $ruleFilterConditionParts = array();
        foreach($rules as $rule) {
            $ruleCondition = "(
                    website_id IN (" . $adapter->quote(explode(',', $rule->getStoreIds())) . ") AND 
                    group_id IN (" . $adapter->quote(explode(',', $rule->getOldCustomergroup())) . ")
                )";
            $ruleFilterConditionParts[] = $ruleCondition;
        }
        $ruleFilterCondition = implode(" OR ", $ruleFilterConditionParts);

        $collection->addAttributeToSelect('*');

        $collection->getSelect()->where($ruleFilterCondition);

        return $collection;
    }

    /**
     * @param $ruleIds
     * @return Plugincompany_Groupswitch_Model_Mysql4_Rule_Collection
     */
    public function getActiveRuleCollectionByIds($ruleIds)
    {
        return $this
            ->getActiveRulesCollection()
            ->addIdFilter($ruleIds)
            ;
    }

    /**
     * @return Plugincompany_Groupswitch_Model_Mysql4_Rule_Collection
     */
    public function getActiveRulesCollection()
    {
        return $this
            ->getCollection()
            ->addIsActiveFilter()
            ->addDateFilter()
            ->sortByPriority()
            ;
    }

    /**
     * @return Plugincompany_Groupswitch_Model_Mysql4_Rule_Collection
     */
    public function getSortedRulesCollection()
    {
        return $this
            ->getCollection()
            ->sortByPriority();
    }

    private function getCurrentDate()
    {
        $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
        return date('Y-m-d', $currentTimestamp);
    }

    public function getDefaultValues()
    {
        $values = array();

        $values['events'] = array('customer_save','customer_login','order_save','invoice_save','recurring_profiles_save','address_save','newsletter_subscriber_save', 'customer_cron_job');

        $values['admin_notification_subject'] = "Customer {{var customer.getName()}} moved to the {{var to_group.customer_group_code}} group";

        $values['admin_notification_contents'] =
"<p>Customer&nbsp;<span>{{var customer.getName()}} (ID:&nbsp;<span>{{var customer.getId()}}) has been moved to a new customer group.</span></span></p>
<p><span><span><strong>Original customer group:</strong><br /><span>{{var from_group.customer_group_code}}</span><br /></span></span></p>
<p><strong>New customer group:<br /></strong>{{var to_group.customer_group_code}}</p>";

        $values['customer_notification_subject'] = "Your account has been moved to the {{var to_group.customer_group_code}} group";

        $values['customer_notification_contents'] =
"<p>Dear&nbsp;<span>{{var customer.getName()}},</span></p>
<p><span>We'd like to notify you that your account has been moved from the&nbsp;{{var from_group.customer_group_code}} group to the&nbsp;{{var to_group.customer_group_code}} group.</span></p>
<p>Best regards,</p>
<p>{{var store.getFrontendName()}}</p>";

        return $values;
    }
}


