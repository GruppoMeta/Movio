<?php
class org_glizy_components_DataGridAjax extends org_glizy_components_Component
{
    private $columns = array();

    function init()
    {
        $this->defineAttribute('cssClass', false, '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('JQueryUI', false, true, COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('dbDebug', false, false, COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('minSearchLenght', false, 0, COMPONENT_TYPE_INTEGER);
        $this->defineAttribute('orderBy',         false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('orderDirection',false,    'asc',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('enablePicker',false, false,        COMPONENT_TYPE_BOOLEAN);

        // nella version 1 (v2==false)
        $this->defineAttribute('recordClassName', true, '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('query', false, 'all', COMPONENT_TYPE_STRING);
        $this->defineAttribute('queryOperator', false, 'OR', COMPONENT_TYPE_STRING);
        $this->defineAttribute('fullTextSearch', false, false, COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('setFiltersToQuery', false, false, COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('filterClass',   false, '',     COMPONENT_TYPE_STRING);

        // call the superclass for validate the attributes
        parent::init();
    }

    function render_html(){
        $tableClass = $this->getAttribute( "cssClass" );
        $id = $this->getId();
        $ajaxUrl = $this->getAjaxUrl();

        $colSpan = 0;
        $headers = '';
        $aoColumnDefs = array();
        $searchable = 0;

        foreach( $this->columns as $column )
        {
            if ( $column['acl'] ) {
                if (!$this->_user->acl($column['acl']['service'], $column['acl']['action'])) {
                    continue;
                }
            }

            $colSpan++;
            $headers .= '<th';
            if ( !$column['visible'] ) $headers .= ' style="display:none;"';
            if ( $column['width'] ) $headers .= ' width="'.$column['width'].'%"';
            $headers .= '>'.$column['headerText'].'</th>';

            $aoColumnDefs[] = array (
                "bSortable" => $column['sortable'],
                "bSearchable" => $column['searchable'],
                "aTargets" => array($colSpan-1),
                "sType" => "html",
                "sClass" => $column['cssClass']
            );
            $searchable += ($column['searchable'] ? 1 : 0);
        }

        $aoColumnDefs = json_encode($aoColumnDefs);

        if (!org_glizy_ObjectValues::get('jquery.dataTables', 'add', false))
        {
            org_glizy_ObjectValues::set('jquery.dataTables', 'add', true);
            $staticDir = org_glizy_Paths::get('STATIC_DIR');
            $html = '<script type="text/javascript" src="'.$staticDir.'/jquery/datatables/media/js/jquery.dataTables.min.js"></script>';
            $html .= '<script type=""text/javascript" src="'.$staticDir.'/jquery/datatables/media/js/jquery.dataTables.bootstrap.js"></script>';
        }

        $orderBy = $this->getAttribute('orderBy');
        $orderDirection = $this->getAttribute('orderDirection');
        $cookieName = 'DataTables_'.md5(__Config::get('SESSION_PREFIX').$this->getId().$this->_application->getPageId().$this->_user->id);
        $sLengthMenu = __T('records per page');
        $sEmptyTable = __T('No record found');
        $sZeroRecords = __T('No record found with current filters');
        $sInfo = __T('Showing _START_ to _END_ of _TOTAL_ entries');
        $sInfoEmpty = __T('Showing 0 to 0 of 0 entries');
        $sInfoFiltered = __T('filtered from _MAX_ total entries');
        $sLoadingRecords = __T('Loading...');
        $sLoadingFromServer = __T('Loading data from server');
        $sProcessing = __T('Processing...');
        $Search = __T('Search');
        $sFirst = __T('First');
        $sLast = __T('Last');
        $sNext = __T('Next');
        $sPrevious = __T('Previous');
        $JQueryUI = $this->getAttribute('JQueryUI') ? 'true' : 'false';
		$bFilter = $searchable ? 'true' : 'false';
        $minSearchLenght = $this->getAttribute('minSearchLenght');

        $html .= <<<EOD
        <table class="$tableClass" id="$id">
            <thead>
                <tr >
                    $headers
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="$colSpan" style="text-align: center" class="dataTables_empty">$sLoadingFromServer</td>
                </tr>
            </tbody>
        </table>
<script type="text/javascript">
// <![CDATA[
\$( function(){
    var table = \$('#$id').dataTable( {
        "sDom": "<'row-fluid filter-row clearfix'<'filter-box'l><'filter-box'f>r>t<'row-fluid clearfix'<'filter-box pull-left'i><'filter-box pull-right'p>>",
        "sPaginationType": "bootstrap",
        "oLanguage": {
            "sLengthMenu": "_MENU_ $sLengthMenu",
            "sEmptyTable": "$sEmptyTable",
            "sZeroRecords": "$sZeroRecords",
            "sInfo": "$sInfo",
            "sInfoEmpty": "$sInfoEmpty",
            "sInfoFiltered": "($sInfoFiltered)",
            "sLoadingRecords": "$sLoadingRecords",
            "sProcessing": "$sProcessing",
            "sSearch": "{$Search}:",
            "oPaginate": {
                "sFirst": "$sFirst",
                "sLast": "$sLast",
                "sNext": "$sNext",
                "sPrevious": "$sPrevious"
            }
        },
        "bJQueryUI": $JQueryUI,
        "bServerSide": true,
        "sAjaxSource": "$ajaxUrl",
        "aoColumnDefs": $aoColumnDefs,
        "bStateSave": true,
        "bFilter": $bFilter,
        "fnStateSave": function (oSettings, oData) {
            localStorage.setItem( "$cookieName", JSON.stringify(oData) );
        },
        "fnStateLoad": function (oSettings) {
            var state = JSON.parse(localStorage.getItem("$cookieName"));
            var orderBy = "$orderBy";
            var orderDirection = "$orderDirection";
            if (state && orderBy) {
                state.aaSorting = [[parseInt(orderBy), orderDirection, 0]];
            }
            return state;
        }
    } );

    \$('.dataTables_filter input')
        .unbind()
        .bind('keyup', function(e){
            if (\$(this).val().length > 0 && \$(this).val().length < $minSearchLenght && e.keyCode != 13) return;
            table.fnFilter(\$(this).val());
        })

    \$('#$id').data('dataTable', table);
});
// ]]>
</script>
EOD;


        if ($this->getAttribute('enablePicker')) {
            $html .= <<<EOD
<div class="formButtons">
    <div class="content">
        <input type="button" value="Inserisci" class="btn" name="insert-selected" id="{$id}-insert-selected">
        <input type="button" value="Seleziona tutte" class="btn" name="select-all" id="{$id}-select-all">
        <input type="button" value="Deseleziona tutte" class="btn" name="deselect-all" id="{$id}-deselect-all">
    </div>
</div>

<script type="text/javascript">
// <![CDATA[
\$( function(){
    var dataGrid = $('#{$id}');
    dataGrid.on('draw.dt', function () {
        $("input[type='checkbox']").each(function(){
            if ($.inArray(this.value, ids) != -1) {
                $(this).attr('checked', 'checked');
            }
        });
    });
    var insertSelected = $('#{$id}-insert-selected');
    var selectAll = $('#{$id}-select-all');
    var deselectAll = $('#{$id}-deselect-all');
    insertSelected.attr('disabled', true);
    var ids = [];
    var data = [];
    $('#{$id}').on('change', "input[type='checkbox']", function(e) {
        if ($.inArray(e.target.value, ids) == -1) {
            ids.push(e.target.value);
            data.push(JSON.parse(e.target.dataset.value));
        } else {
            ids.splice($.inArray(e.target.value, ids), 1);
            data.splice($.inArray(e.target.value, ids), 1);
        }
        insertSelected.attr('disabled', ids.length<1);
    });
    insertSelected.click(function(){
        Glizy.events.broadcast("recordsPicker.set", data);
    });
    selectAll.click(function(){
        $("input[type='checkbox']").each(function(){
            if (!this.checked) {
                $(this).attr('checked', 'checked');
                $(this).trigger('change');
            }
        });
        insertSelected.attr('disabled', false);
    });
    deselectAll.click(function(){
        $("input[type='checkbox']").each(function(){
            if (this.checked) {
                $(this).removeAttr('checked');
                $(this).trigger('change');
            }
        });
        insertSelected.attr('disabled', ids.length<1);
    });
});
// ]]>
</script>
EOD;
        }

        $this->addOutputCode( $html );
    }


    public function getAjaxUrl()
    {
        return parent::getAjaxUrl().__Request::get( 'action', 'Index' );
    }

    public function addColumn( $column )
    {
        if (preg_match("/\{i18n\:.*\}/i", $column['headerText']))
        {
            $code = preg_replace("/\{i18n\:(.*)\}/i", "$1", $column['headerText']);
            $column['headerText'] = org_glizy_locale_Locale::getPlain($code);
        }

        $this->columns[] = $column;
    }

    function process_ajax()
    {
        $sSearch = __Request::get('sSearch');
        $aColumns = $this->columns();
        $it = $this->getModelIterator($sSearch, $aColumns);

        $aaData = array();
        if ($this->getAttribute('dbDebug')) {
            org_glizy_dataAccessDoctrine_DataAccess::enableLogging();
        }

        try {
            foreach( $it as $row ) {
                $rowToInsert = array();
                foreach( $this->columns as &$column ) {
                    $currentColumns++;
                    if ( $column['acl'] ) {
                        if (!$this->_user->acl($column['acl']['service'], $column['acl']['action'])) {
                            continue;
                        }
                    }

                    $value = $row->$column['columnName'];
                    if ( $column['renderCell'] ) {
                        if ( !is_object( $column['renderCell'] ) ) {
                            $column['renderCell'] = org_glizy_ObjectFactory::createObject( $column['renderCell'], $this->_application );
                        }

                        if ( is_object($column['renderCell'])) {
                            $value = $column['renderCell']->renderCell( $row instanceof org_glizy_dataAccessDoctrine_ActiveRecord ? $row->getId() : null,
                                                                        $value,
                                                                        $row,
                                                                        $column['columnName'] );
                        }
                    }

                    if (is_object($value)) {
                        $value = json_encode($value);
                    }
                    $rowToInsert[] = $value;
                }
                $aaData[] = $rowToInsert;

            }
        } catch (Exception $e) {
            var_dump($e);
        }

        if ($this->getAttribute('dbDebug')) {
            org_glizy_dataAccessDoctrine_DataAccess::disableLogging(); die;
        }

        $output = array(
                "sEcho" => intval(__Request::get('sEcho')),
                "iTotalRecords" => $it->count(),
                "iTotalDisplayRecords" => $it->count(),
                "aaData" => $aaData
        );

        return $output;
    }

    /**
     * @param  string $sSearch
     * @param  array $aColumns
     * @return array
     */
    private function getModelIterator($sSearch, $aColumns)
    {
        $it = org_glizy_ObjectFactory::createModelIterator($this->getAttribute('recordClassName'));

        if ($it->getArType() === 'document') {
            $it->setOptions(array('type' => 'PUBLISHED_DRAFT'));
        }

        if ($this->getAttribute('setFiltersToQuery')) {
            $it->load($this->getAttribute('query'), array('filters' => $this->filters($sSearch, $aColumns)));

        } else  {
            $it->load($this->getAttribute('query'));
            if (method_exists($it, 'showAll')) {
                $it->showAll();
            }

            if ($this->getAttribute('fullTextSearch') && $sSearch && false) {
                $it->where('fulltext', '%'.$sSearch.'%', 'ILIKE');
            } else {
                $filters = $this->filters($sSearch, $aColumns, true);
                $filterClassName = $this->getAttribute('filterClass');
                $filterClass = $filterClassName ? org_glizy_ObjectFactory::createObject($filterClassName) : null;
                if ($filterClass) {
                    $filters = $filterClass->getFilters($filters);
                }

                if (!empty($filters)) {
                    if ($this->getAttribute('queryOperator') === 'OR') {
                        $it->setOrFilters($filters);
                    } else {
                        $it->setFilters($filters);
                    }
                }
            }
        }

        // Ordering
        $ordering = $this->ordering($aColumns);
        if ($ordering) {
            $it->orderBy($ordering['field'], $ordering['dir']);
        }

        // Paging
        $paging = $this->paging();
        if ($paging) {
            $it->limit($paging['start'], $paging['length']);
        }

        return $it;
    }

    /**
     * @return array
     */
    private function columns()
    {
        $aColumns = array();
        foreach( $this->columns as $column )
        {
            if ( !in_array( $column['columnName'], $aColumns)) {
                $aColumns[] = $column['columnName'];
            }
        }
        return $aColumns;
    }

    /**
     * @param  string  $sSearch
     * @param  array  $aColumns
     * @param  boolean $withCondition
     * @return array
     */
    private function filters($sSearch, $aColumns, $withCondition=false)
    {
        $filters = array();
        for ( $i=0 ; $i < count($aColumns) ; $i++ ) {
            if (__Request::get('sSearch_'.$i)) {
                $filters[$aColumns[$i]] =  !$withCondition ?
                                                __Request::get('sSearch_'.$i) :
                                                array('value' => __Request::get('sSearch_'.$i), 'condition' => 'LIKE');
            }  else if ($sSearch != '' && __Request::get('bSearchable_'.$i) == "true" ) {
                $filters[$aColumns[$i]] = !$withCondition ?
                                                $sSearch:
                                                array('value' => '%'.$sSearch.'%', 'condition' => 'LIKE');
            }
        }
        return $filters;
    }

    /**
     * @param  array $aColumns
     * @return array
     */
    private function ordering($aColumns)
    {
        if ( __Request::exists('iSortCol_0') ) {
            $iSortingCols = intval( __Request::get( 'iSortingCols' ));
            for ( $i=0 ; $i<$iSortingCols ; $i++ ) {
                if ( __Request::get( 'bSortable_'.intval( __Request::get('iSortCol_'.$i) ) ) == "true" ) {
                    $order = $aColumns[ intval( __Request::get( 'iSortCol_'.$i ) ) ];
                    $order_dir = __Request::get('sSortDir_'.$i);
                    return array('field' => $order, 'dir' => $order_dir);
                }
            }
        }

        return false;
    }

    /**
     * @return array
     */
    private function paging()
    {
        if ( __Request::get( 'iDisplayStart', -1 ) != -1 ) {
            return array('start' => __Request::get( 'iDisplayStart' ), 'length' => __Request::get( 'iDisplayLength', -1 ));
        }

        return false;
    }


    public static function compile($compiler, &$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix, $componentClassInfo, $componentId)
    {
        $compiler->_classSource .= '$n'.$counter.' = org_glizy_ObjectFactory::createComponent(\''.$componentClassInfo['classPath'].'\', $application, '.$parent.', \''.$node->nodeName.'\', '.$idPrefix.'\''.$componentId.'\', \''.$componentId.'\', $skipImport)'.GLZ_COMPILER_NEWLINE;

        if ($parent!='NULL')
        {
            $compiler->_classSource .= $parent.'->addChild($n'.$counter.')'.GLZ_COMPILER_NEWLINE;
        }

        if (count($node->attributes))
        {
            // compila  gli attributi
            $compiler->_classSource .= '$attributes = array(';
            foreach ( $node->attributes as $key=>$value )
            {
                if ($key!='id')
                {
                    $compiler->_classSource .= '\''.$key.'\' => \''.addslashes( $node->getAttribute( $key ) ).'\', ';
                }
            }
            $compiler->_classSource .= ')'.GLZ_COMPILER_NEWLINE;
            $compiler->_classSource .= '$n'.$counter.'->setAttributes( $attributes )'.GLZ_COMPILER_NEWLINE;
        }


        foreach ($node->childNodes as $n )
        {
            if ( strpos( $n->nodeName, ":DataGridColumn" ) !== false )
            {
                $params = array();
                $params['sortable'] = $n->hasAttribute( 'sortable' ) ? $n->getAttribute( 'sortable' ) == 'true' : true;
                $params['searchable'] = $n->hasAttribute( 'searchable' ) ? $n->getAttribute( 'searchable' ) == 'true' : true;
                $params['visible'] = $n->hasAttribute( 'visible' ) ? $n->getAttribute( 'visible' ) == 'true' : true;
                $params['columnName'] = $n->hasAttribute( 'columnName' ) ? $n->getAttribute( 'columnName' ) : '';
                $params['headerText'] = $n->hasAttribute( 'headerText' ) ? $n->getAttribute( 'headerText' ) : '';
                $params['width'] = $n->hasAttribute( 'width' ) ? $n->getAttribute( 'width' ) : '';
                $params['acl'] = $n->hasAttribute( 'acl' ) ? $n->getAttribute( 'acl' ) : '';
                $params['cssClass'] = $n->hasAttribute( 'cssClass' ) ? $n->getAttribute( 'cssClass' ) : '';
                $params['renderCell'] = $n->hasAttribute( 'renderCell' ) ? $n->getAttribute( 'renderCell' ) : '';
                if ($params['acl']) {
                    list( $service, $action ) = explode( ',', $params['acl'] );
                    $params['acl'] = array('service' => $service, 'action' => $action);
                }
                $compiler->_classSource .= '$n'.$counter.'->addColumn('.var_export($params, true).');';
            }
        }
    }
}