<?php
class org_glizycms_speakingUrl_models_proxy_SpeakingUrlProxy extends GlizyObject
{
    public function validate($value, $languageId, $id, $type)
    {
        $ar = org_glizy_ObjectFactory::createModel('org.glizycms.speakingUrl.models.SpeakingUrl');
        $ar->speakingurl_FK_language_id = $languageId;
        $ar->speakingurl_value = $value;
        // TODO: perché non cercare su tutti i campi?
        if ($ar->find()) {
            return $ar->speakingurl_FK == $id && $ar->speakingurl_type == $type;
        }

        return true;
    }


    public function deleteUrl($languageId, $id, $type)
    {
        $ar = org_glizy_ObjectFactory::createModel('org.glizycms.speakingUrl.models.SpeakingUrl');
        $ar->speakingurl_FK_language_id = $languageId;
        $ar->speakingurl_FK = $id;
        $ar->speakingurl_type = $type;
        if ($ar->find()) {
            $ar->delete();
        }
    }

    public function addUrl($value, $languageId, $id, $type, $options=array())
    {
        $ar = org_glizy_ObjectFactory::createModel('org.glizycms.speakingUrl.models.SpeakingUrl');
        $ar->speakingurl_FK_language_id = $languageId;
        $ar->speakingurl_FK = $id;
        $ar->speakingurl_type = $type;
        if (!$ar->find()) {
            $ar->speakingurl_FK_language_id = $languageId;
            $ar->speakingurl_FK = $id;
            $ar->speakingurl_type = $type;
        }

        $ar->speakingurl_value = $value;
        $ar->speakingurl_option = serialize($options);
        $ar->save();
    }

    public function getUrlForId($id, $languageId)
    {
        $ar = org_glizy_ObjectFactory::createModel('org.glizycms.speakingUrl.models.SpeakingUrl');
        $ar->speakingurl_FK_language_id = $languageId;
        $ar->speakingurl_FK = $id;
        $r = $ar->find(array('speakingurl_FK_language_id' => $languageId, 'speakingurl_FK' => $id));
        return $r ? $ar : false;
    }

    public function getUrlByValueAndType($value, $type)
    {
        $ar = org_glizy_ObjectFactory::createModel('org.glizycms.speakingUrl.models.SpeakingUrl');
        $r = $ar->find(array('speakingurl_value' => $value, 'speakingurl_type' => $type));
        return $r ? $ar : false;
    }

    public function deleteAllByType($type)
    {
        $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.speakingUrl.models.SpeakingUrl');
        $it->load('deleteAllByType', array(':type' => $type));
        $it->exec();
    }

    public function getModel()
    {
        return org_glizy_ObjectFactory::createModel('org.glizycms.speakingUrl.models.SpeakingUrl');
    }
}
