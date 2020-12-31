<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Products extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->methods['index_get']['limit'] = 500;
        $this->load->api_model('products_api');
        $this->load->library('form_validation');
        $_GET['nothging_at_all_goes_here_la'] = 'tout_ce_qui_peut_gere_laffaire';

    }

    protected function setProduct($product)
    {
        $product->tax_rate       = $this->products_api->getTaxRateByID($product->tax_rate);
        $product->unit           = $this->products_api->getProductUnit($product->unit);
        $ctax                    = $this->site->calculateTax($product, $product->tax_rate);
        $product->price          = $this->sma->formatDecimal($product->price);
        $product->net_price      = $this->sma->formatDecimal($product->tax_method ? $product->price : $product->price - $ctax['amount']);
        $product->unit_price     = $this->sma->formatDecimal($product->tax_method ? $product->price + $ctax['amount'] : $product->price);
        $product->tax_method     = $product->tax_method ? 'exclusive' : 'inclusive';
        $product->tax_rate->type = $product->tax_rate->type ? 'percentage' : 'fixed';
        $product                 = (array) $product;
        ksort($product);
        return $product;
    }

    public function index_get()
    {
        $code = $this->get('code');

        $filters = [
            'id'       => $this->get('id'),
            'code'     => $code,
            'include'  => $this->get('include') ? explode(',', $this->get('include')) : null,
            'start'    => $this->get('start') && is_numeric($this->get('start')) ? $this->get('start') : 1,
            'limit'    => $this->get('limit') && is_numeric($this->get('limit')) ? $this->get('limit') : 10,
            'order_by' => $this->get('order_by') ? explode(',', $this->get('order_by')) : ['code', 'acs'],
            'brand'    => $this->get('brand_code') ? $this->get('brand_code') : null,
            'category' => $this->get('category_code') ? $this->get('category_code') : null,
        ];

        if ($code === null) {
            if ($products = $this->products_api->getProducts($filters)) {
                $pr_data = [];
                foreach ($products as $product) {
                    if (!empty($filters['include'])) {
                        foreach ($filters['include'] as $include) {
                            if ($include == 'brand') {
                                $product->brand = $this->products_api->getBrandByID($product->brand);
                            } elseif ($include == 'category') {
                                $product->category = $this->products_api->getCategoryByID($product->category);
                            } elseif ($include == 'photos') {
                                $product->photos = $this->products_api->getProductPhotos($product->id);
                            } elseif ($include == 'sub_units') {
                                $product->sub_units = $this->products_api->getSubUnits($product->unit);
                            }
                        }
                    }

                    $pr_data[] = $this->setProduct($product);
                }

                $data = [
                    'data'  => $pr_data,
                    'limit' => $filters['limit'],
                    'start' => $filters['start'],
                    'total' => $this->products_api->countProducts($filters),
                ];
                $this->response($data, REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'message' => 'No product were found.',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);

            }
        }
        else {
            if ($product = $this->products_api->getProduct($filters)) {
                if (!empty($filters['include'])) {
                    foreach ($filters['include'] as $include) {
                        if ($include == 'brand') {
                            $product->brand = $this->products_api->getBrandByID($product->brand);
                        } elseif ($include == 'category') {
                            $product->category = $this->products_api->getCategoryByID($product->category);
                        } elseif ($include == 'photos') {
                            $product->photos = $this->products_api->getProductPhotos($product->id);
                        } elseif ($include == 'sub_units') {
                            $product->sub_units = $this->products_api->getSubUnits($product->unit);
                        }
                    }
                }

                $product = $this->setProduct($product);
                $this->set_response($product, REST_Controller::HTTP_OK);
            } else {
                $this->set_response([
                    'message' => 'Product could not be found for code ' . $code . '.',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function getProductDetails_get($slug , $user_id = '')
    {
        $this->load->shop_model('shop_model');
        if(is_numeric($slug)){
            $slug = $this->db->select('slug')->get_where('products',['id'=>$slug])->row()->slug;
        }
        $product = $this->shop_model->getProductDetails($slug);

        if (!$slug || !$product) {
           $this->response(
               [
                   'status'=>false,
                   'message'=> lang('product_not_found'),
               ]
           );
           return false;
        }


        if ($product->type == 'combo') {
            $this->data['combo_items'] = $this->shop_model->getProductComboItems($product->id);
        }
        $this->shop_model->updateProductViews($product->id, $product->views);


        $this->data['product']        = $product;
        $this->data['unit']           = $this->site->getUnitByID($product->unit);
        $this->data['brand']          = $this->site->getBrandByID($product->brand);
        $this->data['category']       = $this->site->getCategoryByID($product->category_id);
        $this->data['subcategory']    = $product->subcategory_id ? $this->site->getCategoryByID($product->subcategory_id) : null;
        $this->data['options']        = $this->shop_model->getProductOptionsWithWH($product->id);
        $this->data['variants']       = $this->shop_model->getProductOptions($product->id);
        $this->data['images']         = $this->products_api->getProductPhotos($product->id);

        if(!empty($user_id)){
            $this->data['in_wishlist']    = $this->db->get_where('wishlist',['user_id'=>$user_id , 'product_id'=>$product->id])->num_rows() > 0;
        }


//        $this->load->helper('text');
        $this->response($this->data);
        return true;
    }

    public function getOrthersProduct_get($slug){
        $this->load->shop_model('shop_model');
        if(is_numeric($slug)){
            $slug = $this->db->select('slug')->get_where('products',['id'=>$slug])->row()->slug;
        }
        $product = $this->shop_model->getProductBySlug($slug);
        $product = $this->shop_model->getOtherProducts($product->id, $product->category_id, $product->brand);
        $this->response([
            'status'=>true,
            'products'=>$product
        ]);
    }

    public function getBrands_get(){
        $id   = (!empty($_GET['id']))?$_GET['id'] : '';
        $code = (!empty($_GET['code']))?$_GET['code'] : '';
        $slug = (!empty($_GET['slug']))?$_GET['slug'] : '';
        $where = [];
        if(!empty($id)){$where['id'] = $id;}
        if(!empty($code)){$where['code'] = $code;}
        if(!empty($slug)){$where['slug'] = $slug;}

        $brand = $this->products_api->get_data('brands',$where);
        if(!empty($brand)){
            $this->response($brand, REST_Controller::HTTP_OK);
        }
        else{
            $this->response([
               'status'=>'false',
                'message'=> 'item not found'
            ]);
        }

    }

    public function getCategories_get(){
        $id   = (!empty($_GET['id']))?$_GET['id'] : '';
        $code = (!empty($_GET['code']))?$_GET['code'] : '';
        $slug = (!empty($_GET['slug']))?$_GET['slug'] : '';
        $where = [];
        if(!empty($id)){$where['id'] = $id;}
        if(!empty($code)){$where['code'] = $code;}
        if(!empty($slug)){$where['slug'] = $slug;}

        $data = $this->products_api->get_data('categories',$where);
        if(!empty($data)){
            $this->response($data, REST_Controller::HTTP_OK);
        }
        else{
            $this->response([
                'status'=>'false',
                'message'=> 'item not found'
            ]);
        }

    }

    public function getProductPhotos_get(){
        $this->form_validation->set_data($_GET);
        $this->form_validation->set_rules('product_id', 'product_id', 'required');

        if($this->form_validation->run() == TRUE){
            $id   = (!empty($_GET['product_id']))?$_GET['product_id'] : '';
            $data = $this->products_api->getProductPhotos($id);
            if(!empty($data)){
                $this->response($data, REST_Controller::HTTP_OK);
            }
            else{
                $this->response([
                    'status'=>'false',
                    'message'=> 'item not found'
                ]);
            }
        }
        else{
            $respStatus = REST_Controller::HTTP_BAD_REQUEST;
            $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()], $respStatus);
        }
    }

    public function getProductUnit_get(){
        $id   = (!empty($_GET['id']))?$_GET['id'] : '';
        $code = (!empty($_GET['code']))?$_GET['code'] : '';
        $name = (!empty($_GET['name']))?$_GET['name'] : '';
        $where = [];
        if(!empty($id)){$where['id'] = $id;}
        if(!empty($code)){$where['code'] = $code;}
        if(!empty($name)){$where['name'] = $name;}

        $data = $this->products_api->get_data('units',$where);
        if(!empty($data)){
            $this->response($data, REST_Controller::HTTP_OK);
        }
        else{
            $this->response([
                'status'=>'false',
                'message'=> 'item not found'
            ]);
        }

    }

    public function getProductVariant_get(){
        $this->form_validation->set_data($_GET);
        $this->form_validation->set_rules('product_id', 'product_id', 'required');

        if($this->form_validation->run() == TRUE){
            $id   = (!empty($_GET['product_id']))?$_GET['product_id'] : '';
            $data = $this->products_api->getProductVariantByID($id);
            if(!empty($data)){
                $this->response($data, REST_Controller::HTTP_OK);
            }
            else{
                $this->response([
                    'status'=>'false',
                    'message'=> 'item not found'
                ]);
            }
        }
        else{
            $respStatus = REST_Controller::HTTP_BAD_REQUEST;
            $this->response(['status'=>REST_Controller::HTTP_BAD_REQUEST , 'message'=>$this->form_validation->error_as_array()], $respStatus);
        }
    }

}
