<?php

namespace Website\Classes;


use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Config\MenuConfig;
use KikCMS\ObjectLists\MenuGroupMap;
use KikCMS\Objects\CmsMenuGroup;
use KikCMS\Objects\CmsMenuItem;
use Phalcon\Mvc\Router\Group;

/**
 * @inheritdoc
 */
class WebsiteSettings extends WebsiteSettingsBase
{
    /**
     * @inheritdoc
     */
    public function addFrontendRoutes(Group $frontend)
    {
    }

    /**
     * @inheritdoc
     */
    public function addBackendRoutes(Group $backend)
    {
        $backend->add('/test/datatable', 'TestModule::testDataTable');
        $backend->add('/test/personform', 'TestModule::personForm');
    }

    /**
     * @inheritdoc
     */
    public function getMenuGroupMap(MenuGroupMap $menuGroupMap): MenuGroupMap
    {
        $testMenuGroup = (new CmsMenuGroup('test', 'Test'))
            ->add(new CmsMenuItem('datatabletest', 'DataTable test', '/cms/test/datatable'))
            ->add(new CmsMenuItem('personform', 'Person form', '/cms/test/personform'));

        return $menuGroupMap->addAfter($testMenuGroup, 'test', MenuConfig::MENU_GROUP_CONTENT);
    }
}