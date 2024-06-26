<?php
/* update.uss.php => UPDATE USER SOCIAL SET PHP */
require_once ('../../database/connect.db.php');

/* ERROR MESSAGES */
define('EMPTY_REQUEST','Please provide the required information in the form before proceeding.');

/* SUCCESS MESSAGES */
define('UPDATE_SUCCESS','Changes saved successfully.');

session_start();

try {
  /* CHECK REQUEST METHOD */
  if($_SERVER["REQUEST_METHOD"] !== "POST") EXIT_WITH_JSON(BAD_RESPONSE, INVALID_METHOD);

  /* RETRIEVE USER ID FROM SESSION */
  $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

  /* HANDLE MISSING USER ID */
  if(!$user_id) EXIT_WITH_JSON(BAD_RESPONSE, USER_NOT_FOUND, LOGIN_PAGE);

  /* RETRIEVE USER SOCIAL SET FROM POST PARAMETERS */
  $user_social_set = [
    'github' => isset($_POST['github']) ? trim($_POST['github']) : null,
    'instagram' => isset($_POST['instagram']) ? trim($_POST['instagram']) : null,
    'twitter' => isset($_POST['twitter']) ? trim($_POST['twitter']) : null,
    'reddit' => isset($_POST['reddit']) ? trim($_POST['reddit']) : null
  ];

  /* REMOVE NULL VALUES FROM USER SOCIAL SET */
  $user_social_set = array_filter($user_social_set, function($value){
    return $value != null;
  });

  /* HANDLE EMPTY USER SOCIAL SET */
  if(empty($user_social_set)) EXIT_WITH_JSON(BAD_RESPONSE, EMPTY_REQUEST);

  /* CONSTRUCT UPDATE QUERY */
  $data_to_set = implode(', ', array_map(function($key) {
    return "$key = :$key";
  }, array_keys($user_social_set)));

  $sql = "UPDATE ".SOCIAL_SET_TABLE." SET $data_to_set WHERE user_id = :user_id";

  $stmt = $pdo->prepare($sql);

  $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);

  /* BIND USER DATA TO PARAMETERS */
  foreach ($user_social_set as $key => $value) { 
    $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
  }

  /* EXECUTE UPDATE QUERY */
  $stmt->execute();

  /* UPDATE VALUES IN SESSION VARIABLE */
  foreach ($user_social_set as $key => $value) {
    $_SESSION[$key] = $value;
  }

  EXIT_WITH_JSON(GOOD_RESPONSE, UPDATE_SUCCESS,  PROFILE_PAGE);
                
} catch (PDOException $error) {
  /* HANDLE EXCEPTIONS */
  ExceptionHandler($error);
}