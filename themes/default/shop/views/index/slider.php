<?php if(!empty($slider)):;?>
    <section class="slider-container" style="background-color: white">
        <div class="container-fluid" style="background-color: white">
            <div class="row">
                <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
                    <ol class="carousel-indicators margin-bottom-sm">
                        <?php
                        $sr = 0;
                        foreach ($slider as $slide) {
                            if (!empty($slide->image)) {
                                echo '<li data-target="#carousel-example-generic" data-slide-to="' . $sr . '" class="' . ($sr == 0 ? 'active' : '') . '"></li> ';
                            }
                            $sr++;
                        } ?>
                    </ol>

                    <div class="carousel-inner" role="listbox">
                        <?php
                        $sr = 0;
                        foreach ($slider as $slide) {
                            if (!empty($slide->image)) {
                                echo '<div class="item' . ($sr == 0 ? ' active' : '') . '">';
                                if (!empty($slide->link)) {
                                    echo '<a href="' . $slide->link . '">';
                                }
                                echo '<img class="lazy" data-src="' . base_url('assets/uploads/slides/' . $slide->image) . '" alt="">';
                                if (!empty($slide->caption)) {
                                    echo '<div class="carousel-caption">' . $slide->caption . '</div>';
                                }
                                if (!empty($slide->link)) {
                                    echo '</a>';
                                }
                                echo '</div>';
                            }
                            $sr++;
                        } ?>
                    </div>

                    <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
                        <span class="fa fa-chevron-left" aria-hidden="true"></span>
                        <span class="sr-only"><?= lang('prev'); ?></span>
                    </a>
                    <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
                        <span class="fa fa-chevron-right" aria-hidden="true"></span>
                        <span class="sr-only"><?= lang('next'); ?></span>
                    </a>
                </div>
            </div>
        </div>
    </section>
<?php endif;?>