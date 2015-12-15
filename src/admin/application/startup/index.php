<?php
org_glizycms_Glizycms::init();
$application = org_glizy_ObjectValues::get('org.glizy', 'application' );
if ($application && $application instanceof org_glizycms_core_application_AdminApplication) {
    $application->registerProxy('movio.modules.ontologybuilder.service.FieldTypeService');
    $application->registerProxy('movio.modules.ontologybuilder.service.EntityTypeService');
    $application->registerProxy('movio.modules.ontologybuilder.service.LocaleService');

    $application->registerProxy('movio.modules.publishApp.service.PublishAppService');
}
