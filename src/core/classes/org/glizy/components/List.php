<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_List extends org_glizy_components_HtmlFormElement
{
    var $_items;
    var $_drawLinked = true;

    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        // define the custom attributes
        $this->defineAttribute('bindTo',        false,     NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('cssClass',            false,     __Config::get('glizy.formElement.cssClass'),        COMPONENT_TYPE_STRING);
        $this->defineAttribute('cssClassLabel',            false,     __Config::get('glizy.formElement.cssClassLabel'),        COMPONENT_TYPE_STRING);
        $this->defineAttribute('dataProvider',    false,     NULL,    COMPONENT_TYPE_OBJECT);
        $this->defineAttribute('disabled',        false,     false,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('emptyValue',    false,     NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('jsAction',        false,     NULL,        COMPONENT_TYPE_STRING);
        $this->defineAttribute('label',            false,     NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('multiSelect',    false,  false,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('rows',            false,     1,        COMPONENT_TYPE_INTEGER);
        $this->defineAttribute('readOnly',        false,     false,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('required',            false,     false,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('size',            false,     '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('value',            false,     NULL,        COMPONENT_TYPE_STRING);
        $this->defineAttribute('wrapLabel',        false,     false,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('insertInto',        false,     NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('target',        false,     NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('title',        false,     NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('checkValues',    false,     NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('searchCondition',    false,     '=',    COMPONENT_TYPE_STRING);
        $this->defineAttribute('adm:suggest',    false,     NULL,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('adm:suggestAdd',    false,     NULL,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('adm:suggestDelimiter',    false,     ' ',    COMPONENT_TYPE_STRING);
        $this->defineAttribute('outputOnlyValue',    false,     false,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('linkedTo',    false,     NULL,    COMPONENT_TYPE_OBJECT);
        $this->defineAttribute('flip',    false,     false,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('setFirstValue',    false,     true,    COMPONENT_TYPE_BOOLEAN);

        // call the superclass for validate the attributes
        parent::init();
    }

    function getChildsInfo(&$result)
    {
        for ($i=0; $i<count($this->childComponents);$i++)
        {
            $result[] = array(    'id' => $this->childComponents[$i]->getId(),
                                'originalId' => $this->childComponents[$i]->getOriginalId(),
                                'className' => get_class($this->childComponents[$i]),
                                'parent' => $this->getId());
            if (method_exists($this->childComponents[$i], 'getChildsInfo'))
            {
                $this->childComponents[$i]->getChildsInfo($result);
            }
        }
    }

    /**
     * Process
     *
     * @return    boolean    false if the process is aborted
     * @access    public
     */
    function process()
    {
        $this->_items = array();
        $linkedTo = $this->getAttribute('linkedTo');
        $bindTo = $this->getAttribute('bindTo');

        if ( !is_subclass_of( $this->_parent, 'org_glizy_components_RecordDetail' ) )
        {
            if ( !is_null( $linkedTo ) )
            {
                $linkedToId = $linkedTo->getId();
                $linkedValue = $this->_parent->loadContent( $linkedToId, $linkedTo->getAttribute('bindTo'));
                if ( $linkedValue == '' )
                {
                    $this->_drawLinked = false;
                    return;
                }
            }

            $dataProvider = &$this->getAttribute('dataProvider');
            if (is_null($dataProvider))
            {
                // legge i valori dai figli
                for ($i=0; $i<count($this->childComponents);$i++)
                {
                    if (method_exists($this->childComponents[$i], 'getItem') &&
                    $this->childComponents[$i]->getAttribute( 'visible' ) &&
                    $this->childComponents[$i]->getAttribute( 'enabled' ) )
                    {
                        $item = $this->childComponents[$i]->getItem();
                        if ($item!==false) {
                            $this->_items[] = $item;
                        }
                    }
                }
            }
            else
            {
                $this->_items = array_merge( $this->_items, $dataProvider->getItems($this->getId(), $bindTo ) );
            }

            if ($this->getAttribute('flip'))
            {
                $this->_items = array_reverse( $this->_items );
            }

            if (!is_null($this->getAttribute('emptyValue')))
            {
				$this->_items = array_merge( array( array('key' => '', 'value' => html_entity_decode( $this->getAttributeString('emptyValue') ), 'selected' => false, 'options' => '') ), $this->_items );
            }


            if (!is_null($this->getAttribute('checkValues')))
            {
                $checkValuesClass =  &org_glizy_ObjectFactory::createObject($this->getAttribute('checkValues'));
                $checkValuesClass->check($this->_items);
            }
        }

// // TODO: da verificare perch� viene fatta questa chiamata
        // $this->_content = $this->_parent->loadContent($this->getId(), $bindTo);
        // $this->_content = $this->getAttribute('value');
        $contentSource = $this->getAttribute('value');
        if (is_object($contentSource))
        {
            $this->_content = $contentSource->loadContent($this->getId(), $bindTo);
        }
        else if (is_null($this->_content))
        {
            $this->_content = $this->_parent->loadContent($this->getId(), $bindTo);
            if ( is_null($this->_content) && count( $this->_items ) && $this->getAttribute('setFirstValue') )
            {
                $this->_content = $this->_items[0]['key'];
            }
        }

        if (method_exists($this->_parent, 'setFilterValue') && !empty($this->_content) && $this->getAttribute('searchCondition')=='=')
        {
            $this->_parent->setFilterValue(!empty($bindTo) ? $bindTo : $this->getId(), array('condition' => '=', 'value' => html_entity_decode( $this->_content) ), $this->_content );
        }

        $this->processChilds();
    }

    function render_html()
    {
        $linkedTo = $this->getAttribute('linkedTo');
        if (!is_null( $linkedTo ) )
        {
            $id = $this->getId();
            $id_holder = $this->getId().'_holder';
            $linkedToId = $linkedTo->getId();
            $ajaxPath = 'ajax.php?pageId='.$this->_application->getPageId().'&ajaxTarget='.$id.'&'.$linkedToId.'=';
            $select = $this->_drawLinked ? $this->_render() : '';

            $output = <<<EOD
<div id="$id_holder">$select</div>
<script language="JavaScript" type="text/JavaScript">
window.addEvent('domready', function() {
    \$( "$linkedToId" ).addEvent( 'change', function() {
        if ( \$( "$linkedToId" ).get( "value" ) != "" )
        {
            new Request.HTML( { url: "$ajaxPath"+\$( "$linkedToId" ).get( "value" ),
                                update: \$( "$id_holder" )
                                 } ).get();
        }
        else
        {
            \$( "$id_holder" ).set( "text", "" );
        }
    });
});
</script>
EOD;
            $this->addOutputCode( $output );
        }
        else
        {
            $this->addOutputCode( $this->_render() );
        }
    }

    function _render()
    {

        if ( $this->getAttribute( 'outputOnlyValue' ) )
        {
            return $this->_content;
        }
        else
        {
            $output = '';

            $attributes                 = array();
            $attributes['id']             = $this->getId();
            $attributes['name']         = $this->getOriginalId();
            $attributes['disabled']     = $this->getAttribute('disabled') ? 'disabled' : '';
            $attributes['class']         = $this->getAttribute('required') ? 'required' : '';
            $attributes['class']         .= $this->getAttribute( 'cssClass' ) != '' ? ( $attributes['class'] != '' ? ' ' : '' ).$this->getAttribute( 'cssClass' ) : '';
            $attributes['title']         = $this->getAttributeString('title');
            $attributes['onchange']         = $this->getAttribute('onChange');

            if ( $this->getAttribute('readOnly') )
            {
                $output .= org_glizy_helpers_Html::hidden( $attributes['name'], $this->_content );
                $attributes['name'] .= '_orig';
                $attributes['id'] .= '_orig';
                $attributes['disabled'] = true;
            }

            if ( $this->getAttribute('rows')>1)
            {
                $attributes['size']         = $this->getAttribute('rows');
                $attributes['multiple']     = $this->getAttribute('multiSelect') ? 'multiple' : '';
            }

            $output .= '<select '.$this->_renderAttributes($attributes).'>';
            foreach($this->_items as $item)
            {
				if ( $this->_content=='' || is_null( $this->_content ) )
                {
                    $selected = (isset($item['selected']) && $item['selected']) ? ' selected="selected"':'';
                }
                else
                {
                    if ( is_array( $this->_content ) )
                    {
                        $selected = in_array( strtoupper( $item['key'] ), $this->_content ) ? ' selected="selected"':'';
                    }
                    else
                    {
                        $selected = strtoupper($item['key'])==strtoupper($this->_content) ? ' selected="selected"':'';
                    }
                }
                $disabled = isset($item['disabled']) ? ' '.$item['disabled'] : '';
                $options = isset($item['options']) ? ' data-options="'.$item['options'].'"' : '';
                $output .= '<option value="'.glz_encodeOutput($item['key']).'"'.$selected.$disabled.$options.'>'.glz_encodeOutput($item['value']).'</option>';
            }
            $output .= '</select>';


            $label = $this->getAttributeString('label') ? : '';
            if ($label) {
                $cssClassLabel = $this->getAttribute( 'cssClassLabel' );
                $cssClassLabel .= ( $cssClassLabel ? ' ' : '' ).($this->getAttribute('required') ? 'required' : '');
                if ($this->getAttribute('wrapLabel')) {
                    $label = org_glizy_helpers_Html::label($this->getAttributeString('label'), $this->getId(), true, $output, array('class' => $cssClassLabel ), false);
                    $output = '';
                } else {
                    $label = org_glizy_helpers_Html::label($this->getAttributeString('label'), $this->getId(), false, '', array('class' => $cssClassLabel ), false);
                }
            }
            return $this->applyItemTemplate($label, $output);
        }
    }

    function getContent()
    {
        return glz_encodeOutput($this->getText());
    }

    function process_ajax()
    {

        $this->setAttribute( 'linkedTo', null );
        $this->process();
        return array( 'html' => $this->_render() );
    }

    function addItem( $key, $value, $selected = false )
    {
        $this->_items[] = array('key' => $key, 'value' => $value, 'selected' => $selected, 'options' => array());
    }
}