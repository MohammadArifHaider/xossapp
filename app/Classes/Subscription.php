<?php

namespace App\Classes;

use App\Classes\SubscriptionException;
use App\Classes\Core;

class Subscription extends core{
    var $server;
    var $applicationId;
    var $password;
    
    
       public function getStatus($address){
		 
		 $this->server = 'https://developer.bdapps.com/subscription/getstatus';

        $arrayField = array("applicationId" => $this->applicationId,
            "password" => $this->password,
            "subscriberId" => $address
            );

        $jsonObjectFields = json_encode($arrayField);
        return $this->handleResponse(json_decode($this->sendRequest($jsonObjectFields,$this->server)));
    }

			

    public function __construct($server,$applicationId,$password){
        $this->server = $server;
        $this->applicationId = $applicationId;
        $this->password = $password;
    }
	

    public function subscribe($subscriberId){
        $arrayField = array(
				        	"applicationId" => $this->applicationId, 
				            "password" => $this->password,
				            "version" => "1.0",
				            "subscriberId" => $subscriberId,
				            "action" => "1"
				        );
        $jsonObjectFields = json_encode($arrayField); 
        return $this->handleResponse(json_decode($this->sendRequest($jsonObjectFields,$this->server)));
    }
	
	public function unsubscribe($subscriberId){
        $arrayField = array(
				        	"applicationId" => $this->applicationId, 
				            "password" => $this->password,
				            "version" => "1.0",
				            "subscriberId" => $subscriberId,
				            "action" => "0"
				        );
        $jsonObjectFields = json_encode($arrayField); 
        return $this->handleResponse(json_decode($this->sendRequest($jsonObjectFields,$this->server)));
    }
	
	private function handleResponse($jsonResponse){
    
        if(empty($jsonResponse))
            throw new CassException('Invalid server URL', '500');
        
        $statusCode = $jsonResponse->statusCode;
        $statusDetail = $jsonResponse->statusDetail;
        
        if(!(empty($jsonResponse->subscriptionStatus)))
            return $jsonResponse->subscriptionStatus;
        
        if(strcmp($statusCode, 'S1000')==0)
            return 'ok';
        else
            throw new CassException($statusDetail, $statusCode);
    }
}