<?php /** Created by PhpStorm. User: john Date: 8/29/2020 Time: 6:34 PM */?>
<h3>debugin</h3>

<?php

$_POST = [
    'orderCode'=>'SALES127',
];
$this->load->helper('string');
if(!empty($_POST)){
    $api_base_url           = $this->config->item('api_base_url');
    $api_auth_url           = $api_base_url.'service/auth';
    $api_checkout_url       = $api_base_url.'order';
    $api_operation_token    = $this->config->item('api_operation_token');
    $api_currency           = $this->config->item('api_currency');
    $api_auth_name          = $this->config->item('api_authentication_name');
    $api_auth_token         = $this->config->item('api_authentication_token');
    $order                  = str_replace('/','_',$_POST['orderCode']).''.random_string('alnum',3);
    $amount                 = "500";


    $api_auth_data = [
        'auth'=>[
            'name'=>$api_auth_name,
            'authentication_token'=>$api_auth_token,
            'order'=> $order
        ],
    ];
    $api_auth = curl_init();
    curl_setopt_array($api_auth, array(
        CURLOPT_URL => $api_auth_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION  =>  TRUE ,
        CURLOPT_POST=>1,
        CURLOPT_ENCODING => "",
//        CURLOPT_MAXREDIRS => 10,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($api_auth_data),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));
    $apiAuthResponse  = curl_exec($api_auth);
    $apiAuthError     = curl_error($api_auth);
    curl_close($api_auth);

    //IF FIRST STEP 1 IS OK (INIT PAYMENT IS OK)
    $API_AUTH = json_decode($apiAuthResponse);
    if(!empty($API_AUTH) && !empty($API_AUTH->auth_token)){
        $token = $API_AUTH->auth_token;
        $data = [
            "currency"            =>$api_currency,
            "name"                =>$api_auth_name,
            "operation_token"     =>$api_operation_token,
            "order"               =>$order,
            "transaction_amount"  =>$amount,
            "jwt"                 =>$token
        ];

        $payload = json_encode($data);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://crossroadtest.net:6968/order",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Cookie: _session_id=a19af450486a2c337489bc989fb16c6f",
                'Content-Length: ' . strlen($payload),
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;


    }

    else{

        $this->sma->send_json([
            'response_status'=>false,
            'stauts'=>'error',
            'message'=>'could not initiate the payment please try again...'
        ]);
    }

}

?>