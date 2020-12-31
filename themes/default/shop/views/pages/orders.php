<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .progress{background-color:white;}
    .delete_sale{
        position: absolute;
        bottom: 6px;
        right: 27px;
        font-size: 30px;
        display: none;
        color: #ff0000b5;
    }

    .order_item:hover .delete_sale{
        display: inline-block !important;
    }
</style>
<?php
    if(!empty($installment_orders)){
        $pay_progress = [];
        foreach($installment_orders as $io){
            $sale_id = $io['order_id'];
            $total_paid = 0;
            for($i = 1 ; $i<=12 ; $i++){
                $paid = (!empty($io['w'.$i]))?$io['w'.$i] : 0;

                $total_paid+= $paid;
            }


            $percentage_paid = round(($total_paid * 100) / (float) $io['total'] ,2);
            $percentage_paid = ($percentage_paid > 100)?100 : $percentage_paid;

            $pay_progress['sale_'.$sale_id] = $percentage_paid;
        }
    }
?>
<section class="page-contents">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">

                <div class="row">
                    <div class="col-sm-9 col-md-10">

                        <div class="panel panel-default margin-top-lg">
                            <div class="panel-heading text-bold">
                                <i class="fa fa-list-alt margin-right-sm"></i> <?= lang('my_orders'); ?>
                            </div>
                            <div class="panel-body">
                                <?php if(!empty($orders)):; ?>


                                    <div class="row">
                                       <div class="col-sm-12 text-bold"><?=lang('click_to_view');?></div>
                                        <div class="clearfix"></div>
                                            <?php foreach($orders as $order):
                                                $link = 'orders/' . $order->id;
                                                if($order->payment_method == 'installment'){
                                                        if($this->db->get_where('installment_pay_record',['sale_id'=>$order->id])->num_rows() == 0){
                                                            $link = 'installment/' . $order->id;
                                                        }
                                                }
                                        ?>
                                                <div class="col-md-12 order_item" id="order_no<?=$order->id;?>">
                                                    <a href="<?=shop_url($link); ?>" class="link-address<?= $order->payment_status == 'paid' ? '' : ' active' ?>">
                                                        <table class="table table-borderless table-condensed text-info text-bold" style="margin-bottom:0;">
                                                            <tr><td><?=lang('date');?></td><td><?= $this->sma->hrld($order->date); ?></td></tr>
                                                            <tr><td><?=lang('ref');?></td><td><?= $order->reference_no; ?>. </td></tr>
                                                            <tr><td><?=lang('sale_status');?></td><td><?= lang($order->sale_status); ?></td></tr>
                                                            <tr><td><?=lang('amount');?></td><td><?= $this->sma->formatMoney($order->grand_total, $this->default_currency->symbol); ?></td></tr>
                                                            <tr><td><?=lang('payment_status');?></td>
                                                                <td>
                                                                    <?php if($order->payment_method == 'installment'):;?>
                                                                        <div class="row">
                                                                            <div class="col-sm-9">
                                                                                <div class="progress" style="height: 18px;">
                                                                                    <div class="progress-bar progress-bar-striped progress-bar-success" role="progressbar" style="width: <?=(!empty($pay_progress['sale_'.$order->id]))?$pay_progress['sale_'.$order->id]:'2'?>%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"><?=$pay_progress['sale_'.$order->id]?> %</div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-3"><span class="label <?=($order->payment_status == 'paid')?'label-success' : 'label-info';?>"><?=lang($order->payment_status);?></span></div>
                                                                        </div>

                                                                        <?php else :  ;?>
                                                                        <span class="label <?=($order->payment_status == 'paid')?'label-success' : 'label-info';?>"> <?= ($order->payment_status ? lang($order->payment_status) : lang('no_payment'))?></span>
                                                                    <?php endif;?>
                                                                </td>
                                                            </tr>
                                                            <tr><td><?=lang('delivery_status');?></td><td><span class="label  <?=($order->delivery_status == 'delivered' ? 'label-success' : 'label-info');?>"> <?=($order->delivery_status ? lang($order->delivery_status) : lang('verifying'))?></span></td></tr>
                                                            <?php if($this->db->get_where('installment_pay_record',['sale_id'=>$order->id])->num_rows() == 0 and $order->payment_method == 'installment'):;?>
                                                                <tr>
                                                                    <td>Notice</td>
                                                                    <td>
                                                                        <a href="<?=shop_url('installment/'.$order->id);?>" class="btn btn-theme">Complete Order</a>
                                                                    </td>
                                                                </tr>
                                                            <?php endif;?>
                                                        </table>
                                                        <span class="count"><i><?= $order->id; ?></i></span>
                                                        <span class="edit"><i class="fa fa-eye"></i></span>
                                                    </a>
                                                    
                                                    <?php if($this->db->get_where('payments',['sale_id'=>$order->id])->num_rows() == 0 ):;?>
                                                        <span class="delete_sale" data-container="body" data-toggle="popover" data-placement="left" data-content="<p>Etre vous sûre ?</p><a class='btn btn-danger po-delete delete_this_sale' data-id='<?=$order->id;?>' data-tag='#order_no<?=$order->id;?>' href='##' >Oui j’en suis sûre</a> <button class='btn po-close'>Non</button>"><i class="fa fa-trash"></i></span>
                                                    <?php endif;?>
                                                </div>
                                            <?php endforeach;?>
                                    </div>
                                    <?php else :  ;?>
                                        <strong><?=lang('no_data_to_display');?></strong>
                                <?php endif;?>


                            </div>
                        </div>
                    </div>

                    <div class="col-sm-3 col-md-2">
                        <?php include 'sidebar1.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<script>
    $(document).on('click','.delete_sale',{passive:true},function () {
        const popover_tag = $('#'+$(this).attr('aria-describedby')+'');
        const content = $(this).attr('data-content');
        $(popover_tag).find('.popover-content').html(content);

        const top =  parseInt($(popover_tag).css('top'));
        const left =  parseInt($(popover_tag).css('left'));

        $(popover_tag).css('top',''+(top+18)+'px');
        $(popover_tag).css('left',''+(left+90)+'px')
    });

    $(document).on('click','.delete_this_sale',{passive:true},function () {
        const id = $(this).attr('data-id');
        const tag = $($(this).attr('data-tag'));
        const t = $(this);

        aoData = [
            {name:'<?= $this->security->get_csrf_token_name() ?>' , value:'<?=$this->security->get_csrf_hash() ?>'},
            {name:'id',value:id},
        ];
        $.post(base_url+'shop/delete_sale',aoData, function (response) {
            if(response.status === true){
                show_message('success' ,response.message);
                const pop = $(t).parents('.popover.fade').remove();
                $(tag).hide('slow');
                setTimeout(function(){
                    $(tag).remove();
                },500);
            }

            else{
                show_message('error' , response.message);
            }
        })
    })

    $(document).on('click','.po-close',{passive:true},function () {
        const pop = $(this).parents('.popover.fade').remove();
    });
</script>