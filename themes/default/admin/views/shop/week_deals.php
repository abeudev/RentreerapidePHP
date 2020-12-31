<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link href="<?= $assets ?>datepicker/css/datepicker.min.css" rel="stylesheet"/>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i> <?=lang('edit_week_deals');?></h2>
    </div>
    <div class="box-content">
        <?php $attrib = []; echo admin_form_open('shop_settings/update_deals', $attrib); ?>
        <div class="row">
                <div class="col-lg-12">
                    <p class="introtext"><?= lang('enter_info'); ?></p>
                    <div class="row">
                        <div class="row">
                            <div class="col-md-12" id="sticker">
                                <div class="well well-sm">
                                    <div class="form-group" style="margin-bottom:0;">
                                        <div class="input-group wide-tip">
                                            <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                            <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang('au_pr_name_tip') . '"'); ?>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="control-group table-group">
                                    <label class="table-label"><?= lang('products'); ?> *</label>

                                    <div class="controls table-controls">
                                        <table id="qaTable" class="table items table-striped table-bordered table-condensed table-hover">
                                            <thead>
                                            <tr>
                                                <th class="col-md-2"><?= lang('image'); ?></th>
                                                <th><?= lang('product_name') . ' (' . lang('company') . ')'; ?></th>
                                                <th class="col-md-2"><?= lang('price'); ?></th>
                                                <th class="col-md-2"><?= lang('prix_promotion'); ?></th>
                                                <th class="col-md-1"><?= lang('quantity'); ?></th>
                                                <th class="col-md-2"><?= lang('Titre Deal'); ?></th>
                                                <th class="col-md-3"><?= lang('date_fin'); ?></th>
                                                <th style="max-width: 30px !important; text-align: center;">
                                                    <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                                </th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($week_deals as $deal):?>
                                                    <tr id="row_<?=$deal->product_id;?>" class="row_item" data-item-id="<?=$deal->product_id;?>">
                                                        <td>
                                                            <div class="text-center">
                                                                <img style="width: 62px" src="<?=base_url('assets/uploads/'.$deal->image);?>" alt="">
                                                            </div>
                                                        </td>                                                        <td>
                                                            <input name="product_id[]" type="hidden" class="rid" value="<?=$deal->product_id;?>">
                                                            <span class="sname" id="name_<?=$deal->product_id;?>"> <?=$deal->name;?>(<?=$deal->warehouse_name;?>)</span>
                                                        </td>
                                                        <td><div class="text-center"><?=$deal->price;?></div></td>
                                                        <td><input type="number" class="form-control text-center is-valid" max="<?=$deal->price;?>" value="<?=$deal->promotion_price;?>" name="promotion_price[]" required=""></td>
                                                        <td><input class="form-control text-center rquantity" min="1" required="" tabindex="2" name="quantity[]" type="text" value="<?=$deal->quantity;?>" data-id="<?=$deal->product_id;?>" data-item="<?=$deal->product_id;?>" id="quantity_<?=$deal->product_id;?>" onclick="this.select();"></td>
                                                        <td><input type="text" class="form-control text-center" value="<?=$deal->deal_title;?>" required="required" name="deal_title[]"></td>
                                                        <td><input type="text" class="form-control text-center datepicker-here future-date-only" value="<?=$deal->ending_date;?>" autocomplete="off" data-date-format="yyyy-mm-dd" data-timepicker="true" data-time-format="hh:ii:00" required="required" name="ending_date[]"></td>
                                                        <td class="text-center"><i class="fa fa-times tip qadel remove_item" data-row="#row_<?=$deal->product_id;?>" id="rem_<?=$deal->product_id;?>" title="Supprimer" style="cursor:pointer;"></i></td>
                                                    </tr>
                                                <?php endforeach;?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <div class="row">
            <div class="text-right">
                <button type="submit" class="btn btn-primary">Enregister</button>
            </div>
        </div>
        </form>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>datepicker/js/datepicker.js"></script>
