<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\assets\Bootstrap5;
use yii\helpers\Html;


//load bootstrrap asset
Bootstrap5::register($this);

?>
<?php $this->beginPage() ?>
<!doctype html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="UTF-8">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300italic,300,400italic,600,600italic,700italic,700,800,800italic' rel='stylesheet' type='text/css'>
    <?php $this->head() ?>
</head>

<body>
    <?php $this->beginBody() ?>
    <div>
        <div class="header row">
            <div class="col-lg-5 col-md-8 col-12">
                <h2>Vikas Yadav</h2>
                <p style="font-size: 0.8em">
                    B+15, Phase 1, Parsn Palm Legend,<br />
                    Ondipudur, Coimbature, Tamil Nadu, 641016 India<br />
                    Phone: (+91) 60052 68037<br />
                    PAN: AATPY7555M
                </p>
            </div>

            <div class="col-lg-2 col-md-12 col-12 offset-lg-1">
                <div class="header-contact">
                    <img class="icon-mail" src="/images/mail.png" />
                    <p><a href="mailto:vikas@thevikas.com">vikas@thevikas.com</a></p>
                </div>
            </div>

            <div class="col-lg-2 col-md-12 col-12">
                <div class="header-contact">
                    <img class="icon-telephone" src="/images/phone.png" />
                    <p>+91 6005268037</p>
                </div>
            </div>

            <div class="col-lg-2 col-md-12 col-12">
                <div class="header-contact" style="border-right: none">
                    <img class="icon-web" src="/images/world.png" />
                    <p><a href="http://thevikas.com">thevikas.com</a></p>
                </div>
            </div>
        </div>
        <!--BS5 header-->

        <?= $content ?>

        <div class="footer row">
            <div class="col-lg-5 col-md-3 col-12">
                &nbsp;<!-- <img src="/images/footer-logo.png"> -->
            </div>
            <div class="col-lg-2 col-md-3 col-12 offset-lg-1">
                <p><a href="mailto:vikas@thevikas.com">vikas@thevikas.com</a></p>
            </div>
            <div class="col-lg-2 col-md-3 col-12">
                <p>+91 6005268037</p>
            </div>
            <div class="col-lg-2 col-md-3 col-12">
                <p style="border:none;"><a href="http://thevikas.com">thevikas.com</a></p>
            </div>
        </div>

    </div>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>