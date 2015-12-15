<?php
class movio_views_components_Unity3DCmp extends org_glizy_components_Groupbox
{
	function getContent()
	{
		$content = parent::getContent();
		$content = $this->stripIdFromContent($content);

		if (!empty($content['unity3DFile']['mediaId'])) {
			return array(
				'fileId' => $content['unity3DFile']['mediaId'],
				'width' => empty($content['width']) ? '800' : $content['width'],
				'height' => empty($content['height']) ? '600' : $content['height'],
				'backgroundColor' => strtoupper(str_replace('#', '', $content['backgroundColor'])),
				'borderColor' => strtoupper(str_replace('#', '', $content['borderColor'])),
				'textColor' => strtoupper(str_replace('#', '', $content['textColor'])),
				'logoimage' => 'http://localhost/mibac_movio/wwwRoot/getImage.php?id=' . $content['logoimage']['mediaId'],
				'disableContextMenu' => empty($content['disableContextMenu']) ? 'false' : $content['disableContextMenu'],
				'disableFullscreen' => empty($content['disableFullscreen']) ? 'false' : $content['disableFullscreen'],
				'attributes' => json_encode($content['attributeList'])
			);
		}
		
		return array();
	}

	function render($mode)
	{
		if (!$this->_application->isAdmin()) {
			ini_set('display_errors', 'On');
			$this->setAttribute('skin', 'Unity3D.html');
		}
		parent::render($mode);
	}
}