<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Testpay extends REST_Controller
{
    private$init_table = 'testpay_init';
    private $checkout_table = 'testpay_checkout';
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->helper('string_helper');
    }

    public function index_get()
    {
        $this->response('welcome to testPay');
    }

    public function init_post(){
        $this->form_validation->set_rules('merchant-id', 'merchant-id', 'required');
        $this->form_validation->set_rules('api-key', 'api-key', 'required');
        $this->form_validation->set_rules('currency', 'currency', 'required');
        $this->form_validation->set_rules('amount', 'amount', 'required');
        $this->form_validation->set_rules('order-id', 'order-id', 'required');
        $this->form_validation->set_rules('post-url', 'post-url', 'required');

        if ($this->form_validation->run() == TRUE) {

            $status = 1;
            $token  = random_string('alnum' , 50);

            $data = [
                'merchant_id'=> $_POST['merchant-id'],
                'api_key'=> $_POST['api-key'],
                'currency'=> $_POST['currency'],
                'amount'=> $_POST['amount'],
                'order_id'=> $_POST['order-id'],
                'post_url'=> $_POST['post-url'],
            ];
            $data['status'] = $status;
            $data['token'] = $token;

            $this->db->insert($this->init_table,$data);

            $this->response([
                'status'=>$status,
                'token'=>$token,
            ]);



        }
        else {
            $this->response(
                [
                    'status'=>'error',
                    'message'=>$this->form_validation->error_as_array(),
                ]
            );
        }
    }

    public function checkout_post($platform = 'mobile'){
        if($platform == 'mobile'){
            $this->form_validation->set_rules('merchant-id', 'merchant-id', 'required');
            $this->form_validation->set_rules('api-key', 'api-key', 'required');
            $this->form_validation->set_rules('token', 'token', 'required');
            $this->form_validation->set_rules('mobile-number', 'mobile-number', 'required');
            $this->form_validation->set_rules('mobile-network', 'mobile-network', 'required');

            if ($this->form_validation->run() == TRUE) {

                $status = 1;
                $token  = random_string('alnum' , 50);

                $init = $this->db->get_where($this->init_table , ['token'=>$_POST['token']])->row();

//                $result_text  = 'Approved';
                $result_text  = 'Pending';
                $order_id = $init->order_id;
                $auth_code = random_string('alnum',12);
                $transaction_id = random_string('alnum',12);
                $date_processed = date('d-m-Y H:i:s',time());
                $result = '4'; //4 = pending ..... 1 = approved

                $data = [
                    'init_id'=>$init->id,
                    'result_text'=> $result_text,
                    'order_id'=> $order_id,
                    'auth_code'=> $auth_code,
                    'transaction_id'=> $transaction_id,
                    'date_processed'=> $date_processed,
                    'token'=> $token,
                    'result'=> $result,
                    'currency'=> $init->currency,
                    'amount'=> $init->amount,
                ];

                $this->db->insert($this->checkout_table , $data);

                $this->response([
                   'result-text'=>$result_text,
                   'order-id'=>$order_id,
                   'auth-code'=>$auth_code,
                   'transaction-id'=>$transaction_id,
                   'date-processed'=>$date_processed,
                    'currency'=>$init->currency,
                    'amount'=>$init->amount,
                    'token'=>$token,
                    'result'=>$result,
                ]);


            }
            else {
                $this->response(
                    [
                        'status'=>'error',
                        'message'=>$this->form_validation->error_as_array(),
                    ]
                );
            }
        }
    }

    public function query_post(){
        $this->form_validation->set_rules('merchant-id', 'merchant-id', 'required');
        $this->form_validation->set_rules('api-key', 'api-key', 'required');
        $this->form_validation->set_rules('token', 'token', 'required');

        if ($this->form_validation->run() == TRUE) {
            $checkout_data = $this->db->get_where($this->checkout_table , ['token'=>$_POST['token']]);
            if($checkout_data->num_rows() > 0){
                $checkout_data = $checkout_data->row();

                $this->response([
                   'status'=>($checkout_data->result_text == 'Approved')?'Success':'Transaction declined',
                    'order-id'=>$checkout_data->order_id,
                    'token'=>$checkout_data->token,
                    'result'=>$checkout_data->result,
                    'result-text'=>($checkout_data->result_text),
                    'transaction-id'=>$checkout_data->transaction_id,
                    'date-processed'=>$checkout_data->date_processed,
                    'auth-code'=>$checkout_data->auth_code,

                ]);
            }
            else{
                $this->response([
                    'status'=>false,
                    'message'=>'invalid token',
                ]);
            }
        }
        else {
            $this->response(
                [
                    'status'=>'error',
                    'message'=>$this->form_validation->error_as_array(),
                    'post'=>$_POST
                ]
            );
        }
    }
}
