<?php
 namespace Bondzu;

 require 'vendor/autoload.php';

 use Parse\ParseClient;
 use Parse\ParseObject;
 use Parse\ParseUser;
 use Parse\ParseQuery;
 use Parse\ParseSessionStorage;
 use Parse\ParseException;

session_start();

class User {

  public static function singup($name, $lastname, $email, $password) {

    if(!empty($name) && !empty($lastname) && !empty($email) && !empty($password)) {
      $cleanName = filter_var($name, FILTER_SANITIZE_STRING);
      $cleanLastName = filter_var($lastname, FILTER_SANITIZE_STRING);
      $cleanEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
      $cleanPassword = filter_var($password, FILTER_SANITIZE_STRING);

      $user = new ParseUser();
      $user->set("username", $cleanEmail);
      $user->set("password", $cleanPassword);
      $user->set("email", $cleanEmail);

      $customer = self::createStripeId($cleanEmail);
      // other fields can be set just like with ParseObject
      $user->set("stripeId", $customer['id']);
      $user->set("name", $cleanName);
      $user->set("lastname", $cleanLastName);
      try {
        $user->signUp();
        return 200;
      } catch (ParseException $ex) {
        return 500;
      }
    }
    else {
      return 500;
    }
  }// end signup


  public function createStripeId($email=null, $name=null) {
    if($email != null) {
      $customer = \Stripe\Customer::create(array(
        "description" => "Customer for ". $email,
        "email" => $email
      ));
    }
    else {
      $customer = \Stripe\Customer::create(array(
        "description" => "Customer for ". $name
      ));
    }

    return $customer;
  }

  public static function login($email, $password) {
    if(!empty($email) && !empty($password)) {
      $cleanEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
      $cleanPassword = filter_var($password, FILTER_SANITIZE_STRING);
      try {
        // Hooray! Let them use the app now.
        ParseUser::logIn($cleanEmail,$cleanPassword);
        return 200;
      } catch (ParseException $ex) {
        return 500;
      }
    }
    else {
      return 500;
    }
  }// end login

  public static function customFacebookUser($user, $fb) {
    $nameArray = explode(" ", $fb["name"]);
    $firstname = $nameArray[0];
    $lastname = "";
    $customer = null;
    for($x=1; $x<count($nameArray); $x++) {
      $lastname = $lastname.$nameArray[$x]." ";
    }
    if(isset($fb["email"])) {
      $customer = self::createStripeId($email=$fb["email"]);
    }
    else {
      $customer = self::createStripeId($name=$fb["name"]);
    }

    $user->set("stripeId", $customer["id"]);
    $user->set("username",$fb["email"]);
    $user->set("name",$firstname);
    $user->set("lastname", $lastname);
    $user->set("email",$fb["email"]);
    try {
      $user->save();
      } catch (ParseException $ex) {
        // Show the error message somewhere and let the user try again.
        return $ex->getMessage();
      }
      return "Ok";
  }

  public static function loginWithFacebookGraph($fb) {

      $helper = $fb->getRedirectLoginHelper();
      try {
        $accessToken = $helper->getAccessToken();
      } catch(Facebook\Exceptions\FacebookResponseException $e) {
        // When Graph returns an error
        $out['code'] = 500;
        $out['message'] = 'Graph returned an error: ' . $e->getMessage();
        return $out;
      } catch(Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
        $out['code'] = 500;
        $out['message'] = 'Facebook SDK returned an error: ' . $e->getMessage();
        return $out;
      }

      if (isset($accessToken)) {
        $out['code'] = 200;
        $out['message'] = 'Welcome';
        $out['token'] = $accessToken;
        return $out;
      }
  }// end login

  public static function getFacebookUrl($fb) {
    $helper = $fb->getRedirectLoginHelper();
    $permissions = ['email']; // Optional permissions
    $loginUrl = $helper->getLoginUrl('http://localhost:3000/fb-callback', $permissions);

    return $loginUrl;
  }

  public static function retrieveFacebookProfile($fb, $token) {
    try {
      // Returns a `Facebook\FacebookResponse` object
      $response = $fb->get('/me?fields=id,name,email', $token);
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }
    $user = $response->getGraphUser();
    return $user;
  }


  public static function getBasicInfo($user) {
    $relationAnimal = $user->getRelation("adoptersRelation");
    $animals = $relationAnimal->getQuery()->find();
    $relationTransactions = $user->getRelation("Compras");
    $transactions = $relationTransactions->getQuery()->includeKey("productoid")->find();
    $a = [];
    $t = [];

    foreach($animals as $animal) {
      $data = [
        "id" => $animal->getObjectId(),
        "name" => $animal->get('name_en'),
        "specie" => $animal->get('species_en'),
        "photo" => $animal->get('profilePhoto')->getURL()
      ];
      array_push($a, $data);
    }

    foreach($transactions as $transaction) {
      $product = $transaction->get('productoid');
      $data = [
        "description" => $transaction->get('descripcion'),
        "amount" => $transaction->get('precio'),
        "title" => $product->get('name_es'),
        "photo" => $product->get('photo')->getURL()
      ];
      array_push($t, $data);
    }

      $u = [
        "id" => $user->getObjectId(),
        "name" => $user->get('name') . $user->get('lastname'),
        "photo" => '',
        "animals" => $a,
        "transactions" => $t

      ];

      if($user->get('photoFile') != null) {
        $u["photo"] = $user->get('photoFile')->getURL();
      }
      else {
        $u['photo'] = 'http://files.parsetfss.com/a82729a1-e6e9-43f0-acae-a060f3153c6d/tfss-3d88ecd0-d988-4ab3-a297-cf44a2682864-perfil3.jpg';
      }

    return $u;
  }

  public static function addCard($user, $card) {
    $id = $user->get("stripeId");
    $cu = \Stripe\Customer::retrieve($id);
    $cu->sources->create(array("source" => $card));
  }

  public static function removeCard($user, $cardId) {
    $id = $user->get("stripeId");
    $customer = \Stripe\Customer::retrieve($id);
    $customer->sources->retrieve($cardId)->delete();
  }

  public static function getAllCards($user) {
    $id = $user->get("stripeId");
    $cards =\Stripe\Customer::retrieve($id)->sources->all(array(
    'object' => 'card'));
    $cards_list = [];
    foreach($cards->data as $c) {
      $data= [
        "num" => $c->last4,
        "card_id" => $c->id,
        "brand" => $c->brand
      ];
      array_push($cards_list, $data);
    }
    return $cards_list;

  }


}
