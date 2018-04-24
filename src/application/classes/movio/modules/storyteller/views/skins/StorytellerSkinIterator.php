<?php
class movio_modules_storyteller_views_skins_StorytellerSkinIterator extends GlizyObject implements Iterator
{
    private $pos = 0;
    private $count = 0;
    private $content;
    private $pageId;
    private $pageUrl;
    private $captchaBuilder;
    private $sessionEx;

    function __construct( $content, $pageId, $pageUrl )
    {
        GlizyClassLoader::addLib('Gregwar\Captcha', __Paths::get('APPLICATION_LIBS').'captcha');

        $this->content = $content;
        $this->pageId = $pageId;
        $this->pageUrl = $pageUrl;
        $this->count = count($this->content);

        $this->sessionEx = new org_glizy_SessionEx($pageId);
        $this->captchaBuilder = new Gregwar\Captcha\CaptchaBuilder;
    }

    function current()
    {
        $value = $this->content[$this->pos];
        if (!property_exists($value, 'commentsEnabled')) {
            $value->commentsEnabled = false;
        }
        if (!property_exists($value, 'textAfter')) {
            $value->textAfter = '';
        }
        if (!property_exists($value, 'galleryType')) {
            $value->galleryType = 'slideshow';
        }
        if (!property_exists($value, 'galleryImageCrop')) {
            $value->galleryImageCrop = 0;
        }
        if (!property_exists($value, 'galleryImagePan')) {
            $value->galleryImagePan = 0;
        }

        $value->showArrow = $this->pos == 0 ? '' : '1';
        $value->externalLink = '';
        $this->prepareText($value);
        switch ($value->type) {
            case 'text_media':
                $this->prepareTextMedia($value);
                break;
            case 'video':
                $this->prepareVideo($value);
                break;
            case 'video_ext':
                $this->prepareVideoExt($value);
                break;
            case 'image':
                $this->prepareImage($value);
                break;
            case 'photogallery':
                $this->preparePhotogallery($value);
                break;
            case 'audio':
                $this->prepareAudio($value);
                break;
        }

        $this->addDocuments($value);
        $this->addComments($value);
        $this->addLinks($value);


        return $value;
    }

    function key()
    {
        return $this->pos;
    }

    function next()
    {
        $this->pos++;
    }

    function rewind()
    {
        $this->pos = 0;
    }

    function valid()
    {
        return $this->pos < $this->count;
    }

    function count()
    {
        return $this->count;
    }

    private function prepareText($value)
    {
        $value->text = org_glizy_helpers_Link::parseInternalLinks($value->text);
        $value->textAfter = org_glizy_helpers_Link::parseInternalLinks($value->textAfter);
    }

    private function prepareTextMedia($value)
    {
        $value->type = 'text';
        $this->prepareImage($value);
        $this->prepareAudio($value);
        $this->prepareVideo($value);
    }

    private function prepareVideo($value)
    {
        if ($value->video && is_string($value->video)) {
            $img = json_decode($value->video);
            $value->video = $img->id;
        }
    }

    private function prepareVideoExt($value)
    {
        preg_match_all('/http(s)?:\/\/(?:www.)?(vimeo|youtube|youtu)(\.\w{2,3})\/(?:watch\?v=)?(.*?)(?:\z|&)/', $value->url, $match);
        if (count($match[0])) {
            $value->externalLink = $value->url;
            if ($match[2][0] == 'vimeo') {
                $value->type = 'vimeo';
                $value->url = 'http://player.vimeo.com/video/'.$match[4][0].'?title=0&amp;byline=0&amp;portrait=0';
            } else {
               $value->type = 'youtube';
               $value->url = 'http://www.youtube.com/embed/'.$match[4][0].'?rel=0';
            }
        } else {
            $value->type = '';
        }
    }

    private function prepareImage($value)
    {
        if ($value->image && is_string($value->image)) {
            $img = json_decode($value->image);
            $value->image = $img->id;
        }
    }

    private function preparePhotogallery($value)
    {
        $filter = $value->gallery;
        if (is_array($filter) && count($filter)) {
            $value->gallery = org_glizy_ObjectFactory::createModelIterator('org.glizycms.models.Media')
                        ->load('all')
                        ->where('media_type', 'IMAGE')
                        ->orderBy('media_title');

            foreach($filter as $v) {
                $value->gallery->where('media_category', '%"'.$v.'"%', 'LIKE');
            }

            $value->galleryImagePosition = $value->galleryImageCrop == 1 && $value->galleryImagePan == 1 ? 'top right' : '50%';
            $value->galleryImageCrop = $value->galleryImageCrop == 1 ? 'true' : 'false';
            $value->galleryImagePan = $value->galleryImagePan == 1 ? 'true' : 'false';
        } else {
            $value->gallery = array();
        }
    }

    private function prepareAudio($value)
    {
        if ($value->audio && is_string($value->audio)) {
            $img = json_decode($value->audio);
            $value->audio = $img->id;
        }
    }

    private function addComments($value)
    {
        $it = org_glizy_ObjectFactory::createModelIterator('movio.modules.storyteller.models.Comment')
            ->load('getCommentFromStory', array('menuId' => $this->pageId, 'hash' => $value->hash));
        $value->comments = $it;
        $value->numComments = $it->count();

        // captcha
        $this->captchaBuilder->build();
        $this->sessionEx->set('captcha'.$value->hash, $this->captchaBuilder->getPhrase(), GLZ_SESSION_EX_VOLATILE);
        $value->captcha = '<img src="'.$this->captchaBuilder->inline().'" />';
        $value->formAuthor = $this->sessionEx->get($value->hash.'_author', '');
        $value->formEmail = $this->sessionEx->get($value->hash.'_email', '');
        $value->formText = $this->sessionEx->get($value->hash.'_text', '');
        $value->formError = $this->sessionEx->get($value->hash.'_error', '');
    }

    private function addLinks($value)
    {
        $value->urlPermalink = $this->pageUrl.'#'.$value->hash;
        $value->urlFacebook = 'http://www.facebook.com/sharer.php?u='.urlencode($value->urlPermalink);
        $value->urlTwitter = 'http://twitter.com/home?status=Sto%20leggendo+'.urlencode($value->urlPermalink);
    }

    private function addDocuments($value)
    {
        if (!empty($value->documents)) {
            $results = array();
            $application = org_glizy_ObjectValues::get('org.glizy', 'application');
            $speakingUrlManager = $application->retrieveProxy('org.glizycms.speakingUrl.Manager');

            foreach($value->documents as $v) {
                if (is_object($v)) {
                    $v = 'movioContent:'.$v->id;
                }

                $url = $speakingUrlManager->makeLink($v);
                if ($url) {
                    $results[] = $url;
                }
            }

            $value->documents = $results;
        }
    }
}
