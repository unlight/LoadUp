<?php if (!defined('APPLICATION')) exit();

$PluginInfo['LoadUp'] = array(
	'Name' => 'Load Up',
	'Description' => 'Allow users upload files (this is NOT "attachments" for Vanilla 2). Simple upload form.',
	'Version' => '1.4',
	'Date' => '10 Aug 2010',
	'Author' => 'Fox Grinder',
	'RequiredPlugins' => array('PluginUtils' => '>=1.997'),
	'RegisterPermissions' => array('Plugins.Garden.LoadUp.Allow', 'Plugins.Garden.LoadUp.Overwrite'),
	'HasLocale' => True
);

/* =======================================
CHANGELOG:
30 Jul 2010 / 1.3
- allow upload multiple files
05 Jun 2010	/ 1.1
- RC related fixes

TODO:
![alt text](/path/img.jpg "Title") - markdown image markup
%[alt text](/path/img.jpg "Title") - centered
>
[alt text](/path/img.jpg "Title")
[alt text](/path/img.jpg "Title")
< 
- mass centered

CLEANUP:
1. You can remove old fields Plugins.Garden.LoadUp.* from Gdn_Permission table

======================================*/ 

class LoadUpPlugin extends Gdn_Plugin {
	
	public function Base_GetAppSettingsMenuItems_Handler(&$Sender){
		$Menu =& $Sender->EventArguments['SideMenu'];
		$Menu->AddLink('Dashboard', Gdn::Translate('Upload File'), 'dashboard/plugin/loadup', 'Plugins.Garden.LoadUp.Allow');
	}
	
	public function Setup(){
		$Config = Gdn::Factory(Gdn::AliasConfig);
		$Config->Load(PATH_CONF.DS.'config.php', 'Save');
		$Config->Set('Plugins.LoadUp.Path', 'uploads', False);
		$Config->Save();
	}
	
	public function PluginController_DummyError_Create(&$Sender) {
		trigger_error('Dummy error.');
	}
	
	public function PluginController_LoadUp_Create(&$Sender){
		
		$Sender->UploadedFiles = array();
		
		$Sender->Permission('Plugins.Garden.LoadUp.Allow');
		if(!property_exists($Sender, 'Form')) $Sender->Form = Gdn::Factory('Form');
		
		$Sender->AddJsFile('jquery.livequery.js');
		$Sender->AddJsFile('jquery.autogrow.js');
		$Sender->AddJsFile('plugins/LoadUp/loadup.js');
		$Sender->AddJsFile('plugins/LoadUp/jquery.clipboard.js');
		$Sender->AddDefinition('TextClipboarded', Gdn::Translate('saved to clipboard.'));
		
		$Session = Gdn::Session();
		
		if ($Sender->Form->AuthenticatedPostBack() != False) {

			$UploadTo = $Sender->Form->GetFormValue('UploadTo', Gdn::Config('Plugins.Garden.LoadUp.Path', 'uploads'));
			$bOverwrite = $Sender->Form->GetFormValue('Overwrite') && $Session->CheckPermission('Plugins.Garden.LoadUp.Overwrite');
			$bRename = $Sender->Form->GetFormValue('Rename');
			$Upload = new Gdn_Upload();
			
			for($Count = count(GetValueR('Files.name', $_FILES)), $i = 0; $i < $Count; $i++){
				$_FILES['File'] = array();
				foreach(array('name', 'type', 'tmp_name', 'error', 'size') as $Key){
					$Value = GetValueR("Files.{$Key}.{$i}", $_FILES);
					SetValue($Key, $_FILES['File'], $Value);
				}
				$Temp = $Upload->ValidateUpload('File', False);
				$Name = $Upload->GetUploadedFileName();
				if($Temp === False){
					$Sender->Form->AddError($Upload->Exception);
					continue;
				}
				$TargetFile = GenerateCleanTargetName($UploadTo, $Name, '', $Temp, $bOverwrite);
				/*if (file_exists($TargetFile)) {
					if ($bRename) $TargetFile = GenerateCleanTargetName($UploadTo, $Name);
					elseif (!$bOverwrite) $Sender->Form->AddError(sprintf(T('File exists (%s)! Rename or confirm to overwrite.'), $Name));
				}*/
				$Upload->SaveAs($Temp, $TargetFile);
				$Sender->UploadedFiles[] = $TargetFile;
			}
		}
		
		$Sender->UploadTo = $this->CollectUploadTo();

		$Sender->View = $this->GetView('loadup.php');
		$Sender->AddSideMenu('dashboard/plugin/loadup');
		$Sender->AddCssFile('plugins/LoadUp/style.css');
		$Sender->Render();
	}
	

	
	protected function CollectUploadTo(){
		
		$UploadTo = array();
		$LoadUpPath = Gdn::Config('Plugins.Garden.LoadUp.Path', 'uploads');
		$FirstUploadDirectories = ProcessDirectory($LoadUpPath, False);
		
		foreach($FirstUploadDirectories as $Directory){
			$WritableDirectory = property_exists($Directory, 'bDirectory') && $Directory->IsWritable;
			if($WritableDirectory == False) continue;
			$NextLoadUpPath = $LoadUpPath . DS . $Directory->Filename;
			
			$UploadTo[] = $NextLoadUpPath;
			
			$SecondUploadDirectories = ProcessDirectory($NextLoadUpPath);
			foreach($SecondUploadDirectories as $Directory){
				$WritableDirectory = property_exists($Directory, 'bDirectory') && $Directory->IsWritable;
				if($WritableDirectory) $UploadTo[] = $NextLoadUpPath . DS . $Directory->Filename;
			}
		}
		if(Count($UploadTo) > 0) $UploadTo = array_combine($UploadTo, $UploadTo);
		
		return $UploadTo;
	}
	
	
	public static function Upload($TargetFolder, $InputName, $bOverwrite = False, $bRename = False){
		trigger_error(__METHOD__.'() deprecated.', E_USER_ERROR);
		/*if(!$TargetFolder) $TargetFolder = C('Plugins.LoadUp.Path', 'uploads');
		
		$Files = ArrayValue('File', $_FILES);

		$Upload = new Gdn_Upload();
		$this->Upload =& $Upload;
		
		$Upload->AllowFileExtension('doc');
		
		$Temp = $Upload->ValidateUpload($InputName, False);
		if($Temp === False) return $Temp;
		
		$Name =  ArrayValue('name', ArrayValue('File', $_FILES));
		$TargetFile = GenerateCleanTargetName($TargetFolder, $Name, '', $Temp, $bOverwrite);
		
		if (file_exists($TargetFile)) {
			if ($bRename) $TargetFile = GenerateCleanTargetName($TargetFolder, $Name);
			elseif (!$bOverwrite) throw new Exception(sprintf(T('File exists (%s)! Rename or confirm to overwrite.'), $Name));
		}
		$Upload->SaveAs($Temp, $TargetFile);
		return $TargetFile;*/
	}
	
}