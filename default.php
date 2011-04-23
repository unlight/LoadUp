<?php if (!defined('APPLICATION')) exit();

$PluginInfo['LoadUp'] = array(
	'Name' => 'Load Up',
	'Description' => 'Allow users upload files (this is NOT "attachments" for Vanilla 2). Simple upload form.',
	'Version' => '1.5.16',
	'Date' => '6 Mar 2011',
	'Author' => 'Fox Grinder',
	'RequiredPlugins' => array('UsefulFunctions' => '>=1.997'),
	'RegisterPermissions' => array(
		'Plugins.Garden.LoadUp.Allow',
		'Plugins.Garden.LoadUp.Overwrite', 
		'Plugins.Garden.LoadUp.Destination'
	),
	'HasLocale' => True
);

class LoadUpPlugin extends Gdn_Plugin {
	
	public function Base_GetAppSettingsMenuItems_Handler(&$Sender) {
		$Menu =& $Sender->EventArguments['SideMenu'];
		$Menu->AddLink('Dashboard', Gdn::Translate('Upload File'), 'dashboard/plugin/loadup', 'Plugins.Garden.LoadUp.Allow');
	}
	
	public function Setup() {
		if (!C('Plugins.LoadUp.Path'))
			SaveToConfig('Plugins.LoadUp.Path', 'uploads');
	}
	
	public function PluginController_LoadUp_Create(&$Sender) {
		
		$Sender->UploadedFiles = array();
		
		$Sender->Permission('Plugins.Garden.LoadUp.Allow');
		if (!property_exists($Sender, 'Form')) $Sender->Form = Gdn::Factory('Form');
		
		$Sender->AddJsFile('jquery.livequery.js');
		$Sender->AddJsFile('jquery.autogrow.js');
		$Sender->AddJsFile('plugins/LoadUp/loadup.js');
		$Sender->AddJsFile('plugins/LoadUp/jquery.clipboard.js');
		$Sender->AddDefinition('TextClipboarded', Gdn::Translate('saved to clipboard.'));
		
		$Session = Gdn::Session();
		
		if ($Sender->Form->AuthenticatedPostBack() != False) {
			$UploadTo = False;
			if ($Session->CheckPermission('Plugins.Garden.LoadUp.Destination'))
				$UploadTo = $Sender->Form->GetFormValue('UploadTo', Gdn::Config('Plugins.Garden.LoadUp.Path', 'uploads'));
			if (!$UploadTo) $UploadTo = 'uploads' . DS . date('Y');
			$bOverwrite = $Sender->Form->GetFormValue('Overwrite') && $Session->CheckPermission('Plugins.Garden.LoadUp.Overwrite');
			$bRename = $Sender->Form->GetFormValue('Rename');
			$Upload = new Gdn_Upload();
			
			for($Count = count(GetValueR('Files.name', $_FILES)), $i = 0; $i < $Count; $i++) {
				$_FILES['File'] = array();
				foreach(array('name', 'type', 'tmp_name', 'error', 'size') as $Key) {
					$Value = GetValueR("Files.{$Key}.{$i}", $_FILES);
					SetValue($Key, $_FILES['File'], $Value);
				}
				$Temp = $Upload->ValidateUpload('File', False);
				$Name = $Upload->GetUploadedFileName();
				if ($Temp === False) {
					$Sender->Form->AddError($Upload->Exception);
					continue;
				}
				$TargetFile = GenerateCleanTargetName($UploadTo, $Name, '', $Temp, $bOverwrite);
				$Upload->SaveAs($Temp, $TargetFile);
				$Sender->UploadedFiles[] = $TargetFile;
			}
		}
		
		if ($Session->CheckPermission('Plugins.Garden.LoadUp.Destination')) {
			// TODO: FIX ME, TO SLOOOOW
			// POSSIBLE CUSTOM DIRECTORIES
			$Sender->UploadTo = $this->CollectUploadTo();
		}
		
		$Sender->View = $this->GetView('loadup.php');
		$Sender->AddSideMenu('dashboard/plugin/loadup');
		//$Sender->AddCssFile('plugins/LoadUp/style.css');
		$Sender->Title(T('Upload File'));
		$Sender->Render();
	}
	

	
	protected function CollectUploadTo() {
		
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
	
	
	public function PluginController_DummyError_Create(&$Sender) {
		trigger_error('Dummy error.');
	}
	
}