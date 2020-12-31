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
<?= $html ?>
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
