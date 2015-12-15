<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */



use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

/**
 * Debug bar subclass which adds all included collectors
 */
class org_glizy_debug_dataCollector_GlizyCollector extends DataCollector implements Renderable
{
    private $calledControllers = array();

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        $application = org_glizy_ObjectValues::get('org.glizy', 'application');

        $result = array();

        $result['pageId'] = $application->getPageId();
        $result['routing'] = __Request::get('__routingName__').' > '.__Request::get('__routingPattern__');
        $result['controllers'] = implode(', ', $this->calledControllers);

        $user = $application->getCurrentUser();
        $userData = array(
                'id' => $user->id,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email,
                'groupId' => $user->groupId,
                'backEndAccess' => $user->backEndAccess,
            );
        $result['user'] = $this->getDataFormatter()->formatVar($userData);

        $menu = $application->getCurrentMenu();
        $menuData = array(
                'id' => $menu->id,
                'title' => $menu->title,
                'parentId' => $menu->parentId,
                'pageType' => $menu->pageType,
                'type' => $menu->type,
                'depth' => $menu->depth,
            );
        $result['menu'] = $this->getDataFormatter()->formatVar($menuData);


        // request
        $data = __Request::getAllAsArray();
        $tempData = array();
        foreach($data as $k=>$v) {
            if (strpos($k, '__')!==0) {
                $tempData[$k] = $v;
            }
        }
        $result['__Request'] = $this->getDataFormatter()->formatVar($tempData);
        $result['__Config'] = $this->getDataFormatter()->formatVar(__Config::getAllAsArray());
        $result['__Routing'] = $this->getDataFormatter()->formatVar(__Routing::getAllAsArray());
        $result['__Session'] = $this->getDataFormatter()->formatVar(__Session::getAllAsArray());


        return $result;
    }

    public function addCalledController($name)
    {
        $this->calledControllers[] = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'glizy';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        $name = $this->getName();
        return array(
            "$name" => array(
                "icon" => "archive",
                "widget" => "PhpDebugBar.Widgets.VariableListWidget",
                "map" => $name,
                "default" => "{}"
            )
        );
    }
}