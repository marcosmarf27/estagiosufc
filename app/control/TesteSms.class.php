<?php

use Twilio\Rest\Client;
class TesteSms extends TPage
{
    public function __construct()
    {
        parent::__construct();
        $sid = "AC761fca70e34181dd52ade809180efbc9";
        $token = "ca2093b87b5671b382dbfbcac6ae0ed7";
        $twilio = new Client($sid, $token);

        $message = $twilio->messages
                  ->create("+5588992798233", // to
                           [
                               "body" => "caramba marcos vc é foda!?",
                               "from" => "+18324303112"
                           ]
                  );

print_r($message->sid);

       







        
        
    }
}