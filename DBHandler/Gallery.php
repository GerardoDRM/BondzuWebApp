<?php
 namespace Bondzu;

 require 'vendor/autoload.php';

 use Parse\ParseClient;
 use Parse\ParseObject;
 use Parse\ParseUser;
 use Parse\ParseQuery;


class Gallery extends ParseObject{

  public static $parseClassName = "Gallery";

  private $_gallery;


  public function getImage() {
    $this->_gallery->get("image");
  }
}
