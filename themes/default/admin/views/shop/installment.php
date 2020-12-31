<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i> <?=lang('edit_installment_settings');?></h2>
    </div>
    <div class="box-content">
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open('shop_settings/installment_settings', $attrib);
        ?>
            <div class="row">
                <div class="col-lg-12">
                    <p class="introtext"><?= lang('enter_info'); ?></p>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('One month Penalty', 'month_1_tax'); ?>
                                    <input type="text" name="month_1_tax" value="<?=$settings->month_1_tax;?>" class="form-control" id="month_1_tax" required data-fv-notempty-message="Title is required" data-bv-field="month_1_tax">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('Two month Penalty', 'month_2_tax'); ?>
                                    <input type="text" name="month_2_tax" value="<?=$settings->month_2_tax;?>" class="form-control" id="month_2_tax" required data-fv-notempty-message="Title is required" data-bv-field="month_2_tax">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('Three month Penalty', 'month_3_tax'); ?>
                                    <input type="text" name="month_3_tax" value="<?=$settings->month_3_tax;?>" class="form-control" id="month_3_tax" required data-fv-notempty-message="Title is required" data-bv-field="month_3_tax">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <?= lang('Terms and Condittion', 'terms'); ?>*

                                    <?= form_textarea('terms', $settings->terms, 'class="form-control terms" id="body" required data-fv-notempty-message="' . lang('body_required') . '"'); ?>
                                </div>

                                <input type="submit"  value="Save Settings" class="btn btn-primary">
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $('#installment_settings').addClass('active');
</script>
