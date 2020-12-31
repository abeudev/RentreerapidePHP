<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Fetchproduct extends REST_Controller
{
    private $loggedIn;
    public function __construct()
    {
        parent::__construct();
    }

    public function alephProduct_get(){
        $api_url = $this->config->item('aleph_api_url');
        $compay_email = 'info@alephcatalogue.com';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));
        $response = curl_exec($curl);
        $response_err = curl_error($curl);

        if(empty($response_err)){
            curl_close($curl);
            $response = str_replace('"[','',$response);
            $response = str_replace(']"','',$response);
            writeJsonFile('alephProducts',$response);
        }

        $productArray = openJSONFile('alephProducts');
        $rresponse = $this->import_api_product($productArray , $compay_email);

        $this->response($rresponse);
    }

    public function import_api_product($productArray = [] , $company_email = '')
    {
        $this->load->admin_model('products_model');
        $_POST['product_group'] = '1'; //xs-item

        $rresponse = '';
        if (!empty($productArray)) {
            $arrResult = $productArray;
            $updated = 0;
            $items   = [];
            $company_data   = $this->db->get_where('companies',['email'=>$company_email])->row();
            $warehouse_data = $this->db->get_where('warehouses',['email'=>$company_email])->row();
            foreach ($arrResult as $itm) {
                $item = $itm;
                $item['category_code']      = strtolower(str_replace(' ','_',$itm['category']));
                $item['subcategory_code']   = strtolower(str_replace(' ','_',$itm['subcategory']));
                $item['cf1'] = '';
                $item['cf2'] = '';
                $item['cf3'] = '';
                $item['cf4'] = '';
                $item['cf5'] = '';
                $item['cf6'] = '';
                $item['supplier1'] = $company_data->id;
                $item['warehouse'] = $warehouse_data->id;
                $item['warehouse_name'] = ucwords($company_data->name);
                $item['code'] = 'alpc'.random_string('alnum',20);
                $item['slug'] = 'alps'.random_string('alnum',20);
                $item['type'] = 'standard';
                $item['barcode_symbology'] = 'code128';
                $item['sale_unit'] = '';
                $item['purchase_unit'] = '';
                $item['tax_rate'] = '';
                unset($item['category']);
                unset($item['subcategory']);

                if ($catd = $this->products_model->getCategoryByCode($item['category_code'])) {
                    $tax_details   = $this->products_model->getTaxRateByName($item['tax_rate']);
                    $prsubcat      = $this->products_model->getCategoryByCode($item['subcategory_code']);
                    $brand         = $this->products_model->getBrandByName($item['brand']);
                    $unit          = $this->products_model->getUnitByCode($item['unit']);
                    $base_unit     = $unit ? $unit->id : null;
                    $sale_unit     = $base_unit;
                    $purcahse_unit = $base_unit;

                    unset($item['category_code'], $item['subcategory_code']);
                    $item['unit']           = $base_unit;
                    $item['sale_unit']      = $sale_unit;
                    $item['category_id']    = $catd->id;
                    $item['purchase_unit']  = $purcahse_unit;
                    $item['brand']          = $brand ? $brand->id : null;
                    $item['tax_rate']       = $tax_details ? $tax_details->id : null;
                    $item['subcategory_id'] = $prsubcat ? $prsubcat->id : null;

                    if ($product = $this->products_model->getProductByCode($item['code'])) {
                        if ($product->type == 'standard') {
                            if ($item['variants']) {
                                $vs = explode('|', $item['variants']);
                                foreach ($vs as $v) {
                                    $variants[] = ['product_id' => $product->id, 'name' => trim($v)];
                                }
                            }
                            unset($item['variants']);
                            if ($this->products_model->updateProduct($product->id, $item, null, null, null, null, $variants)) {
                                $updated++;
                            }
                        }
                        $item = false;
                    }
                }

                if ($item) {
                    $items[] = $item;
                }
            }
        }

        if (!empty($items)) {
            $rresponse = 'items retrieved';

            $added_count = 0;
            foreach ($items as $item){
                $item['quantity'] = (int)$item['quantity'];
                $warehouse_qty = [
                    'warehouse_id'  =>$item['warehouse'],
                    'quantity'      =>$item['quantity'],
                    'rack'          =>null
                ];
                $product_items      = null;
                $product_attributes = null;
                $photos             = null;

                if ($this->products_model->addProduct($item , $product_items , $warehouse_qty , $product_attributes ,$photos)) {
                    $updated = $updated ? '<p>' . sprintf(lang('products_updated'), $updated) . '</p>' : '';
                    $added_count++;
                }
            }

            if($added_count >0){
               $rresponse = sprintf(lang('products_added'), $added_count) . '('.$added_count.')'."\n".$updated;
            }

        }

        else {
            if (isset($items) && empty($items)) {
                if ($updated) {
                    $rresponse = sprintf(lang('products_updated'), $updated);
                } else {
                    $rresponse =  lang('csv_issue');
                }
            }
        }

        return $rresponse;
    }
}
