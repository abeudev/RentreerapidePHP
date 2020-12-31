<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .ob {
        list-style: none;
        padding: 0;
        margin: 0;
        margin-top: 10px;
    }

    .ob li {
        width: 49%;
        margin: 0 10px 10px 0;
        float: left;
    }

    .ob li .btn {
        width: 100%;
    }

    .ob li:nth-child(2n+2) {
        margin-right: 0;
    }
</style>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header modal-primary">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="ajaxModalLabel">
                <?= lang('school_stuffs') ?>
            </h4>
        </div>
        <div class="modal-body" style="padding-bottom:0;">
            <?= $r ? $this->lang->line('click_to_add') : ''; ?>
            <div class="html_con"><?= $html ?></div>
            <div class="clearfix"></div>
        </div>
        <?php if ($page) {
    ?>
            <div class="modal-footer" style="padding:0;">
                <div class="text-center">
                    <div class="page_con"><?= $page ?></div>
                </div>
            </div>
        <?php
} ?>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('.pagination a').attr('data-toggle', 'ajax');
        $('.sus_sale').on('click', function (e) {
            var sid = $(this).attr("id");
            if (count > 1) {
                bootbox.confirm({
                    message: "<?= $this->lang->line('load_cart_building') ?>",
                    callback: function (gotit) {
                        if (gotit == false) {
                            return true;
                        } else {
                            window.location.href = "<?= admin_url('schools/cart_builder/'.$this->session->userdata('opened_school').'/'.$this->session->userdata('opened_class')) ?>/" + sid;
                        }
                    },
                    buttons: {
                        confirm: { label: 'Oui', className: 'btn-success'},
                        cancel: { label: 'Non', className: 'btn-danger'}
                    },
                });
            } else {
                window.location.href = "<?= admin_url('schools/cart_builder/'.$this->session->userdata('opened_school').'/'.$this->session->userdata('opened_class')) ?>/" + sid;
            }
            return false;
        });
    });
</script>
