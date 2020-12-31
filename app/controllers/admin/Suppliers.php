<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Suppliers extends MY_Controller
{
    private $sup_settings_table     = 'supplier_settings';
    private $sup_font_size_table    = 'supplier_font_size';
    private $warehouse_id;
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->lang->admin_load('suppliers', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('companies_model');
        $this->load->admin_model('settings_model');

        $this->lang->admin_load('front_end', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('shop_admin_model');

        $this->warehouse_id = $this->session->userdata('warehouse_id');

    }

    public function add()
    {
        $this->sma->checkPermissions(false, true);

        $this->form_validation->set_rules('email', $this->lang->line('email_address'), 'is_unique[companies.email]');
        $this->form_validation->set_rules('password', $this->lang->line('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', $this->lang->line('confirm_password'), 'required');

        $save_user = false;
        if ($this->form_validation->run('companies/add') == true) {

            $photo = 'unknow.png';

            if ($_FILES['logo']['size'] > 0) {
                $this->load->library('upload');

                $config['upload_path']   = 'assets/uploads/company_logo';
                $config['allowed_types'] = 'gif|jpg|png';
                $config['max_size'] = '500';
                $config['overwrite']    = false;
                $config['encrypt_name'] = true;
                $config['max_filename'] = 25;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload('logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                }

                $photo = $this->upload->file_name;

                $this->load->helper('file');
                $this->load->library('image_lib');

                $config['image_library']  = 'gd2';
                $config['source_image']   = 'assets/uploads/company_logo/' . $photo;
                $config['new_image']      = 'assets/uploads/company_logo/thumbs/' . $photo;
                $config['maintain_ratio'] = true;
                $config['width']          = 150;
                $config['height']         = 150;

                $this->image_lib->clear();
                $this->image_lib->initialize($config);

                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
            }

            $email = strtolower($_POST['email']);
            $company_name = $_POST['company'];

            $data = [
                'company'     => $company_name,
                'name'        => $company_name,
                'email'       => $email,
                'phone'       => $this->input->post('phone'),
                'address'     => $this->input->post('address'),
                'city'        => $this->input->post('city'),
                'state'       => $this->input->post('state'),
                'country'     => 'Côte d’ivoire',
                'logo'        =>  $photo,
                'group_id'    => '4',
                'group_name'  => 'supplier',
            ];
            $save_user = true;
        }

        elseif ($this->input->post('add_supplier'))
        {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('suppliers');
        }

        if ($this->form_validation->run() == true && $sid = $this->companies_model->addCompany($data)) {

            $company = $this->companies_model->getCompanyByID($sid);

            if($save_user){

                $wcode = random_string('alnum',27);
                $ware_house_data = [
                    'code'           => $wcode,
                    'name'           => strtoupper($company_name).' '.'ENTREPÔT',
                    'phone'          => $this->input->post('phone'),
                    'email'          => $email,
                    'address'        => $this->input->post('address'),
                    'ordering_id'    =>$_POST['ordering'],
                    'price_group_id' => '1',
                    'map'            => null,
                    ];

                $this->db->insert('warehouses', $ware_house_data);
                $ware_house_id = $this->db->get_where('warehouses',['code'=>$wcode])->row()->id;


                $active                  = '1';
                $notify                  = '0';
                $username                = $this->input->post('username');
                $password                = $this->input->post('password');
                $additional_data         = [
                    'first_name' => $this->input->post('first_name'),
                    'last_name'  => $this->input->post('last_name'),
                    'phone'      => $this->input->post('phone'),
                    'gender'     => 'male',
                    'company_id' => $company->id,
                    'company'    => $company_name,
                    'group_id'   => 6,
                    'warehouse_id'=>$ware_house_id,
                    'sup_id'     => $company->id,
                ];
                $this->load->library('ion_auth');

                if ($this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
                    $this->session->set_flashdata('message', $this->lang->line('user_added'));
                }
            }

            $this->session->set_flashdata('message', $this->lang->line('supplier_added'));
            $ref = isset($_SERVER['HTTP_REFERER']) ? explode('?', $_SERVER['HTTP_REFERER']) : null;
            admin_redirect($ref[0] . '?supplier=' . $sid);
        }

        else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suppliers/add', $this->data);
        }
    }

    public function add_user($company_id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        $this->form_validation->set_rules('email', $this->lang->line('email_address'), 'is_unique[users.email]');
        $this->form_validation->set_rules('password', $this->lang->line('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', $this->lang->line('confirm_password'), 'required');

        if ($this->form_validation->run('companies/add_user') == true)
        {
            $active                  = $this->input->post('status');
            $notify                  = $this->input->post('notify');
            list($username, $domain) = explode('@', $this->input->post('email'));
            $email                   = strtolower($this->input->post('email'));
            $password                = $this->input->post('password');
            $additional_data         = [
                'first_name' => $this->input->post('first_name'),
                'last_name'  => $this->input->post('last_name'),
                'phone'      => $this->input->post('phone'),
                'gender'     => $this->input->post('gender'),
                'company_id' => $company->id,
                'company'    => $company->company,
                'group_id'   => 6,
            ];
            $this->load->library('ion_auth');
        }
        elseif ($this->input->post('add_user'))
        {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('suppliers');
        }

        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
            $this->session->set_flashdata('message', $this->lang->line('user_added'));
            admin_redirect('suppliers');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company']  = $company;
            $this->load->view($this->theme . 'suppliers/add_user', $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $company_details = $this->companies_model->getCompanyByID($id);
        $this->db->where(['email'=>$company_details->email])->delete('warehouses');
        if ($this->companies_model->deleteSupplier($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('supplier_deleted')]);
        } else {
            $this->sma->send_json(['error' => 1, 'msg' => lang('supplier_x_deleted_have_purchases')]);
        }
    }

    public function edit($id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $company_details = $this->companies_model->getCompanyByID($id);

        $previous_logo = $company_details->logo;

        if ($this->input->post('email') != $company_details->email) {
            $this->form_validation->set_rules('code', lang('email_address'), 'is_unique[companies.email]');
        }

        if ($this->form_validation->run('companies/add') == true) {

            $photo = $previous_logo;
            if ($_FILES['logo']['size'] > 0) {
                $this->load->library('upload');

                $config['upload_path']   = 'assets/uploads/company_logo';
                $config['allowed_types'] = 'gif|jpg|png';
                $config['max_size'] = '500';
                $config['overwrite']    = true;
                $config['encrypt_name'] = true;
                $config['max_filename'] = 25;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload('logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                }

                $photo = $this->upload->file_name;

                $this->load->helper('file');
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = 'assets/uploads/company_logo/' . $photo;
                $config['new_image']      = 'assets/uploads/company_logo/thumbs/' . $photo;
                $config['maintain_ratio'] = true;
                $config['width']          = 150;


                $this->image_lib->clear();
                $this->image_lib->initialize($config);

                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
            }

            $email = strtolower($_POST['email']);
            $company_name = $_POST['company'];
            $old_email = $this->db->get_where('companies',['id'=>$id])->row()->email;

            $data = [
                'company'     => $company_name,
                'name'        => $company_name,
                'email'       => $email,
                'phone'       => $this->input->post('phone'),
                'address'     => $this->input->post('address'),
                'city'        => $this->input->post('city'),
                'state'       => $this->input->post('state'),
                'logo'        =>  $photo,
            ];

            if($photo != $previous_logo){
                unlink('assets/uploads/company_logo/' . $previous_logo);
                unlink('assets/uploads/company_logo/thumbs/' . $previous_logo);
            }

        }

        elseif ($this->input->post('edit_supplier')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {

            $warehouse = $this->db->get_where('warehouses',['email'=>$old_email]);
            if($warehouse->num_rows() > 0){
                $warehouse_id = $warehouse->row()->id;

                $ware_house_data = [
                    'name'           => strtoupper($company_name).' '.'ENTREPÔT',
                    'phone'          => $this->input->post('phone'),
                    'email'          => $email,
                    'address'        => $this->input->post('address'),
                    'ordering_id'    =>$_POST['ordering'],
                ];

                $this->db->where(['id'=>$warehouse_id])->update('warehouses',$ware_house_data);
                $this->db->where(['warehouse'=>$warehouse_id])->update('products',['warehouse_name'=>$company_name]);

                if(!empty($_POST['user_id'])){
                    $user_data = [
                        'first_name'=>$_POST['first_name'],
                        'last_name'=>$_POST['last_name'],
                        'email'=>$_POST['email'],
                        'phone'      => $_POST['phone'],
                        'company'    => $company_name,
                    ];
                    $this->db->where(['id'=>$_POST['user_id']])->update('users',$user_data);
                }
            }

            $this->session->set_flashdata('message', $this->lang->line('supplier_updated').' (Nom : '.$company_name.')');
            redirect($_SERVER['HTTP_REFERER']);
        }
        else {
            $this->data['company'] = $company_details;
            $this->data['user'] = $this->db->select('first_name , last_name, username,id')->where(['email'=>$company_details->email])->get('users')->row();
            $this->data['warehouse'] = $this->db->where(['email'=>$company_details->email])->get('warehouses')->row();

            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suppliers/edit', $this->data);
        }
    }

    public function getSupplier($id = null)
    {
        // $this->sma->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        $this->sma->send_json([['id' => $row->id, 'text' => $row->company]]);
    }

    public function getSuppliers()
    {
        $this->sma->checkPermissions('index');

        $this->load->library('datatables');
        $this->datatables
            ->select('companies.id as id, ordering_id,company, companies.name, companies.email, companies.phone, city, country, vat_no, gst_no')
            ->from('companies')
            ->join('warehouses','warehouses.email = companies.email')
            ->where('group_name', 'supplier')
            ->add_column('Actions', "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line('list_products') . "' href='" . admin_url('products?supplier=$1') . "'><i class=\"fa fa-list\"></i></a> <a class=\"tip\" title='" . $this->lang->line('edit_supplier') . "' href='" . admin_url('suppliers/edit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line('delete_supplier') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('suppliers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash\"></i></a></div>", 'id');
//        ->unset_column('id');
        echo $this->datatables->generate();
    }

    public function import_by_csv()
    {
        $this->sma->checkPermissions('add', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('csv_file', $this->lang->line('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (DEMO) {
                $this->session->set_flashdata('warning', $this->lang->line('disabled_in_demo'));
                redirect($_SERVER['HTTP_REFERER']);
            }

            if (isset($_FILES['csv_file'])) {
                $this->load->library('upload');

                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = '2000';
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload('csv_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('suppliers');
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5001, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles  = array_shift($arrResult);
                $rw      = 2;
                $updated = '';
                $data    = [];
                foreach ($arrResult as $key => $value) {
                    $supplier = [
                        'company'     => isset($value[0]) ? trim($value[0]) : '',
                        'name'        => isset($value[1]) ? trim($value[1]) : '',
                        'email'       => isset($value[2]) ? trim($value[2]) : '',
                        'phone'       => isset($value[3]) ? trim($value[3]) : '',
                        'address'     => isset($value[4]) ? trim($value[4]) : '',
                        'city'        => isset($value[5]) ? trim($value[5]) : '',
                        'state'       => isset($value[6]) ? trim($value[6]) : '',
                        'postal_code' => isset($value[7]) ? trim($value[7]) : '',
                        'country'     => isset($value[8]) ? trim($value[8]) : '',
                        'vat_no'      => isset($value[9]) ? trim($value[9]) : '',
                        'gst_no'      => isset($value[10]) ? trim($value[10]) : '',
                        'cf1'         => isset($value[11]) ? trim($value[11]) : '',
                        'cf2'         => isset($value[12]) ? trim($value[12]) : '',
                        'cf3'         => isset($value[13]) ? trim($value[13]) : '',
                        'cf4'         => isset($value[14]) ? trim($value[14]) : '',
                        'cf5'         => isset($value[15]) ? trim($value[15]) : '',
                        'cf6'         => isset($value[16]) ? trim($value[16]) : '',
                        'group_id'    => 4,
                        'group_name'  => 'supplier',
                    ];

                    if (empty($supplier['company']) || empty($supplier['name']) || empty($supplier['email'])) {
                        $this->session->set_flashdata('error', lang('company') . ', ' . lang('name') . ', ' . lang('email') . ' ' . lang('are_required') . ' (' . lang('line_no') . ' ' . $rw . ')');
                        admin_redirect('suppliers');
                    } else {
                        if ($this->Settings->indian_gst && empty($supplier['state'])) {
                            $this->session->set_flashdata('error', lang('state') . ' ' . lang('is_required') . ' (' . lang('line_no') . ' ' . $rw . ')');
                            admin_redirect('suppliers');
                        }
                        if ($supplier_details = $this->companies_model->getCompanyByEmail($supplier['email'])) {
                            if ($supplier_details->group_id == 4) {
                                $updated .= '<p>' . lang('supplier_updated') . ' (' . $supplier['email'] . ')</p>';
                                $this->companies_model->updateCompany($supplier_details->id, $supplier);
                            }
                        } else {
                            $data[] = $supplier;
                        }
                        $rw++;
                    }
                }

                // $this->sma->print_arrays($data, $updated);
            }
        }

        elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('suppliers');
        }

        if ($this->form_validation->run() == true && !empty($data)) {
            if ($this->companies_model->addCompanies($data)) {
                $this->session->set_flashdata('message', $this->lang->line('suppliers_added') . $updated);
                admin_redirect('suppliers');
            }
        } else {

            if (isset($data) && empty($data)) {
                if ($updated) {
                    $this->session->set_flashdata('message', $updated);
                } else {
                    $this->session->set_flashdata('warning', lang('data_x_suppliers'));
                }
                admin_redirect('suppliers');
            }

            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suppliers/import', $this->data);
        }
    }

    public function index($action = null)
    {
        $this->sma->checkPermissions();

        $this->data['error']  = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc                   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('suppliers')]];
        $meta                 = ['page_title' => lang('suppliers'), 'bc' => $bc];
        $this->page_construct('suppliers/index', $meta, $this->data);
    }

    public function suggestions($term = null, $limit = null)
    {
        // $this->sma->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', true);
        }
        $limit           = $this->input->get('limit', true);
        $rows['results'] = $this->companies_model->getSupplierSuggestions($term, $limit);
        $this->sma->send_json($rows);
    }

    public function supplier_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->companies_model->deleteSupplier($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('suppliers_x_deleted_have_purchases'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line('suppliers_deleted'));
                    }
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('email'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('phone'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('address'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('city'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('state'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('postal_code'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('country'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('vat_no'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('gst_no'));
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('scf1'));
                    $this->excel->getActiveSheet()->SetCellValue('M1', lang('scf2'));
                    $this->excel->getActiveSheet()->SetCellValue('N1', lang('scf3'));
                    $this->excel->getActiveSheet()->SetCellValue('O1', lang('scf4'));
                    $this->excel->getActiveSheet()->SetCellValue('P1', lang('scf5'));
                    $this->excel->getActiveSheet()->SetCellValue('Q1', lang('scf6'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $customer = $this->site->getCompanyByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $customer->company);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $customer->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $customer->email);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $customer->phone);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $customer->address);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $customer->city);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $customer->state);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $customer->postal_code);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $customer->country);
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $customer->vat_no);
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, $customer->gst_no);
                        $this->excel->getActiveSheet()->SetCellValue('L' . $row, $customer->cf1);
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, $customer->cf2);
                        $this->excel->getActiveSheet()->SetCellValue('N' . $row, $customer->cf3);
                        $this->excel->getActiveSheet()->SetCellValue('O' . $row, $customer->cf4);
                        $this->excel->getActiveSheet()->SetCellValue('P' . $row, $customer->cf5);
                        $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $customer->cf6);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'suppliers_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_supplier_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function users($company_id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }

        $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company']  = $this->companies_model->getCompanyByID($company_id);
        $this->data['users']    = $this->companies_model->getCompanyUsers($company_id);
        $this->load->view($this->theme . 'suppliers/users', $this->data);
    }

    public function view($id = null)
    {
        $this->sma->checkPermissions('index', true);
        $this->data['error']    = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['supplier'] = $this->companies_model->getCompanyByID($id);
        $this->load->view($this->theme . 'suppliers/view', $this->data);
    }

    public function import_csv()
    {
        if(!empty($_POST)){
            $this->load->admin_model('auth_model');
            $this->load->library('ion_auth');
            $this->sma->checkPermissions('add', true);
            $this->load->helper('security');
            $this->form_validation->set_rules('csv_file', $this->lang->line('upload_file'), 'xss_clean');

            if ($this->form_validation->run() == true) {
                if (isset($_FILES['csv_file'])) {
                    $this->load->library('upload');
                    $config['upload_path']   = 'files/';
                    $config['allowed_types'] = 'csv';
                    $config['max_size']      = '2000';
                    $config['overwrite']     = false;
                    $config['encrypt_name']  = true;

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload('csv_file')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect('suppliers');
                    }

                    $csv = $this->upload->file_name;
                    $arrResult = [];
                    $handle    = fopen('files/' . $csv, 'r');
                    if ($handle) {
                        while (($row = fgetcsv($handle, 5001, ',')) !== false) {
                            $arrResult[] = $row;
                        }
                        fclose($handle);
                    }
                    $titles  = array_shift($arrResult);
                    $rw      = 2;
                    $updated = '';
                    $data    = [];
                    $errors = '';
                    $user_count = 0;
                    foreach ($arrResult as $key => $value) {
                        $supplier = [
                            'company'     => isset($value[0]) ? trim($value[0]) : '',
                            'name'        => isset($value[1]) ? trim($value[1]) : '',
                            'email'       => isset($value[2]) ? trim($value[2]) : '',
                            'phone'       => isset($value[3]) ? trim($value[3]) : '',
                            'username'    => isset($value[4]) ? trim($value[4]) : '',
                            'password'    => isset($value[5]) ? trim($value[5]) : '',
                            'address'     => isset($value[6]) ? trim($value[6]) : '',
                            'city'        => isset($value[7]) ? trim($value[7]) : '',
                            'state'       => isset($value[8]) ? trim($value[8]) : '',
                            'postal_code' => isset($value[9]) ? trim($value[9]) : '',
                            'country'     => isset($value[10]) ? trim($value[10]) : '',
                            'vat_no'      => isset($value[11]) ? trim($value[11]) : '',
                            'gst_no'      => isset($value[12]) ? trim($value[12]) : '',
                            'cf1'         => isset($value[13]) ? trim($value[13]) : '',
                            'cf2'         => isset($value[14]) ? trim($value[14]) : '',
                            'cf3'         => isset($value[15]) ? trim($value[15]) : '',
                            'cf4'         => isset($value[16]) ? trim($value[16]) : '',
                            'cf5'         => isset($value[17]) ? trim($value[17]) : '',
                            'cf6'         => isset($value[18]) ? trim($value[18]) : '',
                            'group_id'    => 4,
                            'password_confirm'    => isset($value[5]) ? trim($value[5]) : '',
                            'group_name'  => 'supplier',
                        ];
                        $this->form_validation->set_data($supplier);
                        $this->form_validation->set_value('company','Companie','required');
                        $this->form_validation->set_value('name','Nom','required');
                        $this->form_validation->set_value('email','email','required');
                        $this->form_validation->set_value('username','username','required');
                        $this->form_validation->set_value('password','password','required');
                        if ($this->form_validation->run() == TRUE) {

                            //supplier already exist
                            if ($supplier_details = $this->companies_model->getCompanyByEmail($supplier['email'])){
                                if ($supplier_details->group_id == 4) {
                                    $user = $this->db->get_where('users',['company'=>$supplier['company']]);
                                    //update user details
                                    if($user->num_rows() > 0){
                                        $user = $user->row();
                                        $user_data = [
                                            'first_name'=>$supplier['name'],
                                            'email'=>$supplier['email'],
                                            'phone'=>$supplier['phone'],
                                            'username'=>$supplier['username'],
                                            'password'=> $this->ion_auth->get_new_password($supplier['password'] , $user->salt),
                                            'group_id'=>'6',
                                        ];
                                        $this->db->where(['id'=>$user->id])->update('users',$user_data);
                                    }

                                    unset($supplier['username']);
                                    unset($supplier['password']);
                                    unset($supplier['password_confirm']);

                                    $this->companies_model->updateCompany($supplier_details->id, $supplier);
                                    $updated .= '<p>' . lang('supplier_updated') . ' (' . $supplier['email'] . ')</p>';
                                }
                            }
                            //new supplier
                            else {
                                $sup = $supplier;
                                unset($supplier['username']);
                                unset($supplier['password']);
                                unset($supplier['password_confirm']);
                                $data[] = $supplier;
                                if($this->add_by_csv($sup)){
                                    $user_count++;
                                    $updated .= '<p>' . lang('supplier_added') . ' (' . $sup['email'] . ')</p>';
                                }
                            }
                            $rw++;
                        }
                        else {
                            $errors.=validation_errors().$rw;
//                            $errors.=lang('company') . ', ' . lang('name') . ', ' . lang('email') . ' ' . lang('are_required') . ' (' . lang('line_no') . ' ' . $rw . ')';
                        }
                    }

                    if(!empty($data)){
                        if ($this->companies_model->addCompanies($data)){
                            if($updated) $this->session->set_flashdata('message',$updated);
                            else { $this->session->set_flashdata('warning', lang('data_x_suppliers'));}
                            admin_redirect('suppliers');
                        }
                    }

                    if ($updated){ $this->session->set_flashdata('message', $updated);}
                    else if ($errors) { $this->session->set_flashdata('message', $errors);}

                    admin_redirect('suppliers');
                }
            }
            else{
                $this->session->set_flashdata('error', validation_errors());
                admin_redirect('suppliers');
            }
        }


        else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suppliers/import', $this->data);
        }
    }

    public function add_by_csv($sup_data = [])
    {
        $save_user = false;
        if (!empty($sup_data))
        {
            $photo = 'unknow.png';
            if (!empty($_FILES['logo']['size'])) {
                $this->load->library('upload');

                $config['upload_path']   = 'assets/uploads/company_logo';
                $config['allowed_types'] = 'gif|jpg|png';
                $config['max_size'] = '500';
                $config['overwrite']    = false;
                $config['encrypt_name'] = true;
                $config['max_filename'] = 25;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload('logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                }

                $photo = $this->upload->file_name;

                $this->load->helper('file');
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = 'assets/uploads/company_logo/' . $photo;
                $config['new_image']      = 'assets/uploads/company_logo/thumbs/' . $photo;
                $config['maintain_ratio'] = true;
                $config['width']          = 150;
                $config['height']         = 150;

                $this->image_lib->clear();
                $this->image_lib->initialize($config);

                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
            }
            $data = [
                'company'           => $sup_data['company'],
                'name'              => $sup_data['name'],
                'email'             => $sup_data['email'],
                'phone'             => $sup_data['phone'],
                'address'           => $sup_data['address'],
                'city'              => $sup_data['city'],
                'state'             => $sup_data['state'],
                'postal_code'       => $sup_data['postal_code'],
                'country'           => $sup_data['country'],
                'vat_no'            => $sup_data['vat_no'],
                'logo'              =>  $photo,
                'group_id'          => $sup_data['group_id'],
                'group_name'        => $sup_data['group_name'],
                'cf1'               => $sup_data['cf1'],
                'cf2'               => $sup_data['cf2'],
                'cf3'               => $sup_data['cf3'],
                'cf4'               => $sup_data['cf4'],
                'cf5'               => $sup_data['cf5'],
                'cf6'               => $sup_data['cf6'],
                'gst_no'            => $sup_data['gst_no'],
            ];
            $save_user = true;

            if ($sid = $this->companies_model->addCompany($data)) {
                $company = $this->companies_model->getCompanyByID($sid);

                if($save_user){
                    $wcode = random_string('alnum',27);
                    $ware_house_data = [
                        'code'           => $wcode,
                        'name'           => strtoupper($sup_data['company']).' '.'ENTREPÔT',
                        'phone'          => $sup_data['phone'],
                        'email'          => strtolower($sup_data['email']),
                        'address'        => $sup_data['address'],
                        'price_group_id' => '1',
                        'map'            => null,
                    ];

                    $this->db->insert('warehouses', $ware_house_data);
                    $ware_house_id = $this->db->get_where('warehouses',['code'=>$wcode])->row()->id;
                    $active                  = '1';
                    $notify                  = '0';
                    $email                   = $sup_data['email'];
                    $username                = $sup_data['username'];
                    $password                = $sup_data['password'];
                    $additional_data         = [
                        'first_name' => $sup_data['name'],
                        'last_name'  => '',
                        'phone'      => $sup_data['phone'],
                        'gender'     => 'male',
                        'company_id' => $company->id,
                        'company'    => $company->company,
                        'group_id'   => '6',
                        'warehouse_id'=>$ware_house_id,
                        'sup_id'     => $company->id,
                    ];
                    $this->load->library('ion_auth');

                    if ($this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
                    }
                }
            }

            return true;
        }

        else return false;
    }

    public function slides($action = ''){
        if (!empty($_POST)) {
            if($action == 'update'){
                $previous_slider  = $this->db->get_where('supplier_slides',['user_id'=>$this->session->userdata('user_id')])->row()->slides;
                $previous_slider = json_decode($previous_slider);

                $uploaded = ['image1' => '', 'image2' => '', 'image3' => '', 'image4' => '', 'image5' => ''];
                $this->load->library('upload');
                $config['upload_path']   = 'assets/uploads/slides';
                $config['allowed_types'] = 'gif|jpg|png';
                $config['max_size']      = '1024';
                $config['overwrite']     = true;
                $config['max_filename']  = 25;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);

                $images = ['image1', 'image2', 'image3', 'image4', 'image5'];
                foreach ($images as $image) {
                    if ($_FILES[$image]['name'] !=  '') {
                        if (!$this->upload->do_upload($image))
                        {
                            $error = $this->upload->display_errors();
                            $this->session->set_flashdata('error', $error);
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                        $uploaded[$image] = $this->upload->file_name;

                    }
                }

                $data = [
                    [
                        'image'   => (!empty($uploaded['image1']) ? $uploaded['image1'] : $previous_slider[0]->image),
                        'link'    => $this->input->post('link1'),
                        'caption' => $this->input->post('caption1'),
                    ],
                    [
                        'image'   => (!empty($uploaded['image2']) ? $uploaded['image2'] : $previous_slider[1]->image),
                        'link'    => $this->input->post('link2'),
                        'caption' => $this->input->post('caption2'),
                    ],
                    [
                        'image'   => (!empty($uploaded['image3']) ? $uploaded['image3'] : $previous_slider[2]->image),
                        'link'    => $this->input->post('link3'),
                        'caption' => $this->input->post('caption3'),
                    ],
                    [
                        'image'   => (!empty($uploaded['image4']) ? $uploaded['image4'] : $previous_slider[3]->image),
                        'link'    => $this->input->post('link4'),
                        'caption' => $this->input->post('caption4'),
                    ],
                    [
                        'image'   => (!empty($uploaded['image5']) ? $uploaded['image5'] : $previous_slider[4]->image),
                        'link'    => $this->input->post('link5'),
                        'caption' => $this->input->post('caption5'),
                    ],
                ];

                foreach ($data as $index=>&$img) {
                    if (empty($img['image']))
                    {
                        unset($img['image']);
                    }
                    if($img['image'] != $previous_slider[$index]->image){
                        $file = 'assets/uploads/slides/' . $previous_slider[$index]->image;
                       if(file_exists($file)){
                           unlink($file);
                       }
                    }
                }

                if ($this->shop_admin_model->updateSupplierSlider($data)) {
                    $this->session->set_flashdata('message', lang('silder_updated'));
                    admin_redirect('suppliers/slides');
                }
            }

            if($action == 'delete' and !empty($_POST['index'] || $_POST['index'] == '0')){
                $previous_slider  = $this->db->get_where('supplier_slides',['user_id'=>$this->session->userdata('user_id')])->row()->slides;
                $previous_slider = json_decode($previous_slider);
                $index = $_POST['index'];

                if(!empty($previous_slider[$index]->image)){
                    $file = 'assets/uploads/slides/' . $previous_slider[$index]->image;
                    if(file_exists($file)){
                        unlink($file);
                    }

                    unset($previous_slider[$index]);
                }

                $this->db->where(['user_id'=>$this->session->userdata('user_id')])->update('supplier_slides' , ['slides'=>json_encode($previous_slider)]);
            }
        }

        else {
            $slides = $this->db->get_where('supplier_slides' , ['user_id'=>$this->session->userdata('user_id')]);
            if($slides->num_rows() > 0){
                $slides = $slides->row()->slides;
                json_decode($slides);
            }
            else{
                $slides = [];
            }
            $shop_settings                 = $this->shop_admin_model->getShopSettings();
            $this->data['slides']          = $slides;
            $this->data['error']           = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $bc                            = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('shop_settings'), 'page' => lang('shop_settings')], ['link' => '#', 'page' => lang('slider_settings')]];
            $meta                          = ['page_title' => lang('slider_settings'), 'bc' => $bc];
            $this->page_construct('suppliers/slides', $meta, $this->data);
        }
    }

    public function site_colors(){
    $compny_id = $this->session->userdata('warehouse_id');
    if(!empty($_POST)){

        $db_data = [
            'theme'=>$_POST['theme'],
            'theme_text'=>$_POST['theme_text'],
            'main_header'=>$_POST['main_header'],
            'library_name_bg'=>$_POST['library_name_bg'],
            'library_name'=>$_POST['library_name'],
            'product_bg'=>$_POST['product_bg'],
            'product_bg_hover'=>$_POST['product_bg_hover'],
            'product_name'=>$_POST['product_name'],
            'product_price'=>$_POST['product_price'],
            'old__product_price'=>$_POST['old__product_price'],
            'discount_text'=>$_POST['discount_text'],
            'discount_bg'=>$_POST['discount_bg'],
            'product_category'=>$_POST['product_category'],
            'button_bg'=>$_POST['button_bg'],
            'button_bg_hover'=>$_POST['button_bg_hover'],
            'navbar_bg'=>$_POST['navbar_bg'],
            'category_list'=>$_POST['category_list'],
        ];

        $this->db->where(['warehouse_id'=>$compny_id])->update('supplier_color_settings',$db_data);
        $this->session->set_flashdata('message','Couleur modifié');
        redirect('admin/suppliers/site_colors');
    }

    else {

        $supplier_colors = $this->db->get_where('supplier_color_settings',['warehouse_id'=>$compny_id]);
        if($supplier_colors->num_rows()> 0){
            $supplier_colors = $supplier_colors->row();
        }
        else{
            $this->db->insert('supplier_color_settings' ,['warehouse_id'=>$compny_id]);
            $supplier_colors = $this->db->get_where('supplier_color_settings',['warehouse_id'=>$compny_id])->row();
        }

        $this->data['colors'] = $supplier_colors;
        $this->data['error']           = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                            = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('shop_settings'), 'page' => lang('shop_settings')], ['link' => '#', 'page' => lang('slider_settings')]];
        $meta                          = ['page_title' => lang('slider_settings'), 'bc' => $bc];
        $this->page_construct('suppliers/site_colors', $meta, $this->data);
    }
}

    public function font_styles($action = ''){
        $compny_id = $this->session->userdata('warehouse_id');
        $where = ['warehouse_id'=>$this->warehouse_id];
        if(!empty($_POST)){
            if($action == 'update_font' and !empty($_POST['font_id'])){
                $update_data = ['font_id'=>$_POST['font_id']];
                $this->db->where($where)->update($this->sup_settings_table , $update_data);
                $this->sma->send_json('Police modifier avec succès');
            }

            else if($action == 'update_font_size' and !empty($_POST['font_id'])){
                $where = ['warehouse_id'=>$this->warehouse_id , 'font_id'=>$_POST['font_id']];
                $insert_data = ['font_size'=>$_POST['font_size'] , 'font_id'=>$_POST['font_id'] , 'warehouse_id'=>$this->warehouse_id];
                $update_data = ['font_size'=>$_POST['font_size']];
                $this->com_model->insert_or_update($this->sup_font_size_table , $where , $insert_data , $update_data);
                $this->sma->send_json('Police modifier avec succès');
            }

            else if($action == 'update_settings'){
                $key = $_POST['key'];
                $value = $_POST['value'];
                $this->db->where($where)->update($this->sup_settings_table , [$key=>$value]);
                $this->sma->send_json('parametres modifier succès');
            }
        }

        else {

            $this->data['fonts']            = $this->db->get('fonts')->result();
            $this->data['settings']         = $this->com_model->insert_if_not_exist($this->sup_settings_table , ['warehouse_id'=>$this->session->userdata('warehouse_id')] , ['warehouse_id'=>$this->warehouse_id]);
            $this->data['font_sizes']       = $this->com_model->parseArray($this->db->get_where($this->sup_font_size_table , $where)->result() , 'font_id' , 'font_size');
            $this->data['error']            = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $bc                             = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('shop_settings'), 'page' => lang('shop_settings')], ['link' => '#', 'page' => lang('slider_settings')]];
            $meta                           = ['page_title' => lang('slider_settings'), 'bc' => $bc];
            $this->page_construct('suppliers/font_styles', $meta, $this->data);
        }
    }



}
