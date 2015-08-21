<?php
class Dazzle_Service_Model_Observer
{


    public function getSapData()
   {

 

   }

 public function send_data_to_sap($observer){
   //  Mage::log("Name", null, 'test.log');
   $customerData = $observer->getEvent()->getCustomer()->getData();    
   $customer = Mage::getModel('customer/customer')
 ->load($customerData['entity_id']); //insert cust ID
$customerData['email'];
$customerData['firstname'].' '.$customerData['lastname'];


  //Mage::log(var_export($customer->getAddresses(),TRUE), null, 'testsattt -'.$customerData['entity_id'].'.log');
  
  
  $to = $customerData['email'];
$subject = "Gemz Gallery";

$message = "
<html>
<head>
<title>Gemz Gallery</title>
</head>
<body>
<p>Hello ". $customerData['firstname'].' '.$customerData['lastname']."
<br>

<p>Thank you for signing up with the Gemz Gallery </p>

</body>
</html>
";

// Always set content-type when sending HTML email
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

// More headers
$headers .= 'From: <Info@GEMZ.GALLERY>' . "\r\n";
//$headers .= 'Cc: myboss@example.com' . "\r\n";

//mail($to,$subject,$message,$headers);



 }
}
