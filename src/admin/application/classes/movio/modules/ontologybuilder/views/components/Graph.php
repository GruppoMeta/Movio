<?php
class movio_modules_ontologybuilder_views_components_Graph extends org_glizy_components_Component
{
    private $language;
    private $localeService;

    function init()
    {
    	// define the custom attributes
		$this->defineAttribute('entityTypeId', false, null, COMPONENT_TYPE_INTEGER);
        $this->defineAttribute('generateLinks', false, false, COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('addGraphJsLibs.js', false, true, COMPONENT_TYPE_BOOLEAN);

		// call the superclass for validate the attributes
		parent::init();

        $this->language = $this->_application->isAdmin() ? $this->_application->getEditingLanguage() : $this->_application->getLanguage();
        $this->localeService = $this->_application->retrieveProxy('movio.modules.ontologybuilder.service.LocaleService');
	}

    protected function escape($s, $trim=false)
    {
        return $this->localeService->getTranslation($this->language, $s);
    }

    protected function makeUrl($pageId, $title)
    {
        return org_glizy_helpers_Link::makeUrl('link', array('pageId' => $pageId, 'title' => $title));
    }

    public function getGraph($entityTypeId, &$visited, &$edges)
    {
        if ($visited[$entityTypeId]) {
            return '';
        } else {
            $entityTypeService = $this->_application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');
            $entityProperties = $entityTypeService->getEntityTypeProperties($entityTypeId);

            $graph = '';
            $color = __Config::get('movio.graph.shapeColor');
            $entityTypeName = $entityTypeService->getEntityTypeName($entityTypeId);

            if ($this->getAttribute('generateLinks')) {
                $entityResolver = org_glizy_objectFactory::createObject('movio.modules.ontologybuilder.EntityResolver');
                $ar = $entityResolver->getMenuVisibleEntity($entityTypeId);

                if ($ar) {
                    $url = $this->makeUrl($ar->id, $ar->title);
                }
            }

            // se è il nodo da cui inizia la ricerca ricorsiva
           if (count($visited) == 0) {
                if (empty($url)) {
                    $graph .= '"'.$this->escape($entityTypeName).'" [label="<div class=\'main-node\'>' . $this->escape($entityTypeName) . '</div>",style="fill: #ddd"];'.PHP_EOL;
                } else {
                    $graph .= '"'.$this->escape($entityTypeName).'" [label="<div class=\'main-node\'><a href=\'' . $url . '\'>' . $this->escape($entityTypeName) . '</a></div>",style="fill: #ddd"];'.PHP_EOL;
                }
            } else if ($url) {
                $graph .= '"'.$this->escape($entityTypeName).'" [label="<div><a href=\'' . $url . '\'>' . $this->escape($entityTypeName) . '</a>"];'.PHP_EOL;
            }

            $visited[$entityTypeId] = true;


            foreach ((array)$entityProperties as $entityProperty) {
                if ($entityProperty['entity_properties_target_FK_entity_id']) {
                    $toEntityTypeId = $entityProperty['entity_properties_target_FK_entity_id'];
                    $toEntityTypeName = $entityTypeService->getEntityTypeName($toEntityTypeId);
                    $label = $this->escape($entityProperty['entity_properties_label_key']);
                    if (!$edges[$entityTypeName][$toEntityTypeName]) {
                        $edges[$entityTypeName][$toEntityTypeName] = true;
                        $graph .= '"'.$this->escape($entityTypeName).'" -> "'.$this->escape($toEntityTypeName).'" [label="'.$label.'"];'.PHP_EOL;
                    }
                    $graph .= $this->getGraph($toEntityTypeId, $visited, $edges);
                }
            }

            $referenceRelations = $entityTypeService->getEntityTypeReferenceRelations($entityTypeId);

            foreach ((array)$referenceRelations as $referenceRelation) {
                if ($referenceRelation['entity_properties_target_FK_entity_id']) {
                    $toEntityTypeId = $referenceRelation['entity_properties_FK_entity_id'];
                    $toEntityTypeName = $entityTypeService->getEntityTypeName($toEntityTypeId);
                    $label = $this->escape($referenceRelation['entity_properties_label_key']);
                    if (!$edges[$toEntityTypeName][$entityTypeName]) {
                        $edges[$toEntityTypeName][$entityTypeName] = true;
                        $graph .= '"'.$this->escape($toEntityTypeName).'" -> "'.$this->escape($entityTypeName).'" [label="'.$label.'"];'.PHP_EOL;
                    }
                    $graph .= $this->getGraph($toEntityTypeId, $visited, $edges);
                }
            }

            $visited[$entityTypeId] = true;

            return $graph;
        }
    }

    public function render_html()
    {
        $graphCode = '';

        $entityTypeId = $this->getAttribute('entityTypeId') ? $this->getAttribute('entityTypeId') : __Request::get('entityTypeId');

        if ($entityTypeId) {
            $visited = array();
            $edges = array();
            $graphCode = $this->getGraph($entityTypeId, $visited, $edges);
        }

        if (!$this->getAttribute('generateLinks')) {
            $this->language = $this->_application->getEditingLanguage();
            $this->localeService = $this->_application->retrieveProxy('movio.modules.ontologybuilder.service.LocaleService');

            $html  = '<form id="myForm" method="post" class="form-horizontal row-fluid" >';
            $html .= '<label for="entityTypeId" class="control-label required">'.__T('Entity').'</label>';
            $html .= '<select id="entityTypeId" name="entityTypeId">';

            $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.Entity', 'all');

            foreach ($it as $ar) {
                $selected = __Request::get('entityTypeId') == $ar->getId() ? 'selected="selected"' : '';
                $html .= '<option value="'.$ar->getId().'" '.$selected.'>'.$this->localeService->getTranslation($this->language, $ar->entity_name).'</option>';
            }

            $html .= '</select>';
            $html .= '<input class="submit btn btn-primary" type="submit" value="'.__T('Draw').'">';
            $html .= '</form>';
        }

        $graphCode = <<<EOD
digraph "" {
    $graphCode
}
EOD;
        $graphCode = str_replace(array("\r","\n"), '', addslashes($graphCode));

        $html .= <<<EOD
<div style="text-align: center; position: relative; width: 100%;">
    <svg width="800" height="600">
      <g transform="translate(20, 20)"/>
    </svg>
</div>
<script>
        function tryDraw() {
            var result;
            try {
                result = graphlibDot.parse('$graphCode');
            } catch (e) {
                alert('Errore di caricamento del grafo!');
                throw e;
            }

            if (result) {
                var svg = d3.select("svg");
                var svgGroup = svg.append('g');

                var renderer = new dagreD3.Renderer();

                var layout = renderer.run(result, svgGroup);

                var parentWidth = 800;
                svg.attr('width', parentWidth);
                svg.attr('height', layout.graph().height + 250);
                var xCenterOffset = (svg.attr('width') - layout.graph().width) / 2;
                svgGroup.attr('transform', 'translate(' + xCenterOffset + ', 100)');
            }
        }

        function fixBaseTagProblem() {
            $('g[class^="edgePaths"]').find('path').each(function() {
                $(this).attr('marker-end', 'url(' + window.location + '#arrowhead)');
            });
        }

        function selectRootNode() {
            $('div[class="main-node"]').parent().parent().parent().parent().children('rect').attr('class', 'main-node');
        }

        $(window).bind('load', function() {
            tryDraw();
            fixBaseTagProblem();
            selectRootNode();
        });
</script>
EOD;
        if ($this->getAttribute('addGraphJsLibs.js')) {
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile(__Paths::get('STATIC_DIR').'dagre-d3/d3.v3.js'));
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile(__Paths::get('STATIC_DIR').'dagre-d3/dagre-d3.js'));
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile(__Paths::get('STATIC_DIR').'dagre-d3/graphlib-dot.min.js'));
        }

        $this->addOutputCode( $html );
    }
}