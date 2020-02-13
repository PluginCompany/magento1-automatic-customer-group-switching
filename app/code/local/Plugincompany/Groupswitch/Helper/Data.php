<?php
class Plugincompany_Groupswitch_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_allowedFormats = array(
        Mage_Core_Model_Locale::FORMAT_TYPE_FULL,
        Mage_Core_Model_Locale::FORMAT_TYPE_LONG,
        Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM,
        Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
    );

    /**
     * Format date using current locale options and time zone.
     *
     * @param   date|Zend_Date|null $date
     * @param   string              $format   See Mage_Core_Model_Locale::FORMAT_TYPE_* constants
     * @param   bool                $showTime Whether to include time
     * @return  string
     */
    public function formatDate($date = null, $format = Mage_Core_Model_Locale::FORMAT_TYPE_SHORT, $showTime = false)
    {
        if (!in_array($format, $this->_allowedFormats, true)) {
            return $date;
        }
        if (!($date instanceof Zend_Date) && $date && !strtotime($date)) {
            return '';
        }
        if (is_null($date)) {
            $date = Mage::app()->getLocale()->date(Mage::getSingleton('core/date')->gmtTimestamp(), null, null);
        } else if (!$date instanceof Zend_Date) {
            $date = Mage::app()->getLocale()->date(strtotime($date), null, null, false);
        }

        if ($showTime) {
            $format = Mage::app()->getLocale()->getDateTimeFormat($format);
        } else {
            $format = Mage::app()->getLocale()->getDateFormat($format);
        }

        return $date->toString($format);
    }
}
