<?php

/* @var $this \yii\web\View */
/* @var $content string */
/* @var $company array Company information with keys: name, address, phone, email, website, pan */

use app\assets\Boostrap5InvoiceAsset;
use yii\helpers\Html;

// Default company information - can be overridden from controller via $this->view->params['issuing_company']
if (empty($this->params['issuing_company'])) {
    $companyId = Yii::$app->params['defaultCompanyId'];
    $company = Yii::$app->params['companies'][$companyId];
}
else {
    $company = $this->params['issuing_company'];
}

//load bootstrrap asset
Boostrap5InvoiceAsset::register($this);

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
                <h2><?= $company['name'] ?></h2>
                <p style="font-size: 0.8em">
                    <?= $company['address'] ?><br />
                    Phone: (+91) <?= Html::encode($company['phone']) ?><br />
                    PAN: <?= Html::encode($company['pan']) ?>
                    <?php if (!empty($company['cin'])): ?>
                        <br />CIN: <?= Html::encode($company['cin']) ?>
                    <?php endif; ?>
                </p>
            </div>

            <div class="col-lg-2 col-md-12 col-12 offset-lg-1">
                <div class="header-contact">
                    <img class="icon-mail" src="/images/mail.png" />
                    <p><a href="mailto:<?= Html::encode($company['email']) ?>"><?= Html::encode($company['email']) ?></a></p>
                </div>
            </div>

            <div class="col-lg-2 col-md-12 col-12">
                <div class="header-contact">
                    <img class="icon-telephone" src="/images/phone.png" />
                    <p><?= Html::encode($company['phone']) ?></p>
                </div>
            </div>

            <div class="col-lg-2 col-md-12 col-12">
                <div class="header-contact" style="border-right: none">
                    <img class="icon-web" src="/images/world.png" />
                    <p><a href="http://<?= Html::encode($company['website']) ?>"><?= Html::encode($company['website']) ?></a></p>
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
                <p><a href="mailto:<?= Html::encode($company['email']) ?>"><?= Html::encode($company['email']) ?></a></p>
            </div>
            <div class="col-lg-2 col-md-3 col-12">
                <p><?= Html::encode($company['phone']) ?></p>
            </div>
            <div class="col-lg-2 col-md-3 col-12">
                <p style="border:none;"><a href="http://<?= Html::encode($company['website']) ?>"><?= Html::encode($company['website']) ?></a></p>
            </div>
        </div>

    </div>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>