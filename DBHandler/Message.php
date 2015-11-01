<?php
 namespace Bondzu;

 require 'vendor/autoload.php';

 use Parse\ParseClient;
 use Parse\ParseObject;
 use Parse\ParseUser;
 use Parse\ParseQuery;


class Message extends ParseObject{

  public static $parseClassName = "Messages";

  private $_message;


  public function getMessage() {
    $this->_message->get("message");
  }
  public function getPhotoMessage() {
    $this->_message->get("photo_message");
  }
  public function getLikes() {
    $this->_message->get("likesRelation");
  }
}
