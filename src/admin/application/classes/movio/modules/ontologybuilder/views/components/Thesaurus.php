<?php
class movio_modules_ontologybuilder_views_components_Thesaurus extends org_glizy_components_ComponentContainer
{
    // NOTA: la posizione di questo componente è corretta?
    function process()
    {
        $terms = $this->_parent->loadContent($this->getId());

        $this->_content = array();
        if ($terms && is_array($terms) && count($terms)) {

            $thesaurusProxy = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.proxy.ThesaurusProxy');
            $termVO = $thesaurusProxy->loadTerm($terms[0]->id);

            foreach ($terms as $term) {
                $this->_content[]= array(
                    'title' => $term->text,
                    'type' => $termVO->type,
                    'url' => 'thesaurus&termId='.$term->id
                );
            }
        }
    }

    function loadContent($id)
    {
        return $this->_content[$id];
    }
}