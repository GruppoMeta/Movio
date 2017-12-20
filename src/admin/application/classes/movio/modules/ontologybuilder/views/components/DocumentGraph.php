<?php
class movio_modules_ontologybuilder_views_components_DocumentGraph extends org_glizy_components_Component
{
    function init()
    {
        // define the custom attributes
        $this->defineAttribute('entityTypeId', false, null, COMPONENT_TYPE_INTEGER);
        $this->defineAttribute('documentId', false, null, COMPONENT_TYPE_INTEGER);
        $this->defineAttribute('addGraphJsLibs.js', false, true, COMPONENT_TYPE_BOOLEAN);

        parent::init();
    }

    protected function escape($s, $trim=false)
    {
        // si taglia la stringa dopo i 60 caratteri
        $s = glz_strtrim($s, 60);
        $s = htmlentities($s, null, "UTF-8");
        $s = str_replace('"', "&quot;", $s);
        return $s;
    }

    protected function makeUrl($entityTypeId, $document_id)
    {
        return org_glizy_helpers_Link::makeUrl('showEntityDetail', array('entityTypeId' => $entityTypeId, 'document_id' => $document_id));
    }

    public function getDocumentGraph($entityTypeId, $documentId)
    {
        $edges = array();

        $entityProxy = org_glizy_objectFactory::createObject('movio.modules.ontologybuilder.models.proxy.EntityProxy');
        $data = $entityProxy->getRelations($documentId);

        // se non ci sono documenti correlati
        if (empty($data['relations']) && empty($data['reference_relations'])) {
            return '';
        }

        $color = __Config::get('movio.graph.shapeColor');
        $entityTypeService = $this->_application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');

        $graphCode = '"'.$this->escape($data['title']).'" [label="<div class=\'main-node\'>' . $this->escape($data['title']) . '</div>",style="rounded,filled", height=0.4, color="'.$color.'", fillcolor="'.$color.'", fontcolor=white, fontsize=13];'.PHP_EOL;

        foreach ($data['relations'] as $relation) {
            $url = $this->makeUrl($relation['entityTypeId'], $relation['document_id']);
            $graphCode .= '"'.$this->escape($relation['title']).'" [label="<div><a href=\''.$url.'\'>' . $this->escape($relation['title']) . '</a></div>"];'.PHP_EOL;
            $entityTypeName = $entityTypeService->getEntityTypeName($relation['entityTypeId']);

            if (!$edges[$data['title']][$entityTypeName]) {
                $graphCode .= '"'.$this->escape($data['title']).'" -> "'.$entityTypeName.'";'.PHP_EOL;
                $edges[$data['title']][$entityTypeName] = true;
            }

            $graphCode .= '"'.$entityTypeName.'" -> "'.$this->escape($relation['title']).'";'.PHP_EOL;
            $graphCode .= '"'.$entityTypeName.'" [label="<div>' . $entityTypeName . '</div>"];'.PHP_EOL;
        }

        $entityTypeName = $entityTypeService->getEntityTypeName($entityTypeId);
        $graphCode .= '"'.$entityTypeName.'" -> "'.$this->escape($data['title']).'";'.PHP_EOL;
        $graphCode .= '"'.$entityTypeName.'" [label="<div>' . $entityTypeName . '</div>"];'.PHP_EOL;

        foreach ($data['reference_relations'] as $relation) {
            $url = $this->makeUrl($relation['entityTypeId'], $relation['document_id']);
            $graphCode .= '"'.$this->escape($relation['title']).'" [label="<div><a href=\''.$url.'\'>' . $this->escape($relation['title']) . '</a></div>"];'.PHP_EOL;

            if (!$edges[$relation['title']][$entityTypeName]) {
                $subEntityTypeName = $entityTypeService->getEntityTypeName($relation['entityTypeId']);
                /*$graphCode .= '"'.$this->escape($relation['title']).'" -> "'.$entityTypeName.'" [dir=back];'.PHP_EOL;
                $graphCode .= '"'.$subEntityTypeName.'" -> "'.$this->escape($relation['title']).'" [dir=back];'.PHP_EOL;*/
                $graphCode .= '"'.$entityTypeName.'" -> "'.$this->escape($relation['title']).'";'.PHP_EOL;
                $graphCode .= '"'.$this->escape($relation['title']).'" -> "'.$subEntityTypeName.'";'.PHP_EOL;
                //$graphCode .= '"'.$this->escape($relation['title']).'" [label="<div>' . $this->escape($relation['title']) . '</div>"];'.PHP_EOL;
                $graphCode .= '"'.$subEntityTypeName.'" [label="<div>' . $subEntityTypeName . '</div>"];'.PHP_EOL;
                $edges[$data['title']][$entityTypeName] = true;
            }
        }

        return $graphCode;
    }

    public function render_html()
    {
        $graphCode = '';

        $entityTypeId = $this->getAttribute('entityTypeId') ? $this->getAttribute('entityTypeId') : __Request::get('entityTypeId');
        $documentId = $this->getAttribute('documentId') ? $this->getAttribute('documentId') : __Request::get('document_id');

        if ($entityTypeId && $documentId) {
            $graphCode = $this->getDocumentGraph($entityTypeId, $documentId);
        }
        if(empty($graphCode)) {
            return;
        }

        $title = __T('Relations');
        $id = $this->getId();

        $graphCode = <<<EOD
digraph "" {
    $graphCode
}
EOD;
        $graphCode = str_replace(array("\r","\n"), '', addslashes($graphCode));

        $html .= <<<EOD
<article class="box collapsible big">
    <h1>{$title}</h1>
    <button data-toggle="collapse" class="show-content-box" type="button" data-target="#{$id}"></button>
    <div id="{$id}" style="text-align: center;">
        <svg width="800" height="600">
          <g transform="translate(20, 20)"/>
        </svg>
    </div>
</article>
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

                //var parentWidth = parseInt($(svg).parent().css('width').replace('px', '')) - 250;
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
            $('.collapsible').children('div').addClass('collapse');
        });
</script>
EOD;

        if ($this->getAttribute('addGraphJsLibs.js')) {
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile(__Paths::get('STATIC_DIR').'dagre-d3/d3.v3.js'), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile(__Paths::get('STATIC_DIR').'dagre-d3/dagre-d3.js'), 'head');
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile(__Paths::get('STATIC_DIR').'dagre-d3/graphlib-dot.min.js'), 'head');
        }
        $this->addOutputCode( $html );
    }
}