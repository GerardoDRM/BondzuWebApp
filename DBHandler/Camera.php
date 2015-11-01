<?php
 namespace Bondzu;

 require 'vendor/autoload.php';

 use Parse\ParseClient;
 use Parse\ParseObject;
 use Parse\ParseQuery;

class Camera extends ParseObject{
  public static $parseClassName = "Camera";

  private $objectId;
  private $_animal;
  private $_cam;

  public function __construct($objectId = null) {
    parent::__construct("Camera", $objectId, false);

    $query = new ParseQuery("Camera");
    $query->equalTo("objectId", $objectId);
    $this->_cam = $query->first();
    }

    public static function setCamera($camera) {
      $this->_cam = $camera;
    }

    public function getDescription() {
        return $this->_cam->get('description');
    }

    public function getAnimalName() {
        return $this->_cam->get('animal_name');
    }

    public function getAvailability() {
        return $this->_cam->get('funcionando');
    }

    public function getUrlWeb() {
        return $this->_cam->get('url_web');
    }
}
