<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
$installer = $this;
$installer->startSetup();

$table = $installer->getTable('groupswitch/rule');

$sql="
	ALTER TABLE {$table} 
		ADD COLUMN `customer_notification` TINYINT NULL DEFAULT 0 AFTER `events`,
		ADD COLUMN `customer_notification_subject` VARCHAR(255) NULL AFTER `customer_notification`,
		ADD COLUMN `customer_notification_contents` TEXT NULL AFTER `customer_notification_subject`,
		ADD COLUMN `admin_notification` TINYINT NULL DEFAULT 0 AFTER `customer_notification_contents`,
		ADD COLUMN `admin_notification_email` VARCHAR(255) NULL AFTER `admin_notification`,
		ADD COLUMN `admin_notification_subject` VARCHAR(255) NULL AFTER `admin_notification_email`,
		ADD COLUMN `admin_notification_contents` TEXT NULL AFTER `admin_notification_subject`;
";

$installer->run($sql);

$installer->endSetup();
	 