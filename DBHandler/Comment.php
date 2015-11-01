<?php
 namespace Bondzu;

 require 'vendor/autoload.php';

 use Parse\ParseClient;
 use Parse\ParseObject;
 use Parse\ParseUser;
 use Parse\ParseQuery;


class Comment extends ParseObject{

  public static $parseClassName = "Comment";

  private $_comment;


  public function getMessage() {
    $this->_comment->get("message");
  }
}
