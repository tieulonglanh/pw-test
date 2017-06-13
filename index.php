<?php
include 'User.php';
include 'WalletAccount.php';
//Create new user
$data = ['id' => 'test_customer_12'];
$user = new User($data);
$user->topup_limit = 100;
$user->withdraw_limit = 100;

$walletAccount = new WalletAccount($user);
//Get list user wallet account
$walletAccount->getListWalletAccounts();
//Top-up to user wallet account
$walletAccount->topUp('usd', 10);
$walletAccount->topUp('usd', 30);
$walletAccount->topUp('usd', 40);
$walletAccount->topUp('usd', 10);
$walletAccount->topUp('usd', 20);

//transfer usd to credit
$walletAccount->transfer('usd', 'credit', 20, 1);
$walletAccount->getWalletAccountBalance('usd');
$walletAccount->getWalletAccountBalance('credit');

//create euro account
$euro_account = ['id' => 'eur', 'name' => 'EURO', 'currency' => 'EUR', 'balance' => 0, 'usd_rate' => 0.893387592];
$walletAccount->createWalletAccount($euro_account);
$walletAccount->getListWalletAccounts();

//transfer usd to euro
$walletAccount->transfer('usd', 'eur', 40, 0.893387592);
$walletAccount->getWalletAccountBalance('credit');
$walletAccount->getWalletAccountBalance('usd');
$walletAccount->getWalletAccountBalance('eur');

//withdraw euro account
$walletAccount->withdraw('eur', 20);
$walletAccount->getWalletAccountBalance('eur');

$walletAccount->setFreezedAccount('eur');
$walletAccount->withdraw('eur', 10);
$walletAccount->getWalletAccountBalance('eur');


