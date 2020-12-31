<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('importer_par_csv'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('schools/import_csv', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="well well-small">
                <a href="<?php echo base_url(); ?>assets/csv/sample_schools.csv" class="btn btn-primary pull-right"><i
                        class="fa fa-download"></i> Télécharger un fichier exemplaire</a>
                <span class="text-warning">
                    <?= lang('csv1'); ?></span><br/><?= lang('csv2'); ?>
                <div class="text-info"><label>(Inspection (IEP), Code, Nom Etablisement, Cycle, Contact, Dren, L'ordre, type, Sytem)</label></div>
                <br>
                <p class="text-success"></p>
            </div>
            <div class="form-group">
                <?= lang('upload_file', 'csv_file') ?>
                <input id="csv_file" type="file" data-browse-label="<?= lang('browse'); ?>" name="csv_file" data-bv-notempty="true" data-show-upload="false"
                       data-show-preview="false" class="form-control file">
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('import', lang('import'), 'class="btn btn-primary"'); ?>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
