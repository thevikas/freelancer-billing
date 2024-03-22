<?php
//@see https://www.figma.com/file/rwSruzWDPly1ijfI6YbJSt/Freelancer-Billing?type=design&node-id=1-2&t=1XT6kARUyrLPVaDQ-0 

use yii\bootstrap\Html;

$data = $proj->data;

function _box($label, $txt)
{
?>
    <div class="box">
        <div class="lbl">
            <?= $label ?>
        </div>
        <div class="txt">
            <?= $txt ?>
        </div>
    </div>
<?php
}


if(0) 
    echo "<pre>" . print_r($data,true) . "</pre>";

?>
<h2><?=$data['billing']['name']?></h2>

<style type="text/css">
    .frame,.header {
        display: flex;
        flex-direction: row;
    }
    .addressbox,.boxes {
        padding: 1em;
    }
    .box {
        padding: .5em;
    }
</style>

<div id="<?= $projcode ?>" class="header">

    <div class="addressbox">
        <div class="address"><?= $data['billing']['address'] ?></div>
        <div class="email"><?= $data['billing']['email'] ?></div>
        <div class="email"><?= $data['billing']['phone'] ?? "" ?></div>
    </div>

    <div class="boxes">
        <div class="frame" id="frame1">
            <?php
                echo _box(__("Currency"), $data['ccy'] );
                echo _box(__("Per Hour"), $data['per_hour'] );
                echo _box(__("Access"), $data['accesstoken'][0] ?? "Not granted" );
            ?>
        </div>
        <h4>Last Invoice</h4>
        <div class="frame" id="frame2">
            <?php
                echo _box(__("Invoice#"), $data['ccy'] );
                echo _box(__("Date"), $data['per_hour'] );
                echo _box(__("Amount"), 'TODO' );  //#TODO
            ?>
        </div>
        <h4>Current Period</h4>
        <div class="frame" id="frame2">
            <?php
                echo _box(__("Hours"), $data['current']['hours'] );
                echo _box(__("Amount"), $data['current']['amount'] );
            ?>
        </div>
        <?php

        ?>
    </div>
</div>

 ̰