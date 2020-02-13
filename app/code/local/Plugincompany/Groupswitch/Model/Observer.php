<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
/*
 * Class Plugincompany_Groupswitch_Model_Observer
 */
class Plugincompany_Groupswitch_Model_Observer
{
    protected function _isCron(){
        return Mage::registry('is_groupswitch_cron');
    }
    protected function _isBulk(){
        return Mage::registry('is_groupswitch_bulk');
    }
    /**
     * Before customer save event observer
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerSaveBefore(Varien_Event_Observer $observer)
    {
        if($this->_isCron()) return;
        if($this->_isBulk()) return;

        try {
            $customer = $observer->getEvent()->getCustomer();
            if(!$customer) return;

            if($groupId = Mage::registry('force_customer_group_id')){
                $customer->setGroupId($groupId);
            }

            if(!$customer->getId()) return;

            if(!Mage::registry('customer_group_switched')) {
                //register to prevent infinite loop
                Mage::register('customer_group_switched', true);
                //execute rules
                Mage::getModel('groupswitch/rule')->processRules($customer,'customer_save');
            }
        } catch (Exception $e){
            Mage::log($e->getMessage(), null, 'plugincompany_groupswitch.log', true);
        }
    }

    /**
     * After customer save event observer
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerSaveAfter(Varien_Event_Observer $observer)
    {
        if($this->_isCron()) return;
        if($this->_isBulk()) return;

        try{
            $customer = $observer->getEvent()->getCustomer();
            if(!$customer) return;
            if ($customer->getId() && !Mage::registry('customer_group_switched')) {
                //register to prevent infinite loop
                Mage::register('customer_group_switched', true);
                //exectute rules
                Mage::getModel('groupswitch/rule')->processRules($customer,'customer_save',null,true);
            }
        } catch (Exception $e){
            Mage::log($e->getMessage(), null, 'plugincompany_groupswitch.log', true);
        }
    }

    /**
     * After customer address save after event observer
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerAddressSaveAfter(Varien_Event_Observer $observer)
    {
        if(Mage::registry('viv_after_address_save_processed') && !Mage::registry('address_processed_after_viv')){
            Mage::register('address_processed_after_viv',true);
        }elseif (Mage::registry('customer_group_switched') || Mage::registry('customer_group_switched_address')){ //TODO: remove _address??
            return;
        }

        if($this->_isCron()) return;
        if($this->_isBulk()) return;

        try{
            $customer = $observer->getEvent()->getCustomerAddress()->getCustomer();
            if(!$customer) return;
            if ($customer->getId()) {
                //register to prevent infinite loop
                if (!Mage::registry('customer_group_switched_address')) {
                    Mage::register('customer_group_switched_address', true);
                }
                //execute rules
                Mage::getModel('groupswitch/rule')->processRules($customer,'address_save',null,true);
            }
        } catch (Exception $e){
            Mage::log($e->getMessage(), null, 'plugincompany_groupswitch.log', true);
        }
    }

    /**
     * Customer login event
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerLogin(Varien_Event_Observer $observer)
    {
        try{
            $customer = $observer->getEvent()->getCustomer();
            if(!$customer) return;
            if ($customer->getId() && !Mage::registry('customer_group_switched')) {
                //register to prevent infinite loop
                Mage::register('customer_group_switched', true);
                //execute rules
                Mage::getModel('groupswitch/rule')->processRules($customer,'customer_login',null,true);
            }
        } catch (Exception $e){
            Mage::log($e->getMessage(), null, 'plugincompany_groupswitch.log', true);
        }
    }

    public function salesOrderSaveBefore(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();
        if(!$order->getConvertingFromQuote()) return;
        
        $quote = $order->getQuote();
        $groupId = $quote->getCustomerGroupId();
        if(!$groupId) return;
        
        $order->setCustomerGroupId($groupId);
    }

    /**
     * Order save after event observer
     *
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderSaveAfter(Varien_Event_Observer $observer)
    {
        try{
            $customer = $observer->getEvent()->getOrder()->getCustomer();
            if(!$customer){
                $customer = new Varien_Object();
            }
            if (!$customer->getId() && $observer->getEvent()->getOrder()->getCustomerId()) {
                $customer = Mage::getModel('customer/customer')->load($observer->getEvent()->getOrder()->getCustomerId());
            }elseif(!$customer->getId()){
                return;
            }
            $order = $observer->getEvent()->getOrder();
            //register to prevent infinite loop
            if (!Mage::registry('customer_group_switched')) {
                Mage::register('customer_group_switched', true);
            }
            //execute rules
            Mage::getModel('groupswitch/rule')->processRules($customer,'order_save',$order, true);
            
        } catch (Exception $e){
            Mage::log($e->getMessage(), null, 'plugincompany_groupswitch.log', true);
        }
    }

    /**
     * Invoice save after event observer
     *
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderInvoiceSaveAfter(Varien_Event_Observer $observer)
    {
        try{
            $invoice = $observer->getEvent()->getInvoice();
            $order = $invoice->getOrder();
            $customer = $order->getCustomer();

            if(!$customer){
                $customer = new Varien_Object();
            }

            if (!$customer->getId() && $order->getCustomerId()) {
                $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
            }elseif(!$customer->getId()){
                return;
            }
            //register to prevent infinite loop
            if (!Mage::registry('customer_group_switched')) {
                Mage::register('customer_group_switched', true);
            }
            //execute rules
            Mage::getModel('groupswitch/rule')->processRules($customer, 'invoice_save', null, true);
        } catch (Exception $e){
            Mage::log($e->getMessage(), null, 'plugincompany_groupswitch.log', true);
        }
    }

    /**
     * Recurring profiles event
     *
     * @param Varien_Event_Observer $observer
     */
    public function modelSaveCommitAfter(Varien_Event_Observer $observer)
    {
        if($this->_isCron()) return;
        if($this->_isBulk()) return;

        try{
            $object = $observer->getObject();
            if ($object && $object instanceof Mage_Sales_Model_Recurring_Profile) {
                if ($object->getCustomerId()) {
                    $customer = Mage::getModel('customer/customer')->load($object->getCustomerId());
                    if(!$customer) return;
                    //register to prevent infinite loop
                    if (!Mage::registry('customer_group_switched')) {
                        Mage::register('customer_group_switched', true);
                    }
                    //execute rules
                    Mage::getModel('groupswitch/rule')->processRules($customer, 'recurring_profiles_save', null, true);
                }
            }
        } catch (Exception $e){
            Mage::log($e->getMessage(), null, 'plugincompany_groupswitch.log', true);
        }
    }

