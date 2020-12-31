<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Shop extends REST_Controller
{
    private $loggedIn;
    public function __construct()
    {
        parent::__construct();
//        $this->db->query("gitSET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));");
        $this->load->api_model('token_api');
        $this->loggedIn = $this->loggedIn();
        if (file_exists(APPPATH . 'controllers' . DIRECTORY_SEPARATOR . 'shop' . DIRECTORY_SEPARATOR . 'Shop.php')) {
            $this->load->shop_model('shop_model');
            $this->load->library('Tec_cart', '', 'cart');
            $this->shop_settings = $this->shop_model->getShopSettings();
            if ($shop_language = get_cookie('shop_language', true)) {
                $this->config->set_item('language', $shop_language);
                $this->lang->admin_load('sma', $shop_language);
                $this->lang->shop_load('shop', $shop_language);
                $this->Settings->user_language = $shop_language;
            } else {
                $this->config->set_item('language', $this->Settings->language);
                $this->lang->admin_load('sma', $this->Settings->language);
                $this->lang->shop_load('shop', $this->Settings->language);
                $this->Settings->user_language = $this->Settings->language;
            }

            $this->theme = $this->Settings->theme . '/shop/views/';
            if (is_dir(VIEWPATH . $this->Settings->theme . DIRECTORY_SEPARATOR . 'shop' . DIRECTORY_SEPARATOR . 'assets')) {
                $this->data['assets'] = base_url() . 'themes/' . $this->Settings->theme . '/shop/assets/';
            } else {
                $this->data['assets'] = base_url() . 'themes/default/shop/assets/';
            }

            if ($selected_currency = get_cookie('shop_currency', true)) {
                $this->Settings->selected_currency = $selected_currency;
            } else {
                $this->Settings->selected_currency = $this->Settings->default_currency;
            }
            $this->default_currency          = $this->shop_model->getCurrencyByCode($this->Settings->default_currency);
            $this->data['default_currency']  = $this->default_currency;
            $this->selected_currency         = $this->shop_model->getCurrencyByCode($this->Settings->selected_currency);
            $this->data['selected_currency'] = $this->selected_currency;

            $this->loggedIn             = $this->sma->logged_in();
            $this->data['loggedIn']     = $this->loggedIn;
            $this->loggedInUser         = $this->site->getUser();
            $this->data['loggedInUser'] = $this->loggedInUser;
            $this->Staff                = null;
            $this->data['Staff']        = $this->Staff;
            if ($this->loggedIn) {
                $this->Customer         = $this->sma->in_group('customer') ? true : null;
                $this->data['Customer'] = $this->Customer;
                $this->Supplier         = $this->sma->in_group('supplier') ? true : null;
                $this->data['Supplier'] = $this->Supplier;
                $this->Staff            = (!$this->sma->in_group('customer') && !$this->sma->in_group('supplier')) ? true : null;
                $this->data['Staff']    = $this->Staff;
            } else {
                $this->config->load('hybridauthlib');
            }

            if ($sd = $this->shop_model->getDateFormat($this->Settings->dateformat)) {
                $dateFormats = [
                    'js_sdate'    => $sd->js,
                    'php_sdate'   => $sd->php,
                    'mysq_sdate'  => $sd->sql,
                    'js_ldate'    => $sd->js . ' hh:ii',
                    'php_ldate'   => $sd->php . ' H:i',
                    'mysql_ldate' => $sd->sql . ' %H:%i',
                ];
            } else {
                $dateFormats = [
                    'js_sdate'    => 'mm-dd-yyyy',
                    'php_sdate'   => 'm-d-Y',
                    'mysq_sdate'  => '%m-%d-%Y',
                    'js_ldate'    => 'mm-dd-yyyy hh:ii:ss',
                    'php_ldate'   => 'm-d-Y H:i:s',
                    'mysql_ldate' => '%m-%d-%Y %T',
                ];
            }
            $this->dateFormats         = $dateFormats;
            $this->data['dateFormats'] = $dateFormats;
        }
        $this->methods['index_get']['limit'] = 500;

        $this->load->library('ion_auth');
        $this->load->library('form_validation');

        $this->lang->admin_load('auth', $this->Settings->user_language);
        $_GET['nothging_at_all_goes_here_la'] = 'tout_ce_qui_peut_gere_laffaire';
        $this->load->model('shop/shop_model');

        //------------------------------------------------------------------------
        $this->customer = $this->warehouse = $this->customer_group = false;
        $this->shop_settings = $this->shop_model->getShopSettings();
        if ($this->session->userdata('company_id')) {
            $this->customer       = $this->site->getCompanyByID($this->session->userdata('company_id'));
            $this->customer_group = $this->shop_model->getCustomerGroup($this->customer->customer_group_id);
        }

        elseif ($this->shop_settings->warehouse) {
            $this->warehouse = $this->site->getWarehouseByID($this->shop_settings->warehouse);
        }

        $this->m                    = strtolower($this->router->fetch_class());
        $this->v                    = strtolower($this->router->fetch_method());
        $this->data['m']            = $this->m;
        $this->data['v']            = $this->v;
        $this->Settings->indian_gst = false;
        if ($this->Settings->invoice_view > 0) {
            $this->Settings->indian_gst = $this->Settings->invoice_view == 2 ? true : false;
            $this->Settings->format_gst = true;
            $this->load->library('gst');
        }
    }

    private function loggedIn(){
        if(!empty($_POST['token'])){
            if($this->token_api->validate_token($_POST['token'])){
                $this->token_api->get_token_session($_POST['token']);
            }
            else{
                $this->response(['status'=>false , 'message'=>'invalid token']);
                return false;
            }
        }
        return !empty($_SESSION['user_id']) && !empty($_SESSION['email']);
    }

    private function check_login(){

        if(!$this->loggedIn()){
            redirect(base_url('api/v1/auth/show_loggin_issue_msg'));
        }
    }

    // Add/edit customer address
    public function address_post()
    {
        $id = (!empty($_POST['id']))?$_POST['id'] : null;
        $this->check_login();

        $_POST['country'] = 'Côte d’Ivoire.';
        $_POST['postal_code'] = '+225';
        $this->form_validation->set_rules('line1', lang('line1'), 'trim|required');
        $this->form_validation->set_rules('line2', lang('line2'), 'trim|required');
        $this->form_validation->set_rules('city', lang('city'), 'trim|required');
        $this->form_validation->set_rules('state', lang('state'), 'trim|required');
        $this->form_validation->set_rules('phone', lang('phone'), 'trim|required');

        if ($this->form_validation->run() == true) {
            $user_addresses = $this->shop_model->getAddresses();
            if (count($user_addresses) >= 6) {
                $this->response(['status' => 'error', 'message' => lang('already_have_max_addresses'), 'level' => 'error'] , REST_Controller::HTTP_OK);
            }

            $region = $this->db->get_where('shipping_regions',['id'=>$_POST['state']])->row();

            $data = [
            	'line1'  	  => $this->input->post('line1'),
                'line2'       => $this->input->post('line2'),
                'phone'       => $this->input->post('phone'),
                'city'        => $this->input->post('city'),
                'state'       => $region->id,
                'postal_code' => $this->input->post('postal_code'),
                'country'     => $this->input->post('country'),
                'region_id'   => $region->id,
                'company_id'  => $this->session->userdata('company_id'),
                ];

            if ($id) {
                $this->db->update('addresses', $data, ['id' => $id]);
                $this->response(['status'=>true , 'message'=>lang('address_updated') , 'addresses'=>$this->getAddressAndShipping()] , REST_Controller::HTTP_OK);
            }
            else {
                $this->db->insert('addresses', $data);
                $this->response(['status'=>true , 'message'=>lang('address_added'),'addresses'=>$this->getAddressAndShipping()] , REST_Controller::HTTP_OK);
            }
        }

        else{
            $this->response(['status'=>false, 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    private function getAddressAndShipping(){
        $addresses  = $this->shop_model->getAddresses();
        $result = [];
        if(!empty($addresses)){
            foreach ($addresses as $addr){
                $shipping = [
                    'id'=>$addr->id,
                    'company_id'=>$addr->company_id,
                    'line1' => $addr->line1,
                    'line2' => $addr->line2,
                    'city' => $addr->city,
                    'postal_code' => $addr->postal_code,
                    'state' => $addr->state,
                    'phone' => $addr->phone,
                    'region_id' => $addr->region_id,
                    'region_name'=>$addr->region_name,
                    'country'=>$addr->country,
                    'shipping_fee' => $this->get_shipping_fees($addr->region_id),
                ];

                array_push($result , $shipping);
            }
        }
        return ($result);
    }

    // Customer address list
    public function addresses_post()
    {
       $this->check_login();
        if(!empty($_POST['id'])){ $this->db->where(['id'=>$_POST['id']]); }
        $this->response($this->getAddressAndShipping() , REST_Controller::HTTP_OK);
    }

    // Add new Order form shop
    public function order_post()
    {
        $this->check_login();


        $this->form_validation->set_rules('address', lang('address'), 'trim|required');
        $this->form_validation->set_rules('note', lang('comment'), 'trim');
        $this->form_validation->set_rules('payment_method', lang('payment_method'), 'required');

        if ($this->form_validation->run() == true) {
            if(!empty($this->cart->contents())){
                if ($address = $this->shop_model->getAddressByID($this->input->post('address') , 'row_array')) {
                    $new_customer = false;
                    if ($this->input->post('address') != 'new') {
                        $customer = $this->site->getCompanyByID($this->session->userdata('company_id') , 'row_array');
                    }

                    else {
                        if (!($customer = $this->shop_model->getCompanyByEmail($this->input->post('email')))) {
                            $customer                      = new stdClass();
                            $customer->name                = $this->input->post('name');
                            $customer->company             = $this->input->post('company');
                            $customer->phone               = $this->input->post('phone');
                            $customer->email               = $this->input->post('email');
                            $customer->address             = $this->input->post('billing_line1') . '<br>' . $this->input->post('billing_line2');
                            $customer->city                = $this->input->post('billing_city');
                            $customer->state               = $this->input->post('billing_state');
                            $customer->postal_code         = $this->input->post('billing_postal_code');
                            $customer->country             = $this->input->post('billing_country');
                            $customer_group                = $this->shop_model->getCustomerGroup($this->Settings->customer_group);
                            $price_group                   = $this->shop_model->getPriceGroup($this->Settings->price_group);
                            $customer->customer_group_id   = (!empty($customer_group)) ? $customer_group->id : null;
                            $customer->customer_group_name = (!empty($customer_group)) ? $customer_group->name : null;
                            $customer->price_group_id      = (!empty($price_group)) ? $price_group->id : null;
                            $customer->price_group_name    = (!empty($price_group)) ? $price_group->name : null;
                            $new_customer                  = true;
                        }
                    }


                    $biller      = $this->site->getCompanyByID($this->shop_settings->biller);
                    $note        = $this->db->escape_str($this->input->post('comment'));
                    $product_tax = 0;
                    $total       = 0;
                    $gst_data    = [];
                    $total_cgst  = $total_sgst  = $total_igst  = 0;

                    if(!empty($this->cart->contents())){
                        foreach ($this->cart->contents() as $item) {
                            $item_option = null;
                            if ($product_details = $this->shop_model->getProductForCart($item['product_id'])) {
                                $price = $this->sma->setCustomerGroupPrice(($product_details->special_price ? $product_details->special_price : $product_details->price), $this->customer_group);
                                $price = $this->sma->isPromo($product_details) ? $product_details->promo_price : $price;
                                if ($item['option']) {
                                    if ($product_variant = $this->shop_model->getProductVariantByID($item['option'])) {
                                        $item_option = $product_variant->id;
                                        $price       = $product_variant->price + $price;
                                    }
                                }

                                $item_net_price = $unit_price = $price;
                                $item_quantity  = $item_unit_quantity  = $item['qty'];
                                $pr_item_tax    = $item_tax    = 0;
                                $tax            = '';

                                if (!empty($product_details->tax_rate)) {
                                    $tax_details = $this->site->getTaxRateByID($product_details->tax_rate);
                                    $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                                    $item_tax    = $ctax['amount'];
                                    $tax         = $ctax['tax'];
                                    if ($product_details->tax_method != 1) {
                                        $item_net_price = $unit_price - $item_tax;
                                    }
                                    $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);
                                    if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($biller->state == $customer->state), $tax_details)) {
                                        $total_cgst += $gst_data['cgst'];
                                        $total_sgst += $gst_data['sgst'];
                                        $total_igst += $gst_data['igst'];
                                    }
                                }

                                $product_tax += $pr_item_tax;
                                $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);

                                $unit = $this->site->getUnitByID($product_details->unit);

                                $product = [
                                    'product_id'        => $product_details->id,
                                    'product_code'      => $product_details->code,
                                    'product_name'      => $product_details->name,
                                    'product_type'      => $product_details->type,
                                    'option_id'         => $item_option,
                                    'net_unit_price'    => $item_net_price,
                                    'unit_price'        => $this->sma->formatDecimal($item_net_price + $item_tax),
                                    'quantity'          => $item_quantity,
                                    'product_unit_id'   => $unit ? $unit->id : null,
                                    'product_unit_code' => $unit ? $unit->code : null,
                                    'unit_quantity'     => $item_unit_quantity,
                                    'warehouse_id'      => $this->shop_settings->warehouse,
                                    'item_tax'          => $pr_item_tax,
                                    'tax_rate_id'       => $product_details->tax_rate,
                                    'tax'               => $tax,
                                    'discount'          => null,
                                    'item_discount'     => 0,
                                    'subtotal'          => $this->sma->formatDecimal($subtotal),
                                    'serial_no'         => null,
                                    'real_unit_price'   => $price,
                                ];


                                $products[] = ($product + $gst_data);
                                $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                            } else {
                                $this->response(['error'=>lang('product_x_found')]);
                            }
                        }
                    }

                    $region_id   = $this->db->get_where('addresses',['id'=>$_POST['address']])->row()->region_id;
                    $calculated_fee = $this->get_shipping_fees($region_id);
                    $shipping = $calculated_fee;

                    $order_tax   = $this->site->calculateOrderTax($this->Settings->default_tax_rate2, ($total + $product_tax));
                    $total_tax   = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
                    $grand_total = $this->sma->formatDecimal(($total + $total_tax + $shipping), 4);


                    $data = [
                        'date'              => date('Y-m-d H:i:s'),
                        'reference_no'      => $this->site->getReference('so'),
                        'customer_id'       => isset($customer['id']) ? $customer['id'] : '',
                        'customer'          => ($customer['company'] && $customer['company'] != '-' ? $customer['company'] : $customer['name']),
                        'biller_id'         => $biller->id,
                        'biller'            => ($biller->company && $biller->company != '-' ? $biller->company : $biller->name),
                        'warehouse_id'      => $this->shop_settings->warehouse,
                        'note'              => $note,
                        'staff_note'        => null,
                        'total'             => $total,
                        'product_discount'  => 0,
                        'order_discount_id' => null,
                        'order_discount'    => 0,
                        'total_discount'    => 0,
                        'product_tax'       => $product_tax,
                        'order_tax_id'      => $this->Settings->default_tax_rate2,
                        'order_tax'         => $order_tax,
                        'total_tax'         => $total_tax,
                        'shipping'          => $shipping,
                        'grand_total'       => $grand_total,
                        'total_items'       => $this->cart->total_items(),
                        'sale_status'       => 'pending',
                        'payment_status'    => 'pending',
                        'payment_term'      => null,
                        'due_date'          => null,
                        'paid'              => 0,
                        'created_by'        => $this->session->userdata('user_id') ? $this->session->userdata('user_id') : null,
                        'shop'              => 1,
                        'address_id'        => ($this->input->post('address') == 'new') ? '' : $address->id,
                        'hash'              => hash('sha256', microtime() . mt_rand()),
                        'payment_method'    => $this->input->post('payment_method'),
                    ];


                    if ($this->Settings->invoice_view == 2) {
                        $data['cgst'] = $total_cgst;
                        $data['sgst'] = $total_sgst;
                        $data['igst'] = $total_igst;
                    }

                    if ($new_customer) {
                        $customer = (array) $customer;
                    }

                    if ($sale_id = $this->shop_model->addSale($data, $products, $customer, $address)) {
                        $this->order_received($sale_id);
                        //empty cart content after saving sale
                        $this->cart->destroy();

                        $rsp = [
                            'status'=>true,
                            'sale_id'=>$sale_id,
                            'message'=>lang('order_added_make_payment'),
                        ];

                        if ($this->input->post('payment_method') == 'installment') {
                            $rsp['notice'] = 'installement params is required for this order';
                        }


                        $this->response($rsp, REST_Controller::HTTP_OK);

                    }
                }

                else {
                    $this->response(['status'=>false,'message'=>lang('address_x_found')] , REST_Controller::HTTP_OK);
                }
            }

            else{
                $this->response(['status'=>'false','message'=>'cart is empty']);
            }

        }

        else{
            $this->response(['status'=>false , 'message'=>$this->form_validation->error_as_array()]);
        }
    }

    public function guestOrder_post()
    {

        if($this->loggedIn()){
            $this->response(['status'=>'error','message'=>'Please logout first']);
        }
        else{
            $guest_checkout = 'guest_checkout';
            $_POST['billing_line1']         = '123456789';
            $_POST['billing_line2']         = '123456789';
            $_POST['billing_city']          = 'Abidjan';
            $_POST['billing_postal_code']   = '00225';
            $_POST['billing_country']       = 'Côte d’Ivoire.';
            $_POST['shipping_country']      = 'Côte d’Ivoire.';
            $_POST['billing_state']         = 'Abidjan';
            $_POST['company']               = 'rentreefacile';
            $_POST['address']               = 'new';
            $_POST['shipping_state'] = (!empty($_POST['shipping_state']))?$_POST['shipping_state'] : 'Abidjan';
            $_POST['payment_method'] = 'onetime';
            if ($guest_checkout) {
                $this->form_validation->set_rules('name', lang('name'), 'trim|required');
                $this->form_validation->set_rules('email', lang('email'), 'trim|required|valid_email');
                $this->form_validation->set_rules('phone', lang('phone'), 'trim|required');

                $this->form_validation->set_rules('shipping_line1', lang('shipping_address') . ' ' . lang('line1'), 'trim|required');
                $this->form_validation->set_rules('shipping_city', lang('shipping_address') . ' ' . lang('city'), 'trim|required');
                $this->form_validation->set_rules('shipping_phone', lang('shipping_address') . ' ' . lang('shipping_phone'), 'trim|required');
                $this->form_validation->set_rules('shipping_state', lang('shipping_state') . ' ' . lang('shipping_state'), 'trim|required');

            }
            if ($guest_checkout && $this->Settings->indian_gst) {
                $this->form_validation->set_rules('billing_state', lang('billing_address') . ' ' . lang('state'), 'trim|required');
                $this->form_validation->set_rules('shipping_state', lang('shipping_address') . ' ' . lang('state'), 'trim|required');
            }

            if ($this->form_validation->run() == true) {

                if(!empty($this->cart->contents())){
                    if ($guest_checkout) {
                        $new_customer = false;
                        $address = [
                            'phone'       => $this->input->post('shipping_phone'),
                            'line1'       => $this->input->post('shipping_line1'),
                            'line2'       => $this->input->post('shipping_line2'),
                            'city'        => $this->input->post('shipping_city'),
                            'state'       => $this->input->post('shipping_state'),
                            'postal_code' => $this->input->post('shipping_postal_code'),
                            'country'     => $this->input->post('shipping_country'),
                        ];

                        if ($this->input->post('address') != 'new') {
                            $customer = $this->site->getCompanyByID($this->session->userdata('company_id') , 'row_array');
                        }
                        else {
                            if (!($customer = $this->shop_model->getCompanyByEmail($this->input->post('email')))) {
                                $customer                      = new stdClass();
                                $customer->name                = $this->input->post('name');
                                $customer->company             = $this->input->post('company');
                                $customer->phone               = $this->input->post('phone');
                                $customer->email               = $this->input->post('email');
                                $customer->address             = $this->input->post('billing_line1') . '<br>' . $this->input->post('billing_line2');
                                $customer->city                = $this->input->post('billing_city');
                                $customer->state               = $this->input->post('billing_state');
                                $customer->postal_code         = $this->input->post('billing_postal_code');
                                $customer->country             = $this->input->post('billing_country');
                                $customer_group                = $this->shop_model->getCustomerGroup($this->Settings->customer_group);
                                $price_group                   = $this->shop_model->getPriceGroup($this->Settings->price_group);
                                $customer->customer_group_id   = (!empty($customer_group)) ? $customer_group->id : null;
                                $customer->customer_group_name = (!empty($customer_group)) ? $customer_group->name : null;
                                $customer->price_group_id      = (!empty($price_group)) ? $price_group->id : null;
                                $customer->price_group_name    = (!empty($price_group)) ? $price_group->name : null;
                                $new_customer                  = true;
                            }
                        }



//                    $biller      = $this->site->getCompanyByID($this->shop_settings->biller);
                        $biller      = $this->shop_model->getCompanyByEmail($this->input->post('email'));
                        $note        = $this->db->escape_str($this->input->post('comment'));
                        $product_tax = 0;
                        $total       = 0;
                        $gst_data    = [];
                        $total_cgst  = $total_sgst  = $total_igst  = 0;
                        foreach ($this->cart->contents() as $item) {
                            $item_option = null;
                            if ($product_details = $this->shop_model->getProductForCart($item['product_id'])) {
                                $price = $this->sma->setCustomerGroupPrice(($product_details->special_price ? $product_details->special_price : $product_details->price), $this->customer_group);
                                $price = $this->sma->isPromo($product_details) ? $product_details->promo_price : $price;
                                if ($item['option']) {
                                    if ($product_variant = $this->shop_model->getProductVariantByID($item['option'])) {
                                        $item_option = $product_variant->id;
                                        $price       = $product_variant->price + $price;
                                    }
                                }

                                $item_net_price = $unit_price = $price;
                                $item_quantity  = $item_unit_quantity  = $item['qty'];
                                $pr_item_tax    = $item_tax    = 0;
                                $tax            = '';

                                if (!empty($product_details->tax_rate)) {
                                    $tax_details = $this->site->getTaxRateByID($product_details->tax_rate);
                                    $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                                    $item_tax    = $ctax['amount'];
                                    $tax         = $ctax['tax'];
                                    if ($product_details->tax_method != 1) {
                                        $item_net_price = $unit_price - $item_tax;
                                    }
                                    $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);
                                    if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($biller->state == $customer->state), $tax_details)) {
                                        $total_cgst += $gst_data['cgst'];
                                        $total_sgst += $gst_data['sgst'];
                                        $total_igst += $gst_data['igst'];
                                    }
                                }

                                $product_tax += $pr_item_tax;
                                $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);

                                $unit = $this->site->getUnitByID($product_details->unit);

                                $product = [
                                    'product_id'        => $product_details->id,
                                    'product_code'      => $product_details->code,
                                    'product_name'      => $product_details->name,
                                    'product_type'      => $product_details->type,
                                    'option_id'         => $item_option,
                                    'net_unit_price'    => $item_net_price,
                                    'unit_price'        => $this->sma->formatDecimal($item_net_price + $item_tax),
                                    'quantity'          => $item_quantity,
                                    'product_unit_id'   => $unit ? $unit->id : null,
                                    'product_unit_code' => $unit ? $unit->code : null,
                                    'unit_quantity'     => $item_unit_quantity,
                                    'warehouse_id'      => $this->shop_settings->warehouse,
                                    'item_tax'          => $pr_item_tax,
                                    'tax_rate_id'       => $product_details->tax_rate,
                                    'tax'               => $tax,
                                    'discount'          => null,
                                    'item_discount'     => 0,
                                    'subtotal'          => $this->sma->formatDecimal($subtotal),
                                    'serial_no'         => null,
                                    'real_unit_price'   => $price,
                                ];


                                $products[] = ($product + $gst_data);
                                $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                            } else {
                                $this->response(['error'=>lang('product_x_found')] , REST_Controller::HTTP_OK);
                            }
                        }

                        $shipping    = $this->shop_settings->shipping;
                        $order_tax   = $this->site->calculateOrderTax($this->Settings->default_tax_rate2, ($total + $product_tax));
                        $total_tax   = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
                        $grand_total = $this->sma->formatDecimal(($total + $total_tax + $shipping), 4);


                        $data = [
                            'date'              => date('Y-m-d H:i:s'),
                            'reference_no'      => $this->site->getReference('so'),
                            'customer_id'       => isset($customer->id) ? $customer->id : '',
                            'customer'          => ($customer->company && $customer->company != '-' ? $customer->company : $customer->name),
                            'biller_id'         => $biller->id,
                            'biller'            => ($biller->company && $biller->company != '-' ? $biller->company : $biller->name),
                            'warehouse_id'      => $this->shop_settings->warehouse,
                            'note'              => $note,
                            'staff_note'        => null,
                            'total'             => $total,
                            'product_discount'  => 0,
                            'order_discount_id' => null,
                            'order_discount'    => 0,
                            'total_discount'    => 0,
                            'product_tax'       => $product_tax,
                            'order_tax_id'      => $this->Settings->default_tax_rate2,
                            'order_tax'         => $order_tax,
                            'total_tax'         => $total_tax,
                            'shipping'          => $shipping,
                            'grand_total'       => $grand_total,
                            'total_items'       => $this->cart->total_items(),
                            'sale_status'       => 'pending',
                            'payment_status'    => 'pending',
                            'payment_term'      => null,
                            'due_date'          => null,
                            'paid'              => 0,
                            'created_by'        => $this->session->userdata('user_id') ? $this->session->userdata('user_id') : null,
                            'shop'              => 1,
                            'address_id'        => ($this->input->post('address') == 'new') ? '' : $address->id,
                            'hash'              => hash('sha256', microtime() . mt_rand()),
                            'payment_method'    => $this->input->post('payment_method'),
                        ];
                        if ($this->Settings->invoice_view == 2) {
                            $data['cgst'] = $total_cgst;
                            $data['sgst'] = $total_sgst;
                            $data['igst'] = $total_igst;
                        }

                        if ($new_customer) {
                            $customer = (array) $customer;
                        }


                        if ($sale_id = $this->shop_model->addSale($data, $products, $customer, $address)) {
//                        $this->order_received($sale_id);
//                        $this->load->library('sms');
//                        $this->sms->newSale($sale_id);
                            $this->cart->destroy();
//                        echo 'order_added_make_payment';

                            if ($this->input->post('payment_method') == 'installment') {
                                $this->data['message'] = 'order successfully added';
                                $this->data['notice'] = 'installement params ins required for this order';
                            }

                            $this->data['sale_id'] = $sale_id;
                            $this->orders($sale_id , $data['hash']);
                        }

                        if(empty($biller->id)){
                            $this->guestOrder_post();
                        }
                    }
                    else {
                        $this->response(['error'=>lang('address_x_found')]);
                    }
                }
                else{
                    $this->response(['status'=>'error','message'=>'cart is empty']);
                }
            }
            else{
                $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
            }
        }

    }

    public function order_received($id = null, $hash = null)
    {
        if ($inv = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash])) {
            $user     = $inv->created_by ? $this->site->getUser($inv->created_by) : null;
            $customer = $this->site->getCompanyByID($inv->customer_id);
            $biller   = $this->site->getCompanyByID($inv->biller_id);
            $this->load->library('parser');
            $parse_data = [
                'reference_number' => $inv->reference_no,
                'contact_person'   => $customer->name,
                'company'          => $customer->company && $customer->company != '-' ? '(' . $customer->company . ')' : '',
                'order_link'       => shop_url('orders/' . $id . '/' . ($this->loggedIn ? '' : '')),

                'site_link'        => base_url(),
                'site_name'        => $this->Settings->site_name,
                'logo'             => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company && $biller->company != '-' ? $biller->company : $biller->name) . '"/>',
            ];
            $msg     = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/sale.html');
            $message = $this->parser->parse_string($msg, $parse_data);

            $attachment = $this->orders($id, null, true, 'S');
            $subject    = lang('new_order_received');
            $sent       = $error       = $cc       = $bcc       = false;
            $cc         = $customer->email;
            $bcc        = $biller->email;
            $warehouse  = $this->site->getWarehouseByID($inv->warehouse_id);
            if ($warehouse->email) {
                $bcc .= ',' . $warehouse->email;
            }
            try {
                if ($this->sma->send_email(($user ? $user->email : $customer->email), $subject, $message, null, null, $attachment, $cc, $bcc)) {
                    delete_files($attachment);
                    $sent = true;
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            return ['sent' => $sent, 'error' => $error];
        }
    }

    public function getOrders_post(){
        $this->check_login();
        $limit  = 1000;
        $page   = (!empty($_POST['page']))? $this->input->post('page', true) : 1;
        if(!empty($_POST['limit'])){$limit = $_POST['limit'];}
        $offset = ($page * $limit) - $limit;
        $installment_orders    = $this->db->query('select s.id as order_id, s.reference_no,s.total,s.shipping, s.grand_total,s.paid,s.sale_status,s.payment_status, s.payment_method ,sma_deliveries.status as delivery_status, p.weekly_pay , pr.* from sma_sales as s left join sma_installment_pay_record as pr on pr.sale_id = s.id left join sma_installment_param as p on p.sale_id = s.id left join sma_deliveries on sma_deliveries.sale_id = s.id where customer_id = '.$this->session->userdata('company_id').' and s.payment_method = \'installment\'')->result_array();
        $ontime_order          = $this->db->select('sales.id as order_id,reference_no,total,shipping, grand_total,paid,sale_status,payment_status, payment_method, deliveries.status as delivery_status')
            ->join('deliveries', 'deliveries.sale_id=sales.id', 'left')
            ->order_by('sales.id', 'desc')
            ->get_where('sales', ['customer_id' => $this->session->userdata('company_id'),'payment_method'=>'onetime'])->result();

        $data['page_info'] = ['page' => $page, 'installment_order' => count($installment_orders), 'onetime_orders' => count($ontime_order), 'total' => (count($installment_orders) + count($ontime_order))];
        $data['installment_orders'] = $installment_orders;
        $data['onetime_orders']     = $ontime_order;
        $data['company_id'] = $this->session->userdata('company_id');
        $this->response($data);
    }

    public function getOrdersById_post(){
        $this->check_login();
        $this->form_validation->set_rules('order_id','order_id','required');

        if($this->form_validation->run()== true){
            $id = $_POST['order_id'];
            $hash = null;
            if ($order = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash])) {

                $r_data['inv']         = $order;
                $r_data['items']        = $this->shop_model->getOrderItems($id);
                $r_data['customer']    = $this->site->getCompanyByID($order->customer_id);
                $r_data['biller']      = $this->site->getCompanyByID($order->biller_id);
                $r_data['address']     = $this->shop_model->getAddressByID($order->address_id);
                $r_data['return_sale'] = $order->return_id ? $this->shop_model->getOrder(['id' => $id]) : null;
                $r_data['return_rows'] = $order->return_id ? $this->shop_model->getOrderItems($order->return_id) : null;
                $r_data['payment_method'] = $this->db->get_where('sales' , ['id'=>$id])->row()->payment_method;
                $r_data['installment_orders'] = $this->db->query('select s.id as order_id , s.grand_total as total , s.payment_method , p.weekly_pay , pr.* from sma_sales as s join sma_installment_pay_record as pr on pr.sale_id = s.id join sma_installment_param as p on p.sale_id = s.id where p.sale_id = '.$id)->result_array();
                $r_data['barcode'] = 'misc/barcode/' . $this->sma->base64url_encode($order->reference_no) . '/code128/74/0/1';
                $r_data['qrcode'] = $this->sma->get_qrcode('link', urlencode(shop_url('orders/' . $order->id)), 2);
                if($r_data['payment_method'] == 'installment'){
                    if($this->db->get_where('installment_pay_record',['sale_id'=>$order->id])->num_rows() == 0){
                        $r_data['notice']= 'please set installment payment parameter for this order';
                    }
                }

                $this->response($r_data);
            }

            else {
                $this->response(['status'=>'error','message'=>'access denied']);
            }
        }
        else{
            $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }

    }

    // Customer order/orders page
    public function orders($id = null, $hash = null, $pdf = null, $buffer_save = null)
    {
        $get_html = false;
        if($hash == true){
            $get_html = true;
            $hash = null;
        }

        if ($id && !$pdf) {
            if ($order = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash])) {

                $r_data['inv']         = $order;
                $r_data['rows']        = $this->shop_model->getOrderItems($id);
                $r_data['customer']    = $this->site->getCompanyByID($order->customer_id);
                $r_data['biller']      = $this->site->getCompanyByID($order->biller_id);
                $r_data['address']     = $this->shop_model->getAddressByID($order->address_id);
                $r_data['return_sale'] = $order->return_id ? $this->shop_model->getOrder(['id' => $id]) : null;
                $r_data['return_rows'] = $order->return_id ? $this->shop_model->getOrderItems($order->return_id) : null;
//                $r_data['paypal']      = $this->shop_model->getPaypalSettings();
//                $r_data['skrill']      = $this->shop_model->getSkrillSettings();
//                $r_data['page_title']  = lang('view_order');
//                $r_data['page_desc']   = '';
                $r_data['payment_method'] = $this->db->get_where('sales' , ['id'=>$id])->row()->payment_method;
                $r_data['installment_orders'] = $this->db->query('select s.id as order_id , s.total , s.payment_method , p.weekly_pay , pr.* from sma_sales as s join sma_installment_pay_record as pr on pr.sale_id = s.id join sma_installment_param as p on p.sale_id = s.id where p.sale_id = '.$id)->result_array();

                if($r_data['payment_method'] == 'installment'){
                    if($this->db->get_where('installment_pay_record',['sale_id'=>$order->id])->num_rows() == 0){
                        $r_data['notice']= 'please set installment payment parameter';
                    }
                }

                $this->response($r_data);
            }

            else {
                $this->response(['status'=>'error','message'=>'access denied']);
            }
        }

        elseif ($pdf || $this->input->get('download')) {
            $id                          = $pdf ? $id : $this->input->get('download', true);
            $hash                        = $hash ? $hash : $this->input->get('hash', true);
            $order                       = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash]);
            $this->data['inv']           = $order;
            $this->data['rows']          = $this->shop_model->getOrderItems($id);
            $this->data['customer']      = $this->site->getCompanyByID($order->customer_id);
            $this->data['biller']        = $this->site->getCompanyByID($order->biller_id);
            $this->data['address']       = $this->shop_model->getAddressByID($order->address_id);
            $this->data['return_sale']   = $order->return_id ? $this->shop_model->getOrder(['id' => $id]) : null;
            $this->data['return_rows']   = $order->return_id ? $this->shop_model->getOrderItems($order->return_id) : null;
            $this->data['Settings']      = $this->Settings;
            $this->data['shop_settings'] = $this->shop_settings;

            $this->data['payment_method'] = $this->db->get_where('sales' , ['id'=>$id])->row()->payment_method;
            $this->data['installment_orders'] = $this->db->query('select s.id as order_id , s.total , s.payment_method , p.weekly_pay , pr.* from sma_sales as s join sma_installment_pay_record as pr on pr.sale_id = s.id join sma_installment_param as p on p.sale_id = s.id where p.sale_id = '.$id)->result_array();


            $html                        = $this->load->view($this->Settings->theme . '/shop/views/pages/pdf_invoice', $this->data, true);
            if ($this->input->get('view')) {
                echo $html;
                exit;
            }
            else if($get_html){
                return $html;
            }
            else {
                $name = lang('invoice') . '_' . str_replace('/', '_', $order->reference_no) . '.pdf';
                if ($buffer_save)
                {
                    return $this->sma->generate_pdf($html, $name, $buffer_save, $this->data['biller']->invoice_footer);
                }
                else {
                    $this->sma->generate_pdf($html, $name, false, $this->data['biller']->invoice_footer);
                }
            }
        }
    }

    // Display Page
    public function getProductBySlug_post()
    {
        $this->form_validation->set_rules('slug', lang('slug'), 'trim|required');
        if($this->form_validation->run() == true){
            $slug = $_POST['slug'];
            $product = $this->shop_model->getProductBySlug($slug);

            if (!$slug || !$product) {
                $this->response(['error'=>lang('product_not_found')]);
                return false;
            }
            $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $product->code . '/' . $product->barcode_symbology . '/40/0') . "' alt='" . $product->code . "' class='pull-left' />";
            if ($product->type == 'combo') {
                $this->data['combo_items'] = $this->shop_model->getProductComboItems($product->id);
            }
            $this->shop_model->updateProductViews($product->id, $product->views);
            $this->data['product']        = $product;
            $this->data['other_products'] = $this->shop_model->getOtherProducts($product->id, $product->category_id, $product->brand);
            $this->data['unit']           = $this->site->getUnitByID($product->unit);
            $this->data['brand']          = $this->site->getBrandByID($product->brand);
            $this->data['images']         = $this->shop_model->getProductPhotos($product->id);
            $this->data['category']       = $this->site->getCategoryByID($product->category_id);
            $this->data['subcategory']    = $product->subcategory_id ? $this->site->getCategoryByID($product->subcategory_id) : null;
            $this->data['tax_rate']       = $product->tax_rate ? $this->site->getTaxRateByID($product->tax_rate) : null;
            $this->data['warehouse']      = $this->shop_model->getAllWarehouseWithPQ($product->id);
            $this->data['options']        = $this->shop_model->getProductOptionsWithWH($product->id);
            $this->data['variants']       = $this->shop_model->getProductOptions($product->id);
            $this->load->helper('text');

            $this->response($this->data , REST_Controller::HTTP_OK);
        }

        else{
            $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }




    }

    // Customer quotations
    public function quotes($id = null, $hash = null)
    {
        if (!$this->loggedIn && !$hash) {
            redirect('login');
        }
        if ($this->Staff) {
            admin_redirect('quotes');
        }
        if ($id) {
            if ($order = $this->shop_model->getQuote(['id' => $id, 'hash' => $hash])) {
                $this->data['inv']        = $order;
                $this->data['rows']       = $this->shop_model->getQuoteItems($id);
                $this->data['customer']   = $this->site->getCompanyByID($order->customer_id);
                $this->data['biller']     = $this->site->getCompanyByID($order->biller_id);
                $this->data['created_by'] = $this->site->getUser($order->created_by);
                $this->data['updated_by'] = $this->site->getUser($order->updated_by);
                $this->data['page_title'] = lang('view_quote');
                $this->data['page_desc']  = '';
                $this->page_construct('pages/view_quote', $this->data);
            } else {
                $this->session->set_flashdata('error', lang('access_denied'));
                redirect('/');
            }
        } else {
            if ($this->input->get('download')) {
                $id                     = $this->input->get('download', true);
                $order                  = $this->shop_model->getQuote(['id' => $id]);
                $this->data['inv']      = $order;
                $this->data['rows']     = $this->shop_model->getQuoteItems($id);
                $this->data['customer'] = $this->site->getCompanyByID($order->customer_id);
                $this->data['biller']   = $this->site->getCompanyByID($order->biller_id);
                // $this->data['created_by'] = $this->site->getUser($order->created_by);
                // $this->data['updated_by'] = $this->site->getUser($order->updated_by);
                $this->data['Settings'] = $this->Settings;
                $html                   = $this->load->view($this->Settings->theme . '/shop/views/pages/pdf_quote', $this->data, true);
                if ($this->input->get('view')) {
                    echo $html;
                    exit;
                } else {
                    $name = lang('quote') . '_' . str_replace('/', '_', $order->reference_no) . '.pdf';
                    $this->sma->generate_pdf($html, $name);
                }
            }
            $page   = $this->input->get('page') ? $this->input->get('page', true) : 1;
            $limit  = 10;
            $offset = ($page * $limit) - $limit;
            $this->load->helper('pagination');
            $total_rows = $this->shop_model->getQuotesCount();
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['orders']     = $this->shop_model->getQuotes($limit, $offset);
            $this->data['pagination'] = pagination('shop/quotes', $total_rows, $limit);
            $this->data['page_info']  = ['page' => $page, 'total' => ceil($total_rows / $limit)];
            $this->data['page_title'] = lang('my_orders');
            $this->data['page_desc']  = '';
            $this->page_construct('pages/quotes', $this->data);
        }
    }

    // Search products page - ajax
    public function search_post()
    {

        $filters           = [];
        $limit             = 24;
        $where_not_in      = $_POST['fetched_ids'];
        $filters['limit']  = $limit;
        $filters['offset'] = isset($filters['page']) && !empty($filters['page']) && ($filters['page'] > 1) ? (($filters['page'] * $limit) - $limit) : null;
        $filters['query']         = (!empty($_POST['query']))?$_POST['query']:'';
        $filters['category']      = (!empty($_POST['category']))?$_POST['category']:'';
        $filters['subcategory']   = (!empty($_POST['subcategory']))?$_POST['subcategory']:'';
        $filters['brand']         = (!empty($_POST['brand']))?$_POST['brand']:'';
        $filters['promo']         = (!empty($_POST['promo']))?$_POST['promo']:'0';
        $filters['sorting']       = (!empty($_POST['sorting']))?$_POST['sorting']:'name-asc';
        $filters['min_price']     = (!empty($_POST['min_price']))?$_POST['min_price']:'';
        $filters['max_price']     = (!empty($_POST['max_price']))?$_POST['max_price']:'';
        $filters['in_stock']      = (!empty($_POST['in_stock']))?$_POST['in_stock']:'0';
        $filters['page']          = (!empty($_POST['page']))?$_POST['page']:1;
        $filters['featured']      = (!empty($_POST['featured']))?$_POST['featured']:'0';

        if(!empty($_POST['category'])){
            $where = (is_numeric($_POST['category']))? ['id'=>$_POST['category']] : ['name'=>$_POST['category']];
            $category = $this->db->get_where('categories',$where)->row();
            if(!empty($category)){
                $filters['category'] = [
                                         'id'          => $category->id,
                                         'code'        => $category->code ,
                                         'name'        => $category->name ,
                                         'image'       => $category->image ,
                                         'parent_id'   => $category->parent_id ,
                                         'slug'        => $category->slug ,
                                         'description' => $category->description ,
                    ];
            }
        }

        if(!empty($_POST['subcategory'])){
            $where = (is_numeric($_POST['subcategory']))? ['id'=>$_POST['subcategory']] : ['name'=>$_POST['subcategory']];
            $subcategory = $this->db->get_where('categories',$where)->row();
            if(!empty($subcategory)){
                $filters['subcategory'] = [
                                         'id'          => $subcategory->id,
                                         'code'        => $subcategory->code ,
                                         'name'        => $subcategory->name ,
                                         'image'       => $subcategory->image ,
                                         'parent_id'   => $subcategory->parent_id ,
                                         'slug'        => $subcategory->slug ,
                                         'description' => $subcategory->description ,
                    ];
            }
        }

        if(!empty($_POST['brand'])){
            $where = (is_numeric($_POST['brand']))? ['id'=>$_POST['brand']] : ['name'=>$_POST['brand']];
            $brand = $this->db->get_where('brands',$where)->row();
            if(!empty($brand)){
                $filters['brand'] = [
                                         'id'          => $brand->id,
                                         'code'        => $brand->code ,
                                         'name'        => $brand->name ,
                                         'image'       => $brand->image ,
                                         'parent_id'   => $brand->parent_id ,
                                         'slug'        => $brand->slug ,
                                         'description' => $brand->description ,
                    ];
            }
        }
        $filters['where_not_in'] = $where_not_in;
        $filters['where_not_in_key'] = "{$this->db->dbprefix('products')}.id";
        $filters['limit']         = (!empty($_POST['limit']))?$_POST['limit']:$limit;
        $total_rows        = $this->shop_model->getProductsCount2($filters);
        $filters['offset'] = isset($filters['page']) && !empty($filters['page']) && ($filters['page'] > 1) ? (($filters['page'] * $limit) - $limit) : null;

        if ($products = $this->shop_model->getProducts2($filters)) {
            $this->load->helper(['text', 'pagination']);
            foreach ($products as &$value) {
                $value['details'] = character_limiter(strip_tags($value['details']), 50);
                if ($this->shop_settings->hide_price) {
                    $value['price']         = $value['formated_price']         = 0;
                    $value['promo_price']   = $value['formated_promo_price']   = 0;
                    $value['special_price'] = $value['formated_special_price'] = 0;
                }
                else {
                    $value['price']                  = $this->sma->setCustomerGroupPrice($value['price'], $this->customer_group);
                    $value['formated_price']         = $this->sma->convertMoney($value['price']);
                    $value['promo_price']            = $this->sma->isPromo($value) ? $value['promo_price'] : 0;
                    $value['formated_promo_price']   = $this->sma->convertMoney($value['promo_price']);
                    $value['special_price']          = isset($value['special_price']) && !empty($value['special_price']) ? $this->sma->setCustomerGroupPrice($value['special_price'], $this->customer_group) : 0;
                    $value['formated_special_price'] = $this->sma->convertMoney($value['special_price']);
                }
            }

//            $pagination = pagination('main/search', $total_rows, $limit);
            $info       = [
                            'current_page' => (isset($filters['page']) && !empty($filters['page']) ? $filters['page'] : 1),
                            'total_page' => ceil($total_rows / $limit),
                            ];

            $this->sma->send_json(['status'=>true,'info' => $info,'filters' => $filters, 'products' => $products]);
        }
        else {
            $this->sma->send_json(['status'=>false,'info' => false,'filters' => $filters, 'products' => false,]);
        }


    }

    public function searchProduct_post()
    {
        $filters           = $this->input->post('filters') ? $this->input->post('filters', true) : false;
        $limit             = 24;
        $where_not_in      = $_POST['fetched_ids'];
        $filters['where_not_in'] = $where_not_in;
        $filters['where_not_in_key'] = "{$this->db->dbprefix('products')}.id";
        $total_rows        = $this->shop_model->getProductsCount2($filters);
        $filters['limit']  = $limit;
        $filters['offset'] = isset($filters['page']) && !empty($filters['page']) && ($filters['page'] > 1) ? (($filters['page'] * $limit) - $limit) : null;

        if ($products = $this->shop_model->getProducts2($filters)) {
            $this->load->helper(['text', 'pagination']);
            foreach ($products as &$value) {
                $value['details'] = character_limiter(strip_tags($value['details']), 50);
                if ($this->shop_settings->hide_price) {
                    $value['price']         = $value['formated_price']         = 0;
                    $value['promo_price']   = $value['formated_promo_price']   = 0;
                    $value['special_price'] = $value['formated_special_price'] = 0;
                }
                else {
                    $value['price']                  = $this->sma->setCustomerGroupPrice($value['price'], $this->customer_group);
                    $value['formated_price']         = $this->sma->convertMoney($value['price']);
                    $value['promo_price']            = $this->sma->isPromo($value) ? $value['promo_price'] : 0;
                    $value['formated_promo_price']   = $this->sma->convertMoney($value['promo_price']);
                    $value['special_price']          = isset($value['special_price']) && !empty($value['special_price']) ? $this->sma->setCustomerGroupPrice($value['special_price'], $this->customer_group) : 0;
                    $value['formated_special_price'] = $this->sma->convertMoney($value['special_price']);
                }
            }

            $pagination = pagination('main/search', $total_rows, $limit);
            $info       = ['page' => (isset($filters['page']) && !empty($filters['page']) ? $filters['page'] : 1), 'total' => ceil($total_rows / $limit)];

            $this->sma->send_json(['filters' => $filters, 'products' => $products, 'pagination' => $pagination, 'info' => $info]);
        }
        else {
            $this->sma->send_json(['filters' => $filters, 'products' => false, 'pagination' => false, 'info' => false]);
        }
    }

    // Send us email
    public function send_message()
    {
        $this->form_validation->set_rules('name', lang('name'), 'required');
        $this->form_validation->set_rules('email', lang('email'), 'required|valid_email');
        $this->form_validation->set_rules('subject', lang('subject'), 'required');
        $this->form_validation->set_rules('message', lang('message'), 'required');

        if ($this->form_validation->run() == true) {
            try {
                if ($this->sma->send_email($this->shop_settings->email, $this->input->post('subject'), $this->input->post('message'), $this->input->post('email'), $this->input->post('name'))) {
                    $this->response(['status' => 'Success', 'message' => lang('message_sent')]);
                }
                $this->response(['status' => 'error', 'message' => lang('action_failed')]);
            } catch (Exception $e) {
                $this->response(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } elseif ($this->input->is_ajax_request()) {
           $this->response(['status' => 'Error!', 'message' => validation_errors(), 'level' => 'error']);
        } else {
            $this->response(['warning', 'Please try to send message from contact page!']);

        }
    }

    // Customer wishlist page
    public function wishlist_post()
    {
        if (!$this->loggedIn) {
            $this->response(['status' => 'error', 'message' => lang('please_login_first')] , REST_Controller::HTTP_OK);
        }
        $total               = $this->shop_model->getWishlist(true);
        if(!empty($_POST['product_id'])){
            $this->db->where(['product_id'=>$_POST['product_id']]);
        }
        $products            = $this->shop_model->getWishlist();
        $this->load->helper('text');
        foreach ($products as $product) {
            $item          = $this->shop_model->getProductByID($product->product_id);
            $item->details = character_limiter(strip_tags($item->details), 140);
            $items[]       = $item;
        }
        $items     = $products ? $items : null;
        $this->response($items);
    }

    //customer pays using installment process
    public function setPaymentParam_post(){
        $this->check_login();
        $this->form_validation->set_rules('sale_id','sale_id','required');
        $this->form_validation->set_rules('weekly_pay','weekly_pay','required');
        $this->form_validation->set_rules('num_of_week','num_of_week','required');
//        $this->form_validation->set_rules('num_of_month','num_of_month','required');

        if($this->form_validation->run()== true){

            //check the existance of the sale id in the installment_pay_record table
            if($this->db->get_where('installment_pay_record',['sale_id'=>$_POST['sale_id']])->num_rows() > 0){
                $this->response(['status'=>'error','message'=>'installment param alrady exit for this sale']);
            }
            else{
                $data = [
                    'sale_id'=>$_POST['sale_id'],
                    'weekly_pay'=>$_POST['weekly_pay'],
                    'num_of_week'=>$_POST['num_of_week'],
                    'num_of_month'=>(int)$_POST['num_of_week'] / 4,

                ];
                $this->db->insert('sma_installment_param' , $data);
                $this->db->insert('sma_installment_pay_record' , ['sale_id'=>$_POST['sale_id'] , 'num_of_week'=>$_POST['num_of_week']]);

                $total = (float)$_POST['weekly_pay'] * (float)$_POST['num_of_week'];
                $this->db->where(['id'=>$_POST['sale_id']])->update('sales',['grand_total'=>$this->sma->formatDecimal(($total), 4)]);
                $this->response(['status'=>'success','message'=>'params successfully saved']);
            }
        }
        else{
            $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function make_payment(){
        $this->check_login();
        $this->form_validation->set_rules('token', 'token', 'required');
        $this->form_validation->set_rules('mobile_number', lang('mobile_number'), 'trim|required');
        $this->form_validation->set_rules('orderCode', lang('orderCode'), 'trim|required');
        $this->form_validation->set_rules('amount', lang('amount'), 'trim|required');
        $this->form_validation->set_rules('payOption', lang('payOption'), 'trim|required');

        if($this->form_validation->run() == true){

            $redirect_url = base_url('check_pending_transactions_status.php');
            $curl_url  = 'https://api.jimahpay.com/api/init';
            $curl_url2 = 'https://api.jimahpay.com/api/checkout/mobile';

            //MAKE SURE PHONE NUMBER HAS THE RIGHT FORMAT
            //MAKE SURE PHONE NUMBER HAS THE RIGHT FORMAT
            if(!preg_match("#^233#" , $_POST['mobile_number'])){
                $_POST['mobile_number'] = preg_replace("#([0])([0-9]+)#" , "$2" ,$_POST['mobile_number']);
                $_POST['mobile_number'] = "233".$_POST['mobile_number'];
            }
            //GENERATE A NEW ORDER-CODE
            $_POST['orderCode'] = str_replace('/','_',$_POST['orderCode']);
            $transaction_id     = substr(time() . mt_rand(1000, 10000), 0, 12);
            $amount             = $_POST['amount'];
            $reference_no       = $_POST['orderCode'];
            $desc = "Invoice for order: {$reference_no}";


            $payload = [
                "merchant-id"   => $this->config->item('merchant_id'),
                "api-key"       => $this->config->item('api_key'),
                "currency"      => "GHS",
                "amount"        => $amount,
                "order-id"      => $transaction_id,
                "post-url"      => $redirect_url,
            ];

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $curl_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST=>1,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $this->convert_to_form_x_www($payload),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/x-www-form-urlencoded'
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);



            //DECODE CURL RESPONSE
            $response_elem = json_decode($response);

//            echo "response : ".$response;
//            echo "error : ".$err;

            //IF FIRST STEP 1 IS OK
            if(!empty($response_elem) && $response_elem->status == '1'){
                if($response_elem->status == "1"){
                    //BEGIN STEP 2
                    $checkout_data = [
                        "merchant-id"         => $this->config->item('merchant_id'),
                        "api-key"             => $this->config->item('api_key'),
                        "token"               => $response_elem->token,
                        "mobile-number"       => $_POST['mobile_number'],
                        "mobile-network"      => $_POST['payOption'],
                        "mobile-auth-token"   => "",
                    ];

                    $curl2 = curl_init();
                    curl_setopt_array($curl2, array(
                        CURLOPT_URL => $curl_url2,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_POST=>1,
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => $this->convert_to_form_x_www($checkout_data),
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/x-www-form-urlencoded'
                        ),
                    ));

                    $response2 = curl_exec($curl2);
                    $err2 = curl_error($curl2);
                    curl_close($curl2);

                    $response_elem2 = json_decode($response2);

                    if(empty($err2)){
                        $result_text_key    = 'result-text';
                        $order_id_key       = 'order-id';
                        $auth_code_key      = 'auth-code';
                        $transaction_id_key = 'transaction-id';
                        $date_processed_key = 'date-processed';
                        $transaction_data = [
                            'order_id'      =>$response_elem2->$order_id_key,
                            'amount'        =>$response_elem2->amount,
                            'amount_paid'   =>$_POST['amount'],
                            'token'         =>$response_elem2->token,
                            'result'        =>$response_elem2->result,
                            'status'        =>$response_elem2->$result_text_key,
                            'currency'      =>$response_elem2->currency,
                            'auth_code'     =>$response_elem2->$auth_code_key,
                            'transaction_id'=>$response_elem2->$transaction_id_key,
                            'date_processed'=>$response_elem2->$date_processed_key,
                            'payment_method'=>$_POST['payOption'],
                            'timestamp'     => time(),
                            'response'      =>$response,
                            'reference_no'  =>$reference_no,
                            'mobile_number' =>$_POST['mobile_number']
                        ];

                        if(!empty($transaction_data['order_id']) and !empty($transaction_data['amount'])){
                            //delete pending and declined transactions related to the same reference_no
                            $this->db->where(['reference_no'=> $reference_no , 'status != '=>'Approved'])->delete('transactions');
                            //SAVE TRANSACTION DETAILS
                            $this->db->insert('transactions',$transaction_data);
                        }
                        else{
                            $this->response(['status'=>'error','messages'=>$response_elem2]);
                        }
                    }


                    if(!empty($response_elem2) && $response_elem2->result == '4'){
                        $this->response(['status'=>'success', 'message'=>'payment process successfully initiated']);
                    }

                    if(!empty($err2)){
                        $this->response(['status'=>'error' , 'errors'=>[$err , $err2]]);
                    }
                }
            }
            else{
                $this->response(['status'=>'error','message'=>$err]);
            }
        }
        else{
            $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function makePayment_post(){
        $this->check_login();
        $this->form_validation->set_rules('token', lang('token'), 'required');
        $this->form_validation->set_rules('mobile_number', lang('mobile_number'), 'required');
        $this->form_validation->set_rules('sale_id', 'sale_id', 'required');
        $this->form_validation->set_rules('amount', lang('amount'), 'required');
        $this->form_validation->set_rules('payOption', lang('payOption'), 'required');

        if($this->form_validation->run() == true){
            if(!empty($_POST)){
                $redirect_url = base_url('shop/check_pending_transactions');

                $init_payment_url    = 'https://api.jimahpay.com/api/init';
                $checkout_url        = 'https://api.jimahpay.com/api/checkout/mobile';

                if(base_url() == 'http://abapamall/'){
                    $init_payment_url  = base_url('api/v1/testpay/init');
                    $checkout_url      = base_url('api/v1/testpay/checkout/mobile');
                }


                //MAKE SURE PHONE NUMBER HAS THE RIGHT FORMAT
                if(!preg_match("#^233#" , $_POST['customerMobileNumber'])){
                    $_POST['customerMobileNumber'] = preg_replace("#([0])([0-9]+)#" , "$2" ,$_POST['customerMobileNumber']);
                    $_POST['customerMobileNumber'] = "233".$_POST['customerMobileNumber'];
                }

                //GENERATE A NEW ORDER-CODE
                $_POST['orderCode'] = str_replace('/','_',$_POST['orderCode']);
                $transaction_id     = substr(time() . mt_rand(1000, 10000), 0, 12);
                $amount             = $_POST['amount'];
                $reference_no       = $this->db->select('reference_no')->get_where('sales',['id'=>$_POST['sale_id']])->row()->reference_no;
                $desc = "Invoice for order: {$reference_no}";

                $init_payment_data = [
                    "merchant-id"   => $this->config->item('merchant_id'),
                    "api-key"       => $this->config->item('api_key'),
                    "currency"      => "GHS",
                    "amount"        => $amount,
                    "order-id"      => $transaction_id,
                    "post-url"      => $redirect_url,
                ];

                $init_payment = curl_init();

                curl_setopt_array($init_payment, array(
                    CURLOPT_URL => $init_payment_url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST=>1,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $this->convert_to_form_x_www($init_payment_data),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/x-www-form-urlencoded'
                    ),
                ));

                $init_payment_response  = curl_exec($init_payment);
                $init_payment_error     = curl_error($init_payment);
                curl_close($init_payment);

                //DECODE CURL RESPONSE INTO OBJECT
                $init_payment_response_obj = json_decode($init_payment_response);


                //IF FIRST STEP 1 IS OK (INIT PAYMENT IS OK)
                $transaction_token = '';
                if(!empty($init_payment_response_obj) && $init_payment_response_obj->status == '1'){
                    //BEGIN STEP 2
                    $checkout_data = [
                        "merchant-id"         => $this->config->item('merchant_id'),
                        "api-key"             => $this->config->item('api_key'),
                        "token"               => $init_payment_response_obj->token,
                        "mobile-number"       => $_POST['mobile_number'],
                        "mobile-network"      => $_POST['payOption'],
                        "mobile-auth-token"   => "",
                    ];


                    $checkout = curl_init();
                    curl_setopt_array($checkout, array(
                        CURLOPT_URL => $checkout_url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_POST=>1,
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => $this->convert_to_form_x_www($checkout_data),
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/x-www-form-urlencoded'
                        ),
                    ));

                    $checkout_response  = curl_exec($checkout);
                    $checkout_error     = curl_error($checkout);
                    curl_close($checkout);
                    //DECODE CURL RESPONSE ONTO OBJECT
                    $checkout_response_obj = json_decode($checkout_response);

                    $result_text_key    = 'result-text';
                    $order_id_key       = 'order-id';
                    $auth_code_key      = 'auth-code';
                    $transaction_id_key = 'transaction-id';
                    $date_processed_key = 'date-processed';


                    //CHECKOUT SUCCEED AND PAYMENT STATUS IS PENDING
                    if(!empty($checkout_response_obj) && $checkout_response_obj->result == '4'){

                        $transaction_data = [
                            'order_id'      =>$checkout_response_obj->$order_id_key,
                            'sale_id'       =>$_POST['sale_id'],
                            'token'         =>$checkout_response_obj->token,
                            'result'        =>$checkout_response_obj->result,
                            'status'        =>$checkout_response_obj->$result_text_key,
                            'currency'      =>$checkout_response_obj->currency,
                            'amount'        =>$checkout_response_obj->amount,
                            'amount_paid'   =>$_POST['amount'],
                            'auth_code'     =>$checkout_response_obj->$auth_code_key,
                            'transaction_id'=>$checkout_response_obj->$transaction_id_key,
                            'date_processed'=>$checkout_response_obj->$date_processed_key,
                            'reference_no'  => $reference_no,
                            'timestamp'     => time(),
                            'payment_method'=>$_POST['payOption'],
                            'response'      =>$checkout_response,
                            'mobile_number' => $_POST['customerMobileNumber'],
                        ];
                        $transaction_token = $checkout_response_obj->token;
                        //delete pending and declined transactions related to the same sale
                        $this->db->where(['sale_id'=> $_POST['sale_id'] , 'status != '=>'Approved'])->delete('transactions');
                        //delete previous transaction_session
                        $this->db->where(['sale_id'=> $_POST['sale_id']])->delete('transaction_session');
                        //SAVE TRANSACTION DETAILS
                        $this->db->insert('transactions',$transaction_data);
                        //if everything went ok
                        $transaction = $this->db->get_where('transactions',['token'=>$transaction_data['token']])->row();


                        $session_data = json_encode($_SESSION);
                        $transaction_session_data = [
                            'transaction_id'=> $transaction->id,
                            'transaction_token'=>$transaction->token,
                            'sale_id'=>$_POST['sale_id'],
                            'reference_no'=>$transaction->reference_no,
                            'session_data'=>$session_data,
                        ];
                        //SAVE TRANSACTION SESSION
                        $this->db->insert('transaction_session',$transaction_session_data);

                        $this->response([
                            'response_status'=>true,
                            'transaction_id'=>$transaction->id,
                            'reference_no'=>$transaction->reference_no,
                            'result'=>$transaction->result,
                            'token'=>$transaction->token,
                            'status'=>$transaction->status,
                        ]);

                    }

                    else{
                        $this->response([
                            'response_status'=>false,
                            'message'=>'Veillez-vous assurez que vous avez enter un numéro valide'
                        ]);
                    }
                }

                else{

                    $this->response([
                        'response_status'=>false,
                        'stauts'=>'error',
                        'message'=>'could not initiate the payment please try again...'
                    ]);
                }

            }
        }
        else{
            $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }


    }

    public function getInstallmentCharges_get(){
        $data = $this->db->get_where('installment_settings',['id'=>1])->row();
        $this->response(
            [
                'status'=>'success',
                'm1'=>$data->month_1_tax,
                'm2'=>$data->month_2_tax,
                'm3'=>$data->month_2_tax,
            ]
        );
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

    public function check_transaction_status_post(){
        $this->form_validation->set_rules('transaction_id', 'transaction_id', 'required');

        if($this->form_validation->run() == true){
            $token = $this->db->get_where('transactions',['id'=>$_POST['transaction_id']])->row()->token;
            $check_transaction_status_url = $this->config->item('check_payment_status_url');

            if(base_url() == 'http://abapamall/'){
                $check_transaction_status_url = base_url('api/v1/testpay/query');
            }

            $check_transaction_status_data = [
                "merchant-id"   => $this->config->item('merchant_id'),
                "api-key"       => $this->config->item('api_key'),
                "token"         => $token,
            ];

            $check_transaction_status = curl_init();

            curl_setopt_array($check_transaction_status, array(
                CURLOPT_URL => $check_transaction_status_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST=>1,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $this->convert_to_form_x_www($check_transaction_status_data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/x-www-form-urlencoded'
                ),
            ));

            $transaction_status_response            = curl_exec($check_transaction_status);
            $transaction_status_response_error      = curl_error($check_transaction_status);
            curl_close($check_transaction_status);

            //DECODE CURL RESPONSE INTO OBJECT
            $transaction_status_response_obj = json_decode($transaction_status_response);


            if(empty($transaction_status_response_error)){
                $result_text_key    = 'result-text';
                $order_id_key       = 'order-id';
                $auth_code_key      = 'auth-code';
                $transaction_id_key = 'transaction-id';
                $date_processed_key = 'date-processed';

                $result_text         = $transaction_status_response_obj->$result_text_key;
                $result           = $transaction_status_response_obj->result;
                $transaction_id = $transaction_status_response_obj->$transaction_id_key;

                $transaction_data = $this->db->get_where('transactions',['token'=>$token])->row();
                if(strtolower($result) == '1'){
                    $others_data = [
                        'reference_no' => $transaction_data->reference_no,
                        'amount-paid'    => $transaction_data->amount,
                        'transaction_token'   => $transaction_data->token,
                    ];
                    $sale_id = $transaction_data->sale_id;

                    $this->response([
                        'status'=>true,
                        'transaction_status'=> strtolower($result_text),
                    ]);
                }
                else{
                    $this->response([
                        'status'=>true,
                        'transaction_status'=> strtolower($result_text),
                    ]);
                }

                $this->db->where(['transaction_id'=>$transaction_id])->update('transactions',[
                    'status'=>$result_text,
                    'result'=>$result,
                    'response'=>$transaction_status_response
                ]);

            }
            else{
                $this->response([
                    'status'=>false,
                    'message'=>$transaction_status_response_error
                ]);
            }
        }
        else{
            $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function productByType_get(){
        $this->load->model('shop/shop_model');
        $date = date('Y-m-d H:i:s',time());
        $filters           = [];
        $limit             = 12;
        $filters['limit']  = $limit;
        $filters['offset'] = isset($filters['page']) && !empty($filters['page']) && ($filters['page'] > 1) ? (($filters['page'] * $limit) - $limit) : null;
        $filters['query']         = (!empty($_POST['query']))?$_POST['query']:'';
        $filters['category']      = (!empty($_POST['category']))?$_POST['category']:'';
        $filters['subcategory']   = (!empty($_POST['subcategory']))?$_POST['subcategory']:'';
        $filters['brand']         = (!empty($_POST['brand']))?$_POST['brand']:'';
        $filters['promo']         = 'yes';
        $filters['sorting']       = (!empty($_POST['sorting']))?$_POST['sorting']:'name-asc';
        $filters['min_price']     = (!empty($_POST['min_price']))?$_POST['min_price']:'';
        $filters['max_price']     = (!empty($_POST['max_price']))?$_POST['max_price']:'';
        $filters['in_stock']      = (!empty($_POST['in_stock']))?$_POST['in_stock']:'0';
        $filters['page']          = (!empty($_POST['page']))?$_POST['page']:1;
        $filters['featured']      = (!empty($_POST['featured']))?$_POST['featured']:'0';
        $filters['limit']         = (!empty($_POST['limit']))?$_POST['limit']:90;
        $filters['offset']        = (!empty($_POST['offset']))?$_POST['offset']:null;
        if ($products = $this->shop_model->getProducts($filters)) {
            $this->load->helper(['text', 'pagination']);
            foreach ($products as &$value) {
                $value['details'] = character_limiter(strip_tags($value['details']), 140);
                if ($this->shop_settings->hide_price) {
                    $value['price']         = $value['formated_price']         = 0;
                    $value['promo_price']   = $value['formated_promo_price']   = 0;
                    $value['special_price'] = $value['formated_special_price'] = 0;
                } else {
                    $value['price']                  = $this->sma->setCustomerGroupPrice($value['price'], $this->customer_group);
                    $value['formated_price']         = $this->sma->convertMoney($value['price']);
                    $value['promo_price']            = $this->sma->isPromo($value) ? $value['promo_price'] : 0;
                    $value['formated_promo_price']   = $this->sma->convertMoney($value['promo_price']);
                    $value['special_price']          = isset($value['special_price']) && !empty($value['special_price']) ? $this->sma->setCustomerGroupPrice($value['special_price'], $this->customer_group) : 0;
                    $value['formated_special_price'] = $this->sma->convertMoney($value['special_price']);
                }
            }

            $info       = ['page' => (isset($filters['page']) && !empty($filters['page']) ? $filters['page'] : 1), 'total' => ceil($total_rows / $limit)];

            $this->response(['filters' => $filters, 'products' => $products, 'info' => $info]);
        }

        $data['standart_products']        = [];
        $data['product_categories']       = $this->db->query("select DISTINCT(category_id)  , c.name ,c.slug ,c.image , cc.total_items from sma_products as p join sma_categories as c on c.id = p.category_id join (select count(category_id) as total_items , cc.name , cc.id from sma_products as pp join sma_categories as cc on cc.id = pp.category_id  GROUP BY pp.category_id) as cc on cc.id = c.id ".$where)->result();
//        $data['product_categories_count'] = $this->db->query("select count(category_id) as total_items , c.name from sma_products as p join sma_categories as c on c.id = p.category_id {$where} GROUP BY p.category_id")->result();

        //fetch 12 products for each categories
        foreach ($data['product_categories'] as $category){
            $category_products = $this->shop_model->getStandartProducts2(['c.id'=>$category->category_id] , 6);
            foreach ($category_products as $product){
                $data['standart_products'][] = $product;
                $fetched_product_ids[] = $product->id;
            }
        }
        $data['fetched_ids'] = $fetched_product_ids;
        $data['week_deals'] = $this->db->select("week_deals.* ,p.name , p.price, p.image , p.category_id , p.warehouse_name , c.name as category_name")->where(['ending_date >'=>$date])->join('products as p', 'p.id = week_deals.product_id')->join('categories as c','c.id = p.category_id')->get('week_deals')->result();


        $data['promotion_products']       = $products;
        $data['featured_products']        = $this->shop_model->getFeaturedProducts();
        $this->response($data);
    }

    private function get_shipping_fees_2($region_id = ''){
        $cart_contents = $this->get_cart_content()['contents'];
        $product_ids = [];
        foreach($cart_contents as $cart_content){
            array_push($product_ids , $cart_content['product_id']);
        }
        $product_ids = array_unique($product_ids);
        $product_shipping = $this->db->where_in('product_id',$product_ids)
            ->join('shipping_groups','shipping_groups.id = product_shipping_group.group_id')
            ->join('shipping_region_group','shipping_region_group.group_id = product_shipping_group.group_id')
            ->join('shipping_regions','shipping_regions.id = shipping_region_group.region_id')
            ->where(['shipping_regions.id'=>$region_id])
            ->get('product_shipping_group')->result();

        $total_fees = 0;
        foreach($product_shipping as $shipping){
            $total_fees+= (float)$shipping->shipping_fee;
        }
        return ($total_fees > 0)?$total_fees : $this->shop_settings->shipping;
    }

    public function get_shipping_fees($region_id = ''){
        $fees = $this->db->get_where('shipping_region_group',['region_id'=>$region_id])->row()->shipping_fee;

        return ($fees > 0)?$fees : $this->shop_settings->shipping;
    }

    private function get_cart_content(){
        $cart = $this->cart->cart_data(true);
        $cart_info = [];
        $cart_item = [];
        $cart_content = $cart['contents'];
        foreach($cart_content as $k=>$v){
            array_push($cart_item , $v);
        }
        $cart_info['contents']      = $cart_item;
        return $cart_info;
    }

    public function getRegions_get(){
        $regions = $this->db->get_where('shipping_regions',['parent_id'=>'0','visible'=>'1'])->result();
        $this->response($regions);

    }

    public function getCities_get($region_id = ''){
    	if(!empty($region_id)){
    		$cities = $this->db->get_where('shipping_regions',['parent_id'=>$region_id])->result();
        	$this->response([
        		'status'=>true,
        		'message'=>'ok',
        		'cities'=>$cities
        	]);
    	}
    	else{
    		$this->response([
    			'status'=>false,
    			'message'=>'region_id is required',
    		]);
    	}
    }

    public function send_email()
    {
        $name = 'AbapaMall';
        $this->form_validation->set_rules('to', lang('to'), 'required');
        $this->form_validation->set_rules('from', lang('from'), 'required|valid_email');
        $this->form_validation->set_rules('subject', lang('subject'), 'required');
        $this->form_validation->set_rules('message', lang('message'), 'required');

        if ($this->form_validation->run() == true) {
            try {
                if ($this->sma->send_email($_POST['to'], $this->input->post('subject'), $this->input->post('message'), $this->input->post('from'), $name)) {
                    $this->response(['status' => 'Success', 'message' => lang('message_sent')]);
                }
                else{
                    $this->response(['status' => 'error', 'message' => lang('action_failed')]);
                }

            } catch (Exception $e) {
                $this->response(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } elseif ($this->input->is_ajax_request()) {
            $this->response(['status' => 'Error!', 'message' => validation_errors(), 'level' => 'error']);
        } else {
            $this->response(['warning', 'Please try to send message from contact page!']);

        }
    }

    public function sendEmail_post($id = null, $hash = null)
    {

    }

    public function sendEmailForInstallment_post($id = null , $hash = null){
        $this->check_login();

        $this->form_validation->set_rules('token', 'token', 'required');

        if ($this->form_validation->run() == TRUE or FALSE) {
            if ($inv = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash])) {
                $user     = $inv->created_by ? $this->site->getUser($inv->created_by) : null;
                $customer = $this->site->getCompanyByID($inv->customer_id);
                $biller   = $this->site->getCompanyByID($inv->biller_id);
                $this->load->library('parser');
                $parse_data = [
                    'reference_number' => $inv->reference_no,
                    'contact_person'   => $customer->name,
                    'company'          => $customer->company && $customer->company != '-' ? '(' . $customer->company . ')' : '',
                    'order_link'       => shop_url('orders/' . $id . '/' . ($this->loggedIn ? '' : '')),
                    'site_link'        => base_url(),
                    'site_name'        => $this->Settings->site_name,
                    'logo'             => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company && $biller->company != '-' ? $biller->company : $biller->name) . '"/>',
                ];
                $msg     = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/installment.html');
                $message = $this->parser->parse_string($msg, $parse_data);
                $message = $this->orders($id, true, true, 'S');

                $attachment = $this->orders($id, null, true, 'S');
                $subject    = lang('Installment_payment_details');
                $sent       = $error       = $cc       = $bcc       = false;
                $cc         = $customer->email;
                $bcc        = $biller->email;
                $warehouse  = $this->site->getWarehouseByID($inv->warehouse_id);
                try {
                    if ($this->sma->send_email(($customer->email), $subject, $message, null, null, $attachment, $cc, $bcc)) {
                        delete_files($attachment);
                        $sent = true;
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }

                $this->response(
                    ['sent' => $sent, 'error' => $error]
                );
            }
        }

        else {
            $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }



    }

    public function getUnpaidSales_post(){
        $this->check_login();
        $this->form_validation->set_rules('token', 'token', 'required');
        if ($this->form_validation->run() == TRUE) {
            $unpaid_orders = $this->db->select('sales.id as sale_id , grand_total, paid , payment_method')
                ->order_by('id', 'desc')
                ->get_where('sales', ['customer_id' => $this->session->userdata('company_id'),'paid'=>'0.0000'])->result();

            $this->response([
                'status'=>true,
                'total'=>count($unpaid_orders),
                'unpaid_sales'=>$unpaid_orders,
            ]);

        } else {
            $this->response(['status' => REST_Controller::HTTP_BAD_REQUEST, 'message' => $this->form_validation->error_as_array()], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    private function isUnpaidSale($sale_id = ''){
        return $this->db->select('sales.id')->get_where('sales', ['paid'=>'0.0000','id'=>$sale_id])->num_rows() > 0;
    }

    public function delete_sale_post()
    {
        $this->check_login();
        $this->form_validation->set_rules('sale_id', 'sale_id', 'required');
        if ($this->form_validation->run() == TRUE) {

            if(!empty($_POST['sale_id'])){
                $this->load->admin_model('sales_model');

                $id = $_POST['sale_id'];
                if($this->isUnpaidSale($id)){
                    $inv = $this->sales_model->getInvoiceByID($id);
                    if ($inv->sale_status == 'returned') {
                        $this->response(['status' => false, 'message' => lang('sale_x_action')]);
                    }

                    if ($this->sales_model->deleteSale($id)) {
                        $this->response(['status' => true, 'message' => lang('sale_deleted')]);
                    }
                    else{
                        $this->response(['status' => true, 'message' => lang('sale_not_deleted')]);
                    }
                }
                else{
                    $this->response(['status'=>false,'message'=> 'cannot delete the specified sale']);
                }
            }

        } else {
            $this->response(['status' => REST_Controller::HTTP_BAD_REQUEST, 'message' => $this->form_validation->error_as_array()], REST_Controller::HTTP_BAD_REQUEST);
        }


    }

    public function getSlides_get(){
        $slides            = json_decode($this->shop_settings->slider);
        $sl = [];
        foreach ($slides as $value) {
        	$sl[] =$value->image;
        }

        $this->response($sl);
    }


}