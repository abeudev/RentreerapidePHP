<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel">Ajouter une region</h4>
        </div>
        <?php  $attrib = ['class'=>'ajax-form' , 'onsubmit'=>'ajax_submit_form_callback = after_action'];
        echo admin_form_open('shop_settings/add_region', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="regions_name">Nom</label>
                        <input type="text" id="regions_name" name="region_name" class="form-control" required>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" id="description" name="description" class="form-control">
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="parent_id">Région</label>
                        <select name="parent_id" id="parent_id" class="select form-control">
                            <option value="">Selectionnez une région</option>
                            <?php foreach($regions as $region):?>
                                <option value="<?=$region->id;?>"><?=$region->region_name;?></option>
                            <?php endforeach;?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_category', lang('add'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
