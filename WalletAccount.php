<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AccountWallet
 * AccountWallet class use for handle all wallet accounts of an user
 * This class include method that get, set wallet account, 
 * @author Tieulonglanh
 */
class WalletAccount extends Object{
    //put your code here
    
    public $user;
    
    public $errors = [
        1 => 'Account is freezed',
        2 => 'Account is virtual',
        3 => 'Account is already freezed',
        4 => 'Account is existed',
        5 => 'Account is not existed',
        6 => 'User top-up limit is not set',
        7 => 'Currency is existed', 
        8 => 'Top-up amount is greater than top-up limit',
        9 => 'Transfer from account is not exist',
        10 => 'Transfer to account is not exist',
        11 => 'Transfer amount is greater than balance',
        12 => 'Transfer from account is freezed',
        13 => 'Transfer to account is freezed',
        14 => 'User withdraw limit is not set',
        15 => 'withdraw amount is greater than withdraw limit'
    ];
    
    public $error = 0;
    
    public $message = "Success";
    
    public $wallet_accounts = [
        'credit' => ['id' => 'credit', 'name' => 'CREDIT', 'currency' => 'CREDIT', 'balance' => 0, 'usd_rate' => 1],
        'usd' => ['id' => 'usd', 'name' => 'USD', 'currency' => 'USD', 'balance' => 0, 'usd_rate' => 1]
    ];
    
    public $credit_to_usd_rate = 1;
        
    public $default_account = 'usd';
    
    public $freezed_account = [];
    
    public $total_topup_amount = [];
    
    public $total_withdraw_amount = [];
    
    /**
     * Construct function
     * $walletAccount = new WalletAccount($user);
     * @param Object $user
     */
    public function WalletAccount($user) {
        $this->user = $user;
    }
    
    /**
     * Set default wallet account
     * $walletAccount->setDefaultAccount('usd');
     * @param string $id
     * @return mix
     */
    public function setDefaultAccount($id) {
        $is_allowed = $this->isAllowSetDefault($id);
        
        if($is_allowed['error'] === 0) {
            $this->default_account = strtolower($id);
        }
        $this->log("setDefaultAccount - User {$this->user->id} set default account {$id}: " . json_encode($is_allowed));
        return $is_allowed;
    }
    
    /**
     * Check if wallet account is allowed to set default
     * $walletAccount->isAllowSetDefault('credit');
     * @param string $id
     * @return mix
     */
    public function isAllowSetDefault($id) {        
        $is_exist_account = $this->isExistAccount($id);
        if(!$is_exist_account) {
            return ['error' => 5, 'message' => $this->errors[5]];
        }
        $is_freezed_account = $this->isFreezedAccount($id);
        if($is_freezed_account) {
            return ['error' => 1, 'message' => $this->errors[1]];
        }        
        $is_virtual_account = $this->isVirtualAccount($id);
        if($is_virtual_account) {
            return ['error' => 2, 'message' => $this->errors[2]];
        }
        return ['error' => $this->error, 'message' => $this->message];
    }
    
    /**
     * Set wallet account to freezed status
     * $walletAccount->setFreezedAccount('usd');
     * @param string $id
     * @return mix
     */
    public function setFreezedAccount($id) {
        $is_allowed = $this->isAllowFreeze($id);
        if($is_allowed['error'] === 0) {
            $this->freezed_account[] = strtolower($id);
        }
        $this->log("setFreezedAccount - User {$this->user->id} set freezed account {$id}:" . json_encode($is_allowed));
        return $is_allowed;
    }
    
    /**
     * Check if wallet account is allowed to set freeze
     * $walletAccount->isAllowFreeze('usd');
     * @param string $id
     * @return mix
     */
    public function isAllowFreeze($id) {
        $is_exist_account = $this->isExistAccount($id);
        if(!$is_exist_account) { 
            return ['error' => 5, 'message' => $this->errors[5]];
        }
        if(in_array($id, $this->freezed_account)) {
            return ['error' => 3, 'message' => $this->errors[3]];
        }   
        $is_virtual_account = $this->isVirtualAccount($id);
        if($is_virtual_account) {
            return ['error' => 2, 'message' => $this->errors[2]];
        }
        return ['error' => $this->error, 'message' => $this->message];
    }
    
