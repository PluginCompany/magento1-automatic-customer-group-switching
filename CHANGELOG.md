# Changelog: Automatic Customer Group Switching for Magento 1 / OpenMage

All releases of this extension, newest first. This repository contains the latest release, tagged [`1.12.1`](https://github.com/PluginCompany/magento1-automatic-customer-group-switching/releases/tag/1.12.1); earlier versions are listed for reference and have no tag.

## 1.12.1 — 2020-02-13

- Replaced occurence of short array syntax with regular array syntax. The short array syntax causes issues when using an older version of PHP, for example during cron execution.

## 1.12.0 — 2019-09-23

- Added 'Last Automatic Group Switch' customer attribute
- Added 'Last automatic group switch days ago' condition for group switching rules

## 1.11.0 — 2019-04-03

- Optimized performance for on demand rule execution in the admin area by pre-filtering the customer collection on "From Group" and "Website" combination(s) based on the selected rule(s)
- Optimized performance for cron job group switching by pre-selecting rules enabled for cron job event & current date and by pre-filtering customer collection on possible "From Group" and "Website" combination(s) from the currently active rule(s)
- Improved error reporting during manual rule execution from admin area
- Fixed customer attribute loading issue during manual rule execution from admin area

## 1.10.6 — 2018-01-24

- Fix for issue related to total qty in cart condition
- Added translation csv file
- Fix for date display in subselection condition. Previously displayed date was adjusted for timezone.

## 1.10.4 — 2018-01-13

- Added support for Magento 1.4.x - 1.6.x. (Additional patch is needed when installing the extension on these Magento versions)

## 1.10.3 — 2017-11-06

- Dropped Magento 1.5.x support due to Magento Marketplace code style limitations

## 1.10.2 — 2017-10-28

- Minor code improvements

## 1.10.1 — 2017-10-12

- Fixed bug regarding default billing address condition
- Some minor optimizations and improvements

## 1.10.0 — 2017-09-29

- Added payment method to order history conditions
- Shipping method condition is now select option

## 1.9.3 — 2017-07-22

- Fixed issue regarding on-demand bulk group switcher

## 1.9.2 — 2017-07-21

- Fixed issue regarding Total Invoiced minus Refunded condition

## 1.9.1 — 2016-12-21

- Fixed minor bug regarding recurring profile rule validation

## 1.9.0 — 2016-12-08

- Added 'Assign New Group at Checkout' option in Rule Action tab

## 1.8.0 — 2016-08-18

- Added new conditions to order history section

## 1.7.0 — 2016-08-15

- Added on demand group switching mass action
- Added on demand group switching action for single rule
- Added new order rule conditions
- Added new customer rule attribute conditions and possibility to use custom attributes
- Fixed issue regarding address field conditions in account creation screen

## 1.6.1 — 2016-05-02

- Fixed extension compatibility with Magento 1.4
- Minor bug fix

## 1.6.0 — 2016-01-05

- Added 'Cronjob group switching batch' to rule processing events
- Added Automatic Customer Group Switching Cron configuration options

## 1.5.3 — 2016-01-04

- Fixed compatibility with Mandrill extension

## 1.5.2 — 2015-12-17

- Fixed infinite loop bug

## 1.5.1 — 2015-12-10

- Fixed 'Mage registry already exists' bug

## 1.5.0 — 2015-12-05

- Added customer & admin e-mail notification functionality
- Fixed group switching issue regarding virtual & downloadable products

## 1.4.0 — 2015-11-19

- Added newsletter subscription event to Actions tab

## 1.3.3 — 2015-11-11

- Made compatible with SUPEE-6788

## 1.3.2 — 2015-10-22

- Conditions based on ordered products (new orders) now also respect applicable order statuses

## 1.3.1 — 2015-10-22

- Fixed bug regarding order and invoice save event

## 1.3.0 — 2015-10-01

- Added customer e-mail address to rule conditions

## 1.2.4 — 2015-09-04

- Fixed customer address saving bug

## 1.2.3 — 2015-08-25

- Fixed customer attribute bug

## 1.2.2 — 2015-07-14

- Fixed table prefix bug

## 1.2.1 — 2015-06-16

- Fixed newsletter subscriptions bug
- Fixed Observer.php bug

## 1.2.0 — 2015-05-11

- Added order history date filter to rule conditions
- Added option to switch groups on customer log in event
- Added option to enable rules for specific events using multi select
- Added round robin switching to multiple customer groups
- Added extra customer attributes to rule conditions

## 1.1.3 — 2015-04-08

- Added Paypal recurring profiles to rule conditions
- Fixed some minor bugs

## 1.1.2 — 2015-04-07

- Fixed current order/cart rule bug

## 1.1.1 — 2015-01-25

- Added new customer group auto-switching conditions
- Fixed some minor issues regarding extension compatibility with Magento 1.4 to 1.6

## 1.0.0 — 2014-10-30

- First release of Magento Automatic Customer Group Switching extension by Plugin Company.
