<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?=$assets;?>custom_switch/component-custom-switch.min.css">

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
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i>Modifier Parametre de livraison</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-sm-6">
                <table id="zero_config" class="text-center table table-striped table-bordered" width="100%">
                    <thead>
                    <tr>
                        <th>Region</th>
                        <th>Ville</th>
                        <th>Description</th>
                        <th>
                            <a class="white" href="<?php echo admin_url('shop_settings/add_region'); ?>" data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-plus"></i> Ajouter
                            </a>
                        </th>
                        <th>visible</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach($regions as $region):
                            $checked = (($region->visible == '1')?'checked':null);
                            ?>
                            <tr id="row_<?=$region->id;?>" >
                                <td <?=(!empty($ville_count[$region->id]))? 'rowspan="'.((int)$ville_count[$region->id] +1).'"' : null;?>>
                                    <a href="<?php echo admin_url('shop_settings/edit_region/'.$region->id); ?>" data-toggle="modal" data-target="#myModal">
                                        <?=$region->region_name;?>
                                    </a>
                                 </td>

                            <?php if(!empty($ville_count[$region->id])):;?>
                                </tr>
                                <?php $add_switch = true; foreach($villes as $ville):?>
                                    <?php if($ville->parent_id == $region->id):;?>
                                        <tr id="row_<?=$ville->id;?>" >
                                            <td><?=ucfirst($ville->ville);?></td>
                                            <td><?=ucfirst($ville->description);?></td>
                                            <td>
                                                <a class="" href="<?php echo admin_url('shop_settings/edit_region/'.$ville->id); ?>" data-toggle="modal" data-target="#myModal"><i class="fa fa-pencil"></i></a>
                                                <button data-id="<?=$ville->id;?>" data-value="<?=ucfirst($ville->ville);?>" data-row="#row_<?=$ville->id;?>" class="btn-link delete_region" title="delete"><i class="fa fa-trash text-danger"></i></button>
                                                <button data-id="<?=$ville->id;?>" data-name="<?=ucfirst($ville->ville);?>" data-description="<?=$ville->description;?>" class="btn-link set_shiping_fees" title="set shipping fees"><i class="fa fa-money"></i></button>
                                            </td>
                                            <?php if($add_switch):;?>
                                                <td <?=(!empty($ville_count[$region->id]))? 'rowspan="'.((int)$ville_count[$region->id]).'"' : null;?>>
                                                    <div class="custom-switch custom-switch-xs pl-0">
                                                        <input class="custom-switch-input" id="reg_<?=$region->id?>_visible" type="text" <?=$checked;?>>
                                                        <label class="custom-switch-btn" for="reg_<?=$region->id?>_visible" data-region_id="<?=$region->id?>"></label>
                                                    </div>
                                                </td>
                                            <?php $add_switch = false; endif;?>
                                        </tr>
                                    <?php endif;?>
                                <?php endforeach;?>
                                <?php else :; ?>
                                <td>...</td>
                                <td><?=ucfirst($region->description);?></td>
                                <td>
                                    <a class="" href="<?php echo admin_url('shop_settings/edit_region/'.$region->id); ?>" data-toggle="modal" data-target="#myModal"><i class="fa fa-pencil"></i></a>
                                    <button data-id="<?=$region->id;?>" data-parent_id="<?=$region->parent_id;?>" data-value="<?=ucfirst($region->ville);?>" data-row="#row_<?=$region->id;?>" class="btn-link delete_region" title="delete"><i class="fa fa-trash text-danger"></i></button>
                                </td>
                                <td>
                                    <div class="custom-switch custom-switch-xs pl-0">
                                        <input class="custom-switch-input" id="reg_<?=$region->id?>_visible" type="text" <?=$checked;?>>
                                        <label class="custom-switch-btn" data-region_id="<?=$region->id?>" for="reg_<?=$region->id?>_visible"></label>
                                    </div>
                                </td>
                            <?php endif;?>
                        <?php endforeach;?>
                    </tbody>

                    <tfoot>

                    </tfoot>
                </table>
            </div>

            <div class="col-sm-6">
                <table id="zero_config" class="text-center table table-striped table-bordered" width="100%">
                    <thead>
                    <tr>
                        <th>Group</th>
                        <th>Description</th>
                        <th>Actions
                            <button id="add_group" class="btn-link action-btn text-white pull-right" title="add group"><i class="fa fa-plus"></i></button>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($groups as $group):?>
                        <tr id="row_<?=$group->id;?>">
                            <td><?=ucfirst($group->group_name);?></td>
                            <td><?=$group->description;?></td>
                            <td>
                                <button data-id="<?=$group->id;?>" data-name="<?=ucfirst($group->group_name);?>" data-description="<?=$group->description;?>" class="btn-link edit_group" title="edit"><i class="fa fa-pencil"></i></button>
                                <button data-id="<?=$group->id;?>" data-value="<?=ucfirst($group->group_name);?>" data-row="#row_<?=$group->id;?>" class="btn-link delete_group" title="delete"><i class="fa fa-trash text-danger"></i></button>
                            </td>
                        </tr>
                    <?php endforeach;?>
                    </tbody>
                    <tfoot>

                    </tfoot>
                </table>
            </div>
        </div>
        <div id="add_group_form_container" class="dis-none">
            <?php $attrib = ['class'=>'ajax-form' , 'onsubmit'=>'ajax_submit_form_callback = after_action']; echo admin_form_open('shop_settings/shipping_settings/add_group', $attrib); ?>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="regions_name">Group Name</label>
                        <input type="text" id="group_name" name="group_name" class="form-control" required>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="description1">Description</label>
                        <input type="text" id="description1" name="description" class="form-control">
                    </div>
                </div>

                <div class="col-sm-12 text-right">
                    <button type="submit" class="btn btn-primary">Add</button>
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

    $('#shipping_settings').addClass('active');
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
    //==================================================================================================================
    //=============================================REGIONS ACTION ======================================================
    //==================================================================================================================
    $(document).on('click','.delete_region',{passive:true},function () {
        const id = $(this).attr('data-id');
        const row = $($(this).attr('data-row'));
        const value = $(this).attr('data-value');
        aoData = [
            {name:'<?= $this->security->get_csrf_token_name() ?>' , value:'<?=$this->security->get_csrf_hash() ?>'},
            {name:'region_id',value:id},
        ];
        $.post(base_url+'admin/shop_settings/shipping_settings/delete_region',aoData,function () {
            remove_tag(row);
            show_message('success',''+value+' successfully removed');
        })
    });

    $(document).on('click','.set_shiping_fees',{passive:true},function () {
        const id = $(this).attr('data-id');
        const region_name = $(this).attr('data-name');
        const description = $(this).attr('data-description');

        aoData = [
            {name:'<?= $this->security->get_csrf_token_name() ?>' , value:'<?=$this->security->get_csrf_hash() ?>'},
            {name:'region_id',value:id},
            {name:'region_name',value:region_name},
            {name:'description',value:description},
        ];
        $.post(base_url+'admin/shop_settings/shipping_settings/get_product_group',aoData,function (page) {
            sweetModal1({html : page , title:'set Sheeping fee for '+region_name+''});
        })

    });

    //==================================================================================================================
    //==============================================GROUPS ACTION ======================================================
    //==================================================================================================================
    $(document).on('click','#add_group',{passive:true},function () {
        const form = $('#add_group_form_container').html();
        sweetModal1({html:form , title:'Add Group'})
    });

    $(document).on('click','.delete_group',{passive:true},function () {
        const id = $(this).attr('data-id');
        const row = $($(this).attr('data-row'));
        const value = $(this).attr('data-value');
        aoData = [
            {name:'<?= $this->security->get_csrf_token_name() ?>' , value:'<?=$this->security->get_csrf_hash() ?>'},
            {name:'region_id',value:id},
        ];
        $.post(base_url+'admin/shop_settings/shipping_settings/delete_region',aoData,function () {
            remove_tag(row);
            show_message('success',''+value+' successfully removed');
        })
    });

    $(document).on('click','.edit_group',{passive:true},function () {
        const id = $(this).attr('data-id');
        const region_name = $(this).attr('data-name');
        const description = $(this).attr('data-description');

        const html = '<form action="'+base_url+'admin/shop_settings/shipping_settings/edit_group" onsubmit="ajax_submit_form_callback = after_action" class="ajax-form" method="post">\n' +
            '                <div class="row">\n' +
            '                <div class="col-sm-6">\n' +
            '                    <div class="form-group">\n' +
            '                        <label for="group_name">Group Name</label>\n' +
            '                        <input type="text" id="group_name" name="group_name" value="'+region_name+'" class="form-control" required>\n' +
            '                        <input type="hidden" name="'+tk_name+'" value="'+tk_value+'">\n' +
            '                        <input type="hidden" name="group_id" value="'+id+'">\n' +
            '                    </div>\n' +
            '                </div>\n' +
            '\n' +
            '                <div class="col-sm-6">\n' +
            '                    <div class="form-group">\n' +
            '                        <label for="description">Description</label>\n' +
            '                        <input type="text" id="ed_description" name="description" value="'+description+'" class="form-control">\n' +
            '                    </div>\n' +
            '                </div>\n' +
            '\n' +
            '                <div class="col-sm-12 text-right">\n' +
            '                    <button type="submit" class="btn btn-primary">Edit</button>\n' +
            '                </div>\n' +
            '            </div>\n' +
            '            </form>'

        sweetModal1({html : html , title:'Edit Region'});
    });

    $(document).on('click','[data-toggle="modal"]',{passive:true},function () {
        console.log('changing type');
        const switches = $('.custom-switch-input');
        $(switches).attr('type','text');

        setTimeout(function(){
            $(switches).attr('type','checkbox');
        },2000);
    });

    $(document).on('click','.modal',{passive:true},function () {
        //make sure the custom switch input type is changed back to checkbox
        console.log('revert type');
        const switches = $('.custom-switch-input');
        $(switches).attr('type','checkbox');
    });


    $(document).on('click','.custom-switch-btn',{passive:true},function () {
        const input = $('#'+$(this).attr('for'));
        active_switch_tag = input;
        const init_state = $(input).prop('checked');
        const final_state =!init_state;
        const new_value = ((final_state)?'1' : '0');
        const region_id = $(this).attr('data-region_id');

        aoData = [
            {name:'<?= $this->security->get_csrf_token_name() ?>' , value:'<?=$this->security->get_csrf_hash() ?>'},
            {name:'region_id',value:region_id},
            {name:'new_value',value:new_value},
        ];

        $.post('<?=base_url('admin/shop_settings/shipping_settings/change_visibility');?>' , aoData , function (response) {
            show_message('success',response);
        });
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
