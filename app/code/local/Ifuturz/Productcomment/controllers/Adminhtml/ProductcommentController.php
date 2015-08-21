<?php

class Ifuturz_Productcomment_Adminhtml_ProductcommentController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
	{
		$this->loadLayout()
			->_setActiveMenu('productcomment')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Add productcomment Category Management'), Mage::helper('adminhtml')->__('Add productcomment category Management'));
		
		return $this;
	}
	
	public function indexAction() 
	{
		$this->_initAction()
			->renderLayout();
	}
	
	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('productcomment/productcomment')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
			Mage::register('productcomment_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('productcomment');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Add productcommentegory Management'), Mage::helper('adminhtml')->__('Add productcomment category Management'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Rule News'), Mage::helper('adminhtml')->__('Rule News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('productcomment/adminhtml_productcomment_edit'))
				->_addLeft($this->getLayout()->createBlock('productcomment/adminhtml_productcomment_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcomment')->__('productcomment does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	
	public function newAction() 
	{
		$this->_forward('edit');
	}
	
	public function saveAction() 
	{
		if ($data = $this->getRequest()->getPost()) 
		{	
			
			$model = Mage::getModel('productcomment/productcomment');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
						
			try 
			{				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('productcomment')->__('User was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) 
				{
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            }
			catch (Exception $e) 
			{
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcomment')->__('Unable to find User to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('productcomment/productcomment');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('productcomment was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $productcommentIds = $this->getRequest()->getParam('productcomment');
        if(!is_array($productcommentIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcomment')->__('Please select Productcomment(s)'));
        } else {
            try {
                foreach ($productcommentIds as $productcommentId) {
                    $productcomment = Mage::getModel('productcomment/productcomment')->load($productcommentId);
                    $productcomment->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($productcommentIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $productcommentIds = $this->getRequest()->getParam('productcomment');
        if(!is_array($productcommentIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Productcomment(s)'));
        } else {
            try {
                foreach ($productcommentIds as $productcommentId) {
                    $productcomment = Mage::getSingleton('productcomment/productcomment')
                        ->load($productcommentId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($productcommentIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
}