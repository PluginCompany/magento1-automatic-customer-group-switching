<?php
ini_set('display_errors',1);
class Plugincompany_Groupswitch_Block_Adminhtml_Groupswitch_Bulkprocess_Content
    extends Mage_Core_Block_Template
{

    public function getRules()
    {
        return $this->getRuleModel()
            ->getCollection()
            ->addFieldToFilter(
                'rule_id',
                array(
                    'in' => $this->getRuleIds()
                )
            )
            ->setOrder('sort_order','ASC')
            ;
    }

    public function getApplicableCustomerCount()
    {
        return $this->getRuleModel()
            ->getCustomerCollectionForRulesByIds(
                $this->getRuleIds(),
                false
            )
            ->count()
        ;
    }
    
    public function getRuleIds()
    {
        return Mage::app()->getRequest()->getParam('rule_ids');
    }

    private function getRuleModel()
    {
        return Mage::getModel('groupswitch/rule');
    }
}
