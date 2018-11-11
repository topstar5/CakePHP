<?php

$QA = output('FAQ_PAIRS');

if (count($QA) != 0) {
    $x = 0;
    ?>
    <ul class="list-unstyled">
        <?php

        foreach ($QA as $pair) {
            ?>
            <li>
                <a data-toggle="collapse" data-parent="#accordion" href="#A_<?=$x?>" aria-expanded="true" aria-controls="#A_<?=$x?>">
                    <h4><i class="fa fa-question-circle"></i> <?=$pair['Q']?></h4>
                </a>
                <div id="A_<?=$x?>" class="collapse" role="tabpanel">
                    <p><?=$pair['A']?></p>
                    <br>
                </div>
            </li>
            <?php
            $x++;
        }

        ?>
    </ul>
<?php
}