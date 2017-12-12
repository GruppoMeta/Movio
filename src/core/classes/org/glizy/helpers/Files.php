<?php
/**
 *
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2006 Daniele Ugoletti <daniele@ugoletti.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 *
 * @copyright    Copyright (c) 2005, 2006 Daniele Ugoletti
 * @link         http://www.glizy.org Glizy Project
 * @license      http://www.gnu.org/copyleft/lesser.html GNU LESSER GENERAL PUBLIC LICENSE
 * @package      glizy
 * @subpackage   glizy.helpers
 * @author		 Alessandro Graziano <alessandro.graziano@gruppometa.it>
 * @category	 script
 * @since        Glizy v 0.01
 * @version      $Rev$
 * @modifiedby   $LastChangedBy$
 * @lastmodified $Date$
 */

class org_glizy_helpers_Files extends GlizyObject
{
    public static function deleteDirectory($dir, $expireTime=null, $onlyContent=false)
    {
      	if (!file_exists($dir)) return false;

        if (!is_dir($dir) || is_link($dir)) {
            if ($expireTime) {
                $fileCreationTime = filectime($dir);
                if ((time() - $fileCreationTime) < $expireTime) {
                    return true;
                }
            }
            return unlink($dir);
        }

        foreach (scandir($dir) as $item)
        {
            if ($item == '.' || $item == '..') continue;

            if (!org_glizy_helpers_Files::deleteDirectory($dir . "/" . $item, $expireTime)) {
                @chmod($dir . "/" . $item, 0777);
                if (!org_glizy_helpers_Files::deleteDirectory($dir . "/" . $item, $expireTime)) return false;
            }
        }

        return !$onlyContent ? @rmdir($dir) : true;
    }


    public static function copyDirectory($src, $dst)
    {
        if (is_dir($src)) {
            @mkdir($dst, fileperms($src), true);
            $files = scandir($src);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    self::copyDirectory($src.'/'.$file, $dst.'/'.$file);
                }
            }
        } else if (file_exists($src)) {
            copy($src, $dst);
        }
    }
    
    public static function rsearch($folder, $pattern)
    {
        $dir = new RecursiveDirectoryIterator($folder);
        $ite = new RecursiveIteratorIterator($dir);
        $files = new RegexIterator($ite, $pattern, RegexIterator::GET_MATCH);
        $fileList = array();
        foreach($files as $file) {
            $fileList = array_merge($fileList, $file);
        }
        return $fileList;
    }
}