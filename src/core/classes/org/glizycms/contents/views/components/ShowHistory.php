<?php
class org_glizycms_contents_views_components_ShowHistory extends org_glizy_components_ComponentContainer
{
    function render()
    {
        $menuId = __Request::get('menuId');

        // TODO spostare nel model
        $it = org_glizy_objectFactory::createModelIterator('org.glizycms.core.models.Content');
        $it->addSelect('u.*')
           ->join($it::DOCUMENT_TABLE_ALIAS, __Config::get('DB_PREFIX').'users_tbl', 'u',
                  $it->expr()->eq($it::DOCUMENT_DETAIL_TABLE_ALIAS.'.'.$it::DOCUMENT_DETAIL_FK_USER, 'u.user_id'))
           ->where("id", $menuId)
           ->orderBy('document_detail_modificationDate', 'DESC')
           ->allStatuses();

        // TODO localizzare
        $output = '<table class="table table-bordered table-striped">';
        $output .= '<thead><tr><th>Aggiornato da</th><th>Commento</th><th width="140">Data modifica</th></tr></thead>';
        $output .= '<tbody>';

        foreach ($it as $ar) {
            $output .= '<tr>'.
            '<td><input type="radio" name="history_a" value="'.$ar->document_detail_id.'" /> '.
            '<input type="radio" name="history_b" value="'.$ar->document_detail_id.'" /> '.
            $ar->user_firstName.' '.$ar->user_lastName.'</td>'.
            '<td></td>'.
            // '<td>'.$ar->document_detail_note.'</td>'.
            '<td>'.$ar->document_detail_modificationDate.'</td>'.
            '</tr>';
        }

        $output .= '</tbody>';
        $output .= '</table>';
        $output .= '<div id="diff"></div>';

        $ajaxUrl = $this->getAjaxUrl();
        $output .= <<<EOD
<script>
$(function(){
  $('input.js-glizycms-history').click(function(e){
      e.preventDefault();
      var a = $('input[name=history_a]:checked').val();
      var b = $('input[name=history_b]:checked').val();
      if (a && b && a!=b) {
        $.ajax({
            'url': '$ajaxUrl',
            'data': {a: a, b: b},
            'dataType': 'html',
            'success': function(data) {
              $("#diff").html(data);
            }
        });
      }
  });
});
</script>
EOD;
        $this->addOutputCode($output);
    }

    public function process_ajax()
    {
      $a = __Request::get('a');
      $b = __Request::get('b');

      $it = org_glizy_objectFactory::createModelIterator('org.glizycms.core.models.Content');
      $it->where("document_detail_id", $a)
           ->allStatuses();
      $ar_a = $it->first();

      $it = org_glizy_objectFactory::createModelIterator('org.glizycms.core.models.Content');
      $it->where("document_detail_id", $b)
           ->allStatuses();
      $ar_b = $it->first();

      $a = explode("\n", str_replace("<\/p>", "<\/p>\n", json_encode(json_decode($ar_a->document_detail_object), JSON_PRETTY_PRINT)));
      $b = explode("\n", str_replace("<\/p>", "<\/p>\n", json_encode(json_decode($ar_b->document_detail_object), JSON_PRETTY_PRINT)));

      glz_importLib('Diff/Diff.php');
      glz_importLib('Diff/Diff/Renderer/Html/SideBySide.php');
      // Options for generating the diff
      $options = array(
        //'ignoreWhitespace' => true,
        //'ignoreCase' => true,
      );
      $diff = new Diff($a, $b, $options);

      $renderer = new Diff_Renderer_Html_SideBySide;
      $result = $diff->Render($renderer);
      return array('html' => $result);
    }
}
