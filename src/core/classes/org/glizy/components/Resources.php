<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Resources extends org_glizy_components_Component
{
    private $resources = array();
    private $resourcesSignature = array();
    private $cacheObj = array();


    /**
     * @param string $type
     * @param string $src
     * @param string $region
     * @param boolean $minify
     * @param string $media
     */
    public function addResource($type, $src, $region, $minify=false, $media=null)
    {
        $key = $this->keyFromTypeAndRegion($type, $region);

        if (!isset($this->resources[$key])) {
            $this->resources[$key] = array();
            $this->resourcesSignature[$key] = array();
        }
        $this->resources[$key][] =  array('src' => $src, 'minify' => $minify, 'media' => $media);
        $this->resourcesSignature[$key][] = $src.$minify;
    }

    /**
     * @param string $outputMode
     * @param bool|false $skipChilds
     * @throws Exception
     */
    public function render($outputMode=NULL, $skipChilds=false)
    {
        $language = $this->_application->getLanguage();
        foreach($this->resources as $k=>$v) {
            $typeAndRegion = $this->typeAndRegionFromKey($k);
            $fileName = $this->compiledResource($typeAndRegion['type'],
                                                $language,
                                                $v,
                                                implode('', $this->resourcesSignature[$k]));

            if ($typeAndRegion['type']=='js') {
                $this->addOutputCode(org_glizy_helpers_JS::linkJSfile($fileName), $typeAndRegion['region']);
            } else if ($typeAndRegion['type']=='css') {
                $this->addOutputCode(org_glizy_helpers_CSS::linkCSSfile($fileName), $typeAndRegion['region']);
            }
        }
    }

    /**
     * @param  string $type
     * @return org_glizy_cache_CacheFile
     */
    private function cacheObj($type)
    {
        if (!$this->cacheObj[$type]) {
            $options = array(
                'cacheDir' => org_glizy_Paths::get('CACHE_JS'),
                'lifeTime' => __Config::get('CACHE_CODE'),
                'readControlType' => '',
                'fileExtension' => '.'.$type
            );

            $this->cacheObj[$type] = org_glizy_ObjectFactory::createObject( 'org.glizy.cache.CacheFile', $options);
        }

         return $this->cacheObj[$type];
    }

    /**
     * @param  string $type
     * @param  string $language
     * @param  array $files
     * @param  string $cacheSignature
     * @return string
     */
    private function compiledResource($type, $language, $files, $cacheSignature)
    {
        $debugMode = __Config::get('DEBUG');
        $cacheObj = $this->cacheObj($type);
        $cacheSignature .= $language.__Config::get('APP_VERSION');

        $jsFileName = $cacheObj->verify( $cacheSignature );
        if ($jsFileName===false || $debugMode) {
            $fileSource = '';

            foreach($files as $item) {
                $file = $item['src'];
                $file = $this->resolveLanguage($file, $language);
                $file = $this->resolveConfig($file);
                $file = $this->resolvePaths($file);

                try {
                    if (is_dir($file)) {
                        $fileSource .= PHP_EOL.$this->readFolder($file, $type, $debugMode ? false : $item['minify'], $item['media']);
                    } else {
                        $fileSource .= PHP_EOL.$this->readFile($file, $type, $debugMode ? false : $item['minify'], $item['media']);
                    }
                } catch (Exception $e) {
                    throw org_glizy_exceptions_GlobalException::resourceNotFound($file);
                }
            }
            $cacheObj->save($fileSource, NULL, $cacheSignature);
            $jsFileName = $cacheObj->getFileName();
        }
        return $jsFileName;
    }

    /**
     * @param  string $dir
     * @param  string $type
     * @param  boolean $minify
     * @param  string $media
     * @return array
     */
    private function readFolder($dir, $type, $minify, $media)
    {
        $fileSource = '';
        foreach(glob($dir.'/*.'.$type) as $file) {
            $fileSource .= $this->readFile($file, $type, $minify, $media);
        }
        return $fileSource;
    }

    /**
     * @param  string $src
     * @param  string $type
     * @param  boolean $minify
     * @param  string $media
     * @return string
     */
    private function readFile($src, $type, $minify, $media)
    {
        $fileContent = file_get_contents($src);
        if (!$minify) {
            $fileContent = '// '.$src.';'.PHP_EOL.$fileContent;
        }

        if ($type==='css') {
            $pathinfo = pathinfo($src);
            $pathDir = preg_match('/http:|https:/', $pathinfo['dirname']) ? $pathinfo['dirname'].'/' : GLZ_HOST.'/'.$pathinfo['dirname'].'/';
            return $this->postProcessingCSS($fileContent, $minify, $media, $pathDir);
        } else {
            return $this->postProcessingJS($fileContent, $minify);
        }
    }

    /**
     * @param  string $content
     * @param  string $minify
     * @return string
     */
    private function postProcessingJS($content, $minify)
    {
        require_once (org_glizy_Paths::get('CORE_LIBS').'/jsmin/jsmin.php');
        $content = $this->replaceConfig($content);
        $content = $this->replaceLocale($content);
        return $minify ? JSMin::minify($content) : $content;
    }

    /**
     * @param  string $content
     * @param  string $minify
     * @param  string $media
     * @param  string $pathDir
     * @return string
     */
    private function postProcessingCSS($content, $minify, $media, $pathDir)
    {
        $content = $this->fixUrlInCss($content, $pathDir);

        if ($media) {
            return '@media '.$media.' {'.$content.'}';
        }
        return $content;
    }

    /**
     * @param  string $content
     * @param  string $pathDir
     * @return string
     */
    private function fixUrlInCss($content, $pathDir)
    {
        preg_match_all('/url\(\s*["\']?([^(\)"\')]*)["\']?\s*\)/', $content, $matches);
        if (count($matches)) {
            $num = count($matches[0]);
            for($i=0; $i<$num; $i++) {
                if (strpos($matches[1][$i], 'data:')===0) continue;
                $newUrl = $this->relateveToAbsoluteUrl($pathDir.trim($matches[1][$i]));
                $content = str_replace($matches[0][$i],
                                        'url("'.$newUrl.'")',
                                        $content);
            }
        }
        return $content;
    }

    /**
     * @param  string $src
     * @param  string $language
     * @return string
     */
    private function resolveLanguage($src, $language)
    {
        $language2 = $language.'-'.strtoupper($language);
        return str_replace(array('##LANG##', '##LANG2##'), array($language, $language2), $src);
    }

    /**
     * @param  string $src
     * @return string
     */
    private function resolveConfig($src)
    {
        return $this->resolveFromRegExp('/{config:(.*)}/Ui', __Config, $src);
    }

    /**
     * @param  string $src
     * @return string
     */
    private function resolvePaths($src)
    {
        return $this->resolveFromRegExp('/{path:(.*)}/Ui', __Paths, $src);
    }

    /**
     * @param  string $pattern
     * @param  function $function
     * @param  string $src
     * @return string
     */
    private function resolveFromRegExp($pattern, $function, $src)
    {
        preg_match_all($pattern, $src, $match);
        if (count($match) && count($match[0])) {
            for ($i=0; $i<count($match[0]); $i++) {
                $value = $function::get($match[1][$i]);
                $src = str_replace($match[0][$i], $value, $src);
            }
        }
        return $src;
    }

    /**
     * @param  string $type
     * @param  string $region
     * @return strin
     */
    private function keyFromTypeAndRegion($type, $region)
    {
        return $type.':'.$region;
    }

    /**
     * @param  string $key
     * @return array
     */
    private function typeAndRegionFromKey($key)
    {
        list($type, $region) = explode(':', $key);
        return  array('type' => $type, 'region' => $region);
    }

    /**
     * @param  string $url
     * @return string
     */
    private function relateveToAbsoluteUrl($url)
    {
        list($protocol, $urlPart) = explode('://', $url);
        $newUrlPart = array_reduce(explode('/', $urlPart), function($carry, $item) {
            if ($item=='..') {
                array_pop($carry);
                return $carry;
            } else if ($item=='') {
                return $carry;
            }
            $carry[] = $item;
            return $carry;
        }, array());

        return $protocol.'://'.implode($newUrlPart, '/');
    }

    /**
     * @param  string $content
     * @return string
     */
    private function replaceLocale($content)
    {
        preg_match_all('/(\{)((i18n:)([^(\'"\})]*))(\})/', $content, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches[0])) {
            for ($i=count($matches[0])-1; $i>=0;$i--) {
                $content = str_replace($matches[0][$i][0], __Tp($matches[4][$i][0]), $content);
            }
        }
        return $content;
    }

    /**
     * @param  string $content
     * @return string
     */
    private function replaceConfig($content)
    {
        preg_match_all('/(\{)((config:)([^(\}]*))(\})/', $content, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches[0])) {
            for ($i=count($matches[0])-1; $i>=0;$i--) {
                $content = str_replace($matches[0][$i][0], __Config::get($matches[4][$i][0]), $content);
            }
        }
        return $content;
    }

    /**
     * @param  Class $compiler
     * @param  DomNode &$node
     * @param  array &$registredNameSpaces
     * @param  integer &$counter
     * @param  string $parent
     * @param  string $idPrefix
     * @param  string $componentClassInfo
     * @param  string $componentId
     * @return boolean
     */
 	public static function compile($compiler, &$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix, $componentClassInfo, $componentId)
    {
        $compiler->compile_baseTag( $node, $registredNameSpaces, $counter, $parent, $idPrefix, $componentClassInfo, $componentId );
        $supportedAssets = array('js', 'css');

        foreach ($node->childNodes as $n ) {
            $type = str_replace('glz:', '', $n->nodeName);
            if ( in_array($type, $supportedAssets)) {
                $src = $n->getAttribute('src');
                $region = $n->getAttribute('editableRegion');
                $minify = $n->hasAttribute('minify') ? $n->getAttribute('minify') : 'false';
                $media = $n->hasAttribute('media') ? $n->getAttribute('media') : '';

                if ( $src && $region ) {
                    $compiler->_classSource .= sprintf('$n%s->addResource("%s", "%s", "%s", %s, "%s");', $counter, $type, $src, $region, $minify, $media);
                }
            }

        }
        return false;
    }
}
