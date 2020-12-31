<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Shop_admin_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addPage($data)
    {
        if ($this->db->insert('pages', $data)) {
            return true;
        }
        return false;
    }

    public function deletePage($id)
    {
        if ($this->db->delete('pages', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function getAllBrands()
    {
        return $this->db->get('brands')->result();
    }

    public function getAllCategories()
    {
        $this->db->where('parent_id', null)->or_where('parent_id', 0)->order_by('name');
        return $this->db->get('categories')->result();
    }

    public function getAllPages()
    {
        $q = $this->db->get('pages');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllProducts()
    {
        $this->db->select('id, LOWER(name) as name, slug');
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPageByID($id)
    {
        $q = $this->db->get_where('pages', ['id' => $id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPageBySlug($slug)
    {
        $q = $this->db->get_where('pages', ['slug' => $slug]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getShopSettings()
    {
        $q = $this->db->get('shop_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSubCategories($parent_id)
    {
        $this->db->where('parent_id', $parent_id)->order_by('name');
        return $this->db->get('categories')->result();
    }

    public function updatePage($id, $data)
    {
        if ($this->db->update('pages', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateShopSettings($data)
    {
        if ($this->db->update('shop_settings', $data, ['shop_id' => 1])) {
            return true;
        }
        return false;
    }

    public function updateSlider($data)
    {
        if ($this->db->update('shop_settings', ['slider' => json_encode($data)], ['shop_id' => 1])) {
            return true;
        }
        return false;
    }

    public function updateSupplierSlider($data)
    {
        if($this->db->get_where('supplier_slides',['user_id'=>$this->session->userdata('user_id')])->num_rows() > 0){
            if ($this->db->where(['user_id'=>$this->session->userdata('user_id')])->update('supplier_slides', ['slides' => json_encode($data)])) {
                return true;
            }
        }
        else{
            $this->db->insert('supplier_slides',['warehouse_id'=>$this->session->userdata('warehouse_id'),'user_id'=>$this->session->userdata('user_id') , 'slides'=>json_encode($data)]);
            return true;
        }

        return false;
    }

    public function updateSmsSettings($data)
    {
        if ($this->db->update('sms_settings', $data, ['id' => 1])) {
            return true;
        }
        return false;
    }

    public function get_week_deals ($where = array() , $order = 'ASC')
    {
        $this->session->set_userdata('deal_limit','all');
        return
            $this->db->select("week_deals.* , gallery.g_item_id , gallery.picture_name , predefine_sub_category.s_c_id , predefine_sub_category.sub_category_name ")
                ->where('week_deals.ending_time >= now()')
                ->where('week_deals.total_sold < available_quantity')
                ->order_by('week_deal_id',$order)
                ->join("gallery", "week_deals.g_item_id = gallery.g_item_id")
                ->join("predefine_sub_category", "week_deals.s_c_id = predefine_sub_category.s_c_id")
                ->get('week_deals');

    }
}
