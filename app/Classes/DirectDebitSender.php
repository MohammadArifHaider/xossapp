<?php

namespace App\Classes;

use App\Classes\CassException;
use App\Classes\Core;


class DirectDebitSender extends core{
    var $server = "https://developer.bdapps.com/caas/direct/debit";
    var $applicationId = "APP_017166";
    var $password = "85d518f39b54d61a2f49ce1160e936f1";
            

    // public function __construct($server,$applicationId,$password){
    //     $this->server = $server;
    //     $this->applicationId = $applicationId;
    //     $this->password = $password;
    // }

    /*
        Get parameters form the application
        check one or more addresses
        Send them to cassMany
    **/
    public function cass( $externalTrxId, $subscriberId, $amount){
       
        if (is_array($subscriberId)) {
            return $this->cassMany( $externalTrxId, $subscriberId,  $amount);
        } else if (is_string($subscriberId) && trim($subscriberId) != "") {
            return $this->cassMany( $externalTrxId, $subscriberId,  $amount);
        } else {
            throw new Exception("Address should be a string or a array of strings");
        }
    }
    

    private function cassMany($externalTrxId, $subscriberId, $amount){
        $arrayField = array(
                            "applicationId" => $this->applicationId, 
                            "password" => $this->password,
                            "externalTrxId" => $externalTrxId,
                            "subscriberId" => $subscriberId,
                            "amount" => $amount
                        );
        $jsonObjectFields = json_encode($arrayField); 
        return $this->handleResponse(json_decode($this->sendRequest($jsonObjectFields,$this->server)));
    }
    

    private function handleResponse($jsonResponse){
    
        if(empty($jsonResponse))
            throw new CassException('Invalid server URL', '500');
        
        $statusCode = $jsonResponse->statusCode;
        $statusDetail = $jsonResponse->statusDetail;
        
        // if(strcmp($statusCode, 'S1000')==0)
            return json_encode($jsonResponse);
    //     else
    //   // return json_encode($jsonResponse);
    //         //throw new CassException($statusDetail, $statusCode);
    }

}
