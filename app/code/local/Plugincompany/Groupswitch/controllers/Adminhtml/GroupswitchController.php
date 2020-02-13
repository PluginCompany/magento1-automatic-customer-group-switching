<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
?>
<?php
class Plugincompany_Groupswitch_Adminhtml_GroupswitchController extends Mage_Adminhtml_Controller_Action
{

    protected function _initRule()
    {
        $this->_title($this->__('Promotions'))->_title($this->__('Shopping Cart Price Rules'));

        Mage::register('current_customer_switch_rule', Mage::getModel('groupswitch/rule'));
        $id = (int)$this->getRequest()->getParam('id');

        if (!$id && $this->getRequest()->getParam('rule_id')) {
            $id = (int)$this->getRequest()->getParam('rule_id');
        }

        if ($id) {
            Mage::registry('current_customer_switch_rule')->load($id);
        }
    }
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("groupswitch/groupswitch")->_addBreadcrumb(Mage::helper("adminhtml")->__("Groupswitch  Manager"),Mage::helper("adminhtml")->__("Groupswitch Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Groupswitch"));
			    $this->_title($this->__("Manager Groupswitch"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
            $this->_title($this->__("Groupswitch"));
            $this->_title($this->__("Groupswitch"));
            $this->_title($this->__("Edit Item"));

            $id = $this->getRequest()->getParam("id");

            $model = Mage::getModel('groupswitch/rule');

            if ($id) {
                $model->load($id);
                if (! $model->getRuleId()) {
                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('groupswitch')->__('This rule no longer exists.'));
                    $this->_redirect('*/*');
                    return;
                }
            }
            // set entered data if was error when we do save
            $data = Mage::getSingleton('adminhtml/session')->getPageData(true);
            if (!empty($data)) {
                $model->addData($data);
            }

            $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
            $model->getActions()->setJsFormObject('rule_actions_fieldset');

            Mage::register('current_customer_switch_rule', $model);
            Mage::register("groupswitch_data", $model);

            $this->loadLayout();
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->getLayout()->getBlock('head')->setCanLoadRulesJs(true);
            $this->_setActiveMenu("groupswitch/groupswitch");
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Groupswitch Manager"), Mage::helper("adminhtml")->__("Groupswitch Manager"));
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Groupswitch Description"), Mage::helper("adminhtml")->__("Groupswitch Description"));
            $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock("groupswitch/adminhtml_groupswitch_edit"))->_addLeft($this->getLayout()->createBlock("groupswitch/adminhtml_groupswitch_edit_tabs"));
            $this->renderLayout();
		}

		public function newAction()
		{
            $this->_forward('edit');

		}

    /**
     * Promo quote save action
     *
     */
    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                $model = Mage::getModel('groupswitch/rule');

                $data = $this->getRequest()->getPost();
                $data = $this->_filterDates($data, array('from_date', 'to_date'));

                $data['old_customergroup'] = implode(',', $data['old_customergroup']);
                $data['new_customergroup'] = implode(',', $data['new_customergroup']);
                $data['events'] = implode(',', $data['events']);
                $data['store_ids'] = implode(',', $data['store_ids']);
                $data['order_status'] = implode(',', $data['order_status']);
                $data['invoice_status'] = implode(',', $data['invoice_status']);
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        Mage::throwException(Mage::helper('groupswitch')->__('Wrong rule specified.'));
                    }
                }


                $session = Mage::getSingleton('adminhtml/session');

                $validateResult = $model->validateData(new Varien_Object($data));
                if ($validateResult !== true) {
                    foreach($validateResult as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                    $session->setPageData($data);
                    $this->_redirect('*/*/edit', array('id'=>$model->getId()));
                    return;
                }

                if (isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                }

                unset($data['rule']);
                $model->loadPost($data);


                //date fix for Magento 1.6 and previous
                if (version_compare(Mage::getVersion(), '1.7', '<')) {
                    if($model->getFromDate()){
                        $model->setFromDate($model->getFromDate()->toString('YYYY-MM-dd'));
                    }else{
                        $model->setFromDate(null);
                    }
                    if($model->getToDate()){
                        $model->setToDate($model->getToDate()->toString('YYYY-MM-dd'));
                    }else{
                        $model->setToDate(null);
                    }
                }

                $session->setPageData($model->getData());

                $model->save();

                $session->addSuccess(Mage::helper('groupswitch')->__('The rule has been saved.'));
                $session->setPageData(false);

                if ($this->getRequest()->getParam('back') == 'edit') {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                if ($this->getRequest()->getParam('back') == 'execute') {
                    $this->_redirect('*/*/bulkProcessRules', array('rule_ids' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('*/*/edit', array('id' => $id));
                } else {
                    $this->_redirect('*/*/new');
                }
                return;

            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('catalogrule')->__('An error occurred while saving the rule data. Please review the log and try again.'));
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('rule_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("groupswitch/rule");
						$model->load($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		
		public function massRemoveAction()
		{
			try {
				$ids = $this->getRequest()->getPost('rule_ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("groupswitch/rule");
					  $model->load($id)->delete();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}

    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('groupswitch/rule'))
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    public function newActionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('groupswitch/rule'))
            ->setPrefix('actions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    public function applyRulesAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }
     
    public function bulkProcessRulesAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function applyRulesBulkAction()
    {
        Mage::register('is_groupswitch_bulk',1);
        $ruleIds = $this->getRequest()->getParam('ruleIds');
        $lastCustomerId = $this->getRequest()->getParam('offset');
        $limit = 10;
        
        //load model and execute for customer batch
        $model = Mage::getModel('groupswitch/rule');
        $result = $model
            ->applySelectedRulesForCustomerRange($lastCustomerId,$limit,$ruleIds);
        
        if(!count($result)){
            $result = 'done';
        }
        
        $this
            ->getResponse()
            ->setHeader('Content-type','application/json',true)
            ->setBody(json_encode($result))
        ;
    }

    /**
     * Returns result of current user permission check on resource and privilege
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
//        return Mage::getSingleton('admin/session')->isAllowed('promo/quote');
    }

}
