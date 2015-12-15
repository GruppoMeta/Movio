<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_views_components_VisualSearch extends org_glizy_components_Form
{
    private $sessionEx;
    private $criteria = array();
    private $filters = array();

    function init()
    {
        $this->defineAttribute('addValidationJs',    false, false,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('rememberValues',    false, true,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('rememberMode',  false, 'persistent',    COMPONENT_TYPE_STRING);

        parent::init();
    }


    function process()
    {
        $this->sessionEx    = new org_glizy_SessionEx($this->getId());
        $this->_command     = org_glizy_Request::get($this->getId().'_command');
        $this->rememberMode = $this->getAttribute( 'rememberMode' ) == 'persistent' ? GLZ_SESSION_EX_PERSISTENT : GLZ_SESSION_EX_VOLATILE;
        if ($this->_command=='RESET') {
            $this->sessionEx->remove('filters_filters');
        } else {
            $name = $this->getId().'_filters';
            $defValue = '';
            if ( $this->getAttribute( 'rememberValues') ) {
                $defValue = $this->sessionEx->get($name);
            }

            $this->filters = __Request::get($name, $defValue);
            $this->sessionEx->set($name, $this->filters, $this->rememberMode);
            $this->filters = json_decode($this->filters);
        }

        if (!$this->filters) {
            $this->filters = array();
        }

        parent::process();
    }


    function render_html_onStart()
    {
        parent::render_html_onStart();

        $folderPath = __Paths::get('CORE').'classes/org/glizycms/js/visualsearch/';
        $output = <<< EOD
<script src="{$folderPath}dependencies.js" type="text/javascript"></script>
<script src="{$folderPath}visualsearch.js" type="text/javascript"></script>
<!--[if (!IE)|(gte IE 8)]><!-->
<link href="{$folderPath}visualsearch-datauri.css" media="screen" rel="stylesheet" type="text/css" />
<!--<![endif]-->
<!--[if lte IE 7]><!-->
 <link href="{$folderPath}visualsearch.css" media="screen" rel="stylesheet" type="text/css" />
<!--<![endif]-->
EOD;
        $this->addOutputCode($output, 'head');

        $id = $this->getId();
        $query = '';
        $callback = array();
        $valuesMapping = array();
        $valuesMappingRev = array();
        $valueMatches = '';
        foreach($this->criteria as $v) {
            $callback[] = array('label' => $v->label, 'id' => $v->id);

            if ('facet'==$v->type) {

                $it = org_glizy_objectFactory::createModelIterator($this->getAttribute('model'));
                $it->where($v->id, '', '<>')
                   ->orderBy($v->id);

                $foundValues = array();
                foreach($it as $ar) {
                    if (is_array($ar->{$v->id})) {
                        foreach ($ar->{$v->id} as $value) {
                            if (!in_array($value, $foundValues)) $foundValues[] = $value;
                        }
                    } else {
                        if (!in_array($ar->{$v->id}, $foundValues)) $foundValues[] = $ar->{$v->id};
                    }
                }
                $valueMatches .= 'case "'.$v->label.'":callback('.json_encode($foundValues).');break;';
            } else if ('static'==$v->type) {
                $foundValues = array();
                $valuesMapping[$v->label] = array();
                $values = json_decode($v->values);
                foreach($values as $kk=>$vv) {
                    $label = __T($vv);
                    $foundValues[] = $label;
                    $valuesMapping[$v->label][$label] = $kk;
                    $valuesMappingRev[$v->label][$kk] = $label;
                }

                $valueMatches .= 'case "'.$v->label.'":callback('.json_encode($foundValues).');break;';
             } else if ('dictionary'==$v->type) {
                $dataProvider = $this->getComponentById($v->dataProvider);
                if (!is_null($dataProvider))
                {
                    $foundValues = array();
                    $valuesMapping[$v->label] = array();
                    $values = $dataProvider->getItems();
                    foreach($values as $vv) {
                        $foundValues[] = $vv['value'];
                        $valuesMapping[$v->label][$vv['value']] = $vv['key'];
                        $valuesMappingRev[$v->label][$vv['key']] = $vv['value'];
                    }
                    $valueMatches .= 'case "'.$v->label.'":callback('.json_encode($foundValues).');break;';
                }
            }
        }

        foreach($this->filters as $item) {
            foreach($item as $k=>$v) {
                if (isset($valuesMappingRev[$k]) && isset($valuesMappingRev[$k][$v])) {
                    $v = $valuesMappingRev[$k][$v];
                }
                $query .= '"'.$k.'": "'.addslashes($v).'" ';
            }
        }

        $callback = json_encode($callback);
        $valuesMapping = json_encode($valuesMapping);

        $output = <<<EOD
<input name="{$id}_filters" id="{$id}_filters" type="hidden" value="">
<div id="{$id}_visualsearch"></div>
<script type="text/javascript" charset="utf-8">

  $(document).ready(function() {
    var valuesMapping = $valuesMapping;
    var visualSearch = VS.init({
      container : $('#{$id}_visualsearch'),
      query     : '$query',
      remainder: '',
      callbacks : {
        facetMatches : function(callback) {
            callback($callback)
        },
        valueMatches : function(facet, searchTerm, callback) {
            switch (facet) {
                $valueMatches
            }
        }
      }
    });

    $('#{$id}').submit(function(){
        var e = jQuery.Event("keydown");
        e.which = 13
        _.each(visualSearch.searchBox.inputViews, function(inputView, i) {
            inputView.box.trigger("focus");
            inputView.box.trigger(e);
        });
        var values = [];
        _.each(visualSearch.searchQuery.facets(), function(el, i){
            _.each(el, function(el2, i2){
                if (valuesMapping[i2]) {
                    var temp = {};
                    temp[i2] = valuesMapping[i2][el2]
                    values.push(temp);
                } else {
                    values.push(el);
                }
            });
        });

        $('#{$id}_filters').val(JSON.stringify(values));
        return true;
    })
  });
</script>
EOD;

        $this->addOutputCode($output);
    }


    public function addCriteria($id, $label, $type, $values, $dataProvider)
    {
        if (preg_match("/\{i18n\:.*\}/i", $label)) {
            $code = preg_replace("/\{i18n\:(.*)\}/i", "$1", $label);
            $label = org_glizy_locale_Locale::getPlain($code);
        }

        $temp = new StdClass();
        $temp->id = $id;
        $temp->label = $label;
        $temp->type = $type;
        $temp->values = $values;
        $temp->dataProvider = $dataProvider;
        $this->criteria[$temp->label] = $temp;
    }

    function getFilters()
    {
        $tempFilters = array();
        foreach($this->filters as $item) {
            foreach($item as $k=>$v) {
                $tempFilters[$this->criteria[$k]->id] = $this->criteria[$k]->type == 'facet' ?
                                                            array('value' => '%"'.$v.'"%', 'condition' => 'LIKE') :
                                                            $v;
            }
        }

        return $tempFilters;
    }

    public static function compile($compiler, &$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix, $componentClassInfo, $componentId)
    {
        $compiler->compile_baseTag( $node, $registredNameSpaces, $counter, $parent, $idPrefix, $componentClassInfo, $componentId );

        $oldcounter = $counter;
        foreach ($node->childNodes as $n ) {
            if ( $n->nodeName == "cms:VisualSearchItem" ) {
                $id = $n->hasAttribute('id') ? $n->getAttribute('id') : '';
                $label = $n->hasAttribute('label') ? $n->getAttribute('label') : '';
                $type = $n->hasAttribute('type') ? $n->getAttribute('type') : 'text';
                $values = $n->hasAttribute('values') ? $n->getAttribute('values') : '';
                $dataProvider = $n->hasAttribute('dataProvider') ? $n->getAttribute('dataProvider') : '';
                if ( $id && $label ) {
                    $compiler->_classSource .= '$n'.$counter.'->addCriteria( "'.$id.'", "'.$label.'", "'.$type.'", "'.addslashes($values).'", "'.$dataProvider.'" );';
                }
                continue;
            }

            $counter++;
            $compiler->_compileXml($n, $registredNameSpaces, $counter, '$n'.$oldcounter, $idPrefix);
        }
        return false;
    }
}
