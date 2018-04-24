<?php
class movio_modules_sharingButtons_views_components_Share extends org_glizy_components_Groupbox
{
    function init()
    {
        parent::init();
    }

    function process()
    {
        $shareButtons = movio_modules_sharingButtons_views_SharingButton::getSharingButtonList();

        if (!$shareButtons['enable'] || empty($shareButtons['buttonList'])){
            $this->_content ='';
        }
        else{
          $dim = $shareButtons['dim'];
          $buttonList = explode(',', $shareButtons['buttonList']);
          $shareMsg = __T('Share on')." ";
          $shareMailMsg = __T('Send link')."!";

          $buttonArray = array(
                              "Twitter"       => array( "id" => "twitter",
                                                        "link" => "https://twitter.com/share"),
                              "Facebook"      => array( "id" => "facebook",
                                                        "link" => "https://facebook.com/sharer/sharer.php?u=#url#"),
                              "Google-plus"   => array( "id" => "google-plus",
                                                        "link" => "https://plus.google.com/share?url=#url#"),
                              "Linkedin"      => array( "id" => "linkedin",
                                                        "link" => "http://www.linkedin.com/shareArticle?mini=true&url=#url#&title=#title#"),
                              "Pinterest"     => array( "id" => "pinterest",
                                                        "link" => "http://www.pinterest.com/pin/create/button/?url=#url#&media=#media#"),
                              "E-mail"        => array( "id" => "mail",
                                                        "link" => "mailto:?body=".__T('Visit this page').": #url#&subject=#title#"),
                              "Facebook-like" => array( "id" => "fbLike",
                                                        "link" => "http://www.facebook.com/plugins/like.php?href=#url#&width&layout=box_count&action=like&show_faces=false&share=false"),
                              "Google+1"      => array("id" => "google+1",
                                                        "link" => " https://apis.google.com/_/+1/fastbutton?usegapi=1&size=large&hl=en&url=#url#")
                              );

          $buttonHtml = array();
          foreach ($buttonList as $i => $button)
          {
              $button = ucfirst(strtolower(trim($button)));
              $dimension = is_array($dim) ? $dim[$i] : $dim;
              if(!$dimension){
                  $dimension = $dim[0];
              }
              $span="";
              $id = "btn_".$buttonArray[$button]['id'];
              $link = $buttonArray[$button]['link'];
              $href = $this->setUrl($link);
              if($button=="E-mail"){
                $a_class = "btn btn-adn btn-social-icon btn-".$dimension;
                $i_class = "fa fa-envelope-o";
                $title = $shareMailMsg;
                $target = "_blank";
              } else if($button=="Facebook-like") {
                  $i_class = "fb-like btn btn-facebook btn-".$dimension;
                  $a_class = "";
                  $title = "";
              } else{
                $i_class = "fa fa-".$buttonArray[$button]['id'];
                $a_class = "btn btn-".$buttonArray[$button]['id']." btn-social-icon btn-".$dimension;
                $title = $shareMsg.$button."!";
              }

              $buttonHtml[] = array(
                          "id" => $id,
                          "name" => $buttonArray[$button]['id'],
                          "a_class" => $a_class,
                          "i_class" => $i_class,
                          "href" => $href,
                          "title" => $title);
          }
          $this->_content = $buttonHtml;
        }

    }

    function render($outputMode = NULL, $skipChilds = false)
    {
        if (!$this->_application->isAdmin()) {
             $this->setAttribute('skin', 'Share.html');
        }
        parent::render($outputMode, $skipChilds);
    }

    private function setUrl($link)
    {
        $id = strtolower(trim($id));
        $currentUrl = $this->curPageURL();
        $pageTitle = $this->_application->getCurrentMenu()->title;
        $media = $this->getImage($currentUrl);
        return str_replace(array('#url#', '#title#', '#media#'), array($currentUrl, $pageTitle, $media), $link);
    }

    private function getImage($currentUrl){
      //TODO mappatura pagina immagine
      $imgToShare = "#";
      return $imgToShare;
    }

    private function curPageURL()
    {
      return urlencode(GLZ_HOST.'/'.__Request::get('__url__'));
    }

}

