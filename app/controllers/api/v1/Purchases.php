<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Purchases extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->methods['index_get']['limit'] = 500;
        $this->load->api_model('purchases_api');
        $this->load->library('form_validation');
        $_GET['nothging_at_all_goes_here_la'] = 'tout_ce_qui_peut_gere_laffaire';

    }

    protected function setPurchase($purchase)
    {
        unset($purchase->attachment, $purchase->updated_at, $purchase->purchase_id, $purchase->return_id, $purchase->return_purchase_ref, $purchase->return_purchase_total);
        if (isset($purchase->items) && !empty($purchase->items)) {
            foreach ($purchase->items as &$item) {
                if (isset($item->option_id) && !empty($item->option_id)) {
                    if ($variant = $this->purchases_api->getProductVariantByID($item->option_id)) {
                        $item->product_variant_id   = $variant->id;
                        $item->product_variant_name = $variant->name;
                    }
                }
                $item->product_unit_quantity = $item->unit_quantity;
                unset($item->id, $item->date, $item->transfer_id, $item->quantity_balance, $item->quantity_received, $item->purchase_id, $item->warehouse_id, $item->real_unit_cost, $item->supplier_part_no, $item->purchase_item_id, $item->option_id, $item->unit_quantity);
                $item = (array) $item;
                ksort($item);
            }
        }
        $purchase = (array) $purchase;
        ksort($purchase);
        return $purchase;
    }

    public function index_get()
    {
        $reference = $this->get('reference');

        $filters = [
            'reference'   => $reference,
            'include'     => $this->get('include') ? explode(',', $this->get('include')) : null,
            'start'       => $this->get('start') && is_numeric($this->get('start')) ? $this->get('start') : 1,
            'limit'       => $this->get('limit') && is_numeric($this->get('limit')) ? $this->get('limit') : 10,
            'start_date'  => $this->get('start_date') && is_numeric($this->get('start_date')) ? $this->get('start_date') : null,
            'end_date'    => $this->get('end_date') && is_numeric($this->get('end_date')) ? $this->get('end_date') : null,
            'order_by'    => $this->get('order_by') ? explode(',', $this->get('order_by')) : ['id', 'decs'],
            'supplier_id' => $this->get('supplier_id') ? $this->get('supplier_id') : null,
            'customer'    => $this->get('customer') ? $this->get('customer') : null,
        ];

        if ($reference === null) {
            if ($purchases = $this->purchases_api->getPurchases($filters)) {
                $sl_data = [];
                foreach ($purchases as $purchase) {
                    if (!empty($filters['include'])) {
                        foreach ($filters['include'] as $include) {
                            if ($include == 'items') {
                                $purchase->items = $this->purchases_api->getPurchaseItems($purchase->id);
                            }
                            if ($include == 'warehouse') {
                                $purchase->warehouse = $this->purchases_api->getWarehouseByID($purchase->warehouse_id);
                            }
                        }
                    }

                    $purchase->created_by = $this->purchases_api->getUser($purchase->created_by);
                    $sl_data[]            = $this->setPurchase($purchase);
                }

                $data = [
                    'data'  => $sl_data,
                    'limit' => (int) $filters['limit'],
                    'start' => (int) $filters['start'],
                    'total' => $this->purchases_api->countPurchases($filters),
                ];
                $this->response($data, REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'message' => 'No purchase record found.',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            if ($purchase = $this->purchases_api->getPurchase($filters)) {
                if (!empty($filters['include'])) {
                    foreach ($filters['include'] as $include) {
                        if ($include == 'items') {
                            $purchase->items = $this->purchases_api->getPurchaseItems($purchase->id);
                        }
                        if ($include == 'warehouse') {
                            $purchase->warehouse = $this->purchases_api->getWarehouseByID($purchase->warehouse_id);
                        }
                    }
                }

                $purchase->created_by = $this->purchases_api->getUser($purchase->created_by);
                $purchase             = $this->setPurchase($purchase);
                $this->set_response($purchase, REST_Controller::HTTP_OK);
            } else {
                $this->set_response([
                    'message' => 'Purchase could not be found for reference ' . $reference . '.',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function countPurchases_get(){
        $supplier_id    = (!empty($_GET['supplier_id']))?$_GET['supplier_id'] : '';
        $start_date     = (!empty($_GET['start_date']))?$_GET['start_date'] : '';
        $end_date       = (!empty($_GET['end_date']))?$_GET['end_date'] : '';

        $where = [];
        if(!empty($supplier_id)){$where['supplier_id']  = $supplier_id;}
        if(!empty($start_date)){$where['start_date']    = $start_date;}
        if(!empty($end_date)){$where['end_date']        = $end_date;}

        $data = $this->purchases_api->countPurchases($where);
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

    public function getPurchaseItems_get(){
        $this->form_validation->set_data($_GET);
        $this->form_validation->set_rules('purchase_id', 'purchase_id', 'required');

        if($this->form_validation->run() == TRUE){
            $id   = (!empty($_GET['purchase_id']))?$_GET['purchase_id'] : '';
            $data = $this->purchases_api->getPurchaseItems($id);
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