<script>
    var base_url = '<?=base_url();?>';
     function init_datepicker(){
        let datepicker_tags = $('.datepicker-here');
        let datepicker_range_tags = $('.datepicker-range-here');
        let datepicker_calendar_tags = $('.datepicker-calendar-here');

        if(datepicker_tags.length>0){
            for(var i = 0; i<datepicker_tags.length; i++){
                var datepicker_tag = $(datepicker_tags[i]);
                //set max date to actual date
                if(!datepicker_tag.hasClass('allow-all-date') && !datepicker_tag.hasClass('future-date-only')){
                    datepicker_tag.datepicker({maxDate: new Date(),autoClose:true});
                }

                if(datepicker_tag.hasClass('future-date-only')){
                    datepicker_tag.datepicker({minDate: new Date(),autoClose:true });
                }

                if(datepicker_tag.hasClass('allow-all-date')){
                    datepicker_tag.datepicker({autoClose:true});
                }
            }
        }

        if(datepicker_range_tags.length > 0){
            for(var u = 0; u<datepicker_range_tags.length ; u++){
                var datepicker_range_tag = $(datepicker_range_tags[u]);
                //set max date to actual date
                if(!datepicker_range_tag.hasClass('allow-all-date') && !datepicker_range_tag.hasClass('future-date-only')){
                    datepicker_range_tag.datepicker({maxDate: new Date(),range:true,inline:false , autoClose:true});
                }
                if(datepicker_range_tag.hasClass('future-date-only')){
                    var autoclose = datepicker_range_tag.attr('data-autoclose') || true;
                    if(has_attr(datepicker_range_tag , 'data-autoclose')){autoclose = (autoclose === 'true')}

                    var option = {
                        minDate: new Date(),
                        range: true,
                        inline: false,
                        autoClose: autoclose,
                    }
                    datepicker_range_tag.datepicker(option);
                }

                if(datepicker_range_tag.hasClass('allow-all-date')){
                    datepicker_range_tag.datepicker({range:true,inline:false , autoClose:true});
                }
            }

        }

        if(datepicker_calendar_tags.length > 0){
            for(var v = 0 ; v<datepicker_calendar_tags.length ; datepicker_calendar_tags++){
                var datepicker_calendar_tag = $(datepicker_calendar_tags[v]);
                datepicker_calendar_tag.datepicker({inline:true});
            }
        }
    }
    var count = 1, an = 1;
    var type_opt = {'addition': '<?= lang('addition'); ?>', 'subtraction': '<?= lang('subtraction'); ?>'};
    $(document).ready(function () {
        init_datepicker();
        $("#add_item").autocomplete({
            source: '<?= admin_url('products/qa_suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
//
                    $(this).removeClass('ui-autocomplete-loading');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_adjustment_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
    });


    /* -----------------------------
     * Add Purchase Item Function
     * @param {json} item
     * @returns {Boolean}
     ---------------------------- */
    function add_adjustment_item(item) {

        var count = 1;
        if (count == 1) {
            qaitems = {};
        }
        if (item == null)
            return;

        var item_id =  item.id;
        if (qaitems[item_id]) {

            var new_qty = parseFloat(qaitems[item_id].row.qty) + 1;
            qaitems[item_id].row.base_quantity = new_qty;
            if(qaitems[item_id].row.unit != qaitems[item_id].row.base_unit) {
                $.each(qaitems[item_id].units, function(){
                    if (this.id == qaitems[item_id].row.unit) {
                        qaitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                    }
                });
            }
            qaitems[item_id].row.qty = new_qty;

        } else {
            qaitems[item_id] = item;
        }
        qaitems[item_id].order = new Date().getTime();
        localStorage.setItem('qaitems', JSON.stringify(qaitems));
        loadItems();
        return true;
    }

    function loadItems() {
        if (localStorage.getItem('qaitems')) {
            qaitems  = JSON.parse(localStorage.getItem('qaitems'));
            sortedItems = (site.settings.item_addition == 1) ? _.sortBy(qaitems, function(o){return [parseInt(o.order)];}) : qaitems;
            $.each(sortedItems, function () {
                var item = this;
                var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
                item.order = item.order ? item.order : new Date().getTime();
                var product_id = item.row.id, item_price = item.row.price, item_qty = item.row.qty, item_option = item.row.option, item_code = item.row.warehouse_name, item_image = item.row.image, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
                var type = item.row.type ? item.row.type : '';


                var row_no = item.id;
                var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');


                tr_html = '<tr id="row_' + row_no + '" class="row_item row_' + item_id + '" data-item-id="' + item_id + '">';
                tr_html += '<td><div class="text-center"><img style="width: 62px" src="'+base_url+'assets/uploads/'+item_image+'" alt=""></div></td>';
                tr_html += '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><span class="sname" id="name_' + row_no + '"> ' + item_name +' ('+item_code+')</span></td>';
                tr_html += '<td><div class="text-center">'+item_price+'</div></td>';
                tr_html += '<td><input type="number" class="form-control text-center" max="'+(item_price)+'" name="promotion_price[]" required></td>';
                tr_html += '<td><input class="form-control text-center rquantity" min="1" required tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="quantity[]" type="text" value="' + formatQuantity2(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
                tr_html += '<td><input type="text" class="form-control text-center" required="required" name="deal_title[]"></td>';
                tr_html += '<td><input type="text" class="form-control text-center datepicker-here future-date-only" id="date_'+row_no+'" autocomplete="off" data-date-format="yyyy-mm-dd" data-timepicker="true" data-time-format="hh:ii:00" required="required" name="ending_date[]"></td>';
                tr_html += '<td class="text-center"><i class="fa fa-times tip qadel remove_item" data-row="#row_' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                tr_html +='</tr>';

                $('#qaTable').append(tr_html);

                count += parseFloat(item_qty);
                an++;

                $('#date_'+row_no+'').datepicker({minDate: new Date(),autoClose:true });

            });
        }
    }

     $(document).on('keyup blur','input[min], input[max]',function () {
         if($(this).attr('type') == 'number'){
             let tag = $(this);
             let tag_id = get_id(this);
             let value = $(this).val();

             value = ((value.length > 0)?value : 0);
             let display = (($(this).hasClass('hide-error'))?'dis-none':'dis-block');
             //has min but not max
             if(has_attr(this,'min') && !has_attr(this,'max')){
                 console.log('has min but not max');
                 let min_length = $(this).attr('min');
                 let msg_tag = $('#mcmsg_'+tag_id+'');
                 msg_tag.remove();

                 if(parseFloat(value)<min_length){
                     let min_char_msg = '<small class="text-danger '+display+'" id="mcmsg_'+tag_id+'">La valeur doit être au moins '+min_length+'</small>';
                     $(this).removeClass('is-valid');
                     $(this).addClass('is-invalid');
                     $(this).after(min_char_msg);
                 }
                 else{
                     $(this).removeClass('is-invalid');
                     $(this).addClass('is-valid');
                     msg_tag.remove();
                 }
             }
             //has max but not min
             else if(!has_attr(this,'min') && has_attr(this,'max')){
                 console.log('has max but not min');

                 let max_length = $(this).attr('max');
                 let msg_tag = $('#macmsg_'+tag_id+'');
                 msg_tag.remove();

                 if(parseFloat(value)>max_length){
                     let max_char_msg = '<small class="text-danger '+display+'" id="macmsg_'+tag_id+'">La valeur doit être au en dessous de '+max_length+'</small>';
                     $(this).removeClass('is-valid');
                     $(this).addClass('is-invalid');
                     $(this).after(max_char_msg);
                 }
                 else{
                     $(this).removeClass('is-invalid');
                     $(this).addClass('is-valid');
                     msg_tag.remove();
                 }
             }
             // has min , has max
             else{
                 let min_length = $(this).attr('min');
                 let max_length = $(this).attr('max');
                 //exactly
                 if(min_length===max_length){
                     console.log('exact');
                     let msg_tag = $('#excmsg_'+tag_id+'');
                     msg_tag.remove();
                     if(parseFloat(value)>max_length || parseFloat(value)<min_length){
                         ex_char_msg = '<small class="text-danger '+display+'" id="excmsg_'+tag_id+'">La valeur doit être exactement '+max_length+'</small>';
                         $(this).removeClass('is-valid');
                         $(this).addClass('is-invalid');
                         $(this).after(ex_char_msg);
                     }
                     else{
                         $(this).removeClass('is-invalid');
                         $(this).addClass('is-valid');
                         msg_tag.remove();
                     }

                 }

                 //between
                 else{
                     // console.log('between : '+min_length+' and '+max_length);
                     let msg_tag = $('#betcmsg_'+tag_id+'');
                     let remain_msg_tag = $('#remain_char_msg_'+tag_id+'');

                     var remaining_tag = parseInt(max_length) - parseInt(value);
                     msg_tag.remove();
                     remain_msg_tag.remove();
                     if(parseFloat(value)<min_length || parseFloat(value)>max_length)
                     {
                         let bet_char_msg = '<small class="text-danger '+display+'" id="betcmsg_'+tag_id+'">La valeur doit être entre '+min_length+' et '+max_length+'</small>';
                         $(this).removeClass('is-valid');
                         $(this).addClass('is-invalid');
                         $(this).after(bet_char_msg);
                         remain_msg_tag.remove();
                     }
                     else{
                         let remaining_char_msg = '<small class="text-success text-right '+display+'" id="remain_char_msg_'+tag_id+'">'+remaining_tag+'</small>';

                         $(this).removeClass('is-invalid');
                         $(this).addClass('is-valid');
                         $(this).after(remaining_char_msg);
                         msg_tag.remove();
                     }

                 }
             }
         }
     });

    function get_id(tag){
        return $(tag).attr('id');
    }

    function has_attr(obj,attrib){
         var attr_val = $(obj).attr(attrib);
         return (typeof  attr_val !== 'undefined')
     }

    $(document).on('click','.remove_item',{passive:true},function () {
         const row = $($(this).attr('data-row'));

         $(row).hide('slow');
         setTimeout(function(){
             $(row).remove();
         },500);

     });
</script>
