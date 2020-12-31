<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$installment_settings = $this->db->get_where('installment_settings',['id'=>1])->row();
?>
<style>
    .dis-none{display: none !important;}
    .text-strike{
        text-decoration: line-through;
        font-weight: normal !important;
    }
</style>
<section class="page-contents">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">

                <div class="row">
                    <div class="col-sm-9 col-md-10">

                        <div class="panel panel-default margin-top-lg">
                            <div class="panel-heading text-bold">
                                <i class="fa fa-list-alt margin-right-sm"></i> <?= lang('installment'); ?>
                            </div>
                            <div class="panel-body">
                                <?= shop_form_open('save_payment_param', 'class="validate" id="guest-checkout"'); ?>
                                <h1 class="text-center"><?=lang('choose_payment_plan');?></h1>

                                <h5><strong><?=lang('ref')?> :</strong> <strong class="pull-right"><?=$sales->reference_no;?></strong></h5><hr>
                                <h5><strong><?=lang('totals')?> :
                                    </strong>
                                    <strong id="old_all" class="pull-right"><?=$sales->grand_total;?></strong>
                                    <br><strong class="pull-right" id="all_to_pay">

                                    </strong>

                                </h5><hr>
                                <h5><strong><?= lang('instalment_period'); ?></strong> : <strong class="numOfWeeks pull-right"></strong></h5><hr>

                                <input type="hidden" name="sale_id" value="<?=$sales->id?>">
                                <input type="hidden" name="weekly_pay" id="to_pay" required>
                                <input type="hidden" name="num_of_week" id="num_of_week" required>

                                <div class="row">
                                    <div class="col-sm-6">

                                        <div class="checkbox bg">
                                            <label style="display: inline-block; width: auto;">
                                                <input type="radio" name="num_of_month" value="1" id="1month" required="required" checked>
                                                <span>
                                                    <i class="fa fa-calendar margin-right-md"></i> 1 <?= lang('month') ?>
                                                </span>
                                            </label>


                                            <label style="display: inline-block; width: auto;">
                                                <input type="radio" name="num_of_month" value="2" id="2month" required="required">
                                                <span>
                                                    <i class="fa fa-calendar margin-right-md"></i> 2 <?= lang('months') ?>
                                                </span>
                                            </label>

                                            <label style="display: inline-block; width: auto;">
                                                <input type="radio" name="num_of_month" value="3" id="3month" required="required">
                                                <span>
                                                    <i class="fa fa-calendar margin-right-md"></i> 3 <?= lang('month') ?>
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <p class="margin-top-lg">
                                            You will pay <b class="margin-left-sm margin-right-sm" id="to_payPerWeek"></b> each weeks within <b class="margin-left-sm margin-right-sm numOfWeeks"></b>
                                            <br>
                                        </p>
                                    </div>
                                </div>
                                <hr>
                                <?php echo form_submit('save_payment_params', lang('save_payment_param'), 'class="btn btn-theme pull-right dis-none save_param_btn batre"');?>
                                <?= form_close(); ?>
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

<script>
    const m1p = Number('<?=$installment_settings->month_1_tax;?>');
    const m2p = Number('<?=$installment_settings->month_2_tax;?>');
    const m3p = Number('<?=$installment_settings->month_3_tax;?>');

    $(function () {
        setTimeout(function(){
            $('#1month').click();
        },300);
    })
</script>