<?php
 namespace Bondzu;

 require 'vendor/autoload.php';

 use Parse\ParseClient;
 use Parse\ParseObject;
 use Parse\ParseUser;
 use Parse\ParseQuery;


class Video extends ParseObject{

  public static $parseClassName = "Video";

  private $_video;


  public function getYoutubeIds() {
    $this->_video->get("youtube_ids");
  }
  public function getTitles() {
    $this->_video->get("titles");
  }
  public function getDescriptions() {
    $this->_video->get("descriptions");
  }
}
