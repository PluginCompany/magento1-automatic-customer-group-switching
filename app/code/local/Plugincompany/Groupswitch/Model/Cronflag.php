<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
class Plugincompany_Groupswitch_Model_Cronflag extends Mage_Core_Model_Flag {

    protected $_flagCode = 'pc_groupswitch_cron';

    public function saveCustomerIdAndTimestamp($id)
    {
        $this->setFlagData(array(
            'customer_id' => $id,
            'time' => time()
        ));
        $this->save();
    }

    public function getLastCustomerId(){
        $data = $this->getFlagData();
        return isset($data['customer_id']) ? $data['customer_id'] : 0;
    }

    public function getLastDate(){
        $data = $this->getFlagData();
        return isset($data['time']) ? $data['time'] : 0;
    }

    public function hasProcessedToday(){
        if(date('Ymd') == date('Ymd', $this->getLastDate())){
            return true;
        }
        return false;
    }
}