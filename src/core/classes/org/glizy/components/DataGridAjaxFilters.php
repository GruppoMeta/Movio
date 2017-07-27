<?php
class org_glizy_components_DataGridAjaxFilters extends org_glizy_components_ComponentContainer
{
    private $fieldNumbers = null;

    function init()
    {
        // define the custom attributes
        $this->defineAttribute('dataGrid', true, '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('fieldNumbers', true, '', COMPONENT_TYPE_STRING);

        parent::init();
    }

    function addOutputCode($output, $editableRegion='', $atEnd=false)
    {
        if (!$this->fieldNumbers) {
            $this->fieldNumbers = explode(',', $this->getAttribute('fieldNumbers'));
        }
        $fieldNumber = array_shift($this->fieldNumbers);
        $dataGridId = $this->getAttribute('dataGrid');
        $id = $this->childComponents[0]->getId();

        $newOutput .= <<<EOD
<div id="{$id}_cont" style="display: none; float: left">{$output}</div>
<script type="text/javascript">
    (function($){
        $(function(){
            var table = $('#$dataGridId').data('dataTable');
            setTimeout(function(){
               $("#{$id}_cont").appendTo("#{$dataGridId}_filter").show();
                var ooSettings = table.fnSettings();
                $("#$id").val(ooSettings.aoPreSearchCols[$fieldNumber].sSearch);
                // force change event to synchronize the filter at the document load
                $('#$id').change();
            }, 100);
           
            $('#$id').change( function () {
                table.fnFilter( $(this).val(), $fieldNumber );
            });
        });
    })(jQuery);
</script>
EOD;

        parent::addOutputCode($newOutput, $editableRegion, $atEnd);
    }
}