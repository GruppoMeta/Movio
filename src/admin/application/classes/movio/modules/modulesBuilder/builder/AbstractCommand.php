<?php
class movio_modules_modulesBuilder_builder_AbstractCommand
{
	var $_application;
	var $parent;

	function __construct( $parent )
	{
		$this->parent = $parent;
		$this->_application = org_glizy_ObjectValues::get('org.glizy', 'application');
	}

	function getError()
	{
		return '';
	}

    /**
     * @param $path
     * @param $file
     * @throws Exception
     */
    protected function throwFileCreationException($path, $file)
    {
        throw new Exception(
            implode(
                "\r\n<br>\r\n",
                array(
                    "Fallimento sul salvataggio dei file PHP localizzato:",
                    "Path previsto: " . $path,
                    "Path previsto " . (realpath($path) === false ? "non " : "") . "generato",
                    "Path previsto " . (is_writable($path) === false ? "senza " : "con ") . "permessi in scrittura",
                    "File PHP " . (realpath($file) === false ? "non " : "") . "generato"
                )
            )
        );
    }
}
