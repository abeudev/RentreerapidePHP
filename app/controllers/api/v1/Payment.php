<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Payment extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->methods['index_get']['limit'] = 500;
//        $this->load->api_model('sales_api');

        $this->load->library('ion_auth');
        $this->load->library('form_validation');
        $this->lang->admin_load('auth', $this->Settings->user_language);
        $_GET['nothging_at_all_goes_here_la'] = 'tout_ce_qui_peut_gere_laffaire';

    }

    public function getToken_get(){
        $this->response([
            'name'=>$this->security->get_csrf_token_name(),
            'value'=>$this->security->get_csrf_hash(),
        ],REST_Controller::HTTP_OK);
    }

    public function index_post()
    {

    }

    public function login_post($m = null)
    {
        $this->form_validation->set_rules('identity', 'identity', 'required');
        $this->form_validation->set_rules('password', 'password', 'required');

        if($this->form_validation->run() == true){

            if (!SHOP || $this->Settings->mmode) {
                $this->response(['must be in shop'],REST_Controller::HTTP_OK);
            }
            if ($this->loggedIn) {
                $this->response(['error'=>$this->session->flashdata('error')],REST_Controller::HTTP_OK);
            }

            if ($this->form_validation->run('auth/login') == true) {
                $remember = (bool)$this->input->post('remember_me');

                if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)) {
                    if ($this->Settings->mmode) {
                        if (!$this->ion_auth->in_group('owner')) {
                            $this->response(lang('site_is_offline_plz_try_later') , REST_Controller::HTTP_BAD_REQUEST);
                        }
                    }

                    $data = [
                         'status'=>REST_Controller::HTTP_OK,
                         'message'=>strip_tags($this->ion_auth->messages()),
                         'user_data'=>[
                         'identity'=>$_SESSION['identity'],
                         'username'=>$_SESSION['username'],
                         'email'=>$_SESSION['email'],
                         'user_id'=>$_SESSION['user_id'],
                         'old_last_login'=>$_SESSION['old_last_login'],
                         'last_ip'=>$_SESSION['last_ip'],
                         'avatar'=>$_SESSION['avatar'],
                         'gender'=>$_SESSION['gender'],
                         'group_id'=>$_SESSION['group_id'],
                         'warehouse_id'=>$_SESSION['warehouse_id'],
                         'view_right'=>$_SESSION['view_right'],
                         'edit_right'=>$_SESSION['edit_right'],
                         'allow_discount'=>$_SESSION['allow_discount'],
                         'biller_id'=>$_SESSION['biller_id'],
                         'company_id'=>$_SESSION['company_id'],
                         'show_cost'=>$_SESSION['show_cost'],
                         'show_price'=>$_SESSION['show_price'],
                        ]
                    ];


                    $this->response($data , REST_Controller::HTTP_OK);
                }

                else {
                   $this->response(['status'=>REST_Controller::HTTP_OK , 'message'=>strip_tags($this->ion_auth->errors())] , REST_Controller::HTTP_OK);
                }
            }

        }
        else{
            $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function logout_get($m = null)
    {

        $logout   = $this->ion_auth->logout();
        $referrer = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/');
        $this->response(['status'=>200,'message'=>strip_tags($this->ion_auth->messages())] , REST_Controller::HTTP_OK);
    }

}
