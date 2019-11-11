<?php
/*
Below is an integration flow on how to use Cashfree's cashgram feature.
Please go through the payout docs here: https://docs.cashfree.com/docs/payout/guide/

The following script contains the following functionalities :
    1.getToken() -> to get auth token to be used in all following calls.
    2.createCashgram() -> to create cashgram.
    3.cashgramGetStatus() -> to get the cashgrams status


All the data used by the script can be found below in config arrays. This includes the clientId, clientSecret, cashgram section.
You can change keep changing the values in the config file and running the script.
Please enter your clientId and clientSecret, along with the appropriate enviornment and bank details
*/

#default parameters
$clientId = '';
$clientSecret = '';
$env = 'test';

#config objs
$baseUrls = array(
    'prod' => 'https://payout-api.cashfree.com',
    'test' => 'https://payout-gamma.cashfree.com',
);
$urls = array(
    'auth' => '/payout/v1/authorize',
    'createCashgram' => '/payout/v1/createCashgram',
    'getCashgramStatus' => '/payout/v1/getCashgramStatus',
);
$cashgramDetails = array(
    'cashgramId' => 'cf10',
    'amount' => '1.00',
    'name' => 'sameera',
    'email' => 'sameera@cashfree.com',
    'phone' => '9000000001',
    'linkExpiry' => '2019/11/13',
    'remarks' => 'sample cashgram',
    'notifyCustomer' => 1
);
$header = array(
    'X-Client-Id: '.$clientId,
    'X-Client-Secret: '.$clientSecret, 
    'Content-Type: application/json',
);


$baseurl = $baseUrls[$env];


function create_header($token){
    global $header;
    $headers = $header;
    if(!is_null($token)){
        array_push($headers, 'Authorization: Bearer '.$token);
    }
    return $headers;
}

function post_helper($action, $data, $token){
    global $baseurl, $urls;
    $finalUrl = $baseurl.$urls[$action];
    $headers = create_header($token);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $finalUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);
    if(!is_null($data)) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); 
    
    $r = curl_exec($ch);
    
    if(curl_errno($ch)){
        print('error in posting');
        print(curl_error($ch));
        die();
    }
    curl_close($ch);
    $rObj = json_decode($r, true);    
    if($rObj['status'] != 'SUCCESS' || $rObj['subCode'] != '200') throw new Exception('incorrect response: '.$rObj['message']);
    return $rObj;
}

function get_helper($finalUrl, $token){
    $headers = create_header($token);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $finalUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);
    
    $r = curl_exec($ch);
    
    if(curl_errno($ch)){
        print('error in posting');
        print(curl_error($ch));
        die();
    }
    curl_close($ch);

    $rObj = json_decode($r, true);    
    if($rObj['status'] != 'SUCCESS' || $rObj['subCode'] != '200') throw new Exception('incorrect response: '.$rObj['message']);
    return $rObj;
}

#get auth token
function getToken(){
    try{
       $response = post_helper('auth', null, null);
       return $response['data']['token'];
    }
    catch(Exception $ex){
        error_log('error in getting token');
        error_log($ex->getMessage());
        die();
    }

}

#create cashgram
function createCashgram($token){
    try{
        global $cashgramDetails;
        $response = post_helper('createCashgram', $cashgramDetails, $token);
        error_log('cashgram created');
    }
    catch(Exception $ex){
        error_log('error in creating cashgram');
        error_log($ex->getMessage());
        die();
    }
}

#get cashgram status
function cashgramGetStatus($token){
    try{
        global $cashgramDetails, $baseurl, $urls;
        $cashgramId = $cashgramDetails['cashgramId'];
        $query_string = "?cashgramId=".$cashgramId;
        $finalUrl = $baseurl.$urls['getCashgramStatus'].$query_string;
        $response = get_helper($finalUrl, $token);
        error_log(json_encode($response));
    }
    catch(Exception $ex){
        error_log('error in getting cashgram status');
        error_log($ex->getMessage());
        die(); 
    }
}

/*
The flow executed below is:
1. fetching the auth token
2. creating a cashgram
3. getting the status of the cashgram
*/


#main execution
$token = getToken();
createCashgram($token);
cashgramGetStatus($token);
?>