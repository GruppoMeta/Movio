<?php
class movio_modules_sharingButtons_views_components_GetSharingButtons extends org_glizy_components_Component
{
    private $buttonListBase = array();
    private $dimList = array();
    private $shareButtons = '';
    private $enabledButtons = array();
    private $selectedDim = '';
    private $enableCheck = '';

    function init()
    {
        parent::init();
        /* Togliere elemento dall' array per disabilitare nel back-end*/
        $this->buttonListBase = array("Twitter",
                                      "Linkedin",
                                      "Facebook",
                                      "Google-plus",
                                      "E-mail",
                                      "Facebook-like",
                                      /*"Google+1",
                                      "Pinterest"*/);

        $this->dimList = array( "xs" => __T('extra small'),
                                "sm" => __T('small'),
                                "md" => __T('medium'),
                                "lg" => __T('large'));

        $this->shareButtons = movio_modules_sharingButtons_views_SharingButton::getSharingButtonList();
    }

    function process()
    {
        if ($this->shareButtons) {
          $this->enabledButtons = explode(',', $this->shareButtons['buttonList']);
          $this->selectedDim = $this->shareButtons['dim'];
          $this->enableCheck = $this->shareButtons['enable'] ? 'checked ' : ' ';
        }
        else {
          $this->enabledButtons = array();
          $this->selectedDim = 'md';
          $this->enableCheck = 'unchecked';
        }
    }

    function render()
    {
        $minHeight = count($this->buttonListBase)*29 +2;
        $output .= '
        <div class="control-group">
            <label class="control-label " for="sharingButton">'.__T('Enable sharing buttons').'</label>
            <div class="controls">
               <input type="checkbox" data-type="checkbox" value ="1"'.$this->enableCheck.'name="sharingButtonCheck" id="sharingButtonCheck">
            </div>
        </div>
        <div id"shareButtonDrag" class="control-group" style="margin-left:20px;">
          <div class ="span2">
            <div style="font-weight:bold; margin-bottom:5px;">
              <hX>'.__T('Available buttons').'</hX>
            </div>
            <ul id="shareButtonListOff" class="sortable js-sharingDisabled" style="min-height:'.$minHeight.'px;">';

        foreach ($this->buttonListBase as $button) {
             if (!in_array ($button, $this->enabledButtons)) {
                $output .= '
                  <li id="shareButton_'.$button.'" class="ui-state-default">'.$button.'</li>';
             }
        }

        $output .= '
            </ul>
            </div>
            <div class ="span2">
            <div style="font-weight:bold; margin-bottom:5px;">
              <hX>'.__T('Enabled buttons').'</hX>
            </div>
            <ul id="shareButtonListOn" class="sortable js-sharingEnabled " style="min-height:'.$minHeight.'px;">';

        foreach ($this->buttonListBase as $button) {
            if(in_array ($button, $this->enabledButtons)){
              $output .= '
              <li id="shareButton_'.$button.'" class="ui-state-default">'.$button.'</li>';
          }
        };

        $output .= '
              </ul>
            </div>
              <div class ="sortableNote">'.__T('Move and sort the buttons').'</div>
          </div>
          <div class="control-group" >
          <label class="control-label " for="shareButtonDim">'.__T('Select dimension').'</label>
            <div class="controls">
            <select name="shareButtonDim" id="shareButtonDim" class="span3">';

        foreach ($this->dimList as $key => $dim) {
            if ($key == trim($this->selectedDim)) {
                $output .= '<option selected="selected" value="'.$key.'">'.$dim.'</option>';
            }
            else {
                $output .= '<option value="'.$key.'">'.$dim.'</option>';
            }
        }

        $output .= '
            </select>
          </div>
          </div>';

        $this->addOutputCode($output);
    }

}
