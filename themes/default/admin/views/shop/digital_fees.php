<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .text-white{color:white !important;}
    .dis-none{display: none !important;}
    .action-btn , .action-btn:hover{
        border-left: solid 1px white;
        padding: 0px 3px;
    }
</style>
<div id="message_area">

</div>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i> Modifier les Frais digitaux</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-sm-12">
                <table id="zero_config" class="text-center table table-striped table-bordered" width="100%">
                    <thead>
                    <tr>
                        <th>Min</th>
                        <th>Max</th>
                        <th>Valeur</th>
                        <th>Actions
                            <button id="add_fee" class="btn-link action-btn text-white pull-right" title="add Region"><i class="fa fa-plus"></i></button>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php foreach($fees as $fee):?>
                            <tr id="row_<?=$fee->id;?>">
                                <td><?=ucfirst($fee->min);?></td>
                                <td><?=$fee->max;?></td>
                                <td><?=$fee->tax;?></td>
                                <td>
                                    <button data-id="<?=$fee->id;?>" data-min="<?=ucfirst($fee->min);?>" data-max="<?=$fee->max;?>" data-tax="<?=$fee->tax;?>" class="btn-link edit_fee" title="Modifier"><i class="fa fa-pencil"></i></button>
                                    <button data-id="<?=$fee->id;?>" data-row="#row_<?=$fee->id;?>" class="btn-link delete_fee" title="Suprimer"><i class="fa fa-trash text-danger"></i></button>
                                </td>
                            </tr>
                        <?php endforeach;?>
                    </tbody>
                    <tfoot>

                    </tfoot>
                </table>
            </div>
        </div>

        <div id="add_fee_form_container" class="dis-none">
            <?php $attrib = ['class'=>'ajax-form' , 'onsubmit'=>'ajax_submit_form_callback = after_action']; echo admin_form_open('shop_settings/digital_fees/add_fee', $attrib); ?>
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="min">Montant Minimum</label>
                        <input type="number" id="min" name="min" class="form-control" required>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="max">Montant Maximum</label>
                        <input type="number" id="max" name="max" class="form-control">
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="tax">Frais</label>
                        <input type="number" id="tax" name="tax" class="form-control">
                    </div>
                </div>

                <div class="col-sm-12 text-right">
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </div>
            </form>
        </div>
    </div>

    <a data-toggle="modal" href="#SweetModal1" class="dis-none"></a>
    <div class="modal fade col-xs-12" id="SweetModal1" tabindex="-1" role="dialog" aria-hidden="true">
        <div id="sweetModalDialog" class="modal-dialog">
            <div class="modal-content" style="top:40px">
                <div class="modal-header">
                    <h5 id="sweetModalTitle" class="modal-title"></h5>
                    <button type="button" class="close close_modal1" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <button type="button" class="btn-link pull-right print_me dis-none" data-target="#SweetModal1 #sweetModalBody" style="margin-top:-5px" data-toggle="tooltip" data-placement="top" title="Imprimer"><i class="mdi-printer mdi"></i></button>
                </div>
                <div id="sweetModalBody" class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const tk_name = '<?=$this->security->get_csrf_token_name();?>';
    const tk_value = '<?=$this->security->get_csrf_hash();?>';
    const base_url = '<?=base_url();?>';

    $('#digital_fees').addClass('active');
    var ajax_submit_form_callback = function(){
        console.log('default_call_back');
    };

    function validate_form(form_id){
        required_fields = $('#'+form_id+' *[required]');

        var the_form_id = form_id+'';

        for(var i = 0 ; i < required_fields.length ; i++){

            var field = $(required_fields[i]);

            if(field.attr('type') != 'hidden' && field.attr('type') != 'file')
            {
                field.keyup();
                if(field.val().trim().length>0)
                {
                    var is_valid_field = '<small id="chk_field_'+i+'"><i class="text-success fas fa-check check"></i></small>';
                    $('#chk_field_'+i).remove();
                    field.removeClass('is-invalid');
                    field.addClass('is-valid');
                }
                else
                {
                    var is_invalid_field = '<small id="chk_field_'+i+'"><i class="text-danger fas fa-times check"></i></small>';
                    $('#chk_field_'+i).remove();
                    field.removeClass('is-valid');
                    field.addClass('is-invalid');
                }

                if(field.attr('type') === 'checkbox'){
                    if(field.hasClass('terms_condition'))
                    {
                        if($('[type="checkbox"]').prop('checked') === false)
                        {
                            let msgg = '<div id="term_cond_msg"><small class="text-danger small">You must agree with our terms of usage and conditions</small></div>';
                            field.removeClass('is-valid');
                            field.addClass('is-invalid');
                            field.parent().after(msgg);
                        }
                        else
                        {
                            field.removeClass('is-invalid');
                            field.addClass('is-valid');
                            $('#term_cond_msg').remove();
                        }
                    }
                }
            }

        }

        total_error = $('#'+the_form_id+' .is-invalid , #'+the_form_id+' .text-danger').length;

        console.log('total-invalid field = '+total_error);

        return total_error <= 0;


    }

    function show_message(notif_type , message) {
        if(typeof message === 'undefined'){message = 'Alert Notification'}
            if(notif_type === 'error') notif_type = 'danger';
            type = [notif_type];
            const msg = '<div class="alert alert-'+notif_type+'"> <button data-dismiss="alert" class="close" type="button">×</button>'+message+'</div>'
        $('#message_area').html(msg);
    }

    function ajaxSubmit(e, form, callBackFunction) {
        function default_function(){}
        callBackFunction = callBackFunction || default_function();
        const form_id = $(form).attr('id');
        if(validate_form(form_id)) {
            e.preventDefault();
            const action = form.attr('action');
            const form2 = e.target;
            const data = new FormData(form2);
            $.ajax({
                type: "POST",
                url: action,
                processData: false,
                contentType: false,
                data: data,
                success: function(response)
                {
                    if(response.length > 0){
                        response = $.parseJSON(response);
                        if (response.status == true) {
                            close_modal1();
                            show_message('success',response.message);

                            if(typeof callBackFunction !== 'undefined'){
                                callBackFunction();
                            }
                        }
                        else{
                            show_message('error',response.message);
                        }
                    }

                    ajax_submit_form_callback = function(){
                        console.log('default callback');
                    }
                },
                error:function () {
                    show_message('error','An error Occurred! Please try again')
                }
            });
        }else {
            show_message('error','Please make sure to fill all the necessary fields')
        }
    }

    $(document).on('submit','.ajax-form , .ajax_form',{passive:true},function (e) {
        e.preventDefault();
        const form = $(this);
        ajaxSubmit(e, form , ajax_submit_form_callback);
    });

    $(document).on('click','#add_fee',{passive:true},function () {
        const form = $('#add_fee_form_container').html();
        sweetModal1({html:form , title:'Add Region'})
    });

    $(document).on('click','.edit_fee',{passive:true},function () {
        const id = $(this).attr('data-id');
        const min = $(this).attr('data-min');
        const max = $(this).attr('data-max');
        const tax = $(this).attr('data-tax');

        const html = '<form action="'+base_url+'admin/shop_settings/digital_fees/edit_fees" onsubmit="ajax_submit_form_callback = after_action" class="ajax-form" method="post">\n' +
            '                <div class="row">\n' +
            '                <div class="col-sm-4">\n' +
            '                    <div class="form-group">\n' +
            '                        <label for="ed_min">Min</label>\n' +
            '                        <input type="text" id="ed_min" name="min" value="'+min+'" class="form-control" required>\n' +
            '                        <input type="hidden" name="'+tk_name+'" value="'+tk_value+'">\n' +
            '                        <input type="hidden" name="fee_id" value="'+id+'">\n' +
            '                    </div>\n' +
            '                </div>\n' +
            '\n' +
            '                <div class="col-sm-4">\n' +
            '                    <div class="form-group">\n' +
            '                        <label for="ed_max">Max</label>\n' +
            '                        <input type="text" id="ed_max" name="max" value="'+max+'" class="form-control" required>\n' +
            '                    </div>\n' +
            '                </div>\n' +
            '                <div class="col-sm-4">\n' +
            '                    <div class="form-group">\n' +
            '                        <label for="ed_tax">Max</label>\n' +
            '                        <input type="text" id="ed_tax" name="tax" value="'+tax+'" class="form-control" required>\n' +
            '                    </div>\n' +
            '                </div>\n' +

            '\n' +
            '                <div class="col-sm-12 text-right">\n' +
            '                    <button type="submit" class="btn btn-primary">Modifier</button>\n' +
            '                </div>\n' +
            '            </div>\n' +
            '            </form>'

        sweetModal1({html : html , title:'Modifier la grille'});
    });
    
    $(document).on('click','.delete_fee',{passive:true},function () {
        const id = $(this).attr('data-id');
        const row = $($(this).attr('data-row'));
        const value = $(this).attr('data-value');
        aoData = [
            {name:'<?= $this->security->get_csrf_token_name() ?>' , value:'<?=$this->security->get_csrf_hash() ?>'},
            {name:'fee_id',value:id},
        ];
        $.post(base_url+'admin/shop_settings/digital_fees/delete_fee',aoData,function () {
            remove_tag(row);
            show_message('success','Grille supprimer avec succès');
        })
    });


    function hide_loader(){
        $('#modal-loading').attr('style','display:none');
        $('.modal-backdrop.fade.in').remove()
    }
    function sweetModal1(params){
        //$('#SweetModal1').perfectScrollbar();
        params = params || {};
        var param = {};
        param.html = params.html || '';
        param.title = params.title || '';
        param.size = params.size || 'auto';
        param.top = params.top || '40px';

        $('#SweetModal1 #sweetModalBody').html(param.html);
        $('#SweetModal1 #sweetModalTitle').html(param.title);
        if(param.size !== 'auto'){
            $('#SweetModal1 #sweetModalDialog').attr('style','width:'+param.size+';max-width:'+param.size+'');
        }

        $('[href="#SweetModal1"]').click();
        
        setTimeout(function(){
            hide_loader();
        },300);
    }
    function close_modal1(){ $('.close_modal1').click(); }
    function remove_tag (tag){
        $(tag).hide('slow');
        setTimeout(function(){
            $(tag).remove();
        },900);
    }
    function do_ajax(action , data, call_back){
        console.log(action);
        function default_cb(){console.log('this is default cb')}
        call_back = call_back || default_cb();
        $.ajax({
            url: base_url+'admin/'+action,
            type: 'POST',
            data: aoData,
            error: function() {
                alert('Something is wrong');
            },
            success: function() {
                call_back();
            }
        });
    }
    function refresh() {
        location.href = location.origin+location.pathname;
    }

    function after_action(){
        setTimeout(function(){
            refresh()
        },1000);
    }
</script>
