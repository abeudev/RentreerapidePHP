<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cogs"></i><?= lang('slider_settings'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('update_info'); ?></p>

                <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('shop_settings/slider/update', $attrib);
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <?php for($i = 1 ; $i<=5 ; $i++):?>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <?= lang('image', 'image'.$i); ?> <?=$i;?>
                                        <input id="image<?=$i;?>" type="file" data-browse-label="<?= lang('browse'); ?>" name="image<?=$i;?>" data-show-upload="false" data-show-preview="true" class="form-control file">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <?= lang('link', 'link'.$i); ?> <?=$i;?>
                                        <?= form_input('link'.$i, set_value('link'.$i, (isset($slides[$i-1]->link) ? $slides[$i-1]->link : '')), 'class="form-control tip" id="link'.$i.'"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('caption', 'caption'.$i); ?> <?=$i;?>
                                        <?= form_input('caption'.$i, set_value('caption'.$i, (isset($slides[$i-1]->caption) ? $slides[$i-1]->caption : '')), 'class="form-control tip" id="caption'.$i.'"'); ?>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <?= lang('actions', 'actions'.$i); ?> <?=$i;?>
                                    <button type="button" class="btn btn-block btn-danger delete_slide" id="actions<?=$i;?>" data-id="<?=$i-1;?>">
                                        <i class="fa fa-trash"></i> <?=lang('delete');?>
                                    </button>
                                </div>
                            </div>
                        <?php endfor;?>
                        <?= form_submit('update', lang('update'), 'class="btn btn-primary"'); ?>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).on('click','.delete_slide',{passive:true},function () {
        const index = $(this).attr('data-id');
        aoData = [
            {name:'<?= $this->security->get_csrf_token_name() ?>' , value:'<?=$this->security->get_csrf_hash() ?>'},
            {name:'index',value:index},
        ];
        $.post('<?=base_url('admin/shop_settings/slider/delete');?>',aoData , function () {
            show_message('success','Slide supprimé avec succès');
        })
    });
</script>