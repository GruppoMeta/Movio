<?php

class org_glizycms_roleManager_views_Input extends org_glizy_components_Input
{
	function render()
	{
		parent::render();

		$rootComponent = &$this->getRootComponent();
		if (!org_glizy_ObjectValues::get('org.glizy.JS.textext', 'add', false)) {
			org_glizy_ObjectValues::set('org.glizy.JS.textext', 'add', true);
			$core = __Paths::get('CORE');
			$output = <<<EOD
<link rel="stylesheet" type="text/css" href="$core/classes/org/glizycms/js/jquery/select2/select2.css" />
<script type="text/javascript" src="$core/classes/org/glizycms/js/jquery/select2/select2.min.js" charset="UTF-8"></script>
EOD;
			$rootComponent->addOutputCode($output, 'head');
		}

		$id = $this->getId();

		$content = $this->_content ? json_encode($this->_content) : '[]';
		$ajaxUrl = 'ajax.php?pageId='.__Request::get('pageId').'&ajaxTarget='.$this->getId();
		$output = <<<EOD
<script type="text/javascript">
$(function(){
	$('#$id').val('');
    $('#$id').select2({
        multiple: true,
        ajax: {
            url: '$ajaxUrl',
            dataType: 'json',
            quietMillis: 100,
            data: function(term, page) {
                return {
                    q: term,
                };
            },
            results: function(data, page ) {
                return { results: data }
            }
        },
    });

    $('#$id').select2('data', $content);
});
</script>
EOD;
		$rootComponent->addOutputCode($output, 'head');
	}

	function process_ajax() {
		$mode = $this->getAttribute('mode');
		$q = __Request::get('q');
		$result = array();
		if ($mode == 'users') {
			$it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.userManager.models.User', 'all');
            $it->setOrFilters(array(
                                "user_firstName" => $q,
                                "user_lastName" => $q,
                                "user_loginId" => $q,
                             ));
			foreach ($it as $ar) {
                $result[] = array('id' => $ar->user_id, 'text' => $ar->user_loginId);
			}
		} else if ($mode == 'groups') {
			$it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.userManager.models.UserGroup', 'all',
					array('filters' => array('usergroup_name' => $q) )
				);
			foreach ($it as $ar) {
                $result[] = array('id' => $ar->usergroup_id, 'text' => $ar->usergroup_name);
			}
		} else {
			$it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.roleManager.models.Role', 'all',
					array('filters' => array('role_name' => $q) )
				);
			foreach ($it as $ar) {
                $result[] = array('id' => $ar->role_id, 'text' => $ar->role_name);
			}
		}
		return $result;
	}
}