<?php
interface org_glizycms_speakingUrl_IUrlResolver
{
    public function compileRouting($ar);
    public function searchDocumentsByTerm($term, $id, $protocol='', $filterType='');
    public function makeUrl($id);
    public function makeUrlFromRequest();
    public function makeLink($id);
}
