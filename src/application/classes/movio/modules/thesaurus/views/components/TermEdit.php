<?php
class movio_modules_thesaurus_views_components_TermEdit  extends org_glizycms_views_components_FormEdit
{
	protected $emptySrc;
	protected $editSrc;
	protected $_pageTypeObj;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	public function init()
	{
		$this->defineAttribute('mode', false, 'container', COMPONENT_TYPE_STRING);
		$this->defineAttribute('initialState', false, 'empty', COMPONENT_TYPE_STRING);
		$this->defineAttribute('editState', false, 'edit', COMPONENT_TYPE_STRING);
		$this->defineAttribute('editUrl', false, __Config::get('glizycms.speakingUrl'), COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('adm:cssClass', false, __Config::get('glizy.formElement.admCssClass'), COMPONENT_TYPE_STRING);
		// call the superclass for validate the attributes
		parent::init();
	}


	public function process() {
		if ($this->getAttribute('mode')=='container') {
			$this->emptySrc = __Routing::makeUrl('linkChangeAction', array( 'action' => $this->getAttribute('initialState')));
			$this->editSrc = __Routing::makeUrl('linkChangeAction', array( 'action' => $this->getAttribute('editState'))).'?dictionaryId='.__Request::get('dictionaryId').'&termId=';
		} else {
	        $termId = __Request::get('termId');
	        $thesaurusProxy = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.proxy.ThesaurusProxy');
            $termVo = $thesaurusProxy->loadTerm($termId);
            $this->setData($termVo);

			parent::process();
		}
	}

	public function render_html_onStart()
	{
        if ($this->getAttribute('mode')=='container') {

			$this->addOutputCode('<iframe id="js-glizycmsPageEdit" src="" data-emptysrc="'.$this->emptySrc.'" data-editsrc="'.$this->editSrc.'"></iframe>');

            $corePath = __Paths::get('CORE');
            $jQueryPath = $corePath.'classes/org/glizycms/js/jquery/';
            $this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jQueryPath.'jquery.pnotify/jquery.pnotify.min.js' ) );
            $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $jQueryPath.'jquery.pnotify/jquery.pnotify.default.css' ) );

    	} else {
			$this->addOutputCode('<div id="message-box"></div>');
			parent::render_html_onStart();
		}
	}

	public function render_html_onEnd($value='')
	{
		if ($this->getAttribute('mode')!='container') {
			parent::render_html_onEnd();
		}
	}
}