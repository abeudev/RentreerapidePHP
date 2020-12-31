<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Shop extends MY_Shop_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($this->Settings->mmode) {
            redirect('notify/offline');
        }
        $this->load->library('form_validation');

        if ($this->shop_settings->private && !$this->loggedIn) {
            redirect('/login');
        }

    }

    // Add/edit customer address
    public function address($id = null)
    {
        if (!$this->loggedIn) {
            $this->sma->send_json(['status' => 'error', 'message' => lang('please_login')]);
        }
        $this->form_validation->set_rules('line1', lang('line1'), 'trim|required');
        // $this->form_validation->set_rules('line2', lang("line2"), 'trim|required');
        $this->form_validation->set_rules('city', lang('city'), 'trim|required');
        $this->form_validation->set_rules('state', lang('state'), 'trim|required');
        // $this->form_validation->set_rules('postal_code', lang("postal_code"), 'trim|required');
        $this->form_validation->set_rules('country', lang('country'), 'trim|required');
        $this->form_validation->set_rules('phone', lang('phone'), 'trim|required');

        if ($this->form_validation->run() == true) {
            $user_addresses = $this->shop_model->getAddresses();
            if (count($user_addresses) >= 6) {
                $this->sma->send_json(['status' => 'error', 'message' => lang('already_have_max_addresses'), 'level' => 'error']);
            }
            $region = $this->db->get_where('shipping_regions',['id'=>$_POST['city']])->row();

            $data = ['line1'  => $this->input->post('line1'),
                'line2'       => $this->input->post('line2'),
                'phone'       => $this->input->post('phone'),
                'city'        => $region->region_name,
                'state'       => $region->parent_id,
                'postal_code' => $this->input->post('postal_code'),
                'country'     => $this->input->post('country'),
                'region_id'   => $region->id,
                'company_id'  => $this->session->userdata('company_id'),
                ];

            if ($id) {
                $this->db->update('addresses', $data, ['id' => $id]);
                $this->session->set_flashdata('message', lang('address_updated'));
                $this->sma->send_json(['redirect' => $_SERVER['HTTP_REFERER']]);
            } else {
                $this->db->insert('addresses', $data);
                $this->session->set_flashdata('message', lang('address_added'));
                $this->sma->send_json(['redirect' => $_SERVER['HTTP_REFERER']]);
            }
        }

        elseif ($this->input->is_ajax_request()) {
            $this->sma->send_json(['status' => 'error', 'message' => validation_errors()]);
        } else {
            shop_redirect('shop/addresses');
        }
    }

    // Customer address list
    public function addresses()
    {
        if (!$this->loggedIn) {
            redirect('login');
        }
        if ($this->Staff) {
            admin_redirect('customers');
        }
        $this->session->set_userdata('requested_page', $this->uri->uri_string());
        $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['addresses']  = $this->shop_model->getAddresses();
        $this->data['page_title'] = lang('my_addresses');
        $this->data['page_desc']  = '';
        $regions = $this->db->get_where('shipping_regions' , ['parent_id'=>'0' , 'visible'=>'1'])->result();
        $this->data['regions'] = $regions;
        $villes  = $this->db->query('select v.id , v.region_name as ville , r.region_name as region , r.description , v.parent_id from sma_shipping_regions as r join sma_shipping_regions as v on v.parent_id = r.id where r.visible = 1')->result();
        $this->data['default_region_id'] = null;
        $rgv = [];
        foreach($regions as $rg){
            if(empty($this->data['default_region_id'])){
                $this->data['default_region_id'] = $rg->id;
            }
            $rgv[$rg->id] = [];
            $vls = [];
            foreach ($villes as $v){
                if($v->parent_id == $rg->id){
                    $vls[$v->id] = $v->ville;
                }
            }
            $rgv[$rg->id] = $vls;
        }
        $this->data['reg_villes'] = json_encode($rgv);
        $this->page_construct('pages/addresses', $this->data);
    }

    // Digital products download
    public function downloads($id = null, $hash = null)
    {
        if (!$this->loggedIn) {
            redirect('login');
        }
        if ($this->Staff) {
            admin_redirect();
        }
        if ($id && $hash && md5($id) == $hash) {
            $sale = $this->shop_model->getDownloads(1, 0, $id);
            if (!empty($sale)) {
                $product = $this->site->getProductByID($id);
                if (file_exists('./files/' . $product->file)) {
                    $this->load->helper('download');
                    force_download('./files/' . $product->file, null);
                    exit;
                } else {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Transfer-Encoding: Binary');
                    header('Content-disposition: attachment; filename="' . basename($product->file) . '"');
                    // header('Content-Length: ' . filesize($product->file));
                    readfile($product->file);
                }
            }
            $this->session->set_flashdata('error', lang('file_x_exist'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $page   = $this->input->get('page') ? $this->input->get('page', true) : 1;
            $limit  = 10;
            $offset = ($page * $limit) - $limit;
            $this->load->helper('pagination');
            $total_rows = $this->shop_model->getDownloadsCount();
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['downloads']  = $this->shop_model->getDownloads($limit, $offset);
            $this->data['pagination'] = pagination('shop/download', $total_rows, $limit);
            $this->data['page_info']  = ['page' => $page, 'total' => ceil($total_rows / $limit)];
            $this->data['page_title'] = lang('my_downloads');
            $this->data['page_desc']  = '';
            $this->page_construct('pages/downloads', $this->data);
        }
    }

    // Add new Order form shop
    public function order()
    {
        $guest_checkout = $this->input->post('guest_checkout');

        if($guest_checkout){
            $this->guest_order();
        }
        else{
            if (!$guest_checkout && !$this->loggedIn) {
                redirect('login');
            }
            $this->form_validation->set_rules('address', lang('address'), 'trim|required');
            $this->form_validation->set_rules('note', lang('comment'), 'trim');
            $this->form_validation->set_rules('payment_method', lang('payment_method'), 'required');
            if ($guest_checkout) {
                $this->form_validation->set_rules('name', lang('name'), 'trim|required');
                $this->form_validation->set_rules('email', lang('email'), 'trim|required|valid_email');
                $this->form_validation->set_rules('phone', lang('phone'), 'trim|required');
                $this->form_validation->set_rules('billing_line1', lang('billing_address') . ' ' . lang('line1'), 'trim|required');
                $this->form_validation->set_rules('billing_city', lang('billing_address') . ' ' . lang('city'), 'trim|required');
                $this->form_validation->set_rules('billing_country', lang('billing_address') . ' ' . lang('country'), 'trim|required');
                $this->form_validation->set_rules('shipping_line1', lang('shipping_address') . ' ' . lang('line1'), 'trim|required');
                $this->form_validation->set_rules('shipping_city', lang('shipping_address') . ' ' . lang('city'), 'trim|required');
                $this->form_validation->set_rules('shipping_country', lang('shipping_address') . ' ' . lang('country'), 'trim|required');
                $this->form_validation->set_rules('shipping_phone', lang('shipping_address') . ' ' . lang('phone'), 'trim|required');
            }
            if ($guest_checkout && $this->Settings->indian_gst) {
                $this->form_validation->set_rules('billing_state', lang('billing_address') . ' ' . lang('state'), 'trim|required');
                $this->form_validation->set_rules('shipping_state', lang('shipping_address') . ' ' . lang('state'), 'trim|required');
            }

            if ($this->form_validation->run() == true) {
                if ($guest_checkout || $address = $this->shop_model->getAddressByID($this->input->post('address') , 'row_array')) {
                    $new_customer = false;
                    if ($guest_checkout) {
                        $address = [
                            'phone'       => $this->input->post('shipping_phone'),
                            'line1'       => $this->input->post('shipping_line1'),
                            'line2'       => $this->input->post('shipping_line2'),
                            'city'        => $this->input->post('shipping_city'),
                            'state'       => $this->input->post('shipping_state'),
                            'postal_code' => $this->input->post('shipping_postal_code'),
                            'country'     => $this->input->post('shipping_country'),
                        ];
                    }

                    //echo '<h1> session : '.$this->session->userdata('company_id').'</h1>';

                    if ($this->input->post('address') != 'new') {
                        $customer = $this->site->getCompanyByID($this->session->userdata('company_id') , 'row_array');
                        //var_dump($customer);
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
                                'warehouse_id'      => $this->shop_model->getWareHouseIdByProductId($product_details->id),
                                'item_tax'          => $pr_item_tax,
                                'tax_rate_id'       => $product_details->tax_rate,
                                'tax'               => $tax,
                                'discount'          => null,
                                'item_discount'     => 0,
                                'subtotal'          => $this->sma->formatDecimal($subtotal),
                                'serial_no'         => null,
                                'real_unit_price'   => $price,
                            ];

                            //echo   'here we are';
                            //var_dump($product);

                            $products[] = ($product + $gst_data);
                            $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                        } else {
                            $this->session->set_flashdata('error', lang('product_x_found') . ' (' . $item['name'] . ')');
                            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'cart');
                        }
                    }

                    $shipping    = $_POST['shipping_fee'];
                    $region_id   = $this->db->get_where('addresses',['id'=>$_POST['address']])->row()->region_id;

                    $calculated_fee = $this->get_shipping_fees($region_id);
                    $shipping = $calculated_fee;

                    $order_tax   = $this->site->calculateOrderTax($this->Settings->default_tax_rate2, ($total + $product_tax));
                    $total_tax   = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
                    $grand_total = $this->sma->formatDecimal(($total + $total_tax + $shipping), 4);

                    //echo    $shipping;

                    $data = [
                        'date'              => date('Y-m-d H:i:s'),
                        'reference_no'      => $this->site->getReference('so'),
                        'customer_id'       => isset($customer['id']) ? $customer['id'] : '',
                        'customer'          => ($customer['company'] && $customer['company'] != '-' ? $customer['company'] : $customer['name']),
                        'biller_id'         => $biller->id,
                        'biller'            => ($biller->company && $biller->company != '-' ? $biller->company : $biller->name),
                        'warehouse_id'      => $this->shop_model->getWareHouseIdByProductId($product_details->id), //$this->shop_settings->warehouse,
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

                    //var_dump($data);

                    if ($this->Settings->invoice_view == 2) {
                        $data['cgst'] = $total_cgst;
                        $data['sgst'] = $total_sgst;
                        $data['igst'] = $total_igst;
                    }

                    if ($new_customer) {
                        $customer = (array) $customer;
                    }


                    //$this->sma->print_arrays($data, $products, $customer, $address);


                    //$sale_id = 'JFIKA96S9DLAPKA';

                    //echo '<h1>Sale_id : '.$sale_id.'</h1>';

                    if ($sale_id = $this->shop_model->addSale($data, $products, $customer, $address)) {
                        if($this->input->post('payment_method') != 'installment'){
                            $this->order_received($sale_id);
                        }

                        $this->load->library('sms');
                        $this->sms->newSale($sale_id);
                        $this->cart->destroy();
                        $this->session->set_flashdata('info', lang('order_added_make_payment'));
                        if ($this->input->post('payment_method') == 'paypal') {
                            redirect('pay/paypal/' . $sale_id);
                        } elseif ($this->input->post('payment_method') == 'skrill') {
                            redirect('pay/skrill/' . $sale_id);
                        } elseif ($this->input->post('payment_method') == 'installment') {
                            redirect('shop/installment/' . $sale_id);
                        } else {
                            shop_redirect('orders/' . $sale_id . '/' . ($this->loggedIn ? '' : $data['hash']));
                        }
                    }
                }

                else {
                    $this->session->set_flashdata('error', lang('address_x_found'));
                    redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'cart/checkout');
                }
            }

            else {
                $this->session->set_flashdata('error', validation_errors());
                redirect('cart/checkout' . ($guest_checkout ? '#guest' : ''));
            }
        }



    }

    private function get_shipping_fees($region_id = ''){
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

    public function guest_order()
    {
        $guest_checkout = $this->input->post('guest_checkout');
        if (!$guest_checkout && !$this->loggedIn) {
            redirect('login');
        }
        $this->form_validation->set_rules('address', lang('address'), 'trim|required');
        $this->form_validation->set_rules('note', lang('comment'), 'trim');
        $this->form_validation->set_rules('payment_method', lang('payment_method'), 'required');
        if ($guest_checkout) {
            $this->form_validation->set_rules('name', lang('name'), 'trim|required');
            $this->form_validation->set_rules('email', lang('email'), 'trim|required|valid_email');
            $this->form_validation->set_rules('phone', lang('phone'), 'trim|required');
            $this->form_validation->set_rules('billing_line1', lang('billing_address') . ' ' . lang('line1'), 'trim|required');
            $this->form_validation->set_rules('billing_city', lang('billing_address') . ' ' . lang('city'), 'trim|required');
            $this->form_validation->set_rules('billing_country', lang('billing_address') . ' ' . lang('country'), 'trim|required');
            $this->form_validation->set_rules('shipping_line1', lang('shipping_address') . ' ' . lang('line1'), 'trim|required');
            $this->form_validation->set_rules('shipping_city', lang('shipping_address') . ' ' . lang('city'), 'trim|required');
            $this->form_validation->set_rules('shipping_country', lang('shipping_address') . ' ' . lang('country'), 'trim|required');
            $this->form_validation->set_rules('shipping_phone', lang('shipping_address') . ' ' . lang('phone'), 'trim|required');
        }
        if ($guest_checkout && $this->Settings->indian_gst) {
            $this->form_validation->set_rules('billing_state', lang('billing_address') . ' ' . lang('state'), 'trim|required');
            $this->form_validation->set_rules('shipping_state', lang('shipping_address') . ' ' . lang('state'), 'trim|required');
        }

        if ($this->form_validation->run() == true) {
            if ($guest_checkout || $address = $this->shop_model->getAddressByID($this->input->post('address') , 'row_array')) {
                $new_customer = false;
                if ($guest_checkout) {
                    $address = [
                        'phone'       => $this->input->post('shipping_phone'),
                        'line1'       => $this->input->post('shipping_line1'),
                        'line2'       => $this->input->post('shipping_line2'),
                        'city'        => $this->input->post('shipping_city'),
                        'state'       => $this->input->post('shipping_state'),
                        'postal_code' => $this->input->post('shipping_postal_code'),
                        'country'     => $this->input->post('shipping_country'),
                    ];
                }

                if ($this->input->post('address') != 'new') {
                    $customer = $this->site->getCompanyByID($this->session->userdata('company_id') , 'row_array');
                } else {
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
//                $biller      = $this->shop_model->getCompanyByEmail($this->input->post('email'));
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

                        //echo   'here we are';
                        //var_dump($product);

                        $products[] = ($product + $gst_data);
                        $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                    } else {
                        $this->session->set_flashdata('error', lang('product_x_found') . ' (' . $item['name'] . ')');
                        redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'cart');
                    }
                }

                $shipping    = $this->shop_settings->shipping;
                $order_tax   = $this->site->calculateOrderTax($this->Settings->default_tax_rate2, ($total + $product_tax));
                $total_tax   = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
                $grand_total = $this->sma->formatDecimal(($total + $total_tax + $shipping), 4);

                //echo    $shipping;

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
                    //$this->order_received($sale_id);
                   // $this->load->library('sms');
                    //$this->sms->newSale($sale_id);
                    $this->cart->destroy();
                    $this->session->set_flashdata('info', lang('order_added_make_payment'));
                    if ($this->input->post('payment_method') == 'paypal') {
                        redirect('pay/paypal/' . $sale_id);
                    } elseif ($this->input->post('payment_method') == 'skrill') {
                        redirect('pay/skrill/' . $sale_id);
                    } elseif ($this->input->post('payment_method') == 'installment') {
                        redirect('shop/installment/' . $sale_id);
                    } else {
                        shop_redirect('orders/' . $sale_id . '/' . ($this->loggedIn ? '' : $data['hash']));
                    }
                }

                if(empty($biller->id)){
                    $this->guest_order();
                }
            }
            else {
                $this->session->set_flashdata('error', lang('address_x_found'));
                redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'cart/checkout');
            }
        }
        else {
            $this->session->set_flashdata('error', validation_errors());
            redirect('cart/checkout' . ($guest_checkout ? '#guest' : ''));
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

    // Customer order/orders page
    public function orders($id = null, $hash = null, $pdf = null, $buffer_save = null)
    {
        $hash = $hash ? $hash : $this->input->get('hash', true);
//        if($hash == 'true'){
//            $hash = null;
//            $pdf = true;
//            $_GET['view']= 'get view';
//        }
        if (!$this->loggedIn && !$hash) {
            redirect('login');
        }

        if ($this->Staff) {
            admin_redirect('sales');
        }

        if(!empty($id)){
            $this->data['installment_orders'] = $this->db->query('select s.id as order_id , s.grand_total as total , s.payment_method , p.weekly_pay , pr.* from sma_sales as s join sma_installment_pay_record as pr on pr.sale_id = s.id join sma_installment_param as p on p.sale_id = s.id where p.sale_id = '.$id)->result_array();
            $this->data['payment_method'] = $this->db->get_where('sales' , ['id'=>$id])->row()->payment_method;
        }

        if ($id && !$pdf) {

            if ($order = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash])) {
                $this->data['inv']         = $order;
                $this->data['rows']        = $this->shop_model->getOrderItems($id);
                $this->data['customer']    = $this->site->getCompanyByID($order->customer_id);
                $this->data['biller']      = $this->site->getCompanyByID($order->biller_id);
                $this->data['address']     = $this->shop_model->getAddressByID($order->address_id);
                $this->data['return_sale'] = $order->return_id ? $this->shop_model->getOrder(['id' => $id]) : null;
                $this->data['return_rows'] = $order->return_id ? $this->shop_model->getOrderItems($order->return_id) : null;
                $this->data['paypal']      = $this->shop_model->getPaypalSettings();
                $this->data['skrill']      = $this->shop_model->getSkrillSettings();
                $this->data['page_title']  = lang('view_order');
                $this->data['page_desc']   = '';


                if($this->data['payment_method'] == 'installment'){
                    if($this->db->get_where('installment_pay_record',['sale_id'=>$order->id])->num_rows() == 0){
                        redirect(shop_url('installment/' . $order->id));
                    }
                }

                $this->page_construct('pages/view_order', $this->data);
            }
            else {
                $this->session->set_flashdata('error', lang('access_denied'));
                redirect('/');
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

            $html                        = $this->load->view($this->Settings->theme . '/shop/views/pages/pdf_invoice', $this->data, true);
            if ($this->input->get('view')) {
                echo $html;
                exit;
            } else {
                $name = lang('invoice') . '_' . str_replace('/', '_', $order->reference_no) . '.pdf';
                if ($buffer_save) {
                    return $this->sma->generate_pdf($html, $name, $buffer_save, $this->data['biller']->invoice_footer);
                } else {
                    $this->sma->generate_pdf($html, $name, false, $this->data['biller']->invoice_footer);
                }
            }
        }

        elseif (!$id) {
            $page   = $this->input->get('page') ? $this->input->get('page', true) : 1;
            $limit  = 10;
            $offset = ($page * $limit) - $limit;
            $this->load->helper('pagination');
            $total_rows = $this->shop_model->getOrdersCount();
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['orders']     = $this->shop_model->getOrders($limit, $offset);
            $this->data['pagination'] = pagination('shop/orders', $total_rows, $limit);
            $this->data['page_info']  = ['page' => $page, 'total' => ceil($total_rows / $limit)];
            $this->data['page_title'] = lang('my_orders');
            $this->data['page_desc']  = '';
            $this->data['payment_method'] = '';


            $this->data['installment_orders'] = $this->db->query('select s.id as order_id , s.grand_total as total , s.payment_method , p.weekly_pay , pr.* from sma_sales as s join sma_installment_pay_record as pr on pr.sale_id = s.id join sma_installment_param as p on p.sale_id = s.id where customer_id = '.$this->session->userdata('company_id'))->result_array();



            $this->page_construct('pages/orders', $this->data);
        }
    }

    // Display Page
    public function page($slug)
    {
        $page                     = $this->shop_model->getPageBySlug($slug);
        $this->data['page']       = $page;
        $this->data['page_title'] = $page->title;
        $this->data['page_desc']  = $page->description;
        $this->page_construct('pages/page', $this->data);
    }

    // Display Page
    public function product($slug)
    {
        if(is_numeric($slug)){
            $slug = $this->db->select('slug')->get_where('products',['id'=>$slug])->row()->slug;
        }
        $product = $this->shop_model->getProductBySlug($slug);

        if (!$slug || !$product) {
            $this->session->set_flashdata('error', lang('product_not_found'));
            $this->sma->md('/');
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
        $this->data['page_title'] = $product->name;
        $this->data['page_desc']  = character_limiter(strip_tags($product->product_details), 160);
        $this->page_construct('pages/view_product', $this->data);
    }

    public function preview_product($slug)
    {
        if(is_numeric($slug)){
            $slug = $this->db->select('slug')->get_where('products',['id'=>$slug])->row()->slug;
        }
        $product = $this->shop_model->getProductBySlug($slug);

        if (!$slug || !$product) {
            $this->sma->send_json(['status'=>false , 'message'=>lang('product_not_found')]);
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $product->code . '/' . $product->barcode_symbology . '/40/0') . "' alt='" . $product->code . "' class='pull-left' />";
        if ($product->type == 'combo') {
            $this->data['combo_items'] = $this->shop_model->getProductComboItems($product->id);
        }
        $this->shop_model->updateProductViews($product->id, $product->views);
        $this->data['product']        = $product;
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
        $this->data['page_title'] = $product->name;
        $this->data['page_desc']  = character_limiter(strip_tags($product->product_details), 160);

        $page = $this->load->view($this->theme.'pages/preview_product', $this->data , true);
        $this->sma->send_json([
           'title'=>'<i class="fa fa-list-alt margin-right-sm"></i>'.$product->name,
            'page'=>$page,
            'product'=>json_encode($product),
            'description'=>character_limiter(strip_tags($product->product_details), 160),
        ]);
    }

    // Products,  categories and brands page
    public function products($category_slug = null, $subcategory_slug = null, $brand_slug = null, $promo = null , $compny_id = null)
    {
        $this->session->set_userdata('requested_page', $this->uri->uri_string());
        if ($this->input->get('category')) {
            $category_slug = $this->input->get('category', true);
        }
        if ($this->input->get('brand')) {
            $brand_slug = $this->input->get('brand', true);
        }
        if ($this->input->get('promo') && $this->input->get('promo') == 'yes') {
            $promo = true;
        }
        if(!empty($compny_id)){
            if($compny_id == 'all'){
                $this->session->unset_userdata(['opened_lib','opened_lib_name','opened_logo','opened_lib_logo','opened_lib_colors']);
            }
            else{
                $where = ['warehouse_id'=>$compny_id];
                $ware_house_data    = $this->db->get_where('warehouses',['id'=>$compny_id])->row();
                $library_data       = $this->db->get_where('companies',['email'=>$ware_house_data->email])->row();
                $this->session->set_userdata('opened_lib',$compny_id);
                $this->session->set_userdata('opened_lib_name',$library_data->name);
                $this->session->set_userdata('opened_lib_logo',$library_data->logo);


                 $this->com_model->insert_if_not_exist('supplier_color_settings' , $where , ['warehouse_id'=>$compny_id]);
                 $this->com_model->insert_if_not_exist('supplier_settings' , $where , ['warehouse_id'=>$compny_id]);

            }
            redirect(base_url());
        }
        $reset = $category_slug || $subcategory_slug || $brand_slug ? true : false;

        $filters = [
            'query'       => $this->input->post('query'),
            'category'    => $category_slug ? $this->shop_model->getCategoryBySlug($category_slug) : null,
            'subcategory' => $subcategory_slug ? $this->shop_model->getCategoryBySlug($subcategory_slug) : null,
            'brand'       => $brand_slug ? $this->shop_model->getBrandBySlug($brand_slug) : null,
            'promo'       => $promo,
            'sorting'     => $reset ? null : $this->input->get('sorting'),
            'min_price'   => $reset ? null : $this->input->get('min_price'),
            'max_price'   => $reset ? null : $this->input->get('max_price'),
            'in_stock'    => $reset ? null : $this->input->get('in_stock'),
            'page'        => $this->input->get('page') ? $this->input->get('page', true) : 1,
        ];

        $this->data['filters']    = $filters;
        $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = (!empty($filters['category']) ? $filters['category']->name : (!empty($filters['brand']) ? $filters['brand']->name : lang('products'))) . ' - ' . $this->shop_settings->shop_name;
        $this->data['page_desc']  = !empty($filters['category']) ? $filters['category']->description : (!empty($filters['brand']) ? $filters['brand']->description : $this->shop_settings->products_description);
        $this->page_construct('pages/products', $this->data);
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
    public function search()
    {
        $filters           = $this->input->post('filters') ? $this->input->post('filters', true) : false;
        $limit             = 12;
        $total_rows        = $this->shop_model->getProductsCount($filters);
        $filters['limit']  = $limit;
        $filters['offset'] = isset($filters['page']) && !empty($filters['page']) && ($filters['page'] > 1) ? (($filters['page'] * $limit) - $limit) : null;

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

            $pagination = pagination('shop/products', $total_rows, $limit);
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
                    $this->sma->send_json(['status' => 'Success', 'message' => lang('message_sent')]);
                }
                $this->sma->send_json(['status' => 'error', 'message' => lang('action_failed')]);
            } catch (Exception $e) {
                $this->sma->send_json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } elseif ($this->input->is_ajax_request()) {
            $this->sma->send_json(['status' => 'Error!', 'message' => validation_errors(), 'level' => 'error']);
        } else {
            $this->session->set_flashdata('warning', 'Please try to send message from contact page!');
            shop_redirect();
        }
    }

    // Customer wishlist page
    public function wishlist()
    {
        if (!$this->loggedIn) {
            redirect('login');
        }
        $this->session->set_userdata('requested_page', $this->uri->uri_string());
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $total               = $this->shop_model->getWishlist(true);
        $products            = $this->shop_model->getWishlist();
        $this->load->helper('text');
        foreach ($products as $product) {
            $item          = $this->shop_model->getProductByID($product->product_id);
            $item->details = character_limiter(strip_tags($item->details), 140);
            $items[]       = $item;
        }
        $this->data['items']      = $products ? $items : null;
        $this->data['page_title'] = lang('wishlist');
        $this->data['page_desc']  = '';
        $this->page_construct('pages/wishlist', $this->data);
    }

    //customer pays using installment process
    public function installment($sale_id){
        if(!empty($sale_id)){
            //check the existance of the sale id in the installment_pay_recor table
            if($this->db->get_where('installment_pay_record',['sale_id'=>$sale_id])->num_rows() > 0){
                redirect('shop');
            }

            $this->data['sales'] = $this->db->get_where('sales',['id'=>$sale_id])->row();
            $this->data['sale_items'] = $this->db->get_where('sale_items',['sale_id'=>$sale_id])->result();

            $this->page_construct('pages/installment', $this->data);
        }
    }

    public function opened_bills($per_page = 0)
    {
        $this->load->helper('text');
        $this->load->library('pagination');
        if ($this->input->get('per_page')) {
            $per_page = '5';
        }

        $this->data['school_id'] = $_POST['school_id'];
        $this->data['class_id'] = $_POST['class_id'];

        $config['base_url']   = admin_url('schools/opened_bills');
        $config['total_rows'] = $this->shop_model->bills_count();
        $config['per_page']   = 6;
        $config['num_links']  = 3;

        $config['full_tag_open']   = '<ul class="pagination pagination-sm">';
        $config['full_tag_close']  = '</ul>';
        $config['first_tag_open']  = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open']   = '<li>';
        $config['last_tag_close']  = '</li>';
        $config['next_tag_open']   = '<li>';
        $config['next_tag_close']  = '</li>';
        $config['prev_tag_open']   = '<li>';
        $config['prev_tag_close']  = '</li>';
        $config['num_tag_open']    = '<li>';
        $config['num_tag_close']   = '</li>';
        $config['cur_tag_open']    = '<li class="active"><a>';
        $config['cur_tag_close']   = '</a></li>';

        $school = $this->db->get_where('schools',['id'=>$_POST['school_id']])->row();
        $class = $this->db->get_where('classes',['id'=>$_POST['class_id']])->row();

        $this->pagination->initialize($config);
        $data['r'] = true;
        $bills     = $this->shop_model->fetch_school_bills($config['per_page'], $per_page , $_POST['school_id'] , $_POST['class_id']);
        if (!empty($bills)) {
            $html = '';
            $html .= '<div>';
            foreach ($bills as $bill) {
                $html .= '
    <div style="text-align: left;border-radius: 3px !important; width: 100% !important;" type="button" class="btn btn-block btn-default">
    <h4>' . character_limiter($school->school_name , 50). '</h4>
        <strong>' . $class->level . ' : '.$bill->suspend_note.'</strong>
        <br><span class="badge" style="width: 55px; margin-right:5px ; text-align: left;">'. lang('items') . '</span> : ' . $bill->count . '
        <br><span class="badge" style="width: 55px; margin-right:5px ; text-align: left;">'. lang('total') . '</span> : ' . $this->sma->formatMoney($bill->total) . '
        <button style="border-radius:3px !important" class="btn-primary btn sus_sale pull-right" id="'.$bill->id.'"><i class="fa fa-cart-plus"></i>'.(($this->data['mobile'] == false)?' Ajouter au panier':'').'</button>
        <div class="clearfix"></div>
        <button style="border-radius:3px !important" class="btn-warning btn sus_receip pull-right" id="'.$bill->id.'"><i class="fa fa-file-text"></i>'.(($this->data['mobile'] == false)?'Voir la liste':'').'</button>
    </div>';
            }
            $html .= '</div>';
        } else {
            $html      = '<h3>' . lang('no_school_stuffs') . '</h3><p>&nbsp;</p>';
            $data['r'] = false;
        }

        $data['html'] = $html;

        $data['page'] = $this->pagination->create_links();
        echo $this->load->view($this->theme . 'schools/cart_builder/opened', $data, true);
    }

    public function delete_sale()
    {
        if(!empty($_POST['id'])){
            $this->load->admin_model('sales_model');

            $id = $_POST['id'];
            $inv = $this->sales_model->getInvoiceByID($id);
            if ($inv->sale_status == 'returned') {
                $this->sma->send_json(['status' => false, 'message' => lang('sale_x_action')]);
            }

            if ($this->sales_model->deleteSale($id)) {
                $this->sma->send_json(['status' => true, 'message' => lang('Command supprimé avec succès')]);
            }
            else{
                $this->sma->send_json(['status' => true, 'message' => lang('sale_not_deleted')]);
            }
        }
    }

    private function update(){
        $wherehouse_prod = $this->db->select('warehouses_products.product_id , companies.name as name')
                                    ->join('warehouses','warehouses.id = warehouses_products.warehouse_id')
                                    ->join('companies','companies.email = warehouses.email')
                                    ->get('warehouses_products')->result();

        foreach($wherehouse_prod as $wp){
            $this->db->where(['id'=>$wp->product_id])->update('products',['warehouse_name'=>$wp->name]);
        }
        echo 'done';
    }
    
    public function build_cart(){
        if(!empty($_POST['sus_id'])){
           $items = $this->db->select('product_id , quantity , id , suspend_id')->get_where('suspended_items',['suspend_id'=>$_POST['sus_id']])->result();
           foreach ($items as $item){
               $this->add_to_cart($item->product_id , (int)$item->quantity);
           }

           $this->sma->send_json($this->cart->cart_data(true));
        }
    }

    public function add_to_cart($product_id = '' , $quantity = '')
    {
        $_POST['quantity'] = $quantity;
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

            if (!$this->Settings->overselling && $this->checkProductStock($product, $quantity, $selected)) {

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
            $this->cart->insert($data);
        }
    }

    private function checkProductStock($product, $qty, $option_id = null)
    {

        if ($product->type == 'service' || $product->type == 'digital') {
            return false;
        }

        $chcek = [];
        if ($product->type == 'standard') {
            $quantity = 0;
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
                    $quantity = 0;
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

    public function view_cart_receip($sus_id = null, $modal = true)
    {
        if ($this->input->get('id')) {
            $sus_id = $this->input->get('id');
        }
        $suspended_sale = $this->shop_model->getOpenBillByID($sus_id);
        $this->load->helper('pos');
        $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv                   = $suspended_sale;

        $this->data['note'] = $suspended_sale->suspend_note;

        $this->data['school'] = $this->db->get_where('schools',['id'=>$suspended_sale->school_id])->row();
        $this->data['class'] = $this->db->get_where('classes',['id'=>$suspended_sale->class_id])->row();

        $this->data['rows']            = $this->shop_model->getSuspendedSaleItems($sus_id);
        $biller_id                     = $inv->biller_id;
        $customer_id                   = $inv->customer_id;
        $this->data['biller']          = $this->shop_model->getCompanyByID($biller_id);
        $this->data['customer']        = $this->shop_model->getCompanyByID($customer_id);
        $this->data['payments']        = $this->shop_model->getInvoicePayments($sus_id);
        $this->data['pos']             = $this->shop_model->getSetting();
        $this->data['barcode']         = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale']     = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : null;
        $this->data['inv']             = $inv;
        $this->data['sid']             = $sus_id;
        $this->data['modal']           = $modal;
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['printer']         = $this->shop_model->getPrinterByID($this->pos_settings->printer);
        $this->data['page_title']      = $this->lang->line('invoice');
        $this->data['sus_id'] = $sus_id;
        $this->load->view($this->theme . 'schools/cart_builder/view', $this->data);
    }

    public function barcode($text = null, $bcs = 'code128', $height = 50)
    {
        return admin_url('products/gen_barcode/' . $text . '/' . $bcs . '/' . $height);
    }

    public function qa_suggestions()
    {
        $term = $this->input->get('term', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed  = $this->sma->analyze_term($term);
        $sr        = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $rows = $this->shop_model->getQASuggestions($sr);
        if ($rows) {
            foreach ($rows as $row) {
                $row->qty    = 1;
                $options     = $this->shop_model->getProductOptions($row->id);
                $row->option = $option_id;
                $row->serial = '';
                $c           = sha1(uniqid(mt_rand(), true));
                $pr[]        = [
                    'id' => $c,
                    'item_id' => $row->id,
                    'label' => ''.$row->name . ' (' . $row->warehouse_name . ')',
                    'name' => ''.$row->name,
                    'row' => $row, 'options' => $options,
                ];
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }
    // Display Page
    public function schools()
    {
        $this->data['page_title'] = lang('schools');
        $this->data['page_desc']  = '';
        $this->data['schools'] = $this->db->get('schools')->result();
        $this->data['school_carts'] = $this->db->get('suspended_bills')->result();
        $this->page_construct('pages/schools', $this->data);
    }

    public function suppliers(){
        $this->data['page_title'] = lang('company');
        $this->data['page_desc']  = '';
        $this->data['suppliers'] = $this->db->select('warehouses.id as id , companies.id as company_id , companies.name , companies.logo')
            ->where(['group_name'=>'supplier'])
            ->join('warehouses','warehouses.email = companies.email')
            ->order_by('warehouses.ordering_id','ASC')->get('companies')->result();
        $this->page_construct('pages/suppliers', $this->data);
    }

    public function compare($product_id = ''){
        if(!empty($product_id)){
            $p = $this->db->select('id , name , category_id , subcategory_id')->get_where('products' , ['id'=>$product_id]);

            if($p->num_rows() > 0){
                $p = $p->row();
                $where = ["{$this->db->dbprefix('products')}.category_id"=>$p->category_id , "{$this->db->dbprefix('products')}.subcategory_id"=>$p->subcategory_id];
                $like = ["{$this->db->dbprefix('products')}.name" , $p->name , 'both'];
                $products = $this->shop_model->getProducts3($where , $like);

                if(count($products) > 1){
                    $this->data['products'] = $products;
                    $this->sma->send_json([
                       'status'=>true ,
                        'products'=>$this->load->view($this->theme.'compare' , $this->data , true),
                    ]);
                }

                else{
                    $this->sma->send_json([
                        'status'=>false,
                        'message'=>'Aucun produit similaire n’a été trouver'
                    ]);
                }
            }
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

    public function save_payment_param(){

        if(!empty($_POST)){
            unset($_POST['save_payment_params']);

            //var_dump($_POST);

            $this->db->insert('sma_installment_param' , $_POST);

            $this->db->insert('sma_installment_pay_record' , ['sale_id'=>$_POST['sale_id'] , 'num_of_week'=>$_POST['num_of_week']]);

            $total = (float)$_POST['weekly_pay'] * (float)$_POST['num_of_week'];
            $this->db->where(['id'=>$_POST['sale_id']])->update('sales',['grand_total'=>$this->sma->formatDecimal(($total), 4)]);

            //$this->sendEmailForInstallment($_POST['sale_id'] , true);

        }

        redirect('shop/orders');
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
                echo 'session restored <br>';
            }
            else{
                $this->sma->send_json([
                    'status'=>false,
                    'message'=>'invalid order_id, transaction not found',
                ]);
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
                    $this->db->where(['sale_id'=>$_POST['sale_id']])->delete('transaction_session');
                    $this->sma->send_json(['status'=>false, 'message'=>lang('sale_already_paid')]);
                }

                $paid_by          = $_POST['paid_by'];

                if ($this->form_validation->run() == true) {
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
                }

                if ($this->form_validation->run() == true && $this->sales_model->addPayment($payment, $customer_id)) {
                    $payment_method = $this->db->get_where('sales',['id',$_POST['sale_id']])->row()->payment_method;

//                    if ($payment_method == 'installment') {
//                        $this->sendEmailForInstallment($_POST['sale_id']);
//                    }
//                    else{
//                        $this->sendEmailForFullPayment($_POST['sale_id']);
//                    }

                    $this->db->where(['sale_id'=>$_POST['sale_id']])->delete('transaction_session');

                    return ['status'=>true, 'message'=>lang('payment_added').' : '.$_POST['amount_paid']];
                }
            }

            else {
                return ['status'=>false, 'message'=>$this->form_validation->error_as_array()];
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

    public function check_transaction_status_old(){
        if(!empty($_POST['sale_id'])){
            $session = $this->session->userdata("show_payment_${$_POST['sale_id']}");
            if(!empty($session)){
                unset($_SESSION["show_payment_${$_POST['sale_id']}"]);
                $this->sma->send_json([
                   'status'=>true,
                    'status_id'=> $session
                ]);
            }
            else{
                $this->sma->send_json([
                    'status'=>false
                ]);
            }
        }
    }

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

    public function debug($product_id = '' , $data = 'qty'){
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
