<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Quotes extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->methods['index_get']['limit'] = 500;
        $this->load->api_model('quotes_api');
        $this->load->library('form_validation');
        $_GET['nothging_at_all_goes_here_la'] = 'tout_ce_qui_peut_gere_laffaire';
    }

    protected function setQuote($quote)
    {
        unset($quote->attachment, $quote->hash, $quote->updated_at);
        if (isset($quote->items) && !empty($quote->items)) {
            foreach ($quote->items as &$item) {
                if (isset($item->option_id) && !empty($item->option_id)) {
                    if ($variant = $this->quotes_api->getProductVariantByID($item->option_id)) {
                        $item->product_variant_id   = $variant->id;
                        $item->product_variant_name = $variant->name;
                    }
                }
                $item->product_unit_quantity = $item->unit_quantity;
                unset($item->id, $item->quote_id, $item->warehouse_id, $item->real_unit_price, $item->quote_item_id, $item->option_id, $item->unit_quantity);
                $item = (array) $item;
                ksort($item);
            }
        }
        $quote = (array) $quote;
        ksort($quote);
        return $quote;
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
            'customer_id' => $this->get('customer_id') ? $this->get('customer_id') : null,
            'customer'    => $this->get('customer') ? $this->get('customer') : null,
        ];

        if ($reference === null) {
            if ($quotes = $this->quotes_api->getQuotes($filters)) {
                $sl_data = [];
                foreach ($quotes as $quote) {
                    if (!empty($filters['include'])) {
                        foreach ($filters['include'] as $include) {
                            if ($include == 'items') {
                                $quote->items = $this->quotes_api->getQuoteItems($quote->id);
                            }
                            if ($include == 'warehouse') {
                                $quote->warehouse = $this->quotes_api->getWarehouseByID($quote->warehouse_id);
                            }
                        }
                    }

                    $quote->created_by = $this->quotes_api->getUser($quote->created_by);
                    $sl_data[]         = $this->setQuote($quote);
                }

                $data = [
                    'data'  => $sl_data,
                    'limit' => (int) $filters['limit'],
                    'start' => (int) $filters['start'],
                    'total' => $this->quotes_api->countQuotes($filters),
                ];
                $this->response($data, REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'message' => 'No quote record found.',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            if ($quote = $this->quotes_api->getQuote($filters)) {
                if (!empty($filters['include'])) {
                    foreach ($filters['include'] as $include) {
                        if ($include == 'items') {
                            $quote->items = $this->quotes_api->getQuoteItems($quote->id);
                        }
                        if ($include == 'warehouse') {
                            $quote->warehouse = $this->quotes_api->getWarehouseByID($quote->warehouse_id);
                        }
                    }
                }

                $quote->created_by = $this->quotes_api->getUser($quote->created_by);
                $quote             = $this->setQuote($quote);
                $this->set_response($quote, REST_Controller::HTTP_OK);
            } else {
                $this->set_response([
                    'message' => 'Quote could not be found for reference ' . $reference . '.',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function getQuoteItems_get(){
        $this->form_validation->set_data($_GET);
        $this->form_validation->set_rules('quote_id', 'quote_id', 'required');

        if($this->form_validation->run() == TRUE){
            $id   = (!empty($_GET['quote_id']))?$_GET['quote_id'] : '';
            $data = $this->quotes_api->getQuoteItems($id);
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
