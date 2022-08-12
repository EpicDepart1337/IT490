<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client = new RabbitMQClient('testRabbitMQ.ini', 'testServer');
if(isset($argv[1])){
        if(isset($argv[3])){
                if(strcmp($argv[3], 'register')==0){
                        $msg = array("user"=>$argv[1], "pass"=>$argv[2], "type"=>"register");
}
                                elseif(strcmp($argv[3], 'login')==0){
                                         $msg = array("user"=>$argv[1], "pass"=>$argv[2], "type"=>"login");

                                }
}
else
{
         if(strcmp($argv[1], 'multiping')==0)
        {
                printf("Testing multiping \n");

                $msg = array("message"=>'test other server', "type"=>"multiping");
        }



        $msg = $argv[1];
}
}
else{
        $msg = array("message"=>"test message", "type"=>"echo");
}

$response = $client->send_request($msg);

echo "client received response: " . PHP_EOL;
print_r($response);
echo "\n\n";

if(isset($argv[0]))
echo $argv[0] . " END".PHP_EOL;