    /**
     * Create new wallet account
     * $account = ['id' => 'euro', 'name' => 'EURO', 'currency' => 'EURO', 'balance' => 0];
     * $walletAccount->createWalletAccount($account);
     * @param mix $account
     * @return mix
     */
    public function createWalletAccount($account) {
        $id = $account['id'];
        $is_allowed = $this->isAllowCreateWalletAccount($account);
        if($is_allowed['error'] === 0) {
            $this->wallet_accounts[$id] = $account;
        }
        $this->log("createWalletAccount - User {$this->user->id} create wallet account {$id}: " . json_encode($is_allowed));
        return $is_allowed;
    }
    
    /**
     * Check if wallet account is allowed to create
     * $walletAccount->isAllowCreateWalletAccount('euro');
     * @param mix $account
     * @return mix
     */
    public function isAllowCreateWalletAccount($account) {
        
        $is_exist_account = $this->isExistAccount($account['id']);
        if($is_exist_account) {
            return ['error' => 4, 'message' => $this->errors[4]];
        }
        $is_exist_currency = $this->isExistCurrency($account['currency']);
        if($is_exist_currency) {
            return ['error' => 7, 'message' => $this->errors[7]];
        }
        
        return ['error' => $this->error, 'message' => $this->message];
    }
    
    /**
     * Get a list of wallet account
     * $walletAccount->getListWalletAccounts();
     * @return type
     */
    public function getListWalletAccounts() {
        $this->log("getListWalletAccounts - User {$this->user->id} get all wallet accounts: " . json_encode($this->wallet_accounts));
        return $this->wallet_accounts;
    }
    
    /**
     * Get wallet account by id
     * $walletAccount->getWalletAccount('usd);
     * @param string $id
     * @return mix
     */
    public function getWalletAccount($id) {
        $this->log("getWalletAccount - User {$this->user->id} get wallet account {$id}: " . json_encode($this->wallet_accounts[$id]));
        return $this->wallet_accounts[$id];
    }
    
    /**
     * Get wallet account balance by id
     * $walletAccount->getWalletAccountBalance('usd');
     * @param string $id
     * @return mix
     */
    public function getWalletAccountBalance($id) {
        $this->log("getWalletAccountBalance - User {$this->user->id} get wallet account {$id} balance: " . $this->wallet_accounts[$id]['balance']);
        return $this->wallet_accounts[$id]['balance'];
    }
    
    /**
     * Topup money to an account
     * $walletAccount->topUp('usd', 10);
     * @param type $id
     * @param float $amount
     * @return mix
     */
    public function topUp($id, $amount) {
        $is_allowed_top_up = $this->isAllowedTopUp($id, $amount);
        if($is_allowed_top_up['error'] === 0) {
            $this->wallet_accounts[$id]['balance'] += $amount;
            $this->total_topup_amount[date('Y-m-d')] += $amount;
            $this->log("topUp - User {$this->user->id} top-up wallet accounts {$id} amount: " . $amount . ", new balance: {$this->wallet_accounts[$id]['balance']}");            
        }else {
            $this->log("topUp - User {$this->user->id} top-up wallet accounts {$id} amount: " . $amount . ", error: " . json_encode($is_allowed_top_up));
        }
        return $is_allowed_top_up;
    }
    
    /**
     * Check if user is allowed to top-up
     * $walletAccount->isAllowedTopUp('usd', 10);
     * @param type $id
     * @param float $amount
     * @return mix
     */
    public function isAllowedTopUp($id, $amount) {
        $is_exist_account = $this->isExistAccount($id);
        if(!$is_exist_account) {
            return ['error' => 5, 'message' => $this->errors[5]];
        }
        $is_freezed_account = $this->isFreezedAccount($id);
        if($is_freezed_account) {
            return ['error' => 1, 'message' => $this->errors[1]];
        }      
        if(!isset($this->user->topup_limit)) {
            return ['error' => 6, 'message' => $this->errors[6]];
        }
        
        if($this->user->topup_limit < ($this->total_topup_amount[date('Y-m-d')] + $amount) * $this->wallet_accounts[$id]['usd_rate']) {
            return ['error' => 8, 'message' => $this->errors[8]];
        }
        return ['error' => $this->error, 'message' => $this->message];
    }
    /**
     * Transfer amount from an account to another
     * $walletAccount->transfer('usd', 'credit', 20, 1);
     * @param string $from
     * @param string $to
     * @param float $amount
     * @param float $transfer_rate
     * @return mix
     */
    public function transfer($from, $to, $amount, $transfer_rate) {
        $is_allowed_transfer = $this->isAllowTransfer($from, $to, $amount);
        if($is_allowed_transfer['error'] === 0) {
            $to_amount = $amount * $transfer_rate;
            $this->wallet_accounts[$from]['balance'] -= $amount;
            $this->wallet_accounts[$to]['balance'] += $to_amount;
            $this->log("Transfer - User {$this->user->id} from: {$from}, amount: " . $amount . ", to {$to}, transfer rate: {$transfer_rate}.");            
        }else{
            $this->log("Transfer - User {$this->user->id} from: {$from}, amount: " . $amount . ", to {$to}, transfer rate: {$transfer_rate}. Error: " . json_encode($is_allowed_transfer));            
        }
        return $is_allowed_transfer;
    }
    