    /**
     * Newsletter Subscriber Save Event
     *
     * @param Varien_Event_Observer $observer
     */
    public function newsletterSubscriberSaveAfter(Varien_Event_Observer $observer)
    {
        try {
            $subscriber = $observer->getEvent()->getSubscriber();
            //check if connected to customer account
            if (!$subscriber->getCustomerId()) {
                return;
            }

            $customer = Mage::getModel('customer/customer')->load($subscriber->getCustomerId());
            //check if customer exists
            if (!$customer || !$customer->getId()) {
                return;
            }

            //register to prevent infinite loop
            if (!Mage::registry('customer_group_switched')) {
                Mage::register('customer_group_switched', true);
            }
            //execute rules
            Mage::getModel('groupswitch/rule')->processRules($customer, 'newsletter_subscriber_save', null, true);

        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'plugincompany_groupswitch.log', true);
        }
    }

    /**
     * Cronjob execution. Executes rules for all customers
     */
    public function bulkCustomerGroupSwitchCron(){
        $activeRules = Mage::getModel('groupswitch/rule')
            ->getActiveRulesCollection()
            ->addEventFilter('customer_cron_job')
        ;
        if(!$activeRules->count()) return;

        // add registry value to make sure rules
        // aren't executed again when customer is saved
        if(!Mage::registry('is_groupswitch_cron')){
            Mage::register('is_groupswitch_cron', true);
        }

        $cronFlag = Mage::getModel('groupswitch/cronflag')->loadSelf();
        $lastCustomerId = $cronFlag->getLastCustomerId();
        $runOnce = Mage::getStoreConfigFlag('groupswitch/cronjob/only_once');

        //check if all customers are already processed today
        if($runOnce && $cronFlag->hasProcessedToday() && $lastCustomerId === 0){
            return;
        }

        //get customer collection
        $customersCollection = Mage::getModel('groupswitch/rule')
            ->getCustomerCollectionForEvent('customer_cron_job')
        ;

        //continue where last cronjob left off
        if($lastCustomerId){
            $customersCollection
                ->addFieldToFilter('entity_id',array('gt' => $lastCustomerId));
        }

        $customersCollection->setPageSize(50);

        //set max pages per cronjob
        $limit = Mage::getStoreConfig('groupswitch/cronjob/max_customer');
        if($limit){
            $pages = $limit / 50;
        }else{
            $pages = $customersCollection->getLastPageNumber();
        }

        $currentPage = 1;

        do {
            $customersCollection->setCurPage($currentPage);
            $customersCollection->load();

            foreach ($customersCollection as $customer) {
                try {
                    Mage::getModel('groupswitch/rule')->processRules($customer, 'customer_cron_job', null, true);
                    $lastCustomerId = $customer->getId();
                }catch(Exception $e){
                    Mage::log($e->getMessage(), null, 'plugincompany_groupswitch.log', true);
                }
            }

            //if less than 50 left (one page), all customers are processed
            if($customersCollection->count() < 50){
                $cronFlag->saveCustomerIdAndTimestamp(0);
            }else{
                $cronFlag->saveCustomerIdAndTimestamp($lastCustomerId);
            }

            $currentPage++;
            //clear collection and free memory
            $customersCollection->clear();
        } while ($currentPage <= $pages);
    }

}
