<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?=$assets;?>custom_switch/component-custom-switch.min.css">
<style>
    <?php foreach($fonts as $font):?>
        <?=$font->url;?>
    <?php endforeach;?>

    <?php foreach($fonts as $font):?>
        .<?=strtolower(str_replace(' ','_',$font->name));?>{
            font-family: <?=$font->code;?> !important;
            font-size: large;
        }
    <?php endforeach;?>

    #query{
        border-radius: <?=$settings->search_border_radius;?>px 0px 0px <?=$settings->search_border_radius;?>px !important;
    }

    #search{
        border-radius: 0px <?=$settings->search_border_radius;?>px <?=$settings->search_border_radius;?>px 0px !important;
        width: <?=$settings->search_button_width;?>px;
    }
    
    #button_border{
        border-radius: <?=$settings->button_border_radius;?>px !important;
    }
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cogs"></i>Police et Styles</h2>
    </div>
</div>

<div class="box">
    <div class="box-header">
        <h2 class="blue">Police</h2>
    </div>
    <div class="box-content">
        <table id="zero_config" class="text-center table table-striped" width="100%">
            <thead>
            <tr>
                <th>Exemplaire</th>
                <th>Taille</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($fonts as $font):
                $font_class = strtolower(str_replace(' ','_',$font->name));
                $checked = ($font->id == $settings->font_id)?'checked':null;
                ?>
                <tr>
                    <td>
                        <div class="<?=$font_class;?>">Pour utiliser les codes de ressources dans les modèles HTML,</div>
                    </td>
                    <td>
                        <input data-font-id="<?=$font->id;?>" type="number" value="<?=(!empty($font_sizes[$font->id]))?$font_sizes[$font->id] : $settings->font_size;?>" class="form-control edit_font_size">
                    </td>
                    <td>
                        <div class="custom-switch custom-switch-xs pl-0">
                            <input class="custom-switch-input choose_font <?=$font_class;?>" id="font_<?=$font->id;?>" type="text" name="font_id" <?=$checked;?>>
                            <label class="custom-switch-btn" for="font_<?=$font->id;?>" data-font-id="<?=$font->id;?>"></label>
                        </div>
                    </td>
                </tr>
            <?php endforeach;?>
            </tbody>
        </table>
    </div>
</div>
<div class="box">
    <div class="box-header">
        <h2 class="blue">Barre de recherche</h2>
    </div>
    <div class="box-content">
       <div class="row">
           <div class="col-sm-3 border-right">
               <label for="query_range">Arrondissement des bordures</label>
               <input type="range" name="search_border_radius" min="0" max="42" value="<?=$settings->search_border_radius;?>" id="query_range" data-action="update_search" data-input="#query" data-button="#search">

               <label for="button_width">Largeur du bouton</label>
               <input type="range" name="search_button_width" data-action="update_search_btn_width" min="0" max="300" value="<?=$settings->search_button_width;?>" id="button_width" data-button="#search">
               <label for="button_content">Bouton de Recherche</label>
               <select id="button_content" name="search_button_content" class="form-control sup_setting_item" data-action="update_search_btn_content" data-button="#search">
                   <option value='<i class="fa fa-search"></i>' <?=($settings->search_button_content != 'Recherche')?'selected':null;?>>Icon</option>
                   <option value="Recherche" <?=($settings->search_button_content == 'Recherche')?'selected':null;?>>Text</option>
               </select>
           </div>


           <div class="col-sm-6">
               <div class="input-group">
                   <input type="text" id="query" class="form-control">
                   <div class="input-group-btn">
                       <button type="submit" id="search" class="btn btn-default btn-search"><?=$settings->search_button_content;?></button>
                   </div>
               </div>
           </div>
       </div>
    </div>
</div>

<div class="box">
    <div class="box-header">
        <h2 class="blue">Boutons</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-sm-3 border-right">
                <label for="query_range">Arrondissement des bordures</label>
                <input type="range" name="button_border_radius" min="0" max="42" value="<?=$settings->button_border_radius;?>" id="btn_radius" data-action="update_button_radius" data-input="" data-button="#button_border">
            </div>


            <div class="col-sm-6">
                <button id="button_border" class="btn btn-default btn-lg">Boutton</button>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).on('click','.custom-switch-btn',{passive:true},function () {
        const input = $('#'+$(this).attr('for'));
        active_switch_tag = input;
        const init_state = $(input).prop('checked');
        const final_state =!init_state;
        const font_id = $(this).attr('data-font-id');

        if(final_state){
            $('.custom-switch-input').prop('checked',false);
            setTimeout(function(){
                $(input).prop('checked',true);
            },300);

            aoData = [
                {name:'<?= $this->security->get_csrf_token_name() ?>' , value:'<?=$this->security->get_csrf_hash() ?>'},
                {name:'font_id',value:font_id},
            ];

            $.post('<?=base_url('admin/shop_settings/font_styles/update_font');?>' , aoData , function (response) {

                show_message('success',response);

            });
        }
    });
    
    $(document).on('blur','.edit_font_size',{passive:true},function () {
        const input = $(this);
        const font_id = $(this).attr('data-font-id');
        const value = $(this).val();
        aoData = [
            {name:'<?= $this->security->get_csrf_token_name() ?>' , value:'<?=$this->security->get_csrf_hash() ?>'},
            {name:'font_id',value:font_id},
            {name:'font_size',value:value},
        ];

        $.post('<?=base_url('admin/shop_settings/font_styles/update_font_size');?>' , aoData , function (response) {

            show_message('success',response);

        });
    });

    $(document).on('change','[type="range"] , .sup_setting_item',{passive:true},function () {
        const val = $(this).val();
        const input = $($(this).attr('data-input'));
        const button = $($(this).attr('data-button'));
        const action = $(this).attr('data-action');
        const name = $(this).attr('name');

        if(action === 'update_search'){
            $(input).attr('style','border-radius:'+val+'px 0px 0px '+val+'px !important;');
            $(button).attr('style','border-radius:0px '+val+'px '+val+'px 0px !important;');
        }

        else if(action === 'update_search_btn_width'){
            $(button).css('width',''+val+'px');

        }

        else if(action === 'update_button_radius'){
            $(button).attr('style','border-radius:'+val+'px !important;');
        }

        else if(action === 'update_search_btn_content'){
            $(button).html(val);
        }


        aoData = [
                    {name:'<?= $this->security->get_csrf_token_name() ?>' , value:'<?=$this->security->get_csrf_hash() ?>'},
                    {name:'key',value:name},
                    {name:'value',value:val},
                ];
        $.post('<?=base_url('admin/shop_settings/font_styles/update_settings');?>' , aoData , function (response) {
            show_message('success',response);
        })
    });

    $(document).on('click','[type="range"]',{passive:true},function () {
        const val = $(this).val();
        const input = $($(this).attr('data-input'));
        const button = $($(this).attr('data-button'));
        const action = $(this).attr('data-action');
        const name = $(this).attr('name');

        if(action === 'update_search'){
            $(input).attr('style','border-radius:'+val+'px 0px 0px '+val+'px !important;');
            $(button).attr('style','border-radius:0px '+val+'px '+val+'px 0px !important;');
        }

        else if(action === 'update_search_btn_width'){
            $(button).css('width',''+val+'px');
            console.log(button);
        }
    });
</script>