<?php if (!defined('APPLICATION')) exit();

$PluginInfo['LoadUp'] = array(
	'Name' => 'Load Up',
	'Description' => 'Allow users upload files (this is NOT "attachments" for Vanilla 2). Simple upload form.',
	'Version' => '1.7',
	'Date' => 'Summer 2011',
	'Author' => 'Fox Grinder',
	'RequiredPlugins' => array('UsefulFunctions' => '>=3.0'),
	'RegisterPermissions' => array(
		'Plugins.Garden.LoadUp.Allow',
		'Plugins.Garden.LoadUp.Overwrite'
	)
);

class LoadUpPlugin extends Gdn_Plugin {
	
	public function Base_GetAppSettingsMenuItems_Handler(&$Sender) {
		$Menu =& $Sender->EventArguments['SideMenu'];
		$Menu->AddLink('Dashboard', Gdn::Translate('Upload File'), 'dashboard/plugin/loadup', 'Plugins.Garden.LoadUp.Allow');
	}
	
	public function Tick_Match_30_Minutes_05_Hours_Handler() { // every night
		// 1. Clean-up temp uploads
		$KeepThis = array('README');
		$MonthAgo = strtotime('30 days ago');
		$Directory = 'uploads/tmp';
		if (!file_exists($Directory)) return;
		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($Directory)) as $File) {
			if (in_array($File->GetFilename(), $KeepThis)) continue;
			$Pathname = $File->GetPathname();
			if ($MonthAgo > $File->GetMTime()) unlink($Pathname);
			Console::Message('Removed: ^3%s', $Pathname);
		}
	}
	
	public function PluginController_LoadUp_Create(&$Sender) {
		$Sender->AddSideMenu('dashboard/plugin/loadup');
		$Sender->UploadedFiles = array();
		
		$Sender->Permission('Plugins.Garden.LoadUp.Allow');
		if (!property_exists($Sender, 'Form')) $Sender->Form = Gdn::Factory('Form');
		
		$Sender->AddJsFile('jquery.livequery.js');
		$Sender->AddJsFile('jquery.autogrow.js');
		$Sender->AddJsFile('plugins/LoadUp/loadup.js');
		
		$Session = Gdn::Session();
		
		if ($Sender->Form->AuthenticatedPostBack() != False) {
			$UploadTo = $Sender->Form->GetFormValue('UploadTo');
			if (!$UploadTo) $UploadTo = 'uploads/i/' . date('Y') . '/' . date('m');
			$bOverwrite = $Sender->Form->GetFormValue('Overwrite') && $Session->CheckPermission('Plugins.Garden.LoadUp.Overwrite');
			$Options = array('Overwrite' => $bOverwrite, 'WebTarget' => True);
			$Sender->UploadedFiles = UploadFile($UploadTo, 'Files', $Options);
		}
		
		$Sender->UploadTo = array('uploads/tmp' => 'uploads/tmp');
		$Sender->View = $this->GetView('index.php');
		$Sender->Title(T('Upload File'));
		$Sender->Render();
	}
	
	
	public function Setup() {
		if (!is_dir('uploads/tmp')) mkdir('uploads/tmp', 0777, True);
	}
	
	
	
}