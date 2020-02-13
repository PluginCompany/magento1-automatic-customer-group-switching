<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
?>
<?php
class Plugincompany_Groupswitch_Model_Mysql4_Rule_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init("groupswitch/rule");
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function addIdFilter($ids)
    {
        if(!$ids) return $this;
        if(!is_array($ids)) $ids = array($ids);
        return $this->addFieldToFilter('rule_id', array('in' => $ids));
    }

    /**
     * @param int $id
     * @return $this
     */
    public function addStoreIdFilter($id)
    {
        return $this->addFieldToFilter('store_ids', array('finset' => $id));
    }

    /**
     * @param string $date
     * @return Plugincompany_Groupswitch_Model_Mysql4_Rule_Collection
     */
    public function addDateFilter($date = null)
    {
        if(!$date) {
            $date = date('Y-m-d', Mage::getModel('core/date')->timestamp(time()));
        }
        return $this
            ->addFieldToFilter(
                'from_date', array(
                    array('lteq' => $date),
                    array('null' => true)
                )
            )
            ->addFieldToFilter(
                    'to_date', array(
                        array('gteq' => $date),
                        array('null' => true)
                    )
                );
    }

    public function addIsActiveFilter()
    {
        return $this->addFieldToFilter('is_active',1);
    }

    public function addEventFilter($event)
    {
        if(!$event) return $this;
        return $this->addFieldToFilter('events', array('finset' => $event));
    }

    public function sortByPriority()
    {
        return $this
            ->setOrder('sort_order','ASC')
            ->setOrder('rule_id','ASC');
    }

    public function getWebsiteIdsForCurrentCollection()
    {
        $websiteIds = array();
        foreach($this->getColumnValues('store_ids') as $ids) {
            $websiteIds = array_merge($websiteIds, explode(',', $ids));
        }
        return $websiteIds;
    }

    public function getPossibleFromGroupsForCurrentCollection()
    {
        $oldCustomerGroups = array();
        foreach($this->getColumnValues('old_customergroup') as $ids) {
            $oldCustomerGroups = array_merge($oldCustomerGroups, explode(',', $ids));
        }
        return $oldCustomerGroups;
    }
}
	 