<?php
/* @var $this AntraegeController */
/* @var $model Antrag */
/* @var $messages array */

$this->breadcrumbs = array(
	Yii::t('app', 'Administration') => $this->createUrl('/admin/index'),
	$model->label(2)                => array('index'),
	Yii::t('app', 'Update'),
);

$this->menu = array(
	array('label' => $model->label() . ' ' . Yii::t('app', 'View'), 'url' => $this->createUrl("antrag/anzeige", array("veranstaltung_id" => $model->veranstaltung->url_verzeichnis, "antrag_id" => $model->id)), "icon" => "eye-open"),
	array('label' => $model->label() . ' ' . Yii::t('app', 'Delete'), 'url' => '#', 'linkOptions' => array('submit' => array('delete', 'id' => $model->id), 'confirm' => 'Diesen Antrag wirklich löschen?'), "icon" => "remove"),
);
?>

	<h1><?php echo Yii::t('app', 'Update') . ': ' . GxHtml::encode($model->label()) . ' ' . GxHtml::encode(GxHtml::valueEx($model)); ?></h1>
	<br>

<?php
if ($model->status == Antrag::$STATUS_EINGEREICHT_UNGEPRUEFT) {
	$form    = $this->beginWidget('GxActiveForm');
	$new_rev = ($model->revision_name == "" ? $model->veranstaltung->naechsteAntragRevNr($model->typ) : $model->revision_name);

	echo '<input type="hidden" name="' . AntiXSS::createToken("antrag_freischalten") . '" value="' . CHtml::encode($new_rev) . '">';
	echo "<div style='text-align: center;'>";
	$this->widget('bootstrap.widgets.TbButton', array('buttonType' => 'submit', 'type' => 'primary', 'icon' => 'ok white', 'label' => 'Freischalten als ' . $new_rev));
	echo "</div>";
	$this->endWidget();
	echo "<br>";
}


if (count($messages) > 0) echo "<strong>" . GxHtml::encode(implode("<br>", $messages)) . "</strong><br><br>";


$this->renderPartial('_form', array(
	'model' => $model));