    /**
     * Check if transfer is allowed
     * $walletAccount->isAllowTransfer('usd', 'eur', 20);
     * @param string $from
     * @param string $to
     * @param float $amount
     * @return mix
     */
    public function isAllowTransfer($from, $to, $amount) {
        $is_exist_from_account = $this->isExistAccount($from);
        if(!$is_exist_from_account) {
            return ['error' => 9, 'message' => $this->errors[9]];
        }
        $is_freezed_from_account = $this->isFreezedAccount($from);
        if($is_freezed_from_account) {
            return ['error' => 12, 'message' => $this->errors[12]];
        }      
        $is_exist_to_account = $this->isExistAccount($to);
        if(!$is_exist_to_account) {
            return ['error' => 10, 'message' => $this->errors[10]];
        }
        $is_freezed_to_account = $this->isFreezedAccount($to);
        if($is_freezed_to_account) {
            return ['error' => 13, 'message' => $this->errors[13]];
        } 
        if($this->wallet_accounts[$from]['balance'] < $amount) {
            return ['error' => 11, 'message' => $this->errors[11]];
        }
        return ['error' => $this->error, 'message' => $this->message];
    }
    
    /**
     * Withdraw money from un-virtual account
     * $walletAccount->withdraw('eur', 20);
     * @param string $id
     * @param float $amount
     * @return mix
     */
    public function withdraw($id, $amount) {
        $is_allowed_withdraw = $this->isAllowWithdraw($id, $amount);
        if($is_allowed_withdraw['error'] === 0) {
            $this->wallet_accounts[$id]['balance'] -= $amount;
            $this->log("Withdraw - User {$this->user->id} from: {$id}, amount: " . $amount);
        }else{
            $this->log("Withdraw - User {$this->user->id} from: {$id}, amount: " . $amount . " Error: " . json_encode($is_allowed_withdraw));
        }
        return $is_allowed_withdraw;
    }
    
    /**
     * Check if witdraw is allowed
     * $walletAccount->isAllowWithdraw('eur', 10);
     * @param string $id
     * @param float $amount
     * @return mix
     */
    public function isAllowWithdraw($id, $amount) {
        $is_virtual_account = $this->isVirtualAccount($id);
        if($is_virtual_account) {
            return ['error' => 2, 'message' => $this->errors[2]];
        }
        $is_exist_account = $this->isExistAccount($id);
        if(!$is_exist_account) {
            return ['error' => 5, 'message' => $this->errors[5]];
        }
        $is_freezed_account = $this->isFreezedAccount($id);
        if($is_freezed_account) {
            return ['error' => 1, 'message' => $this->errors[1]];
        }      
        if(!isset($this->user->withdraw_limit)) {
            return ['error' => 14, 'message' => $this->errors[14]];
        }
        if($this->user->withdraw_limit < ($this->total_withdraw_amount[date('Y-m-d')] + $amount) * $this->wallet_accounts[$id]['usd_rate']) {
            return ['error' => 15, 'message' => $this->errors[15]];
        }
        return ['error' => $this->error, 'message' => $this->message];
    }
    
    /**
     * Check if the account is freezed
     * @param string $id
     * @return boolean
     */
    public function isFreezedAccount($id) {
        if(empty($this->freezed_account)) {
            return false;
        }
        return in_array($id, $this->freezed_account);
    }
    
    /**
     * Check is the account is virtual
     * @param string $id
     * @return boolean
     */
    public function isVirtualAccount($id) {
        return (strtolower($id) === 'credit');
    }
    
    /**
     * Check if account is existed
     * @param string $id
     * @return boolean
     */
    public function isExistAccount($id) {
        return isset($this->wallet_accounts[$id]);
    }
    
    /**
     * Check if an account with currency is existed
     * $walletAccount->isExistCurrency('euro');
     * @param string $currency
     * @return boolean
     */
    public function isExistCurrency($currency) {
        foreach($this->wallet_accounts as $wallet_account) {
            if($wallet_account['currency'] === strtoupper($currency)) {
                return true;
            }
        }
        return false;
    }
    
}
