<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_school'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open('schools/edit_school', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <?php echo lang('teaching_type', 'cycle'); ?>
                        <div class="controls">
                            <select name="cycle" id="cycle" class="form-control">
                                <option value="primary" <?=($school->cycle == 'primary')?'selected':null;?>><?=lang('primary');?></option>
                                <option value="secondary" <?=($school->cycle == 'secondary')?'selected':null;?>><?=lang('secondary');?></option>
                                <option value="superior" <?=($school->cycle == 'superior')?'selected':null;?>><?=lang('superior');?></option>
                                <option value="primary_secondary" <?=($school->cycle == 'primary_secondary')?'selected':null;?>><?=lang('primary_secondary');?></option>
                                <option value="primary_secondary_superior" <?=($school->cycle == 'primary_secondary_superior')?'selected':null;?>><?=lang('primary_secondary_superior');?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?php echo lang('school_type', 'school_type'); ?>
                        <div class="controls">
                            <select name="type" id="school_type" class="form-control">
                                <option value="public" <?=($school->type=='public')?'selected':null;?>><?=lang('public');?></option>
                                <option value="private" <?=($school->type=='private')?'selected':null;?>><?=lang('private');?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <?php echo lang('school_name', 'school_name'); ?>
                        <?php echo form_input('name', set_value('name', $school->name) , 'class="form-control input-tip" id="school_name" required="required"'); ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?php echo lang('code', 'email'); ?>
                        <?php echo form_input('code', set_value('code', $school->code) , 'class="form-control input-tip" id="email" required="required"'); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <?php echo lang('address', 'address'); ?>
                        <?php echo form_input('address', set_value('address', $school->address) , 'class="form-control input-tip" id="address" required="required"'); ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?php echo lang('phone', 'phone'); ?>
                        <?php echo form_input('phone', set_value('phone', $school->phone) , 'class="form-control input-tip" id="phone" required="required"'); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <?=lang('system','system');?>
                        <?php echo form_input('systm', set_value('systm', $school->systm) , 'class="form-control input-tip" id="system" required="required"'); ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?=lang('dren','dren');?>
                        <?php echo form_input('dren', set_value('dren', $school->dren) , 'class="form-control input-tip" id="dren" required="required"'); ?>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <?=lang('ordre enseignement','order');?>
                        <?php echo form_input('order', set_value('order', $school->order) , 'class="form-control input-tip" id="geographical_situation" required="required"'); ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="school_gender" style="margin-right:10px"><?=lang('school_gender');?>:</label>
                <input type="radio" class="checkbox" name="gender_type" value="male" id="male">
                <label for="male" class="padding05"><?= lang('male') ?></label>
                <input type="radio" class="checkbox" name="gender_type" value="female" id="female">
                <label for="female" class="padding05"><?= lang('female') ?></label>
                <input type="radio" class="checkbox" name="gender_type" value="mixte" id="mix" checked="checked">
                <label for="mix" class="padding05"><?= lang('mix') ?></label>
            </div>
        </div>
        <?php echo form_hidden('id', $id); ?>
        <div class="modal-footer">
            <?php echo form_submit('edit_school', lang('edit_school'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
</script>

