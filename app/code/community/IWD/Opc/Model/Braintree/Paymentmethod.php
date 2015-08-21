<?php
if(class_exists(Braintree_Payments_Model_Paymentmethod)){	
	class IWD_Opc_Model_Braintree_Paymentmethod extends Braintree_Payments_Model_Paymentmethod{
	
		/**
	 	* Format param "channel" for transaction
		*
		* @return string
		*/
		protected function _getChannel()
		{
			return 'Magento-IWD';
		}
	}
}