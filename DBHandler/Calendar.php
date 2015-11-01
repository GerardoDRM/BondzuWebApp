<?php
 namespace Bondzu;

 require 'vendor/autoload.php';

 use Parse\ParseClient;
 use Parse\ParseObject;
 use Parse\ParseUser;
 use Parse\ParseQuery;


class Calendar extends ParseObject{

  public static $parseClassName = "Calendar";

  private $_event;


  public function getTitle() {
    $this->_event->get("title");
  }

  public function getDescription() {
    $this->_event->get("description");
  }

  public function getEventPhoto() {
    $this->_event->get("event_photo");
  }

  public function getStartDate() {
    $this->_event->get("start_date");
  }

  public function getEndDate() {
    $this->_event->get("end_date");
  }
}
