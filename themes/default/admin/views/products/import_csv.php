<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('import_products_by_csv'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <?php
                $attrib = ['class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('products/import_csv', $attrib)
                ?>
                <div class="row">
                    <div class="col-md-12">

                        <div class="well well-small">
                            <a href="<?php echo base_url(); ?>assets/csv/sample_products.csv"
                               class="btn btn-primary pull-right"><i
                                    class="fa fa-download"></i> <?= lang('download_sample_file') ?></a>
                            <p>
                                <span class="text-warning"><?= lang('csv1'); ?></span><br/><?= lang('csv2'); ?>
                                <strong class="text-info">
                                    (nom, marque, code category, code sous-cateogru, unite, cout, prix, quantite, quantite-alert, code-hsn, code isbn, image, details(virgule interdite), frais supplementaires 1 mois,	frais supplementaires 2mois,	frais supplementaires 3 mois,	frais penalite 1 mois,	frais penalite 2mois,	frais penalite 3 mois)
                                </strong> <?= lang('csv3'); ?>
                            </p>
                            <p><?= lang('images_location_tip'); ?></p>
                            <span class="text-primary"><?= lang('csv_update_tip'); ?></span>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="csv_file"><?= lang('upload_file'); ?></label>
                                <input type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" class="form-control file" data-show-upload="false" data-show-preview="false" id="csv_file" required="required"/>
                            </div>

                            <div class="form-group">
                                <?php echo form_submit('import', $this->lang->line('import'), 'class="btn btn-primary"'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>
