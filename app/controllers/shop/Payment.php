<?php

require APPPATH . '/libraries/CinetPay.php';
use CinetPay\CinetPay;

class Payment extends MY_Shop_Controller
{
    public function __construct()
    {
        parent::__construct();


    }
    //=====================================
    //==========CINETPAY PAYMENT SYSTEM ===========
    //=====================================
    public function make_payment($data = '' , $amount = ''){
        $this->make_payment_cnp1($data , $amount);
    }
    public function make_payment_cnp1($data = '' , $amount = ''){
        $datas = explode('--',$data);
        $sale_id = $datas[0];
        $reference_no = $datas[1];
        if(!empty($sale_id)){$_POST['sale_id'] = $sale_id; }
        if(!empty($amount)){ $_POST['amount'] = $amount; }
        $this->load->helper('string');

        $apiKey = $this->config->item('cn_api_key'); // Remplacez ce champs par votre APIKEY
        $site_id = $this->config->item('cn_site_id'); // Remplacez ce champs par votre SiteID
        $id_transaction = CinetPay::generateTransId(); // Identifiant du Paiement
        $description_du_paiement = sprintf('Mon produit de ref %s', $reference_no); // Description du Payment
        $date_transaction = date("Y-m-d H:i:s"); // Date Paiement dans votre système
        $montant_a_payer = (int)$_POST['amount']; // Montant à Payer : minimun est de 100 francs sur CinetPay
        $identifiant_du_payeur = $data; // Mettez ici une information qui vous permettra d'identifier de façon unique le payeur
        $formName = "goCinetPay"; // nom du formulaire CinetPay
        $notify_url = base_url('payment/notification'); // Lien de notification CallBack CinetPay (IPN Link)
        $return_url = base_url('payment/return_page/'.$sale_id); // Lien de retour CallBack CinetPay
        $cancel_url = base_url('payment/cancel_page/'.$sale_id); // Lien d'annulation CinetPay

        // Configuration du bouton
        $btnType = 2;//1-5xwxxw
        $btnSize = 'large'; // 'small' pour reduire la taille du bouton, 'large' pour une taille moyenne ou 'larger' pour  une taille plus grande

        // Paramétrage du panier CinetPay et affichage du formulaire
        $cp = new CinetPay($site_id, $apiKey);
        try {
            $cp->setTransId($id_transaction)
                ->setDesignation($description_du_paiement)
                ->setTransDate($date_transaction)
                ->setAmount($montant_a_payer)
                ->setDebug(false)// Valorisé à true, si vous voulez activer le mode debug sur cinetpay afin d'afficher toutes les variables envoyées chez CinetPay
                ->setCustom($identifiant_du_payeur)// optional
                ->setNotifyUrl($notify_url)// optional
                ->setReturnUrl($return_url)// optional
                ->setCancelUrl($cancel_url)// optional
                ->displayPayButton($formName, $btnType, $btnSize);

            //DELETE FAILED TRANSACTIONS RELATED TO THE SAME SALE
            $this->db->where(['sale_id'=>$sale_id , 'status_id !='=>'1'])->delete('transactions');
            $this->db->where(['sale_id'=>$sale_id])->delete('transaction_session');

            //SAVE TRANSACTION
            $this->db->insert('transactions' ,
                [
                    'sale_id'=>$sale_id ,
                    'token'=>$id_transaction,
                    'order_id'=>$id_transaction ,
                    'reference_no'=>$reference_no,
                    'transaction_amount'=>$montant_a_payer
                ]);

            //SAVE TRANSACTION SESSION
            $transaction_session_data = [
                'sale_id' => $sale_id,
                'order_id'=> $id_transaction,
                'reference_no'=> $reference_no,
                'session_data'=>json_encode($_SESSION),
            ];
            $this->db->insert('transaction_session',$transaction_session_data);

        }
        catch (\Exception $e) {
            print $e->getMessage();
        }

    }
    public function make_payment_cnp2($data = '' , $amount = ''){
        $datas = explode('--',$data);
        $sale_id = $datas[0];
        $reference_no = $datas[1];
        if(!empty($sale_id)){ $_POST['sale_id'] = $sale_id; }
        if(!empty($amount)){ $_POST['amount'] = $amount; }
        $this->load->helper('string');

        $apiKey = $this->config->item('cn_api_key'); // Remplacez ce champs par votre APIKEY
        $site_id = $this->config->item('cn_site_id'); // Remplacez ce champs par votre SiteID
        $id_transaction = CinetPay::generateTransId(); // Identifiant du Paiement
        $description_du_paiement = sprintf('Mon produit de ref %s', $id_transaction); // Description du Payment
        $date_transaction = date("Y-m-d H:i:s"); // Date Paiement dans votre système
        $montant_a_payer = (int)$_POST['amount']; // Montant à Payer : minimun est de 100 francs sur CinetPay
        $identifiant_du_payeur = $sale_id; // Mettez ici une information qui vous permettra d'identifier de façon unique le payeur
        $formName = "goCinetPay"; // nom du formulaire CinetPay
        $notify_url = $this->config->item('cn_notification_url'); // Lien de notification CallBack CinetPay (IPN Link)
        $return_url = ''; // Lien de retour CallBack CinetPay
        $cancel_url = ''; // Lien d'annulation CinetPay
        $signature_request = curl_init();

        $signature_data = [
            'cpm_amount' => $montant_a_payer,
            'cpm_currency' => 'CFA',
            'cpm_site_id' => $site_id,
            'cpm_trans_id' => $id_transaction,
            'cpm_trans_date' => $date_transaction,
            'cpm_payment_config' => 'SINGLE',
            'cpm_page_action' => 'PAYMENT',
            'cpm_version' => 'V1',
            'cpm_language' => 'fr',
            'cpm_designation' => $reference_no,
            'cpm_custom' => $site_id,
            'apikey' => $apiKey
        ];

        curl_setopt_array($signature_request, array(
            CURLOPT_URL => $this->config->item('cn_api_base_url'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $signature_data,
            CURLOPT_HTTPHEADER => [],
        ));
        $singature_response = curl_exec($signature_request);
        curl_close($signature_request);
        $signature_json = json_decode($singature_response);

        if(!empty($signature_json)){
            $signature_data['signature']  = $signature_json;
            $signature_data['notify_url'] = $notify_url;
            $signature_data['return_url'] = '';
            $signature_data['cancel_url'] = '';
            $payment_form_req = curl_init();

            curl_setopt_array($payment_form_req, array(
                CURLOPT_URL => "https://secure.cinetpay.com",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $signature_data,
                CURLOPT_HTTPHEADER => [],
            ));

            $response = curl_exec($payment_form_req);
            $response_err = curl_error($payment_form_req);

            curl_close($payment_form_req);

            $response = str_replace('type="text/javascript" src="/','type="text/javascript" src="'.base_url('assets/cnet/') , $response);
            $response = str_replace('script src="/','script src="'.base_url('assets/cnet/') , $response);

            $response = str_replace('link href="/bootstrap/css/font-awesome.css"' , 'link data-type="" href="/bootstrap/css/font-awesome.css"',$response);

            $response = str_replace('link href="/','link href="'.base_url('assets/cnet/') , $response);
            $response = str_replace('link rel="stylesheet" href="/','link rel="stylesheet" href="'.base_url('assets/cnet/') , $response);

            $this->sma->send_json($response);
        }
    }
    public function notification(){
        $id_transaction = $_POST['cpm_trans_id'];
        if (!empty($id_transaction)) {
            try {
                $apiKey = $this->config->item('cn_api_key'); // Remplacez ce champs par votre APIKEY
                $site_id = $this->config->item('cn_site_id'); // Remplacez ce champs par votre SiteID
                $cp = new CinetPay($site_id, $apiKey);
                // Reprise exacte des bonnes données chez CinetPay
                $cp->setTransId($id_transaction)->getPayStatus();
                $paymentData = [
                    "cpm_site_id" => $cp->_cpm_site_id,
                    "signature" => $cp->_signature,
                    "cpm_amount" => $cp->_cpm_amount,
                    "cpm_trans_id" => $cp->_cpm_trans_id,
                    "cpm_custom" => $cp->_cpm_custom,
                    "cpm_currency" => $cp->_cpm_currency,
                    "cpm_payid" => $cp->_cpm_payid,
                    "cpm_payment_date" => $cp->_cpm_payment_date,
                    "cpm_payment_time" => $cp->_cpm_payment_time,
                    "cpm_error_message" => $cp->_cpm_error_message,
                    "payment_method" => $cp->_payment_method,
                    "cpm_phone_prefixe" => $cp->_cpm_phone_prefixe,
                    "cel_phone_num" => $cp->_cel_phone_num,
                    "cpm_ipn_ack" => $cp->_cpm_ipn_ack,
                    "created_at" => $cp->_created_at,
                    "updated_at" => $cp->_updated_at,
                    "cpm_result" => $cp->_cpm_result,
                    "cpm_trans_status" => $cp->_cpm_trans_status,
                    "cpm_designation" => $cp->_cpm_designation,
                    "buyer_name" => $cp->_buyer_name,
                ];
                // Recuperation de la ligne de la transaction dans votre base de données
                $transaction_data = $this->db->get_where('transactions' , ['order_id'=>$id_transaction])->row();

                // Verification de l'etat du traitement de la commande
                // Si le paiement est bon alors ne traitez plus cette transaction : die();
                // On verifie que le montant payé chez CinetPay correspond à notre montant en base de données pour cette transaction
                // On verifie que le paiement est valide

                if ($cp->isValidPayment()) {
                    $sale_id     = $transaction_data->sale_id;
                    $others_data = [
                        'order_id'              => $transaction_data->order_id,
                        'reference_no'          => $transaction_data->reference_no,
                        'amount_paid'           => $cp->_cpm_amount,
                        'paid_by'               => $cp->_payment_method,
                    ];
                    $this->add_payment($sale_id , $others_data);
                }
                $db_data = [
                    'transaction_id'=>$id_transaction,
                    'reference_momo'=>$transaction_data->reference_no,
                    'status_id'=>$paymentData['cpm_trans_status'],
                    'wallet'=>$paymentData['payment_method'].' : '.$paymentData['cel_phone_num'],
                    'paid_transaction_amount'=>$paymentData['cpm_amount'],
                    'currency'=>$paymentData['cpm_currency'],
                    'paid_currency'=>$paymentData['cpm_currency'],
                    'response'=>json_encode($paymentData)
                ];
                $this->db->where(['order_id'=>$id_transaction])->update('transactions' , $db_data);
            }
            catch (Exception $e) {
                die();
            }
        }
    }
    public function return_page($sale_id = ''){
        if(empty($this->session->userdata('user_id'))){
            $session_data = $this->db->get_where('transaction_session',['sale_id'=>$sale_id]);
            if($session_data->num_rows() > 0){
                $session_data = $session_data->row();
                //RESTORE SESSION
                $_SESSION = json_decode($session_data->session_data , true);

                $this->db->where(['sale_id'=>$_POST['sale_id']])->delete('transaction_session');
                redirect('shop/orders/'.$sale_id);

            }
        }

        redirect('shop/orders/'.$sale_id);


//
//        $data['sale_id']=$sale_id;
//       $this->load->view($this->theme.'redirect',$data);
    }
    public function cancel_page($sale_id = ''){
        if(empty($this->session->userdata('user_id'))){
            $session_data = $this->db->get_where('transaction_session',['sale_id'=>$sale_id]);
            if($session_data->num_rows() > 0){
                $session_data = $session_data->row();
                //RESTORE SESSION
                $_SESSION = json_decode($session_data->session_data , true);

                $this->db->where(['sale_id'=>$_POST['sale_id']])->delete('transaction_session');
            }
        }
        $this->session->set_flashdata('info','Paiement annulé');
        redirect('shop/orders/'.$sale_id);
//        $this->load->view($this->theme.'redirect');
    }



    public function check_pending_transactions(){
        var_dump($_POST);
        var_dump($_GET);
    }
    function check_transaction_status($sale_id = ''){
        if(!empty($sale_id)){
            $transaction = $this->db->get_where('transactions',['sale_id'=>$sale_id]);
            if($transaction->num_rows() > 0){
                $transaction_data = $transaction->row();
                $token = $transaction_data->token;
                $api_base_url           = $this->config->item('api_base_url');
                $api_check_url          = $api_base_url.'check_payment_status/'.$transaction_data->order_id;

                $api_check_data = [];
                $api_check = curl_init();
                curl_setopt_array($api_check, array(
                    CURLOPT_URL => $api_check_url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST=>1,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => json_encode($api_check_data),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: Token '.$token
                    ),
                ));
                $apiCheckResponse  = curl_exec($api_check);
                $apiCheckError     = curl_error($api_check);
                curl_close($api_check);
                $this->sma->send_json(json_decode($apiCheckResponse));

            }
            else{
                $this->sma->send_json([
                    'status'=>false,
                    'message'=>'transaction not found'
                ]);
            }

        }
        else{
            $this->sma->send_json([
                'status'=>false,
                'message'=>'sale_id is required'
            ]);
        }
    }

