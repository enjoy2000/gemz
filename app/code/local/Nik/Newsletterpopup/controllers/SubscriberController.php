<?php
require_once 'app/code/core/Mage/Newsletter/controllers/SubscriberController.php';

class Nik_Newsletterpopup_SubscriberController extends Mage_Newsletter_SubscriberController
{
    public function newAction()
    {
        parent::newAction();
        $timeCookiesTimeout = Mage::helper('newsletterpopup')->timeCookiesTimeout();

        //Set or remove cookie //
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $email = (string)$this->getRequest()->getPost('email');
            $period = $timeCookiesTimeout * 86400;
            Mage::getModel('core/cookie')->set('email_subscribed', $email, $period);
        } else {
            if (isset($_COOKIE['email_subscribed']))
                setcookie('email_subscribed', $email, time() - $timeCookiesTimeout * 86400, '/');
        }

        // get poll result to send email
        if ($pollResult = $this->getRequest()->getPost('poll')) {
            //if (isset($status) && $status != Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                $email = (string)$this->getRequest()->getPost('email');
                $poll = [
                    '1' => 'I only buy it to get out of trouble',
                    '2' => 'It looks good on my Pinterest page',
                    '3' => 'I buy seasonal pieces at walmart',
                    '4' => 'I love it! Please keep me informed...',
                ];
                $body = '<table'
                    . '<tr><td colspan="2">Subscriber Info</td></tr>'
                    . '<tr><td>Email</td><td>Poll Result</td></tr>'
                    . '<tr><td>' . $email . '</td><td>' . $poll[$pollResult] . '</td></tr>'
                    . '</table>'
                    ;
                $mail = Mage::getModel('core/email')
                    ->setToName('Debbi')
                    ->setToEmail('dh@gemz.gallery')
                    //->setToEmail('enjoy3005@gmail.com')
                    ->setBody($body)
                    ->setSubject('There is a new subscriber - Gemz.Gallery')
                    ->setFromEmail('dh@gemz.gallery')
                    ->setFromName('GEMZ.GALLERY')
                    ->setType('html');
                
                try {
                    $mail->send();
                } catch (Exception $e) {
                    Mage::log($e->getMessage());
                    Mage::getSingleton('core/session')->addError($e->getMessage());
                }
            //}
        }
    }
}
