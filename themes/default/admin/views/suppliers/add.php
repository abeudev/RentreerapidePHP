<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_supplier'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('suppliers/add', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <!--<div class="form-group">
                    <?= lang('type', 'type'); ?>
                    <?php $types = ['company' => lang('company'), 'person' => lang('person')];
            echo form_dropdown('type', $types, '', 'class="form-control select" id="type" required="required"'); ?>
                </div> -->

            <div class="row">
                <div class="col-md-12">
                    <fieldset>
                        <legend>Info Entreprise</legend>
                        <div class="row">
                            <div class="col-sm-9">
                                <div class="form-group company">
                                    <?= lang('nom de l\'entreprise', 'company'); ?>
                                    <?php echo form_input('company', '', 'class="form-control tip" id="company" data-bv-notempty="true"'); ?>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="form-group">
                                    <?= lang('Ordre', 'ordering'); ?>
                                    <?php echo form_input('ordering', '', 'class="form-control tip" id="ordering" data-bv-notempty="true"'); ?>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?= lang('email_address', 'email_address'); ?>
                                    <input type="email" name="email" class="form-control" required="required" id="email_address"/>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?= lang('phone', 'phone'); ?>
                                    <input type="tel" name="phone" class="form-control" required="required" id="phone"/>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang('address', 'address'); ?>
                                    <?php echo form_input('address', '', 'class="form-control" id="address" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang('city', 'city'); ?>
                                    <?php echo form_input('city', '', 'class="form-control" id="city" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang('state', 'state'); ?>
                                    <?php
                                    if ($Settings->indian_gst) {
                                        $states = $this->gst->getIndianStates(true);
                                        echo form_dropdown('state', $states, '', 'class="form-control select" id="state" required="required"');
                                    } else {
                                        echo form_input('state', '', 'class="form-control" id="state"');
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Compte Utilisateur</legend>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group person">
                                    <?= lang('name', 'name'); ?>
                                    <?php echo form_input('first_name', '', 'class="form-control tip" id="name" data-bv-notempty="true"'); ?>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?= lang('first_name', 'last_name'); ?>
                                    <?php echo form_input('last_name', '', 'class="form-control" id="last_name"'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <?= lang('username', 'username'); ?>
                                    <?php echo form_input('username', '', 'class="form-control" id="username"'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?php echo lang('password', 'password'); ?>
                                    <div class="controls">
                                        <?php echo form_password('password', '', 'class="form-control tip" id="password" required="required" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"'); ?>
                                        <span class="help-block">At least 1 capital, 1 lowercase, 1 number and more than 8 characters long</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?php echo lang('confirm_password', 'password_confirm'); ?>
                                    <div class="controls">
                                        <?php echo form_password('password_confirm', '', 'class="form-control" id="password_confirm" required="required" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" data-bv-identical="true" data-bv-identical-field="password" data-bv-identical-message="' . lang('pw_not_same') . '"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </fieldset>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <?= lang('Logo de la compagnie', 'logo'); ?>
                                <input id="logo" type="file" data-browse-label="<?= lang('browse'); ?>" name="logo" data-show-upload="false" data-show-preview="true" class="form-control file">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 dis-none">
                    <div class="form-group">
                        <?= lang('scf1', 'cf1'); ?>
                        <?php echo form_input('cf1', '', 'class="form-control" id="cf1"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('scf2', 'cf2'); ?>
                        <?php echo form_input('cf2', '', 'class="form-control" id="cf2"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang('scf3', 'cf3'); ?>
                        <?php echo form_input('cf3', '', 'class="form-control" id="cf3"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('scf4', 'cf4'); ?>
                        <?php echo form_input('cf4', '', 'class="form-control" id="cf4"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang('scf5', 'cf5'); ?>
                        <?php echo form_input('cf5', '', 'class="form-control" id="cf5"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang('scf6', 'cf6'); ?>
                        <?php echo form_input('cf6', '', 'class="form-control" id="cf6"'); ?>
                    </div>
                </div>
            </div>


        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_supplier', lang('add_supplier'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>

<?php include_once('upload_file_js.php'); ?>