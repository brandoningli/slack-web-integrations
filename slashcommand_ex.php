<?php
  /*
    for the "/ordersadmin" command.

    `/ordersadmin` handles the admin functions of `/orders`.\n
    `help` : Returns a message about these functions and how to use them.\n
    `meal [meal name]` : Changes the meal name to the given parameter. Will return error if not `breakfast`, `lunch`, `dinner`, or `dessert`.\n
    -`place list` : Lists the places in the database with their corresponding numbers for adding with the `place update` command.\n
    -`place selected` : Lists the selected places for people to choose from.\n
    -`place add [place number] [...]`` : Selects the corresponding place(s) in the database. Will only accept integer values. These can be listed with `place list`.\n
    -`place reset` : De-selects all places in the database.\n
    -`reset` : Resets the orders currently in the orders database.\n
    -`notify` : sends a message to the #general slack channel to submit orders.\n

    All messages from this script are of the ephemeral type. This is the default.
    Some elements of this file have been redacted for security reasons.

  */

require {REDACTED}.php;

/* Format of a basic Text message */
function format_msg($msg){
  return "{ \"text\": \"${msg}\"}";
}

/* Returns Help Text
   This is reccommended for every slash command.
*/
function help(){
  return "`/ordersadmin` handles the admin functions of `/orders`.\n
  -`[no parameters]` or `help` : Returns a message about these functions and how to use them.\n
  -`meal [meal name]` : Changes the meal name to the given parameter. Will return error if not `breakfast`, `lunch`, `dinner`, or `dessert`.\n
  -`place list` : Lists the places in the database with their corresponding numbers for adding with the `place update` command.\n
  -`place selected` : Lists the selected places for people to choose from.\n
  -`place update [place number] [...]`` : Selects the corresponding place(s) in the database. Will only accept integer values. These can be listed with `place list`.\n
  -`place reset` : De-selects all places in the database.\n
  -`reset` : Resets the orders currently in the orders database.\n
  -`notify` : sends a message to the #general slack channel to submit orders.\n";
}

/* Returns a string listing the names of all available restaurants and their index numbers alphabetically. */
function place_list(){
  try{
    //get resturants;
  	mysql_connect(REDACTED);
  	mysql_select_db(REDACTED);
  	$data = mysql_query("SELECT * FROM places ORDER BY `place` ASC ;");
    $ctr = 0;
    $returner = "The restaurants to pick from are: ";
    while($item = mysql_fetch_array($data)){
  		$returner .= "[" . $ctr . "]";
      $returner .= $item['place'];
      $returner .= ", ";
      $ctr += 1;
  	}
    mysql_close();
    return substr($returner, 0, strlen($returner)-2);
  }catch(Exception $e){
    return $e->getMessage();
  }
}

/* Sets the selected restaurants (by index) to selected
   Takes array of integers.
*/
function select_restaurants($arr){
  try{
    mysql_connect(REDACTED);
  	mysql_select_db(REDACTED);
  	$query = mysql_query("SELECT * FROM places ORDER BY `place` ASC ;");
    $results = array();
    while($line = mysql_fetch_array($query, MYSQL_ASSOC)){
      array_push($results, $line);
    }
    mysql_close();

    $size = sizeof($results, 0);
    $places = array();

    foreach($arr as $x){
      if($x >= 0 && $x < $size){
        array_push($places, $results[$x]['place']);
      }
    }


    if(sizeof($places) == 0){
      throw new Exception("place update: No valid places given.");
    }

    return format_msg("place update: " . update_places($places));

  }catch(Exception $e){
    return format_msg($e->getMessage());
  }
}

/* Gets all restaurants that are selected */
function selected_restaurants(){
  try{
    mysql_connect(REDACTED);
    mysql_select_db(REDACTED);
    $data = mysql_query("SELECT * FROM places WHERE `selected` = 1 ORDER BY `place` ASC ;");
    $rtn = "The following places are selected:\n";
    while($item = mysql_fetch_array($data)){
      $rtn .= "•".$item['place']."\n";
    }
    mysql_close();
    return $rtn;
  }catch(Exception $e){return $e->getMessage;}
}

function main(){
  //Set header to JSON for return
  header('Content-Type: application/json');

  $_TOKEN = REDACTED; //Token provided by Slack for Verification
  if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['token'] == $_TOKEN){ //Slack will send its data via POST. Verify token before continuing.
    try{

      /* HELP */
      if(!$_POST['text']){ //help is the default
        return format_msg(help());
      }

      $parameters = explode(" ", $_POST['text']); //explodes the `text` payload into a string array, elements space-separated

      /* MEAL */
      if($parameters[0] == "meal"){
        if(sizeof($parameters) < 2){ //Not enough parameters for `meal` call
          throw new Exception("meal: not enough arguments. Include either `breakfast`, `lunch`, `dinner`, or `dessert`.");
        }
        else {
          $rtn = change_meal($parameters[1]);
          return format_msg("meal: " . $rtn);}
      }

      /* PLACE */
      else if($parameters[0] === "place"){
        if(sizeof($parameters) < 2){ //Not enough parameters for `place` call
          throw new Exception("place: not enough arguments");
        }
        else if ($parameters[1] === "list"){ // `place list`
          return format_msg(place_list());
        }
        else if ($parameters[1] === "selected"){ // `place selected`
          return format_msg(selected_restaurants());
        }
        else if ($parameters[1] === "update"){ // `place update`
            if(sizeof($parameters) < 3){
              throw new Exception("place update: not enough arguments");
            }
            $args = array();
            for($i = 2; $i < sizeof($parameters); $i += 1){
              array_push($args, $parameters[$i]);
            }
            return select_restaurants($args);
        }
        else if ($parameters[1] === "reset"){ // `place reset`
            return format_msg("place reset: " . update_places(array("HOOPLA")));
        }
        else{
          throw new Exception("place: no valid arguments");
        }
      }

      /* RESET */
      else if($parameters[0] === "reset"){
        return format_msg("reset: " . reset_orders());
      }

      /* NOTIFY */
      else if($parameters[0] === "notify"){
        return format_msg("notify: " . notify());
      }

      /* HELP */
      else if($parameters[0] === "help"){
        return format_msg(help());
      }

      /* INVALID PARAMETER */
      else{
        return format_msg("Invalid Parameter. Try one of these.\n" . help());
      }

    }catch(Exception $e){
      return format_msg($e->getMessage());
    }
  }
  else{
    echo "{ \"text\": \"INVALID REQUEST\"}";
  }
}


echo main();

 ?>
