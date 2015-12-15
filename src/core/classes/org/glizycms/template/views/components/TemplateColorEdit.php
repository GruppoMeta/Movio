<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_template_views_components_TemplateColorEdit extends org_glizy_components_Component
{
    private $idParent;

    public function render_html()
    {
        $templateProxy = org_glizy_ObjectFactory::createObject('org.glizycms.template.models.proxy.TemplateProxy');
        $templateRealPath = $templateProxy->getTemplateRealpath();

        if ($templateRealPath) {
            $idParent = $this->_parent->_parent->getId().'-';
            $data = json_decode(file_get_contents($templateRealPath.'/colors.json'));
            if ($data) {
                $output = $this->addFieldset($data, $idParent);
                $this->addOutputCode($output);
            }
        }
    }

    private function addFieldset(&$data, $idParent)
    {
        $id = $this->getId();
        $output = '';
        $presets = array();
        $ids = array();
        $label = __T('Preset');
        foreach($data as $k=>$v) {
            $result = $this->addColorPicker($v, $idParent);
            $output .= '<fieldset><legend>'.__T($k).'</legend>'.
                        implode('', $result['output']).
                        '</fieldset>';
            $presets = array_merge($presets, $result['presets']);
            $ids = array_merge($ids, $result['id']);
        }

        $presets = implode(',', $presets);
        $ids = implode(',', $ids);
        $output = <<<EOD
    <div class="control-group">
        <label class="control-label " for="{$idParent}{$id}">{$label}</label>
        <div class="controls">
            <select data-elements="{$ids}" data-type="valuesPreset" class="span11" name="{$id}" id="{$idParent}{$id}"><option value="">-</option><option data-options="{$presets}" value="0">{$label} 1</option></select>
        </div>
    </div>
    {$output}
EOD;

        return $output;
    }

    private function addColorPicker(&$data, $idParent)
    {
        $result = array('output' => array(), 'presets' => array(), 'id' => array());
        $presets = '';
        foreach($data as $k=>$v) {
            $label = __T($k);
            $id = $v->id[0];
            $result['id'][] = $id;
            $result['presets'][] = $v->preset[0];
            $result['output'][] = <<<EOD
<div class="control-group">
    <label for="{$idParent}{$id}"  class="control-label ">{$label}</label>
    <div class="controls">
        <input id="{$idParent}{$id}" name="{$id}" title="{$label}" class="span11 " type="text" value="" data-type="colorPicker"/>
    </div>
</div>
EOD;
        }
        return $result;
    }
}








