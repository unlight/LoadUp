<?php if (!defined('APPLICATION')) exit();

$Session = Gdn::Session();
$UploadOK = $this->Form->IsPostBack() && $this->Form->ErrorCount() == 0;
$UploadOptions = array(Gdn::Translate('ToRename') => 'Rename', Gdn::Translate('ToOverwrite') => 'Overwrite');

?>

<h1><?php echo Gdn::Translate('Upload File') ?></h1>

<?php echo $this->Form->Open(array('enctype' => 'multipart/form-data')) ?>
<?php echo $this->Form->Errors() ?>

<ul class="LoadUpForm">

<?php foreach ($this->UploadedFiles as $FilePath) {
	/*$FileName = pathinfo($FilePath, 8);
	$Result = $this->Form->Label($FileName);
	$Result .= $this->Form->TextBox($FileName, array('value' => $FilePath));
	echo Wrap($Result, 'li', array('class' => 'UploadResult'));*/
}
if ($UploadOK != False) {
	echo '<li>';
	echo $this->Form->Label('Result', 'Result');
	$Options = array('value' => implode("\n", $this->UploadedFiles), 'Multiline' => True);
	echo $this->Form->TextBox('RawData', $Options);
	//echo $this->Form->CheckBox('WithDomain', Gdn::Translate('WithDomain'));
	echo '</li>';
}
?>

<li>
<?php echo $this->Form->Label('Choose File', 'File') ?>
<?php echo $this->Form->Input('Files[]', 'file', array('multiple' => 'multiple')) ?>
</li>

<li>
<?php echo $this->Form->Label('Upload To', 'UploadTo') ?>
<?php echo $this->Form->DropDown('UploadTo', $this->UploadTo, array('IncludeNull' => True)) ?>
</li>

<li>
<strong><?php echo Gdn::Translate('If File Exists') ?>:</strong>
<?php 
//echo $this->Form->CheckBox('Rename', Gdn::Translate('ToRename')) 
if($Session->CheckPermission('Plugins.Garden.LoadUp.Overwrite'))
	echo $this->Form->CheckBox('Overwrite', Gdn::Translate('ToOverwrite'));
?>
</li>

<?php 
/*
if($UploadOK){
	echo $this->Form->Label('Result', 'MyResult');
	echo $this->Form->TextBox('MyResult', array('value' => $this->UploadedFile));
	</li>
}
*/?>

	
</ul>
<?php echo $this->Form->Button('Upload') ?>



<?php echo $this->Form->Close() ?>