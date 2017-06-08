<?php

namespace KikCMS\Services\Pages;

use KikCMS\Classes\Frontend\FullPage;
use KikCMS\Models\Page;
use KikCMS\ObjectLists\FullPageMap;
use KikCMS\ObjectLists\PageMap;
use KikCMS\Services\CacheService;
use Phalcon\Di\Injectable;

/**
 * @property PageService $pageService
 * @property PageLanguageService $pageLanguageService
 * @property UrlService $urlService
 * @property CacheService $cacheService
 */
class FullPageService extends Injectable
{
    /**
     * @param int $menuId
     * @param string $langCode
     * @param int|null $maxLevel
     * @return FullPageMap
     */
    public function getByMenuId(int $menuId, string $langCode, int $maxLevel = null): FullPageMap
    {
        $fullPageMap = new FullPageMap();

        if ( ! $menu = Page::getById($menuId)) {
            return $fullPageMap;
        }

        $pageMap = $this->pageService->getChildren($menu, $maxLevel);

        return $this->getByPageMap($pageMap, $langCode);
    }

    /**
     * @param PageMap $pageMap
     * @param string $langCode
     * @return FullPageMap
     */
    public function getByPageMap(PageMap $pageMap, string $langCode): FullPageMap
    {
        $fullPageMap = new FullPageMap();

        $pageLangMap    = $this->pageLanguageService->getByPageMap($pageMap, $langCode);
        $pageFieldTable = $this->pageLanguageService->getPageFieldTable($pageMap, $langCode);

        foreach ($pageMap as $pageId => $page) {
            if ( ! $pageLangMap->has($pageId)) {
                continue;
            }

            if (array_key_exists($pageId, $pageFieldTable)) {
                $content = $pageFieldTable[$pageId];
            } else {
                $content = [];
            }

            $pageLang = $pageLangMap->get($pageId);

            $url = $this->urlService->getUrlByPageLanguage($pageLang);

            $fullPageMap->add(new FullPage($page, $pageLang, $content, $url));
        }

        return $fullPageMap;
    }
}