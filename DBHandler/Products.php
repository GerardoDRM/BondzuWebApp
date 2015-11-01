<?php
 namespace Bondzu;

 require 'vendor/autoload.php';

 use Parse\ParseClient;
 use Parse\ParseObject;
 use Parse\ParseUser;
 use Parse\ParseQuery;


class Products extends ParseObject{

  public static $parseClassName = "Productos";

  private $_product;


  public function getName() {
    $this->_product->get("nombre");
  }
  public function getCategory() {
    $this->_product->get("categoria");
  }
  public function getDescription() {
    $this->_product->get("descripcion");
  }
  public function getDescription() {
    $this->_product->get("descripcion");
  }
  public function getAvailability() {
    $this->_product->get("disponible");
  }
  public function getInfo() {
    $this->_product->get("info");
  }
  public function getInfoAmount() {
    $this->_product->get("info_ammount");
  }

}
