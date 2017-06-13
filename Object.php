<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Object
 * Object class provides some method use by serveral subclasses
 * This class includes logging method which is used when subclasses call a method
 * @author tieulonglanh
 */
class Object {
    //put your code here
    
    public function __construct() {
        
    }
    
    public function log($message, $file = 'info.log') {
        if(!is_file($file)) {
            $log_file = fopen($file, "w") or die('Unable to create file');
        }else{
            $log_file = fopen($file, "a") or die('Unable to create file');
        }
        fwrite($log_file, "\n". date("Y-m-d H:i:s") . " ". $message);
        fclose($log_file);
    }

}
