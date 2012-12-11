<?php
require_once("upbase/libs/phpmailer/class.phpmailer.php");

class AppPHPMailer extends PHPMailer {

    function AddAddress($address, $name = ""){
        global $upbase_general_email_override;
        if(isset($upbase_general_email_override) && !empty($upbase_general_email_override)){
         return parent::AddAddress($upbase_general_email_override, $name);
        }
        else{
         return parent::AddAddress($address, $name);
        }
    }
} // upbaseSmarty
?>
