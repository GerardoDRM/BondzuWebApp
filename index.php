<?php
require 'vendor/autoload.php';
require_once 'DBHandler/User.php';
require_once 'DBHandler/Animal.php';
require_once 'DBHandler/Camera.php';

use Parse\ParseClient;
use Parse\ParseObject;
use Parse\ParseUser;
use Parse\ParseQuery;
use Bondzu\User;
use Bondzu\Animal;
use Bondzu\Camera;

$app_id = "7aGqZRDKBITfaIRAXq2oKoBkuWkhNqJZJWmf318I";
$rest_key = "NuedOs7niEPN9TG4vaelbaSqZO3DBAqiYd73Tnkk";
$master_key  = "fF5zsMkXpw3eIcmg4ggwh6HlynYnNpYmZeJyl5Cw";
$stripe = array(
  "secret_key"      => "sk_test_hhoZoNgQXiwD6VQFYyUbZGVg",
  "publishable_key" => "pk_test_5A3XM2TUHd6pob50dw7jhxA0"
);

if(!isset($_SESSION)){
    session_start();
}
// Create Classes
ParseClient::initialize( $app_id, $rest_key, $master_key );
Animal::registerSubclass();
Camera::registerSubclass();

// Stripe Initialization
\Stripe\Stripe::setApiKey($stripe['secret_key']);


$app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Twig()
));

$view = $app->view();
$view->parserOptions = array(
    'debug' => true
);

$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

$view->twigExtensions = array(
    'Twig_Extensions_Slim',
);


$app->get('/test', function () use ($app) {
  $cards = User::getAllCards(ParseUser::getCurrentUser());
  var_dump($cards);
})->name('test');


$app->get('/', function () use ($app) {
  $app->render('about.twig');
})->name('home');

$app->get('/login', function () use ($app) {
  $fb = new Facebook\Facebook([
    'app_id' => '721077627906410',
    'app_secret' => '20bdb4b6922430ab7fd49fa9b3d849c7',
    'default_graph_version' => 'v2.4',
    ]);
  $loginUrl = User::getFacebookUrl($fb);
  $app->render('login.twig', array('loginUrl' => $loginUrl));
})->name('login');

$app->post('/login', function () use ($app) {
  $email = $app->request->post("email");
  $password = $app->request->post("password");
  $res = User::login($email,$password);
  if($res == 200) {
    // Hooray! Let them use the app now.
    $app->flash("fail", "Welcome");
    $app->redirect("/catalogue");
  }
  else {
    $app->flash("fail", "Data field required");
    $app->redirect("/login");
  }

});

$authAdmin = function($app) {
    return function () use ($app) {
      $currentUser = ParseUser::getCurrentUser();
      if (!$currentUser) {
        $app->flash("fail", "No");
        $app->redirect('/login');
      }
    };
};

$app->get('/fb-callback', function () use ($app) {
  $fb = new Facebook\Facebook([
    'app_id' => '721077627906410',
    'app_secret' => '20bdb4b6922430ab7fd49fa9b3d849c7',
    'default_graph_version' => 'v2.4',
    ]);
  $res = User::loginWithFacebookGraph($fb);
  if($res['code'] == 200) {
    // Logged in!
    $_SESSION['facebook_access_token'] = (string) $res['token'];
    $fbData = User::retrieveFacebookProfile($fb, $res['token']);
    $user = ParseUser::logInWithFacebook($fbData['id'], $res['token']->getValue());
    if (($user->get("name") == null)) {
      User::customFacebookUser($user, $fbData);
    }
    $app->flash("fail", "OK");
    $app->redirect("/catalogue");
  }
  else {
    $app->flash("fail", $res['message']);
    $app->redirect("/signup");
  }

});


$app->get('/logout', function () use ($app) {
  ParseUser::logOut();
  $app->flash("fail", "Logout");
  $app->redirect("/login");
})->name('logout');

$app->get('/signup',function () use ($app) {
  $app->render('signup.twig');
})->name('signup');

$app->post('/signup',function () use ($app) {
  $name = $app->request->post("name");
  $lastname = $app->request->post("lastname");
  $email = $app->request->post("email");
  $password = $app->request->post("password");

  $res = User::singup($name, $lastname, $email, $password);
  if($res == 200) {
    // Hooray! Let them use the app now.
    $app->flash("sucess", "Welcome");
    $app->redirect("/catalogue");
  }
  else {
    $app->flash("error", "Data field required");
    $app->redirect("/signup");
  }
});

$app->get('/catalogue', $authAdmin($app),function () use ($app) {
  $animals = Animal::getAllAnimals();
  $videos = Animal::getVideoGallery();
  $currentUser = ParseUser::getCurrentUser();
  //User info
  $user = User::getBasicInfo($currentUser);
  // Add cards
  $user["cards"] = User::getAllCards($currentUser);
  // Animal Info
  $a = [];
  foreach($animals as $animal) {
    $about = $animal->get('about_en');
    $teaser = substr($about, 0, 150) . "...";
    $b = [
        "id" => $animal->getObjectId(),
        "profilePhoto" => $animal->get('profilePhoto')->getURL(),
        "name" => $animal->get('name_en'),
        "species" => $animal->get('species_en'),
        "about" => $teaser
      ];
    array_push($a, $b);
  }
  // Video info
  $v = [];
  foreach($videos as $video) {
    $data = [
        "id" => $video->get('youtube_ids')[0],
        "title" => $video->get('titles_en')[0],
        "description" => $video->get('descriptions_en')[0]
      ];
    array_push($v, $data);
  }
  $app->render('catalogue.twig', array('animals' => $a, 'videos' => $v, 'user' => $user));
})->name('catalogue');


// Getters
$app->get('/animal/:id', $authAdmin($app),function ($id) use ($app) {
  $currentUser = ParseUser::getCurrentUser();
  //User info
  $user = User::getBasicInfo($currentUser);
  // Add cards
  $user["cards"] = User::getAllCards($currentUser);
  $animal = new Animal($id);

  $a = [
    "name" => $animal->getName(),
    "specie" => $animal->getSpecie(),
    "about"=> $animal->getAbout(),
    "characteristics" => $animal->getCharacteristics(),
    "profilePhoto" => $animal->getProfilePhoto(),
    "adopters" => $animal->getAdopters(),
    "cameras" => $animal->getCameras(),
    "events" => $animal->getEvents(),
    "gallery" => $animal->getGallery(),
    "comments" => $animal->getComments(),
    "products" => $animal->getProducts()
  ];
  $app->render('animal.twig', array('animal' => $a, 'user' => $user));
})->name('animal');

$app->run();
