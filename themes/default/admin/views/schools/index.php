<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .l, .m, .r{width:10px; display: table-cell;height: 20px;margin: 0px; padding: 0px !important;}
    .orange{background-color: orange;}
    .white{background-color: white;}
    .green{background-color: green;}
    .cblue{background-color: blue;}
    .red{background-color: red;}
    .text-white{color:white !important;}
</style>
<script>
    function name(x){
        return '<span class="tip" data-placement="top" title="'+x.toUpperCase()+'">'+limit_string(x.toUpperCase() , 20)+'</span>'
    }

    function dren(x){
        return '<span class="badge orange text-white">'+x+'</span>'
    }

    function order(x){
        return '<span class="badge green text-white">'+x+'</span>';
    }

    function niveau(x){
        switch (x){
            case 'primary': return 'Primaire'; break;
            case 'secondary': return 'Secondaire' ;break;
            case 'superior': return limit_string('supérieur' , 10); break;
            case 'primary_secondary': return limit_string('Primaire-Secondaire' , 10); break;
            case 'preschool1' : return limit_string('préscolaire' , 10); break;
            case 'preschool2' : return limit_string('Maternelle' , 10); break;
            case 'primary_secondary_superior': return limit_string('Primaire-secondaire-superior' , 10); break;
            default : return limit_string(x.replaceAll('_','-') , 10); break;
        }
    }

    function systm(x){
        switch (x.toLowerCase()){
            case 'ivoirien': return '<div class="text-center"><span class="l orange"></span> <span class="m white"></span><span class="r green"></span></div>'; break;
            case 'francais': return '<div class="text-center"><span class="l cblue"></span><span class="m white"></span><span class="r red"></span></div>'; break;
            default :  return '<div class="text-center"><span class="l cblue"></span><span class="m white"></span><span class="r red"></span></div>'; break;
        }
    }
    function type(x){
        switch(x){
            case 'private': case 'prive'   : return 'Privé'; break;
            case'public'    : return 'Public'; break;
        }
    }
    $(document).ready(function () {
        oTable = $('#NTTable').dataTable({
            "aaSorting": [[1, "asc"], [2, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('schools/getSchools') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
                {"bVisible": false , "bSortable":false}, //id
                {"bSortable":true , "mRender":systm}, //system
                null, //address
                null, //code
                {"mRender":name} , //name
                {"bSortable": true, "mRender": niveau}, //cycle
                {"bSortable": false}, //phone
                {"bSortable": true , "mRender":dren}, //dren
                {"bSortable":  true, "mRender":order}, //ordre
                {"bSortable":true, "mRender":type},//type
                {"bSortable": false}, //action
            ]
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i><?= lang('schools'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip"  data-placement="left" title="<?= lang('actions') ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= admin_url('schools/add_school'); ?>" data-toggle="modal" data-target="#myModal" id="add">
                                <i class="fa fa-plus-circle"></i> <?= lang('add'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('schools/import_csv'); ?>" data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-plus-circle"></i> <?= lang('importer_par_excell'); ?>
                            </a>
                        </li>

                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="NTTable" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo $this->lang->line('system'); ?></th>
                            <th><?php echo $this->lang->line('address'); ?></th>
                            <th><?php echo $this->lang->line('code'); ?></th>
                            <th><?php echo $this->lang->line('name'); ?></th>
                            <th><?php echo $this->lang->line('Cycle'); ?></th>
                            <th><?php echo $this->lang->line('phone'); ?></th>
                            <th><?php echo $this->lang->line('dren'); ?></th>
                            <th><?php echo $this->lang->line("Ordre d'Enseignement"); ?></th>
                            <th><?php echo $this->lang->line('type'); ?></th>
                            <th><?php echo $this->lang->line('Actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>