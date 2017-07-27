<?php
class org_glizycms_mediaArchive_services_ExifService extends GlizyObject
{
    public function saveExifData($mediaId, $exif)
    {
        if (strpos($exif['SectionsFound'], 'EXIF') !== false) {
            $ar = org_glizy_ObjectFactory::createModel('org.glizycms.mediaArchive.models.Exif');
            $ar->exif_FK_media_id = $mediaId;
            $ar->exif_imageWidth = $exif['COMPUTED']['Width'];
    		$ar->exif_imageHeight = $exif['COMPUTED']['Height'];
    	    $ar->exif_resolution = $this->getResolution($exif['XResolution'], $exif['YResolution'], $exif['ResolutionUnit']);
        	$ar->exif_make = $exif['Make'];
    		$ar->exif_model = $exif['Model'];
    	    $ar->exif_exposureTime = $this->simplify($exif['ExposureTime']);
    		$ar->exif_fNumber = $exif['FNumber'];
        	$ar->exif_exposureProgram = $exif['ExposureProgram'];
    		if (!is_array($exif['ISOSpeedRatings'])) {
    		$ar->exif_ISOSpeedRatings = $exif['ISOSpeedRatings'];
    		}
        	$ar->exif_dateTimeOriginal = $exif['DateTimeOriginal'];
        	$ar->exif_dateTimeDigitized = $exif['DateTimeDigitized'];
            $ar->exif_GPSCoords = $this->getGPSCoords($exif['GPSLatitudeRef'], $exif['GPSLatitude'], $exif['GPSLongitudeRef'], $exif['GPSLongitude']);
            $ar->exif_GPSTimeStamp = $this->getGPSTime($exif['GPSTimeStamp'], ':');
            $ar->exif_data = utf8_encode(serialize($exif));
            $ar->save();
        }
    }
    
    public function delete($mediaId)
    {
        $ar = org_glizy_ObjectFactory::createModel('org.glizycms.mediaArchive.models.Exif');
        $ar->delete(array('exif_FK_media_id' => $mediaId));
    }
    
    protected function getResolution($xResolution, $yResolution, $resolutionUnit)
    {
        if (!$xResolution) {
            return '';
        }
        
        $xResolution = $this->eval_rational($xResolution);
        $yResolution = $this->eval_rational($yResolution);
        
        if ($xResolution < 0 || $yResolution < 0) {
            return '';
        }

        if ($resolutionUnit == 3) {
            return $xResolution/2.54 . 'x' . $yResolution/2.54;
        } else {
            return $xResolution . 'x' . $yResolution;
        }
    }
    
    protected function getGPSCoords($latRef, $lat, $longRef, $long, $glue=', ')
    {
        if (is_null($latRef)) {
            return null;
        }
        
        $latValue = $this->eval_rational($lat[0]) + $this->eval_rational($lat[1])/60 + $this->eval_rational($lat[2])/3600;
        $longValue = $this->eval_rational($long[0]) + $this->eval_rational($long[1])/60 + $this->eval_rational($long[2])/3600;
        
        if ($latRef == 'S') {
            $latValue = -$latValue;
        }
        
        if ($longRef == 'W') {
            $longValue = -$longValue;
        }
        
        return $latValue.$glue.$longValue;
    }
    
    protected function getGPSTime($gpsValArray, $glue=', ')
    {
        if (is_null($gpsValArray)) {
            return null;
        }
        
        $v = array (
            $this->eval_rational($gpsValArray[0]),
            $this->eval_rational($gpsValArray[1]),
            $this->eval_rational($gpsValArray[2])
        );
        
        return implode($glue, $v);
    }
    
    protected function gcd($a,$b)
    {
        $a = abs($a); $b = abs($b);
        if( $a < $b) list($b,$a) = array($a,$b);
        if( $b == 0) return $a;
        $r = $a % $b;
        while($r > 0) {
            $a = $b;
            $b = $r;
            $r = $a % $b;
        }
        return $b;
    }
    
    protected function simplify($e)
    {
        list($n, $d) = explode('/', $e);
        
        if ($d == 0) {
            return '';
        }
        
        $g = $this->gcd($n, $d);
        
        return $n/$g . '/'. $d/$g;
    }
    
    protected function eval_rational($e)
    {
        list($n, $d) = explode('/', $e);
        
        if ($d == 0) {
            return '';
        }
        
        return $n / $d;
    }
}