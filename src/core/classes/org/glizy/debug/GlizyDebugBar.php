<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


use DebugBar\DebugBar;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\ConfigCollector;
use DebugBar\Bridge\DoctrineCollector;

/**
 * Debug bar subclass which adds all included collectors
 */
class org_glizy_debug_GlizyDebugBar extends GlizyObject
{
    private $debugBar;
    private $startTime;
    private $glizyCollector;
    // private $calledControllerName = array();

    public function __construct()
    {
        $this->startTime = microtime(true);
        spl_autoload_register(array($this, 'loadClass'));

        $this->initDebugBar();
        $this->addEventListener(GLZ_LOG_EVENT, $this);
        $this->addEventListener(GLZ_EVT_START_PROCESS, $this);
        $this->addEventListener(GLZ_EVT_END_PROCESS, $this);
        $this->addEventListener(GLZ_EVT_START_RENDER, $this);
        $this->addEventListener(GLZ_EVT_END_RENDER, $this);
        $this->addEventListener(GLZ_EVT_CALL_CONTROLLER, $this);
    }

    public function loadClass($className)
    {
        if (strpos($className, 'DebugBar') === 0 || strpos($className, 'Psr') === 0) {
            glz_importLib(str_replace('\\', '/', $className).'.php');
        }
    }

    public function getJavascriptRenderer($baseUrl = null, $basePath = null)
    {
        return $this->debugBar->getJavascriptRenderer($baseUrl, $basePath);
    }



    public function logByEvent($evt)
    {
        $group = $evt->data['group'] == 'debugBar' ? 'messages' : 'logs';

        switch ($evt->data['level']) {
            case GLZ_LOG_DEBUG:
                $this->debugBar[$group]->debug($evt->data['message']);
                break;
            case GLZ_LOG_INFO:
            case GLZ_LOG_SYSTEM:
                $this->debugBar[$group]->info($evt->data['message']);
                break;
            case GLZ_LOG_WARNING:
                $this->debugBar[$group]->warning($evt->data['message']);
                break;
            case GLZ_LOG_ERROR:
                $this->debugBar[$group]->critical($evt->data['message']);
                break;
            case GLZ_LOG_FATAL:
                $this->debugBar[$group]->critical($evt->data['message']);
                break;
        }
    }

    public function addMesage($message, $level)
    {
        $this->debugBar['messages']->addMessage($message, $level);
    }

    public function getTime()
    {
        return $this->debugBar['time'];
    }

    /**
     * Listeners
     */

    public function onProcessStart($evt)
    {
        $application = org_glizy_ObjectValues::get('org.glizy', 'application');
        $rootComponent = $application->getRootComponent();
        $c = org_glizy_ObjectFactory::createComponent('org.glizy.debug.views.components.DebugBar', $application, $rootComponent, 'debugBar', 'debugBar');
        $c->init();
        $rootComponent->addChild($c);

        $this->debugBar['time']->addMeasure('Booting', $this->startTime, microtime(true));
        $this->debugBar['time']->startMeasure('application.process', 'Process');
    }

    public function onProcessEnd($evt)
    {
        $this->debugBar['time']->stopMeasure('application.process');
    }

    public function onRenderStart($evt)
    {
        $this->debugBar['time']->startMeasure('application.render', 'Render');
    }

    public function onRenderEnd($evt)
    {
        // $this->debugBar['time']->stopMeasure('application.render');
    }

    public function onCallController($evt)
    {
        $this->glizyCollector->addCalledController($evt->data);
    }

    private function initDebugBar()
    {
        $this->glizyCollector = org_glizy_ObjectFactory::createObject('org.glizy.debug.dataCollector.GlizyCollector');
        $this->debugBar = new DebugBar;
        $this->debugBar->addCollector(new PhpInfoCollector());
        $this->debugBar->addCollector(new MessagesCollector());
        $this->debugBar->addCollector(new MessagesCollector('logs'));
        $this->debugBar->addCollector(new TimeDataCollector());
        $this->debugBar->addCollector(new MemoryCollector());
        $this->debugBar->addCollector($this->glizyCollector);

        $conn = org_glizy_dataAccessDoctrine_DataAccess::getConnection();
        $config = $conn->getConfiguration();
        $debugStack = new Doctrine\DBAL\Logging\DebugStack();
        $config->setSQLLogger($debugStack);
        $this->debugBar->addCollector(new DoctrineCollector($debugStack));
    }
}


