<?php
class movio_modules_modulesBuilder_builder_d01DeleteFolders extends movio_modules_modulesBuilder_builder_AbstractCommand
{
	function execute()
	{
		$this->deleteDirectory( $this->parent->getCustomModulesFolder() );
		return true;
	}

	function deleteDirectory($dir)
	{
		if (!file_exists($dir)) return true;
	    if (!is_dir($dir) || is_link($dir)) return unlink($dir);
	        foreach (scandir($dir) as $item) {
	            if ($item == '.' || $item == '..') continue;
	            if (!$this->deleteDirectory($dir . "/" . $item)) {
	                chmod($dir . "/" . $item, 0777);
	                if (!$this->deleteDirectory($dir . "/" . $item)) return false;
	            };
	        }
        return rmdir($dir);
    }
}
