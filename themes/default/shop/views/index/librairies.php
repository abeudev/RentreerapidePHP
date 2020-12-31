<?php
$parteners = $this->db->select('warehouses.id as id , companies.id as company_id , companies.name , companies.logo')
    ->where(['group_name'=>'supplier'])
    ->join('warehouses','warehouses.email = companies.email')
    ->get('companies')->result();
?>

<div class="brands position-relative">
    <div class="container" style="min-width: 98%">
        <div class="row">
            <div class="col">
                <div class="brands_slider_container position-relative">
                    <div class="owl-carousel owl-theme brands_slider">

                        <?php foreach($parteners as $librairy):?>
                            <a href="<?=site_url('library/' . $librairy->id);?>" class="owl-item" data-toggle="tooltip" data-placement="top" title="<?=$librairy->name;?>">
                                <div class="brands_item d-flex flex-column justify-content-center">
                                    <img style="max-width: 100px" class="librairie_img lazy" data-src="<?=base_url().'assets/uploads/company_logo/'.$librairy->logo;?>" alt="">
                                    <small><?=$librairy->name;?></small>
                                </div>

                            </a>
                        <?php endforeach;?>

                    </div>

                    <!-- Brands Slider Navigation -->
                    <div class="brands_nav brands_prev"><i class="fa fa-chevron-left"></i></div>
                    <div class="brands_nav brands_next"><i class="fa fa-chevron-right"></i></div>

                </div>
            </div>
        </div>
    </div>
</div>