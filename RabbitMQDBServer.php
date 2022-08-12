``` <?php

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$dbhost = 'localhost';
$dbuser = 'admin';
$dbpass = 'Admin123';
$dbname = 'TestDB';

// Constants Status codes
$SUCCESS = 200;          // Ok
$UNAUTH = 401;           // Unauthorized
$BADREQ = 400;           // Bad Request
$PAGENOTFOUND = 404; // Not Found
$USEREXIST = 201;        // User already exist
$DBERROR = 150;          // Database generic error

function connectToDatabase(){
                global $dbhost;
                global $dbuser;
                global $dbpass;
                global $dbname;
                $mysqli = new mysqli($dbhost,  $dbuser, $dbpass,  $dbname);

                if($mysqli->connect_errno ) {
                                printf("Connect failed: %s \n", $mysqli->connect_error);
                                exit();
                }
                printf("Connected successfully to database.!.\n");
                return $mysqli;
}

$dbconnection = connectToDatabase();

// Login function
function login_user($user,$pass){
                global $SUCCESS;
                global $UNAUTH;
                global $PAGENOTFOUND;
                // 1. Get user details from database.!
                global $dbconnection;

                $sql = "SELECT password FROM `userDetails` WHERE username='$user'";
                printf("query: %s \n",$sql);
                $result = $dbconnection->query($sql);
                if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                                printf("username: %s, db_password:%s, entered pass:%s \n",

              $user, $row["password"], $pass);
                                        // Check the credentials and gave the appropariate response
                                        if (password_verify($pass, $row["password"])){
                                                        printf("!!INFO!! User:%s is valid username: .!\n", $user);
                                                        $ret_status = $SUCCESS;
                                        }
                                        else{
                                                        $ret_status = $UNAUTH;
                                        }
                                }
                } else {
                                $ret_status = $PAGENOTFOUND;
                                printf("No record found for username:%s \n", $user);
                }
 mysqli_free_result($result);

                return $ret_status;
}

function login($user,$pass){
     //TODO validate user credentials

        printf(" !!INFO!! Requested: login: user:%s pass:%s \n",$user,$pass);
        global $SUCCESS;
        global $UNAUTH;
        global $PAGENOTFOUND;

        $ret_status = login_user($user,$pass);
        if ($ret_status == $PAGENOTFOUND){
                printf("No record found for user:%s \n", $user);
        }
        else if($ret_status == $UNAUTH){
                printf("Un-authorised user:%s \n", $user);
        }
        else if($ret_status == $SUCCESS){
                printf("Authorised user:%s \n", $user);
        }
        else
        {
                printf("Invalid response :%s \n", $ret_status);
        }

//      $userfile = file_get_contents("usernames.txt");
//      $passfile = file_get_contents("passwords.txt");

        //return true;
       return $ret_status;
}


function register($user,$pass){
        //write user to usernames.txt, write passwords to passwords.txt
//      $userfile = fopen("usernames.txt","w");
//      $passfile = fopen("passwords.txt","w");
        //check if username exists already
        //if(strpos(file_get_contents($userfile), $user) !== false) return false;
        //check if username or password has sussy characters
        if (preg_match('/[^A-Za-z0-9.#\\-$]/', $user))
        {
                return array("return_code"=>'205', "message"=>"Bad characters used!");
        }
        if (preg_match('/[^A-Za-z0-9.#\\-$]/', $pass))
        {
                return array("return_code"=>'205', "message"=>"Bad characters used!");

        }
        //if everything is OK hash password and store hash and username in respective files
        $phash = password_hash($pass, PASSWORD_BCRYPT);
        //////////////////////////////////////////////
        //    Database related workspace __START__  //
        //////////////////////////////////////////////
        printf(" !!INFO!! Requested: register: user:%s pass:%s phash:%s \n",$user,$pass,$phash);
        global $SUCCESS;
        global $PAGENOTFOUND;
        global $USEREXIST;
        // Add user details to database.!
		// 1. Check if username exist in database or not.!
        $ret_status = login_user($user,$pass);
        // Putting email to blank.!
        $email = "";
        if ($ret_status == $PAGENOTFOUND){
                        // 2. Add user details to database.!
                        global $dbconnection;
                        $sql = "INSERT INTO `userDetails` (`username`, `password`)  VALUES ('$user', '$phash')";
                        printf("query: %s \n",$sql);
                        $result = $dbconnection->query($sql);
                        if ($result == TRUE) {
                                        printf("!!INFO!! User:%s | email:%s details has been updated to database \n", $user, $email);
                                        $ret_status = $SUCCESS;
                        } else {
                                        $ret_status = $DBERROR;
                                        printf("!!ERROR!! Not able to insert user details into database. query:%s \n", $sql);
                        }
                        //mysqli_free_result($result);
        }
        else{
                        $ret_status = $USEREXIST;
        }
        //////////////////////////////////////////////
        //    Database related workspace __END__    //
        //////////////////////////////////////////////

         //fwrite($userfile, $user + \n);
//      fwrite($passfile, $phash);
//      fclose($userfile);
//      fclose($passfile);
        //return true;
        return $ret_status;
}


function request_processor($req){
        echo "Received Request".PHP_EOL;
        echo "<pre>" . var_dump($req) . "</pre>";
        if(!isset($req['type'])){
                return "Error: unsupported message type";
        }
        //Handle message type
        $type = $req['type'];
        switch($type){
                case "login":
                        return login($req['user'], $req['pass']);
                case "validate_session":
                        return validate($req['session_id']);
                case "echo":
                        return array("return_code"=>'0', "message"=>"Echo: " .$req["message"]);
                case "register":
                        return register($req['user'], $req['pass']);
               // case "login":
                        case "multiping":
                          $client = new RabbitMQClient('testMultiPing.ini', 'testServer');
                          $msg = array("message"=>"test next Server", "type"=>"multiping");
                          $message = $client->send_request($msg);
                           return array("return code"=>'01', "message"=>$message);
        }
        return array("return_code" => '0',
                "message" => "Server received request and processed it");
}

$server = new rabbitMQServer("testRabbitMQ.ini", "sampleServer");

echo "Rabbit MQ Server Start" . PHP_EOL;
$server->process_requests('request_processor');
echo "Rabbit MQ Server Stop" . PHP_EOL;
exit();
?>

                                                                                                                                                                                                                                                                                                    27,9          Top
