<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;
class Cart extends REST_Controller
{
    private $loggedIn;
    public function __construct()
    {
        parent::__construct();
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
//        $this->load->library('cart');

        $this->lang->admin_load('auth', $this->Settings->user_language);
        $_GET['nothging_at_all_goes_here_la'] = 'tout_ce_qui_peut_gere_laffaire';
        $this->load->model('shop/shop_model');

        //------------------------------------------------------------------------
        //$this->Settings = $this->site->get_setting();
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

    private function check_login(){
        if(!$this->loggedIn()){
            redirect(base_url('api/v1/auth/show_loggin_issue_msg'));
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

    private function getCartContent(){
        $cart = $this->cart->cart_data(true);
        $cart_info = [];
        $cart_info['status'] =  true;
        $cart_info['total_items'] = $cart['total_items'];
        $cart_info['total_unique_items'] = $cart['total_unique_items'];
        $cart_item = [];
        $total_tax = 0;
        $total_row_tax = 0;
        $total_amount = 0;

        $cart_content = $cart['contents'];
        foreach($cart_content as $k=>$v){
            array_push($cart_item , $v);
            $subtotal = $v['subtotal'];
            $tax      = $v['tax'];
            $row_tax  = $v['row_tax'];

            $subtotal = preg_replace("#[a-z,]#i",'',$subtotal);
            $tax      = preg_replace("#[a-z,]#i",'',$tax);
            $row_tax  = preg_replace("#[a-z,]#i",'',$row_tax);


            $total_amount+=(float)$subtotal;
            $total_tax+=(float) $tax;
            $total_row_tax+=(float)$row_tax;
        }


        $cart_info['total_amount']  = $total_amount;
        $cart_info['total_tax']     = $total_tax;
        $cart_info['total_row_tax'] = $total_row_tax;
        $cart_info['contents']      = $cart_item;
        return $cart_info;
    }

    public function getShippingFees_post()
    {
        $this->check_login();
        if ($this->cart->total_items() < 1) {
            $this->response([
                'status'=>'error',
                    'message'=> lang('cart_is_empty')
                ]
            );
        }
        else{
            $addresses  = $this->loggedIn ? $this->shop_model->getAddresses() : false;
//        $this->data['cart_info'] = $cart_contents;
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
            $this->response($result);
        }

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

    public function getCartInfo_post()
    {
        if ($this->cart->total_items() < 1) {
            $this->response(['status'=>false,'message'=>lang('cart_empty')]);
        }
        else{

            $this->response($this->getCartContent());
        }
    }

    public function add_post($product_id = '')
    {
        $this->form_validation->set_rules('product_id','product_id','required');
        $this->form_validation->set_rules('quantity','quantity','required');

        if($this->form_validation->run()== true){
            $product_id = $_POST['product_id'];

            $product = $this->shop_model->getProductForCart($product_id);
            $options = $this->shop_model->getProductVariants($product_id);
            $price   = $this->sma->setCustomerGroupPrice((isset($product->special_price) && !empty($product->special_price) ? $product->special_price : $product->price), $this->customer_group);
            $price   = $this->sma->isPromo($product) ? $product->promo_price : $price;
            $option  = false;
            if (!empty($options)) {
                if ($this->input->post('option')) {
                    foreach ($options as $op) {
                        if ($op['id'] == $this->input->post('option')) {
                            $option = $op;
                        }
                    }
                } else {
                    $option = array_values($options)[0];
                }
                $price = $option['price'] + $price;
            }
            $selected = $option ? $option['id'] : false;

            $cart_qty = $this->getCartData($product_id , 'qty');
            $prm_qty = ($this->input->get('qty') ? $this->input->get('qty') : ($this->input->post('quantity') ? $this->input->post('quantity') : 1));
            $qty = $prm_qty + $cart_qty;

            if (!$this->Settings->overselling && $this->checkProductStock($product, $qty, $selected)) {
                $this->sma->send_json(['error' => 1, 'message' => lang('item_stock_is_less_then_order_qty') , 'status'=>false]);
            }

            $tax_rate   = $this->site->getTaxRateByID($product->tax_rate);
            $ctax       = $this->site->calculateTax($product, $tax_rate, $price);
            $tax        = $this->sma->formatDecimal($ctax['amount']);
            $price      = $this->sma->formatDecimal($price);
            $unit_price = $this->sma->formatDecimal($product->tax_method ? $price + $tax : $price);
            $id         = $this->Settings->item_addition ? md5($product->id) : md5(microtime());

            $data = [
                'id'         => $id,
                'product_id' => $product->id,
                'qty'        => ($this->input->get('qty') ? $this->input->get('qty') : ($this->input->post('quantity') ? $this->input->post('quantity') : 1)),
                'name'       => $product->name,
                'slug'       => $product->slug,
                'code'       => $product->code,
                'price'      => $unit_price,
                'tax'        => $tax,
                'image'      => $product->image,
                'option'     => $selected,
                'warehouse_name'=>$product->warehouse_name,
                'in_wishlist'=>$this->db->get_where('wishlist',['user_id'=>$this->session->userdata('user_id') , 'product_id'=>$product->id])->num_rows() > 0,
                'options'    => !empty($options) ? $options : null,
            ];
            if ($this->cart->insert($data)) {
                $this->response(
                  [
                      'status'=>true ,
                      'message'=>lang('item_added_to_cart'),
                      'cart'=>$this->cart->cart_data(true)
                  ]
                );
            }
            else{
                $this->response([
                    'status'=>false,
                    'message'=>lang('unable_to_add_item_to_cart')
                ]);
            }
        }
        else{
            $this->response(['status'=>false , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    //add item to cart
    public function add_old_post()
    {
        $this->form_validation->set_rules('product_id','product_id','required');
        $this->form_validation->set_rules('quantity','quantity','required');

        if($this->form_validation->run()== true){
            $product_id = $_POST['product_id'];
            if ($this->input->is_ajax_request() || $this->input->post('quantity')) {
                $product = $this->shop_model->getProductForCart($product_id);
                $options = $this->shop_model->getProductVariants($product_id);
                $price   = $this->sma->setCustomerGroupPrice((isset($product->special_price) && !empty($product->special_price) ? $product->special_price : $product->price), $this->customer_group);
                $price   = $this->sma->isPromo($product) ? $product->promo_price : $price;
                $option  = false;
                if (!empty($options)) {
                    if ($this->input->post('option')) {
                        foreach ($options as $op) {
                            if ($op['id'] == $this->input->post('option')) {
                                $option = $op;
                            }
                        }
                    } else {
                        $option = array_values($options)[0];
                    }
                    $price = $option['price'] + $price;
                }
                $selected = $option ? $option['id'] : false;

                //var_dump($this->Settings);

                if (!$this->Settings->overselling && $this->checkProductStock($product, 1, $selected)) {
                    if ($this->input->is_ajax_request()) {
                       $this->response(['status' => false, 'message' => lang('item_out_of_stock')]);
                    } else {
                       $this->response(['status' => false, 'message' => lang('item_out_of_stock')]);
                    }
                }

                $tax_rate   = $this->site->getTaxRateByID($product->tax_rate);
                $ctax       = $this->site->calculateTax($product, $tax_rate, $price);
                $tax        = $this->sma->formatDecimal($ctax['amount']);
                $price      = $this->sma->formatDecimal($price);
                $unit_price = $this->sma->formatDecimal($product->tax_method ? $price + $tax : $price);
                $id         = $this->Settings->item_addition ? md5($product->id) : md5(microtime());

                $data = [
                    'id'         => $id,
                    'product_id' => $product->id,
                    'qty'        => ($this->input->get('qty') ? $this->input->get('qty') : ($this->input->post('quantity') ? $this->input->post('quantity') : 1)),
                    'name'       => $product->name,
                    'slug'       => $product->slug,
                    'code'       => $product->code,
                    'price'      => $unit_price,
                    'tax'        => $tax,
                    'image'      => $product->image,
                    'option'     => $selected,
                    'options'    => !empty($options) ? $options : null,
                ];
                if ($this->cart->insert($data)) {
                    if ($this->input->post('quantity')) {
                        $this->response(['status'=>true , 'message'=>lang('item_added_to_cart')]);
                    } else {
                        $this->cart->cart_data();
                    }
                    return true;
                }

                $this->response(['status' => false,'message'=>lang('unable_to_add_item_to_cart')]);
            }
        }
        else{
            $this->response(['status'=>false , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }

    }

    //count cart item
    public function countCartItems_post(){
        if(!empty($_POST['token'])){
            $this->response($this->cart->total_items());
        }
    }

    public function countWhishlistItems_post(){
        $this->check_login();
        $this->response($this->shop_model->getWishlist(true));
    }

    //add item to whishlist
    public function addWishlist_post()
    {
        $this->check_login();
        $this->form_validation->set_rules('product_id','product_id','required');

        if($this->form_validation->run()== true){
            $product_id = $_POST['product_id'];
            if ($this->shop_model->getWishlist(true) >= 60) {
                $this->response(['status' => false, 'message' => lang('max_wishlist'), 'level' => 'warning']);
            }
            if ($this->shop_model->addWishlist($product_id)) {
                $total = $this->shop_model->getWishlist(true);
                $this->response(['status' => true, 'message' => lang('added_wishlist'), 'total' => $total]);
            } else {
               $this->response(['status' => false, 'message' => lang('product_exists_in_wishlist'), 'level' => 'info']);
            }
        }
        else{
            $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    //remove whishlist
    public function removeWishlist_post()
    {
        $this->check_login();
        $this->form_validation->set_rules('product_id','product_id','required');
        $this->form_validation->set_rules('token','token','required');

        if($this->form_validation->run()== true){
            $product_id = $_POST['product_id'];
            if ($this->shop_model->removeWishlist($product_id)) {
                $total = $this->shop_model->getWishlist(true);
               $this->response(['status' => true, 'message' => lang('removed_wishlist'), 'total' => $total]);
            } else {
               $this->response(['status' => false, 'message' => lang('error_occured'), 'level' => 'error']);
            }
        }
        else{
            $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }

    }

    //empty cart content
    public function emptyCart_post(){
        if(!empty($_POST['token']) and $this->cart->total_items() > 0){
            $this->cart->destroy();
            $this->response(['status'=>true,'message'=>'cart_items_deleted']);
        }
        else{
            $this->response(['status'=>false,'message'=>'cart_empty']);
        }
    }

    public function destroy_post()
    {
        $this->cart->destroy();
        $this->response(['status'=>'success','message'=>'cart_items_deleted']);
    }

    public function remove_post()
    {
        $this->form_validation->set_rules('rowid','rowid','required');
        $this->form_validation->set_rules('token','token','required');
        if($this->form_validation->run()== true){
            $rowid = $_POST['rowid'];
            if ($this->cart->remove($rowid)) {
                $this->response($this->getCartContent());
            }
        }
        else{
            $this->response(['status'=>false, 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    //update cart content
    public function update_post()
    {
        $this->form_validation->set_rules('token','token','required');
        $this->form_validation->set_rules('rowid','rowid','required');
        $this->form_validation->set_rules('qty','qty','required');
        if($this->form_validation->run()== true){
            if ($rowid = $this->input->post('rowid', true)) {
                $item = $this->cart->get_item($rowid);
                // $product = $this->site->getProductByID($item['product_id']);
                $product = $this->shop_model->getProductForCart($item['product_id']);
                $options = $this->shop_model->getProductVariants($product->id);
                $price   = $this->sma->setCustomerGroupPrice((isset($product->special_price) ? $product->special_price : $product->price), $this->customer_group);
                $price   = $this->sma->isPromo($product) ? $product->promo_price : $price;
                // $price = $this->sma->isPromo($product) ? $product->promo_price : $product->price;
                if ($option = $this->input->post('option')) {
                    foreach ($options as $op) {
                        if ($op['id'] == $option) {
                            $price = $price + $op['price'];
                        }
                    }
                }
                $selected = $this->input->post('option') ? $this->input->post('option', true) : false;
                if ($this->checkProductStock($product, $this->input->post('qty', true), $selected)) {
                    $this->sma->send_json(['error' => 1, 'message' => lang('item_stock_is_less_then_order_qty') , 'status'=>false]);
                }

                $tax_rate   = $this->site->getTaxRateByID($product->tax_rate);
                $ctax       = $this->site->calculateTax($product, $tax_rate, $price);
                $tax        = $this->sma->formatDecimal($ctax['amount']);
                $price      = $this->sma->formatDecimal($price);
                $unit_price = $this->sma->formatDecimal($product->tax_method ? $price + $tax : $price);

                $data = [
                    'rowid'  => $rowid,
                    'price'  => $price,
                    'tax'    => $tax,
                    'qty'    => $this->input->post('qty', true),
                    'option' => $selected,
                ];
                if ($this->cart->update($data)) {
                    $this->response($this->getCartContent());
                    return true;
                }
                else{
                    $this->response([
                        'status'=>false,
                        'message'=>'error occured',
                    ]);
                }
            }
        }
        else{
            $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()],REST_Controller::HTTP_BAD_REQUEST);
        }

    }

    private function checkProductStock($product, $qty, $option_id = null)
    {

        if ($product->type == 'service' || $product->type == 'digital') {
            return false;
        }

        $chcek = [];
        $quantity =  (int)$product->quantity - (int)$product->alert_quantity;
        if ($product->type == 'standard') {
            if ($pis = $this->site->getPurchasedItems($product->id, $this->shop_settings->warehouse, $option_id)) {
                foreach ($pis as $pi) {
                    $quantity += $pi->quantity_balance;
                }
            }
            $chcek[] = ($qty <= $quantity);
        }

        elseif ($product->type == 'combo') {
            $combo_items = $this->site->getProductComboItems($product->id, $this->shop_settings->warehouse);
            foreach ($combo_items as $combo_item) {
                if ($combo_item->type == 'standard') {
//                    $quantity = 0;
                    if ($pis = $this->site->getPurchasedItems($combo_item->id, $this->shop_settings->warehouse, $option_id)) {
                        foreach ($pis as $pi) {
                            $quantity += $pi->quantity_balance;
                        }
                    }
                    $chcek[] = (($combo_item->qty * $qty) <= $quantity);
                }
            }
        }


        //echo 'qty : '.$qty.' --- quantity : '.$quantity;

//        echo  'returned value : ';
//        echo   in_array(false, $chcek);

        return empty($chcek) || in_array(false, $chcek);
    }

    private function getCartData($product_id = '' , $data = 'qty'){
        if(!empty($product_id)){
            $cart = $this->cart->cart_data(true);
            $cart_content = $cart['contents'];
            $result = 0;
            foreach($cart_content as $k=>$v){
                if($v['product_id'] == $product_id){
                    $result =  $v[$data];
                    break;
                }
            }
            return $result;
        }
        else return 0;

    }

}
