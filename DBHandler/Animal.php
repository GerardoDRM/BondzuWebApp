<?php
 namespace Bondzu;

 require 'vendor/autoload.php';
 require_once 'Camera.php';

 use Parse\ParseClient;
 use Parse\ParseObject;
 use Parse\ParseUser;
 use Parse\ParseQuery;
 use Bondzu\Camera;


class Animal extends ParseObject{

  public static $parseClassName = "AnimalV2";

  private $objectId;
  private $_animal;
  private $_cameras;
  private $_events;
  private $_gallery;
  private $_comments;
  private $products;

  public function __construct($objectId = null) {
    parent::__construct("AnimalV2", $objectId, false);

    $query = new ParseQuery("AnimalV2");
    $query->equalTo("objectId", $objectId);
    $this->_animal = $query->first();
    }

  public function getName() {
      return $this->_animal->get('name_en');
  }
  public function getAbout() {
    return $this->_animal->get('about_en');
  }
  public function getCharacteristics() {
    return $this->_animal->get('characteristics_en');
  }
  public function getProfilePhoto() {
    return $this->_animal->get('profilePhoto')->getURL();
  }
  public function getKeepers() {
    return $this->_animal->get('keepers');
  }
  public function getEvents() {
    $query = new ParseQuery("Calendar");
    $query->equalTo("id_animal", $this);
    $ev = $query->find();
    $this->_events = [];
    foreach($ev as $event) {
      $e = [
        "title" => $event->get("title_en"),
        "description" => $event->get("description_en"),
        "photo" => $event->get("event_photo")->getURL(),
        "start" => $event->get("start_date"),
        "end" => $event->get("end-date")
      ];
      array_push($this->_events, $e);
    }
    return $this->_events;
  }

  public function getAdopters() {
    return $this->_animal->get('adopters');
  }
  public function getSpecie() {
    return $this->_animal->get('species_en');
  }

  public function getCameras() {
    $query = new ParseQuery("Camera");
    $query->equalTo("animal_Id", $this);
    $cams = $query->find();
    $this->_cameras = [];
    foreach($cams as $cam) {
      $c = [
        "description" => $cam->get("description"),
        "url" => $cam->get("url_web")
      ];
      array_push($this->_cameras, $c);
    }
    return $this->_cameras;
  }

  public function getGallery() {
    $query = new ParseQuery("Gallery");
    $query->equalTo("animal_id", $this);
    $gal = $query->find();
    $this->_gallery = [];
    foreach($gal as $photo) {
      $g = [
        "image" => $photo->get("image")->getURL()
      ];
      array_push($this->_gallery, $g);
    }
    return $this->_gallery;
  }

  public function getComments() {
    $query = new ParseQuery("Messages");
    $query->equalTo("animal_Id", $this);
    $query->includeKey("id_user");
    $comments = $query->find();
    $this->_comments = [];
    foreach($comments as $comment) {
      $user = $comment->get('id_user');
      $data = [
        "user_photo" => '',
        "user_name" => $user->get('name') . " " . $user->get('lastname'),
        "message" => $comment->get("message"),
        "likes" => count($comment->get("likesRelation"))
      ];
      if($user->get('photoFile') != null) {
        $data["user_photo"] = $user->get('photoFile')->getURL();
      }
      else {
        if($user->get('photo') != null) {
          $data['user_photo'] = $user->get('photo');
        }
        else{
          $data['user_photo'] = 'http://files.parsetfss.com/a82729a1-e6e9-43f0-acae-a060f3153c6d/tfss-3d88ecd0-d988-4ab3-a297-cf44a2682864-perfil3.jpg';
        }
      }

      array_push($this->_comments, $data);
    }
    return $this->_comments;
  }

  public function getProducts() {
    $query = new ParseQuery("Productos");
    $query->equalTo("animal_Id", $this);
    $products = $query->find();
    $categories = [];
    foreach($products as $p) {
      if (!array_key_exists($p->get('category_en'), $categories)){
        $categories[$p->get('category_en')] = [];
      }
      $product = [
        "name" => $p->get("nombre_en"),
        "photo" => $p->get("photo")->getURL(),
        "description" => $p->get("description_en"),
        "amount" => $p->get("precio2")
      ];
      array_push($categories[$p->get('category_en')], $product);
    }

    return $categories;

  }

  public static function getAllAnimals() {
    $query = new ParseQuery("AnimalV2");
    $animals = $query->find();
    return $animals;
  }

  public static function getVideoGallery() {
    $query = new ParseQuery("Video");
    $videos = $query->find();
    return $videos;
  }

}
