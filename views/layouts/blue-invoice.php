<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\BlueInvoiceAsset;

BlueInvoiceAsset::register($this);
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
<div >
	<div class="header row">

		<div class="large-5 medium-8 small-12 columns">
			<h2>Vikas Yadav</h2>
			<p style="font-size: .8em">B+15, Phase 1, Parsn Palm Legend,<br/>
			Ondipudur, Coimbature, Tamil Nadu, 641016 India<br/>
			Phone: (+91) 60052 68037<br/>
			PAN: AATPY7555M</p>

		</div>

		<div class="large-2 medium-12 small-12 large-offset-1 columns">
			<div class="header-contact">
				<img class="icon-mail" src="/images/mail.png">
				<p><a href="mailto:vikas@thevikas.com">vikas@thevikas.com</a></p>
			</div>
		</div>

		<div class="large-2 medium-12 small-12 columns">
			<div class="header-contact">
				<img class="icon-telephone" src="/images/phone.png">
				<p>+91 6005268037</p>
			</div>
		</div>

		<div class="large-2 medium-12 small-12 columns">
			<div class="header-contact" style="border-right:none;">
				<img class="icon-web" src="/images/world.png">
				<p><a href="http://thevikas.com">thevikas.com</a></p>
			</div>
		</div>

	</div><!--header-->

	<?= $content ?>

	<div class="footer row">
		<div class="large-5 medium-3 columns">
			&nbsp;<!-- <img src="/images/footer-logo.png"> -->
		</div>
		<div class="large-2 medium-3 large-offset-1 columns">
			<p><a href="mailto:vikas@thevikas.com">vikas@thevikas.com</a></p>
		</div>

		<div class="large-2 medium-3 columns">
			<p>+91 6005268037</p>
		</div>

		<div class="large-2 medium-3 columns">
			<p style="border:none;"><a href="http://thevikas.com">thevikas.com</a></p>
		</div>
	</div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>