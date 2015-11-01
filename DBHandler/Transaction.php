<?php
 namespace Bondzu;

 require 'vendor/autoload.php';

 use Parse\ParseClient;
 use Parse\ParseObject;
 use Parse\ParseUser;
 use Parse\ParseQuery;


class Transaction extends ParseObject{

  public static $parseClassName = "Transacciones";

  private $_transaction;


  public function getTransactionId() {
    $this->_transaction->get("transaccionid");
  }
  public function getAmount() {
    $this->_transaction->get("precio");
  }
  public function getDescription() {
    $this->_transaction->get("description");
  }
}
