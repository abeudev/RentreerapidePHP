<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_school'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open('schools/edit', $attrib); ?>
        <div class="modal-body">
            <table id="zero_config" class="text-center table table-striped table-bordered" width="100%">
                <thead>
                <tr>
                    <th>Classe</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($classes as $class):?>
                    <tr>
                        <td><?=$class->level;?></td>
                        <td><a class="btn-link" href="<?=base_url('admin/schools/cart_builder/'.$school->id.'/'.$class->id);?>" data-toggle="tooltip" data-placement="top" title="Definir la liste des Fournitures"><i class="fa fa-th"></i> Definir les Fournitures</a></td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true" type="button"><?=lang('close');?></button>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>