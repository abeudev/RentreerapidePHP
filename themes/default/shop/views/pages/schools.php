<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php

$founiture = [];
foreach($school_carts as $school_cart){
    $founitures["school_{$school_cart->school_id}_class_{$school_cart->class_id}"] = $school_cart->id;
}
?>

<style>
    .l, .m, .r{width:10px; display: table-cell;height: 20px;margin: 0px; padding: 0px !important;}
    .orange{background-color: orange;}
    .white{background-color: white;}
    .green{background-color: green;}
    .cblue{background-color: blue;}
    .red{background-color: red;}
    .text-white{color:white !important;}
    .flag{display: inline-block; vertical-align: middle;}
    #schools .dropdown-menu li , #schools .dropdown{list-style: none !important;}
    #schools .dropdown a{text-decoration: none !important;}
    #schools .dropdown-menu div{padding:15px;}
    #schools .btn-white{
        border: solid thin #e6e0e0;
        box-shadow: 1px 2px 4px -2px;
        color:black !important;
    }
    .pre{display: block;
        padding: 9.5px;
        margin: 0 0 10px;
        font-size: 13px;
        line-height: 1.42857;
        word-break: break-all;
        word-wrap: break-word;
        background-color: #f5f5f5;
        border: 1px solid #ccc;
        border-radius: 0;}

    .school-item .col-sm-4:nth-child(4){
        width: 100% !important;
        padding-top:0px !important;
    }
</style>
<section id="schools" class="page-contents">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="row">
                    <div class="col-sm-9 col-md-10">
                        <div class="panel panel-default margin-top-lg" style="border-top:none !important;">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs " role="tablist" style="background-color: whitesmoke; border:none">
                                <li class="nav-item active" style="width: 50%"> <a class="nav-link active" data-toggle="tab" href="#francais" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">SYSTEM FRANCAIS</span></a> </li>
                                <li class="nav-item" style="width: 50%"> <a class="nav-link" data-toggle="tab" href="#ivoirien" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">SYSTEM IVOIRIEN</span></a> </li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content tabcontent-border panel-body">
                                <div class="tab-pane active" id="francais" role="tabpanel">
                                    <?php include_once('schools/francais.php'); ?>
                                </div>
                                <div class="tab-pane  p-20" id="ivoirien" role="tabpanel">
                                    <?php include_once('schools/ivoirien.php'); ?>
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
    $(document).on('keyup','.search_school',{passive:true},function () {
        const input = $(this);
        const query = $(this).val().trim().toLowerCase();
        const founds = "[data-name*='"+query+"']";
        if(query.length > 0){
            if($(founds).length > 0){
                $(input).css('border-color','green');
                $('.school-item').hide();
                $(founds).show();
            }
            else{
                $(input).css('border-color','red');
            }
        }
        else{
                $(input).css('border-color','none');
                $('.school-item').show();
        }
       

    });

    var fournitures = $.parseJSON('<?=json_encode($founitures) ;?>');
</script>