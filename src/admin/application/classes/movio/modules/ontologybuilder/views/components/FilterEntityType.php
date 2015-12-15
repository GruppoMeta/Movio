<?php
class movio_modules_ontologybuilder_views_components_FilterEntityType extends org_glizy_components_Component
{
    function init()
    {
        // define the custom attributes
        $this->defineAttribute('label', false, '{i18n:Filter by type}', COMPONENT_TYPE_STRING);
        $this->defineAttribute('dataGridAjaxId', true, '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('fieldNumber', true, '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('recordClassName', true, '', COMPONENT_TYPE_STRING);

        parent::init();
    }

    function render()
    {
        $id = $this->getAttribute('id');
        $output  = '<div id="'.$id.'_cont" style="display: inline">';
        $output .= '<label for="'.$id.'">'.$this->getAttribute('label').' ';
        $output .= '<select id="'.$id.'">';
        $output .= '<option value="">All</option>';

        $it = org_glizy_objectFactory::createModelIterator($this->getAttribute('recordClassName'), 'all');

        foreach ($it as $ar) {
            $output .= '<option value="entity'.$ar->getId().'">'.$ar->entity_name.'</option>';
        }

        $output .= '</select></label>';
        $output .= '</div>';

        $dataGridId = $this->getAttribute('dataGridAjaxId');
        $fieldNumber = $this->getAttribute('fieldNumber');

        $output .= <<<EOD
<script type="text/javascript">
    jQuery(function(){
        var table = jQuery('#$dataGridId').data('dataTable');
        setTimeout(function(){
            jQuery("#{$id}_cont").children().appendTo("#{$dataGridId}_filter");
            var ooSettings = table.fnSettings();
            $("#$id").val(ooSettings.aoPreSearchCols[$fieldNumber].sSearch);
        }, 100);

        jQuery('#$id').change( function () {
            table.fnFilter( $(this).val(), $fieldNumber );
        });
    });
</script>
EOD;
        $this->addOutputCode($output);
    }
}