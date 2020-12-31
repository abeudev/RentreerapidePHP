<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Auth extends REST_Controller
{
    private $loggedIn;
    public function __construct()
    {
        parent::__construct();

        $this->methods['index_get']['limit'] = 500;

        $this->load->library('ion_auth');
        $this->load->library('form_validation');
        $this->load->model('shop/shop_model');

        //$this->lang->admin_load('auth', $this->Settings->user_language);
        $_GET['nothging_at_all_goes_here_la'] = 'tout_ce_qui_peut_gere_laffaire';

        $this->loggedIn = $this->loggedIn();

        $this->remove_unuse_token();

    }

    private function loggedIn(){
        if(!empty($_POST['token'])){
            if($this->validate_token($_POST['token'])){
               $this->get_token_session($_POST['token']);
            }
            else{
                $this->response(['status'=>false , 'message'=>'invalid token']);
                return false;
            }
        }
        return !empty($_SESSION['user_id']) && !empty($_SESSION['email']);
    }

    private function check_login(){
        if(!$this->loggedIn){
            redirect(base_url('api/v1/auth/show_loggin_issue_msg'));
        }
    }

    public function getToken_get(){
        if(!empty($_GET['token']))
        {
            $tk = $this->db->get_where('tokens',['token'=>$_GET['token']]);
            if($tk->num_rows() > 0){
                $tke = $this->db->get_where('tokens',['token'=>$_GET['token']])->row();
                if(!$this->is_passed_date($tke->expire_at)){
                    $this->response([
                        'status'=>true,
                        'name'=>'token',
                        'value'=>$tke->token,
                        'type'=>'existing',
                        'session_details'=>json_decode($tke->session_details),
                        'expire_at'=>date('d m Y' , $tke->expire_at)

                    ]);
                    return true;
                }
            }
        }

        $time = new DateTime();
        $expire_at = new DateTime();
        $expire_at->modify('+2 years');

        $token = $this->random_string('alnum',54);
        $tokens_data = [
            'token'=>$token,
            'created_at'=> $time->getTimestamp(),
            'expire_at'=>$expire_at->getTimestamp(),
        ];

        if(!empty($_SESSION['user_id']) and !empty($_SESSION['customer101@gmail.com'])){
            $tokens_data['session_details'] = json_encode($_SESSION);
        }
        $this->db->insert('tokens',$tokens_data);

        $this->response([
            'status'=>true,
            'name'=>'token',
            'value'=>$token,
            'type'=>'new',
            'expire_at'=>$expire_at->format('d m Y')
        ]);
        return true;
    }

    private function remove_unuse_token(){
        $toks = $this->db->get('tokens');
        if($toks->num_rows() >= 5){
            $tokens = $toks->result();
            foreach ($tokens as $token){
                $today = new DateTime();
                $expired = new DateTime();
                $expired->setTimestamp($token->created_at);
                $expired->modify('+2 years');

                if($today->getTimestamp() >$expired->getTimestamp()){
                    $this->db->where(['id'=>$token->id])->delete('tokens');
                }


            }
        }

    }

    private function validate_token($token = ''){
        if(!empty($token)){
            $tk = $this->db->get_where('tokens',['token'=>$token]);

            if($tk->num_rows() > 0){
                if($this->is_passed_date($tk->row()->expire_at)){
                    return false;
                }
                else{
                    return true;
                }
            }
            else{
                return false;
            }
        }
        else return false;
    }

    private function set_token_session($token = ''){
        if(!empty($token)){
            $tk = $this->db->get_where('tokens',['token'=>$token]);
            if($tk->num_rows() > 0){
                //update session_details
                $this->db->where(['id'=>$tk->row()->id])->update('tokens',['session_details'=>json_encode($_SESSION)]);
            }
        }
    }

    private function get_token_session($token = ''){
        if(!empty($token)){
            $tk = $this->db->get_where('tokens',['token'=>$token]);
            if($tk->num_rows() > 0){
//                //update session_details
//                $this->db->where(['id'=>$tk->row()->id])->update('tokens',['session_details'=>json_encode($_SESSION)]);
                $tke = $tk->row();

                $_SESSION = json_decode($tke->session_details , true);

//                $ses = $tke->session_details;
//                $ses = str_replace('{"','',$ses);
//                $ses = str_replace('"}','',$ses);
//                $ses = str_replace('":"',':',$ses);
//                $ses = str_replace('","',',',$ses);
//                $ses = str_replace('":',':',$ses);
//                $ses = str_replace(',"',',',$ses);
//
//                $sessions_items = explode(',',$ses);
//                $ss = [];
//                foreach ($sessions_items as $session_item){
//                    $session = explode(":" , $session_item);
//                    $_SESSION[$session[0]] = $session[1];
//                }
            }
        }
    }

    public function validateToken_post(){
        $response = [];
        if(!empty($_POST['token'])){
            $token = $_POST['token'];
            $tk = $this->db->get_where('tokens',['token'=>$token]);
            if($tk->num_rows() > 0){
                if($this->is_passed_date($tk->row()->expire_at)){
                   $response = [
                     'status'=>false,
                     'message'=>'token expired',
                   ];
                }
                else{
                    $response = [
                        'status'=>true,
                        'message'=>'token is valid',
                    ];
                }
            }
            else{
                $response = [
                    'status'=>false,
                    'message'=>'token not found',
                ];
            }

        }
        else{
            $response = [
                'status'=>false,
                'message'=>'token param required',
            ];
        }

        $this->response($response);
    }

    public function login_post()
    {
        $this->form_validation->set_rules('identity', 'identity', 'required');
        $this->form_validation->set_rules('password', 'password', 'required');
        $this->form_validation->set_rules('token', 'token', 'required');

        $update_token_seession = false;


        if($this->form_validation->run() == true){

            if(!empty($_POST['token'])){
                if($this->validate_token($_POST['token'])){
                    $update_token_seession = true;
                }

                else{
                    $update_token_seession = false;
                    $this->response([
                        'status'=>false,
                        'message'=>'invalid token'
                    ]);

                    return false;
                }
            }

            if ($this->loggedIn) {
                $this->response(
                	[
                		'status'=>true,
                		'message'=>$this->session->flashdata('error'),
                		'user_data'=>$_SESSION
                	],REST_Controller::HTTP_OK);
            }

            if ($this->form_validation->run('auth/login') == true) {
                $remember = (bool)$this->input->post('remember_me');

                if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)) {
                    if ($this->Settings->mmode) {
                        if (!$this->ion_auth->in_group('owner')) {
                            $this->response(lang('site_is_offline_plz_try_later') , REST_Controller::HTTP_BAD_REQUEST);
                        }
                    }

                    $user = $this->db->get_where('users',['id'=>$_SESSION['user_id']])->row();

                    $data = [
                         'status'=>true,
                         'message'=>strip_tags($this->ion_auth->messages()),
                         'user_data'=>[
                         'identity'=>$_SESSION['identity'],
                         'username'=>$_SESSION['username'],
                         'first_name'=>$user->first_name,
                         'last_name'=>$user->last_name,
                         'phone'=>$user->phone,
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


                    if($update_token_seession){
                       $this->set_token_session($_POST['token']);
                    }


                    $this->response($data , REST_Controller::HTTP_OK);
                }

                else {
                   $this->response(
                   	['status'=>false , 'message'=>strip_tags($this->ion_auth->errors())] , REST_Controller::HTTP_OK);
                }
            }

        }
        else{
            $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function logout_post()
    {
        if(!empty($_POST['token'])){
            $logout   = $this->ion_auth->logout();
            if(count($_SESSION) > 3){
                foreach ($_SESSION as $k=>$v){
                    unset($_SESSION[$k]);
                }
            }
            $this->set_token_session($_POST['token']);
            $referrer = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/');
            $this->response(['status'=>200,'message'=>strip_tags($this->ion_auth->messages())] , REST_Controller::HTTP_OK);
        }
        else{
            $this->response(['status'=>false , 'message'=>'token_field is required']);
        }

    }

    public function register_post()
    {
        $_POST['company'] = 'ABAPA';
        $this->form_validation->set_rules('first_name', strtolower('first_name'), 'required');
        $this->form_validation->set_rules('last_name', strtolower('last_name'), 'required');
        $this->form_validation->set_rules('phone', strtolower('phone'), 'required');
        $this->form_validation->set_rules('company', strtolower('company'), 'required');
        $this->form_validation->set_rules('email', strtolower('email_address'), 'required|is_unique[users.email]');
        $this->form_validation->set_rules('username', strtolower('username'), 'required|is_unique[users.username]');
        $this->form_validation->set_rules('password', strtolower('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', strtolower('confirm_password'), 'required');

        if ($this->form_validation->run('') == true) {
            $this->load->model('shop/shop_model');
            $email    = strtolower($this->input->post('email'));
            $username = strtolower($this->input->post('username'));
            $password = $this->input->post('password');

            $customer_group = $this->shop_model->getCustomerGroup($this->Settings->customer_group);
            $price_group    = $this->shop_model->getPriceGroup($this->Settings->price_group);

            $company_data = [
                'company'             => $this->input->post('company') ? $this->input->post('company') : '-',
                'name'                => $this->input->post('first_name') . ' ' . $this->input->post('last_name'),
                'email'               => $this->input->post('email'),
                'phone'               => $this->input->post('phone'),
                'group_id'            => 3,
                'group_name'          => 'customer',
                'customer_group_id'   => (!empty($customer_group)) ? $customer_group->id : null,
                'customer_group_name' => (!empty($customer_group)) ? $customer_group->name : null,
                'price_group_id'      => (!empty($price_group)) ? $price_group->id : null,
                'price_group_name'    => (!empty($price_group)) ? $price_group->name : null,
            ];

            $company_id = $this->shop_model->addCustomer($company_data);

            $additional_data = [
                'first_name' => $this->input->post('first_name'),
                'last_name'  => $this->input->post('last_name'),
                'phone'      => $this->input->post('phone'),
                'company'    => $this->input->post('company'),
                'gender'     => 'male',
                'company_id' => $company_id,
                'group_id'   => 3,
            ];
            $this->load->library('ion_auth');
        }

        if ($this->form_validation->run() == true) {
            $this->ion_auth->register($username, $password, $email, $additional_data);

            $this->response(['status'=>200 , 'message'=>lang('account_created')] , REST_Controller::HTTP_OK);
        }
        else{
            $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function show_loggin_issue_msg_get(){
        unset($_POST['token']);
        $this->response(['status' => 'error', 'message' => lang('please_login_first')] , REST_Controller::HTTP_OK);
    }

    public function getProfile_post(){
        $this->check_login();
        $user = $this->ion_auth->user()->row();
        $customer = $this->site->getCompanyByID($this->session->userdata('company_id'));
        $user_details = [
            'user_id' => $user->user_id,
            'last_ip_address' => $user->last_ip_address,
            'ip_address' => $user->ip_address,
            'username' => $user->username,
            'email' => $user->email,
            'created_on' => date('d/m/Y H:i',$user->created_on),
            'last_login' => date('d/m/Y H:i',$user->last_login),
            'active' => $user->active,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'gender' => $user->gender,
            'allow_discount' => $user->allow_discount,
            'address'=> $customer->address,
            'city'=> $customer->city,
            'region'=> $customer->state,
            'country'=> $customer->country,

        ];


        $this->response(['status'=>'success','user'=>$user_details]);
    }

    public function updateProfile_post(){
        $this->check_login();
        $user = $this->ion_auth->user()->row();
        if(!empty($_POST['first_name'])){$data['first_name'] = $_POST['first_name'];}
        if(!empty($_POST['last_name'])){$data['last_name'] = $_POST['last_name'];}
        if(!empty($_POST['phone'])){$data['phone'] = $_POST['phone'];}
        if(!empty($_POST['email'])){$data['email'] = $_POST['email'];}
        if(!empty($_POST['address'])){$data['address'] = $_POST['address'];}
        if(!empty($_POST['city'])){$data['city'] = $_POST['city'];}
        if(!empty($_POST['region'])){$data['state'] = $_POST['region'];}
        if(!empty($_POST['postal_code'])){$data['postal_code'] = $_POST['postal_code'];}
        if(!empty($_POST['country'])){$data['country'] = $_POST['country'];}


        if(!empty($data)){
            if(!empty($data['name'])){$bdata['name'] = $data['name'];}
            if(!empty($data['phone'])){$bdata['phone'] = $data['phone'];}
            if(!empty($data['email'])){$bdata['email'] = $data['email'];}
            if(!empty($data['address'])){$bdata['address'] = $data['address'];}
            if(!empty($data['city'])){$bdata['city'] = $data['city'];}
            if(!empty($data['state'])){$bdata['state'] = $data['state'];}
            if(!empty($data['postal_code'])){$bdata['postal_code'] = $data['postal_code'];}
            if(!empty($data['country'])){$bdata['country'] = $data['country'];}

            if(!empty($data['first_name'])){$udata['first_name'] = $data['first_name'];}
            if(!empty($data['last_name'])){$udata['last_name'] = $data['last_name'];}
            if(!empty($data['phone'])){$udata['phone'] = $data['phone'];}
            if(!empty($data['email'])){$udata['email'] = $data['email'];}

            if(!empty($data['email'])){
                if ($user->email != $_POST['email']) {
                    $this->form_validation->set_rules('email', lang('email'), 'trim|is_unique[users.email]');

                    if($this->form_validation->run() === FALSE){
                        $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
                        return false;
                    }
                }
            }

            $updated = false;
            if(!empty($udata)){
                $this->ion_auth->update($user->id, $udata);
                $updated = true;
            }
            if(!empty($udata)){
                $this->shop_model->updateCompany($user->company_id, $bdata);
                $updated = true;
            }

            if ($updated) {
                $this->response([
                    'status'=>'success',
                    'messaeg'=>'profile details successfully updated'
                ]);
            }
            else{
                $this->response([
                    'status'=>'error',
                    'message'=>'No update was made'
                ]);
            }
        }
        else{
            $this->response([
                'status'=>'error',
                'message'=>'At least one of the following field is required: first_name, last_name, phone, email, address, city, region, postal_code, country',
            ]);
        }
    }

    public function updatePassword_post(){
        $this->check_login();

        $this->form_validation->set_rules('old_password', 'old_password', 'required');
        $this->form_validation->set_rules('new_password', 'new_password', 'required|min_length[8]|max_length[25]');
        $this->form_validation->set_rules('new_password_confirm', 'confirm_password', 'required|matches[new_password]');

        if ($this->form_validation->run() == false) {
            $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }
        else {
            $identity = $this->session->userdata($this->config->item('identity', 'ion_auth'));
            $change   = $this->ion_auth->change_password($identity,$_POST['old_password'], $_POST['new_password']);

            if ($change) {
              $this->response(['status'=>'success','message'=> strip_tags($this->ion_auth->messages().' Loged out successfully !!')]);
              if(!empty($_POST['token'])){
                  $logout   = $this->ion_auth->logout();
                  $this->set_token_session($_POST['token']);
              }
            } else {
              $this->response(['status'=>'success','message'=> strip_tags($this->ion_auth->errors())]);
            }
        }
    }

    public function forgotPassword_post()
    {
        $this->form_validation->set_rules('email', lang('email_address'), 'required|valid_email');
        $this->form_validation->set_rules('token', lang('token'), 'required');

        if ($this->form_validation->run() == false) {
            $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        } else {
            $identity = $this->ion_auth->where('email', strtolower($_POST['email']))->users()->row();
            if (empty($identity)) {
                $this->response(['status'=>'error', 'message'=>lang('forgot_password_email_not_found')]);
            }

            $forgotten = $this->ion_auth->forgotten_password2($identity->email);
            if ($forgotten != false) {
                $this->response(['status' => 'success', 'message' => strip_tags($this->ion_auth->messages()) , 'data'=>$forgotten]);
            } else {
                $this->response(['status' => 'error', 'message' => strip_tags($this->ion_auth->errors())]);
            }
        }
    }

    public function resetPassword_post($code = null)
    {
        $this->form_validation->set_rules('forgotten_password_code', 'forgotten_password_code', 'required');
        $this->form_validation->set_rules('user_id', 'user_id', 'required');
        $this->form_validation->set_rules('token', lang('token'), 'required');
        $this->form_validation->set_rules('new', 'new', 'required|min_length[8]|max_length[25]|matches[new_confirm]');
        $this->form_validation->set_rules('new_confirm', 'new_confirm', 'required');

        if($this->form_validation->run() == true){
            $code = $_POST['forgotten_password_code'];

            $user = $this->ion_auth->forgotten_password_check($code);

            if ($user) {
                if ($user->id != $this->input->post('user_id')) {
                    $this->ion_auth->clear_forgotten_password_code($code);
                   $this->response(['status'=>'error','message'=>'failed to reset password']);
                }
                else {
                    // finally change the password
                    $identity = $user->email;

                    $change = $this->ion_auth->reset_password($identity, $this->input->post('new'));
                    if ($change) {
                       $this->response(['status'=>'success','message'=>strip_tags($this->ion_auth->messages())]);
                    } else {
                        $this->response(['status'=>'error', 'message'=>strip_tags($this->ion_auth->errors())]);
                    }
                }
            } else {
                //if the code is invalid then send them back to the forgot password page
                $this->response(['status'=>'error', 'message'=>strip_tags($this->ion_auth->errors())]);
            }
        }
        else{
            $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    private function is_passed_date($date)
    {
        if(date('d-m-Y H:i') == date('d-m-Y H:i' , $date)){
            return false;
        }
        else
        {
            //same year
            if(date('Y') == date('Y',$date))
            {
                //same month
                if(date('m') == date('m',$date))
                {
                    // same date
                    if(date('d') == date('d',$date))
                    {
                        //same hour
                        if(date('H') == date('H',$date)){
                            //same minute
                            if(date('i') == date('i',$date)){
                                return false;
                            }
                            else{
                                if(date('i') > date('i',$date)){
                                    return true;
                                }else{return false;}
                            }
                        }
                        else{
                            if(date('H') > date('H',$date)){
                                return true;
                            }else{return false;}
                        }
                    }
                    else{
                        if(date('d') > date('d',$date)){
                            return true;
                        }else{return false;}
                    }
                }
                else
                {
                    //different_month
                    //passed month
                    if(date('m') > date('m',$date))
                    { return true;
                    } else{ return false; }
                }
            }

            //different year
            else{
                //passed year
                if(date('Y') > date('Y',$date)){
                    return true;
                }else return false;
            }
        }


    }

    private function random_string($type = 'alnum', $len = 8)
    {
        switch ($type)
        {
            case 'basic':
                return mt_rand();
            case 'alnum':
            case 'numeric':
            case 'nozero':
            case 'alpha':
                switch ($type)
                {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric':
                        $pool = '0123456789';
                        break;
                    case 'nozero':
                        $pool = '123456789';
                        break;
                }
                return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
            case 'unique': // todo: remove in 3.1+
            case 'md5':
                return md5(uniqid(mt_rand()));
            case 'encrypt': // todo: remove in 3.1+
            case 'sha1':
                return sha1(uniqid(mt_rand(), TRUE));
        }
    }

    public function test_get(){
    	$this->response([
    		'status'=>true,
    		'message'=>'it works'
    	]);
    }

    public function test_post(){
    	$this->response([
    		'status'=>true,
    		'message'=>'it works'
    	]);
    }
}
