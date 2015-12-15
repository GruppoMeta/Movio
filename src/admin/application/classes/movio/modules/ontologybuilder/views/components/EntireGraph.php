<?php
class movio_modules_ontologybuilder_views_components_EntireGraph extends org_glizy_components_Component
{
    function init()
    {
    	// define the custom attributes
		$this->defineAttribute('entityTypeId', false, null, COMPONENT_TYPE_INTEGER);
        $this->defineAttribute('generateLinks', false, false, COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('addGraphJsLibs.js', false, true, COMPONENT_TYPE_BOOLEAN);

		// call the superclass for validate the attributes
		parent::init();
	}

    protected function escape($s, $trim=false)
    {
        //$s = htmlentities(addslashes($s), null, "UTF-8");
        $s = str_replace('"', "'", $s);
        return $s;
    }
    
    protected function makeUrl($pageId, $title)
    {
        return org_glizy_helpers_Link::makeUrl('link', array('pageId' => $pageId, 'title' => $title));
    }

    public function getGraph($entityTypeId, &$visited, &$edges, $firstNode = false)
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
                    $url = 'URL="'.$this->makeUrl($ar->id, $ar->title).'"';
                }
            }

            // se è il nodo da cui inizia la ricerca ricorsiva
            if ($firstNode or empty($url)) {
                $s = $url ? $url.', ' : '';
                $graph .= '"'.$this->escape($entityTypeName).'" [' . $s . 'label="<span>' . $this->escape($entityTypeName . ' ' . __Link::makeLinkWithIcon('actionsMVC', 'icon-pencil icon-white', array('action' => 'edit', 'id' => $entityTypeId, 'title' => __T('GLZ_RECORD_EDIT'))) . '<a href="' . __Link::makeURL('actionsMVC', array('action' => 'delete', 'id' => $entityTypeId)) . '" onclick="if (!confirm(&#39;' . __T('GLZ_RECORD_MSG_DELETE') . '&#39;)){return false}"><i class="icon-remove icon-white"></i></a>') . ' </span>"];'.PHP_EOL;
            } else if ($url) {
                $graph .= '"'.$this->escape($entityTypeName).'" ['.$url.'];'.PHP_EOL;
            }

            $visited[$entityTypeId] = true;

            foreach ((array)$entityProperties as $entityProperty) {
                if ($entityProperty['entity_properties_target_FK_entity_id']) {
                    $toEntityTypeId = $entityProperty['entity_properties_target_FK_entity_id'];
                    $toEntityTypeName = $entityTypeService->getEntityTypeName($toEntityTypeId);
                    $label = __Tp('rel:'.$entityProperty['entity_properties_type']);
                    if (!$edges[$entityTypeName][$toEntityTypeName]) {
                        $edges[$entityTypeName][$toEntityTypeName] = true;
                        $graph .= '"'.$this->escape($entityTypeName).'" -> "'.$this->escape($toEntityTypeName).'" [label="'.$this->escape($label).'"];'.PHP_EOL;
                    }
                    $graph .= $this->getGraph($toEntityTypeId, $visited, $edges);
                }
            }

            $referenceRelations = $entityTypeService->getEntityTypeReferenceRelations($entityTypeId);

            foreach ((array)$referenceRelations as $referenceRelation) {
                if ($referenceRelation['entity_properties_target_FK_entity_id']) {
                    $toEntityTypeId = $referenceRelation['entity_properties_FK_entity_id'];
                    $toEntityTypeName = $entityTypeService->getEntityTypeName($toEntityTypeId);
                    $label = __Tp('rel:'.$referenceRelation['entity_properties_type']);
                    if (!$edges[$toEntityTypeName][$entityTypeName]) {
                        $edges[$toEntityTypeName][$entityTypeName] = true;
                        $graph .= '"'.$this->escape($toEntityTypeName).'" -> "'.$this->escape($entityTypeName).'" [label="'.$this->escape($label).'"];'.PHP_EOL;
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
        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.Entity', 'all');

        if ($it->count()) {
            $visited = array();
            $edges = array();
            foreach ($it as $ar) {
                if (!in_array($ar->getId(), array_keys($visited))) {
                    $graphCode .= $this->getGraph($ar->getId(), $visited, $edges, true);
                }
            }
        }
                        
        $graphCode = <<<EOD
digraph "" {
    $graphCode
}
EOD;
        $graphCode = str_replace(array("\r","\n"), '', addslashes($graphCode));

        $html .= <<<EOD
<div style="text-align: center;">
    <svg width="800" height="600">
      <g transform="translate(20, 20)"/>
    </svg>
</div>
        <style>
        svg {
            overflow: hidden;
        }
        
        /* Stile testo delle entità */
        g.nodes tspan {
            font-family: Helvetica, sans-serif;
            font-size: 14px;
        }
        /* Stile testo delle entità in HTML */
        g.nodes div {
            white-space: nowrap;
            padding: 4px;
            font-family: Helvetica, sans-serif;
            font-size: 13px;
        }
        
        /* Stile testo delle entità principali in HTML */
        div.main-node {
            padding: 2px 7px 2px 7px;
            font-family: Helvetica, sans-serif;
            font-size: 14px;
        }
        
        /* Stile testo delle relazioni */
        g.edgeLabels tspan {
            
        }

        /* Stile riquadro delle entità */
        .node rect {
            stroke: #cfced3;
            stroke-width: 2px;
            fill: none;
        }

        /* Stile delle frecce */
        .edgePath path {
            stroke: black;
            stroke-width: 1.5px;
            fill: none;
        }
        </style>
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

                var parentWidth = parseInt($(svg).parent().css('width').replace('px', '')) - 250;
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
        
        function adjustEntityContainers() {
            $('foreignObject').each(function() {
                var width = parseInt($(this).attr('width'));
                $(this).attr('width', width + 25);
            });
        }

        $(window).bind('load', function() {
            tryDraw();
            fixBaseTagProblem();
            adjustEntityContainers();
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