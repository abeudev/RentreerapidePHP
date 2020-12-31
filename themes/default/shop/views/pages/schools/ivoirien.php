<?php /** Created by PhpStorm. User: john Date: 9/3/2020 Time: 12:28 PM */?>
<div class="panel panel-default margin-top-lg">
    <div class="panel-heading text-bold">
        <div class="form-group">
            <?=lang('schools');?> IVOIRIEN
            <div class="col-sm-4 pull-right">
                <input type="text" class="form-control search_school" placeholder="<?=lang('Recherche');?>">
            </div>
        </div>
    </div>
</div>
<?php foreach($schools as $school):?>
    <?php if($school->systm == 'ivoirien'):;
        $cycles = explode('-',$school->cycle);
        for($i=0; $i<= count($cycles); $i++){
            if(!empty($cycles[$i])){
                switch ($cycles[$i]){
                    case 'primaire':  $cycles[$i] = 'primary'; break;
                    case 'secondaire': $cycles[$i] = 'secondary'; break;
                }
            }
        }
        $div = count($cycles) + 1;

        $col = 'col-sm-3';
        switch ($div){
            case '1':case '2' : $col = 'col-sm-6'; break;
            case '3' : $col = 'col-sm-4'; break;
            case '4' :$col = 'col-sm-4'; break;
        }

        $school_classes = $this->db->where_in('teaching_type',$cycles)->get('classes')->result();
    ?>
        <div>
            <ul class="col-sm-6 school-item" data-name="<?=strtolower($school->name);?> <?=lang($school->cycle);?> <?=lang($school->systm);?>">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle btn btn-white" href="#">
                        <?php
                        switch ($school->systm){
                            case 'ivoirien': echo '<div class="text-center flag"><span class="l orange"></span> <span class="m white"></span><span class="r green"></span></div>'; break;
                            case 'francais': echo '<div class="text-center flag"><span class="l cblue"></span><span class="m white"></span><span class="r red"></span></div>'; break;
                        }
                        ?>
                        <span data-placement="left" title="<?= lang('actions') ?>"><?=ucwords($school->name);?></span>
                    </a>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                        <div class="pre">
                            <code><i class="fas fa-map-marker-alt"></i> <?= $school->address; ?></code>
                            <br>
                            <code><i class="fas fa-phone"></i> (<small><?= $school->phone; ?></small> )</code>
                            <small><?= lang($school->cycle); ?></small>
                        </div>
                        <?php if(!empty($school_classes)):;?>
                            <li>
                                <?php
                                $class_chunk = array_chunk($school_classes , ceil(count($school_classes) / $div));
                                foreach($class_chunk as $classes):?>
                                    <div class="<?=$col;?>">
                                        <?php foreach($classes as $class):?>
                                            <?php $btn_state = (!empty($founitures["school_{$school->id}_class_{$class->id}"]))?null:'disabled'; ?>
                                            <?php $btn_style = (!empty($founitures["school_{$school->id}_class_{$class->id}"]))?'btn-primary':'btn-default'; ?>
                                            <button class="load_stuffs <?=$btn_style;?> btn btn-block <?=$btn_state;?>" data-school_id="<?=$school->id;?>" data-class_id="<?=$class->id;?>" <?=$btn_state;?>><span><?=$class->level;?></span></button>
                                        <?php endforeach;?>
                                    </div>
                                <?php endforeach;?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
        </div>
    <?php endif;?>

<?php endforeach;?>
