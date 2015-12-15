<?php
class movio_modules_codes_controllers_MakeQRCode extends org_glizy_mvc_core_Command
{
    function execute($id)
    {
        if ($id) {
            require_once(__Paths::get('APPLICATION_LIBS').'phpqrcode/qrlib.php');
            
            $ar = org_glizy_objectFactory::createModel('movio.modules.codes.models.Model');
            $ar->load($id);
            $codeType = $ar->custom_code_mapping_code ? 'c' : 'm';
            $value = $codeType == 'c' ? $ar->custom_code_mapping_code : $ar->custom_code_mapping_link;
            
            $codeContents = GLZ_HOST_ROOT.':'.$codeType.':'.$value;
            $filePath = __Paths::get('CACHE').$id.'.png';
            
            QRcode::png($codeContents, $filePath, QR_ECLEVEL_H, 4);
            
            header("Pragma: public");
            header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false);
            header("Content-Type: image/png");
            header("Content-Transfer-Encoding: binary");
            header("Content-Disposition: attachment; filename=\"".$id.'.png'."\"");
            @readfile($filePath) or die();
        }
    }
}
?>