<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="page-contents">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">

                <div class="row">
                    <div class="col-sm-9 col-md-10">

                        <div class="panel panel-default margin-top-lg">
                            <div class="panel-heading text-bold">
                                <i class="fa fa-map margin-right-sm"></i> <?= lang('my_addresses'); ?>
                            </div>
                            <div class="panel-body">
                                <?php
//                                $regions = $this->db->get_where('shipping_regions',['parent_id'=>'0'])->result();
                                $sts = [];
                                foreach($regions as $region){
                                    $sts[$region->id] = ucfirst($region->region_name);
                                }
                                $istates =$sts;

                                if (!empty($addresses)) {
                                    echo '<div class="row">';
                                    echo '<div class="col-sm-12 text-bold">' . lang('select_address_to_edit') . '</div>';
                                    $r = 1;
                                    foreach ($addresses as $address) {
                                        ?>
                                        <div class="col-sm-6">
                                            <a href="#" class="link-address edit-address" data-id="<?= $address->id; ?>">
                                                    <?= $address->line1; ?><br>
                                                    <?= $address->line2; ?><br>
                                                    <?= $address->city; ?><br>
                                                    <?= lang('phone') . ': ' . $address->phone; ?>
                                                    <span class="count"><i><?= $r; ?></i></span>
                                                    <span class="edit"><i class="fa fa-edit"></i></span>
                                                </a>
                                        </div>
                                        <?php
                                        $r++;
                                    }
                                    echo '</div>';
                                }
                                if (count($addresses) < 6) {
                                    echo '<div class="row margin-top-lg">';
                                    echo '<div class="col-sm-12"><a href="#" id="add-address" class="btn btn-primary btn-sm">' . lang('add_address') . '</a></div>';
                                    echo '</div>';
                                }
                                if (true) {
                                    ?>
                                <script>
                                    var istates = <?= json_encode($istates); ?>
                                </script>
                                <?php
                                } else {
                                    echo '<script>var istates = false; </script>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-3 col-md-2">
                        <?php include 'sidebar1.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
var reg_viless = false;
var default_region_id = '<?=$default_region_id;?>';
<?php if(!empty($reg_villes)):;?>
reg_viless =<?=$reg_villes;?>;
<?php endif;?>
var addresses = <?= !empty($addresses) ? json_encode($addresses) : 'false'; ?>;
var addr = {};
<?php foreach($addresses as $address):?>
    addr.reg_<?=$address->id;?> = '<?=ucfirst($address->state);?>';
<?php endforeach;?>
$(document).on('click','#add-address , .edit-address',{passive:true},function () {
    const id = $(this).attr('data-id');
    setTimeout(function(){
        $('.filter-option').text('Region');
        $('#address-country').val('Côte d’Ivoire.');
        $('#address-country').parent().hide();
        $('#address-phone').parent().attr('class','form-group col-sm-6');

        if(id !== undefined && id !== null){
            $('.filter-option').text(addr['reg_'+id]);
            $('#address-state').val(''+id+'');
        }
    },300);
});
</script>
