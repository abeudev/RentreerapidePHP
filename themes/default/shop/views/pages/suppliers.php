<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    .supplier-logo{min-height: 100px; max-height: 100px;}
</style>
<section class="page-contents">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="row">
                    <div class="col-sm-9 col-md-10">
                        <div class="panel panel-default margin-top-lg">
                            <div class="panel-heading text-bold">
                                <div class="form-group">
                                    <?=lang('suppliers');?>
                                    <div class="col-sm-4 pull-right">
                                        <input type="text" class="form-control search_supplier" placeholder="<?=lang('Recherche');?>">
                                    </div>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <?php foreach($suppliers as $supplier):?>
                                        <a data-name="<?=strtolower($supplier->name);?>" data-toggle="tooltip" data-placement="top" title="<?=$supplier->name?>" class="supplier-item btn btn-link col-sm-2 col-xs-6 text-center m-t-30 form-group" href="<?=site_url('library/' . $supplier->id);?>">
                                            <div class="img-responsive">
                                                <img class="img-circle img-thumbnail supplier-logo" src="<?=base_url('assets/uploads/company_logo/'.$supplier->logo);?>" alt="">
                                            </div>
                                            <p style="word-wrap:break-word;display:block;">
                                                <?= limit_string(str_replace('LIBRAIRIE','',$supplier->name) , 14);?>
                                            </p>
                                        </a>
                                    <?php endforeach;?>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-sm-3 col-md-2">
                        <?php include 'sidebar2.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).on('keyup','.search_supplier',{passive:true},function () {
        const input = $(this);
        const query = $(this).val().trim().toLowerCase();
        const founds = "[data-name*='"+query+"']";
        if(query.length > 0){
            if($(founds).length > 0){
                $(input).css('border-color','green');
                $('.supplier-item').hide();
                $(founds).show();
            }
            else{
                $(input).css('border-color','red');
            }
        }
        else{
            $(input).css('border-color','none');
            $('.supplier-item').show();
        }


    });
</script>