    //=====================================
    //==========NGSER PAYMENT SYSTEM
    //=====================================
    public function make_payment_ngser($data = '' , $amount){
        $datas = explode('--',$data);
        $sale_id = $datas[0];
        $reference_no = $datas[1];
        if(!empty($sale_id)){
            $_POST['sale_id'] = $sale_id;
        }
        if(!empty($amount)){
            $_POST['amount'] = $amount;
        }
        $this->load->helper('string');
        if(!empty($_POST)){
            $api_base_url           = $this->config->item('api_base_url');
            $api_auth_url           = $api_base_url.'service/auth';
            $api_checkout_url       = $api_base_url.'order';
            $api_operation_token    = $this->config->item('api_operation_token');
            $api_currency           = $this->config->item('api_currency');
            $api_auth_name          = $this->config->item('api_authentication_name');
            $api_auth_token         = $this->config->item('api_authentication_token');
            $order                  = random_string('alnum',9);
            $amount                 = $_POST['amount'];

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
                CURLOPT_POST=>1,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
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
                $this->data['action']              = $api_checkout_url;
                $this->data['currency']            = $api_currency;
                $this->data['name']                = $api_auth_name;
                $this->data['operation_token']     = $api_operation_token;
                $this->data['order']               = $order;
                $this->data['transaction_amount']  = $amount;
                $this->data['jwt']                 = $token;

                //DELETE FAILED TRANSACTIONS RELATED TO THE SAME SALE
                $this->db->where(['sale_id'=>$_POST['sale_id'] , 'status_id !='=>'1'])->delete('transactions');
                $this->db->where(['sale_id'=>$_POST['sale_id']])->delete('transaction_session');



                //SAVE TRANSACTION
                $this->db->insert('transactions' , ['sale_id'=>$_POST['sale_id'] , 'token'=>$token, 'order_id'=>$order , 'reference_no'=>$reference_no]);
                //SAVE TRANSACTION SESSION
                $transaction_session_data = [
                    'sale_id' => $_POST['sale_id'],
                    'order_id'=> $order,
                    'reference_no'=> $reference_no,
                    'session_data'=>json_encode($_SESSION),
                ];
                $this->db->insert('transaction_session',$transaction_session_data);


                $this->load->view($this->theme.'payment/checkout' , $this->data);


            }

            else{

                $this->sma->send_json([
                    'response_status'=>false,
                    'stauts'=>'error',
                    'message'=>'could not initiate the payment please try again...'
                ]);
            }

        }
    }
    public function payment_notification_ngser(){
        if(!empty($_GET)){
            $transaction_id             = $_GET['transaction_id'];
            $reference_momo             = $_GET['ref_momo'];
            $order_id                   = $_GET['order_id'];
            $status_id                  = $_GET['status_id'];
            $wallet                     = $_GET['wallet'];
            $transaction_amount         = $_GET['transaction_amount'];
            $paid_transaction_amount    = $_GET['paid_transaction_amount'];
            $currency                   = $_GET['currency'];
            $paid_currency              = $_GET['paid_currency'];
            $change_rate                = $_GET['change_rate'];

            $transaction_data = $this->db->get_where('transactions' , ['order_id'=>$order_id])->row();
            if($status_id == '1'){
                $sale_id     = $transaction_data->sale_id;
                $others_data = [
                    'order_id'              => $transaction_data->order_id,
                    'reference_no'          => $transaction_data->reference_no,
                    'amount_paid'           => $paid_transaction_amount,
                    'paid_by'               => $wallet,
                ];

                $this->add_payment($sale_id , $others_data);
            }

            $this->data['status_id'] = $status_id;
            $this->data['sale_id'] = $transaction_data->sale_id;

            $db_data = [
                'transaction_id'=>$transaction_id,
                'reference_momo'=>$reference_momo,
                'status_id'=>$status_id,
                'wallet'=>$wallet,
                'transaction_amount'=>$transaction_amount,
                'paid_transaction_amount'=>$paid_transaction_amount,
                'currency'=>$currency,
                'paid_currency'=>$paid_currency,
                'change_rate'=>$change_rate,
                'response'=>json_encode($_GET)
            ];

            $this->db->where(['order_id'=>$order_id])->update('transactions' , $db_data);

            $this->load->view($this->theme.'payment/result',$this->data);
        }
    }
    public function check_pending_transactions_ngser(){
        var_dump($_POST);
        var_dump($_GET);
    }
    function check_transaction_status_ngser($sale_id = ''){
        if(!empty($sale_id)){
            $transaction = $this->db->get_where('transactions',['sale_id'=>$sale_id]);
            if($transaction->num_rows() > 0){
                $transaction_data = $transaction->row();
                $token = $transaction_data->token;
                $api_base_url           = $this->config->item('api_base_url');
                $api_check_url          = $api_base_url.'check_payment_status/'.$transaction_data->order_id;

                $api_check_data = [];
                $api_check = curl_init();
                curl_setopt_array($api_check, array(
                    CURLOPT_URL => $api_check_url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST=>1,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => json_encode($api_check_data),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: Token '.$token
                    ),
                ));
                $apiCheckResponse  = curl_exec($api_check);
                $apiCheckError     = curl_error($api_check);
                curl_close($api_check);
                $this->sma->send_json(json_decode($apiCheckResponse));

            }
            else{
                $this->sma->send_json([
                    'status'=>false,
                    'message'=>'transaction not found'
                ]);
            }

        }
        else{
            $this->sma->send_json([
                'status'=>false,
                'message'=>'sale_id is required'
            ]);
        }
    }

    private function add_payment($sale_id = '' , $others_data = [])
    {
        if(!empty($sale_id)){$_POST['sale_id']  = $sale_id;}
        if(!empty($others_data)){
            $_POST['order_id']      = $others_data['order_id'];
            $_POST['reference_no']  = $others_data['reference_no'];
            $_POST['amount_paid']   = $others_data['amount_paid'];
            $_POST['paid_by']       = $others_data['paid_by'];
        }

        if(empty($this->session->userdata('user_id'))){
            $session_data = $this->db->get_where('transaction_session',['sale_id'=>$sale_id , 'order_id'=>$_POST['order_id']]);
            if($session_data->num_rows() > 0){
                $session_data = $session_data->row();
                //RESTORE SESSION
                $_SESSION = json_decode($session_data->session_data , true);
            }
        }


        if(!empty($this->session->userdata('user_id')) and !empty($this->session->userdata('company_id'))){
            $this->form_validation->set_data($_POST);
            $this->form_validation->set_rules('sale_id', 'sale_id', 'required');
            $this->form_validation->set_rules('reference_no', 'reference_no', 'required');
            $this->form_validation->set_rules('order_id', 'order_id', 'required');
            $this->form_validation->set_rules('amount_paid', 'amount_paid', 'required');
            $this->form_validation->set_rules('paid_by', 'paid_by', 'required');
            if ($this->form_validation->run() == TRUE) {
                $id = $_POST['sale_id'];
                $this->load->admin_model('sales_model');
                $sale = $this->sales_model->getInvoiceByID($id);
                $customer_id = $sale->customer_id;
                if ($sale->payment_status == 'paid' && $sale->grand_total == $sale->paid) {
//                    $this->db->where(['sale_id'=>$_POST['sale_id']])->delete('transaction_session');
                    return 'sale_already_paid';
                }
                else{
                    $paid_by          = $_POST['paid_by'];
                    $sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
                    $date = date('Y-m-d H:i:s');
                    $payment = [
                        'date'         => $date,
                        'sale_id'      => $this->input->post('sale_id'),
                        'reference_no' => $this->input->post('reference_no'),
                        'amount'       => $this->input->post('amount_paid'),
                        'paid_by'      => $paid_by,
                        'cheque_no'    => null,
                        'cc_no'        => null,
                        'cc_holder'    => null,
                        'cc_month'     => null,
                        'cc_year'      => null,
                        'cc_type'      => null,
                        'note'         => 'payment for sale with id : '.$_POST['sale_id'].' referenced : '.$_POST['reference_no'],
                        'created_by'   => $this->session->userdata('user_id'),
                        'type'         => $sale->sale_status == 'returned' ? 'returned' : 'received',
                    ];

                    if ($this->sales_model->addPayment($payment, $customer_id)) {
                        $payment_method = $this->db->get_where('sales',['id'=>$_POST['sale_id']])->row()->payment_method;
                        if ($payment_method == 'installment') {
//                            $this->sendEmailForInstallment($_POST['sale_id']);
                        }
                        else{
//                            $this->sendEmailForFullPayment($_POST['sale_id']);
                        }
//                        $this->db->where(['sale_id'=>$_POST['sale_id']])->delete('transaction_session');
                        return true;
                    }
                    else{
                        return false;
                    }
                }
            }
            else {
//                return ['status'=>false, 'message'=>$this->form_validation->error_as_array()];
                return false;
            }
        }

        else{
            return ['status'=>false, 'message'=>'Some parameters are missing'];
        }
    }


    private function convert_to_form_x_www($arr = []){
        $result = '';
        if(!empty($arr)){
            $i = 0;
            foreach ($arr as $k=>$v){
                $append = ($i+1 < count($arr))?'&':'';
                $result.= $k.'='.$v.$append;
                $i++;
            }
        }

        return $result;
    }

    //EMAIL FUNCTIONS
    public function sendEmailForInstallment($id = null , $reminder = false)
    {
        if(base_url() != 'http://rentreefacile/'){

            if ($inv = $this->shop_model->getOrder(['id' => $id, 'hash' => null])) {
                $user     = $inv->created_by ? $this->site->getUser($inv->created_by) : null;
                $customer = $this->site->getCompanyByID($inv->customer_id);
                $biller   = $this->site->getCompanyByID($inv->biller_id);
                $this->load->library('parser');

                $installment_param = $this->db->get_where('installment_param' , ['sale_id'=>$id])->row();
                $product_names = $this->db->get_where('sale_items',['sale_id'=>$id])->result();
                $product_name = '';
                foreach ($product_names as $product){
                    $product_name.= ucfirst($product->product_name.' , ');
                }

                $parse_data = [
                    'reference_number' => $inv->reference_no,
                    'contact_person'   => $customer->name,
                    'company'          => $customer->company && $customer->company != '-' ? '(' . $customer->company . ')' : '',
                    'order_link'       => shop_url('orders/' . $id . '/' . ($this->loggedIn ? '' : '')),
                    'site_link'        => base_url(),
                    'site_name'        => $this->Settings->site_name,
                    'logo'             => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company && $biller->company != '-' ? $biller->company : $biller->name) . '"/>',
                    'balance'          => ($inv->grand_total - $inv->paid),
                    'weekly_pay'       => $installment_param->weekly_pay,
                    'total_month'      => $installment_param->num_of_month,
                    'product_name'     => $product_name,
                    'amount_paid'      => $inv->paid,
                ];

                if($reminder){
                    $msg     = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/installment_reminder.html');
                }
                else{
                    $msg     = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/installment.html');
                }
                $message = $this->parser->parse_string($msg, $parse_data);

                $attachment = $this->orders($id, null, true, 'S');
                $subject    = lang('Installment_payment_details');
                $sent       = $error       = $cc       = $bcc       = false;
                $cc         = $customer->email;
                $bcc        = $biller->email;
            }

            try {
                if ($this->sma->send_email($customer->email, $subject, $message, null, null, $attachment, $cc, $bcc)) {
                    delete_files($attachment);
                    $sent = true;
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }

            //$this->sma->send_json(['sent' => $sent, 'error' => $error , 'message'=>$msg , 'email'=> $customer->email]);

        }
        else{
            //$this->sma->send_json(['status'=>true , 'message'=>'payment successfully made']);
        }

    }
    public function sendEmailForFullPayment($id = null, $hash = null)
    {

        if(base_url() != 'http://rentreefacile/'){

            if ($inv = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash])) {
                $user     = $inv->created_by ? $this->site->getUser($inv->created_by) : null;
                $customer = $this->site->getCompanyByID($inv->customer_id);
                $biller   = $this->site->getCompanyByID($inv->biller_id);
                $this->load->library('parser');

                $product_names = $this->db->get_where('sale_items',['sale_id'=>$id])->result();
                $product_name = '';
                foreach ($product_names as $product){
                    $product_name.= ucfirst($product->product_name.' , ');
                }

                $parse_data = [
                    'reference_number' => $inv->reference_no,
                    'contact_person'   => $customer->name,
                    'company'          => $customer->company && $customer->company != '-' ? '(' . $customer->company . ')' : '',
                    'order_link'       => shop_url('orders/' . $id . '/' . ($this->loggedIn ? '' : '')),
                    'site_link'        => base_url(),
                    'site_name'        => $this->Settings->site_name,
                    'logo'             => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company && $biller->company != '-' ? $biller->company : $biller->name) . '"/>',
                    'balance'          => ($inv->grand_total - $inv->paid),
                    'product_name'     => $product_name,
                ];

                $msg     = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/full_payment.html');
                $message = $this->parser->parse_string($msg, $parse_data);

                $attachment = $this->orders($id, null, true, 'S');
                $subject    = lang('Installment_payment_details');
                $sent       = $error       = $cc       = $bcc       = false;
                $cc         = $customer->email;
                $bcc        = $biller->email;
            }

            try {
                if ($this->sma->send_email($customer->email, $subject, $message, null, null, $attachment, $cc, $bcc)) {
                    delete_files($attachment);
                    $sent = true;
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            //$this->sma->send_json(['sent' => $sent, 'error' => $error , 'message'=>$msg , 'email'=> $customer->email]);
        }
        else{
            //$this->sma->send_json(['status'=>true , 'message'=>'payment successfully made']);
        }
    }

    // Add attachment to sale on manual payment
    public function manual_payment($order_id)
    {
        if ($_FILES['payment_receipt']['size'] > 0) {
            $this->load->library('upload');
            $config['upload_path']   = 'files/';
            $config['allowed_types'] = 'zip|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
            $config['max_size']      = 2048;
            $config['overwrite']     = false;
            $config['max_filename']  = 25;
            $config['encrypt_name']  = true;
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('payment_receipt')) {
                $error = $this->upload->display_errors();
                $this->session->set_flashdata('error', $error);
                redirect($_SERVER['HTTP_REFERER']);
            }
            $manual_payment = $this->upload->file_name;
            $this->db->update('sales', ['attachment' => $manual_payment], ['id' => $order_id]);
            $this->session->set_flashdata('message', lang('file_submitted'));
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/shop/orders');
        }
    }


}