<?php
require 'Object.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Accounts
 * Account class use to handle user
 * This class include method to create new user. 
 * This also allow get, set user properties,...
 * @author tieulonglanh
 */
class User extends Object{
    //put your code here
    protected $properties;
    
    public function User($data) {
        $this->properties = $data;
        $this->log('Init user - User info: ' . json_encode($this->properties));
    }
    
    /*
     * Get value of an User's property
     */
    public function __get($property) {
        return isset($this->properties[$property]) ? $this->properties[$property] : false;
    }
    /*
     * Set value of an User's property
     */
    public function __set($property, $value) {
        return ($this->properties[$property] = $value);
    }    
}
