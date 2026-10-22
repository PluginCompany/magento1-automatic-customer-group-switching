# Extension FAQ

## When is a customer group switching rule executed?

Our Magento extension includes three options for executing your automatic customer group switching rules: on specific events, on demand (single rule or mass action) and periodically using Cron. You can read all about these rule execution options in [Executing Customer Group Switching Rules](./09-executing-customer-group-switching-rules.md).

## How do I manage customers based on product memberships?

Magento Automatic Customer Group Switching is perfectly suitable for managing customer groups based on product memberships with any given expiration date, for example a VIP membership card that's valid for 1 year.

In this scenario you'll need to create two customer group switching rules: one for moving customers to the VIP Members group if the membership card is found in the order history of the last 365 days, and one for moving customers back to the original group when the membership has expired (= if the membership has NOT been found in the order history of the last 365 days).

To create the rule for moving customers to the active membership group, select **Ordered Products (order history) > Products subselection** from the Rule Conditions dropdown menu, after which you can select the product attribute SKU to configure the following rule:

*If an item is **FOUND** in the order history **of the last 365 days**, with **ALL** of these conditions true:*

*SKU **is** **vip-membership***

To move customers back to the original group when the membership has expired you can use the same rule as above, only changing the first condition to:

*If an item is **NOT FOUND** in the order history...*

---

[Documentation index](./README.md) · [README](../README.md) · [Online version](https://plugin.company/knowledge-base/docs/magento-automatic-customer-group-switching/faq/)
