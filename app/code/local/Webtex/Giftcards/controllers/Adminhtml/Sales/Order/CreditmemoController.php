<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Saa
 * Date: 25.06.13
 * Time: 15:26
 * To change this template use File | Settings | File Templates.
 */
require_once ('Mage/Adminhtml/controllers/Sales/Order/CreditmemoController.php');
class Webtex_Giftcards_Adminhtml_Sales_Order_CreditmemoController extends Mage_Adminhtml_Sales_Order_CreditmemoController
{
    /**
     * Save creditmemo
     * We can save only new creditmemo. Existing creditmemos are not editable
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('creditmemo');
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            $creditmemo = $this->_initCreditmemo();
            if ($creditmemo) {
                if (($creditmemo->getGrandTotal() <=0) && (!$creditmemo->getAllowZeroGrandTotal())) {
                    $giftCardsOrderCollection = Mage::getModel('giftcards/order')->getCollection()
                        ->addFieldToFilter('id_order', $creditmemo->getOrderId());
                    //webtex giftcards condition for refund
                    if($giftCardsOrderCollection->getSize() == 0) {
                        Mage::throwException(
                            $this->__('Credit memo\'s total must be positive.')
                        );

                    }
                }

                $comment = '';
                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );
                    if (isset($data['comment_customer_notify'])) {
                        $comment = $data['comment_text'];
                    }
                }

                if (isset($data['do_refund'])) {
                    $creditmemo->setRefundRequested(true);
                }
                if (isset($data['do_offline'])) {
                    $creditmemo->setOfflineRequested((bool)(int)$data['do_offline']);
                }

                $creditmemo->register();
                if (!empty($data['send_email'])) {
                    $creditmemo->setEmailSent(true);
                }

                $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $this->_saveCreditmemo($creditmemo);
                $creditmemo->sendEmail(!empty($data['send_email']), $comment);
                $this->_getSession()->addSuccess($this->__('The credit memo has been created.'));
                Mage::getSingleton('adminhtml/session')->getCommentText(true);
                $this->_redirect('*/sales_order/view', array('order_id' => $creditmemo->getOrderId()));
                return;
            } else {
                $this->_forward('noRoute');
                return;
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($this->__('Cannot save the credit memo.'));
        }
        $this->_redirect('*/*/new', array('_current' => true));
    }
}