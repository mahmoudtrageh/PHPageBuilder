<?php

namespace PHPageBuilder\Modules\WebsiteManager;

use PHPageBuilder\Contracts\PageContract;
use PHPageBuilder\Contracts\WebsiteManagerContract;
use PHPageBuilder\Repositories\PageRepository;
use PHPageBuilder\Repositories\SettingRepository;
use DB;
class WebsiteManager implements WebsiteManagerContract
{
    /**
     * Process the current GET or POST request and redirect or render the requested page.
     *
     * @param $route
     * @param $action
     */
    public function handleRequest($route, $action)
    {
        if (is_null($route)) {
            phpb_redirect(route('overview'));
            exit();
        }

        if ($route === 'page_settings') {
            
            if ($action === 'create') {
                $this->handleCreate();
                exit();
            }

            $pageId = $_GET['page'] ?? null;
            $pageRepository = new PageRepository;
            $page = $pageRepository->findWithId($pageId);
            if (! ($page instanceof PageContract)) {
                phpb_redirect(phpb_url('website_manager'));
            }

            if ($action === 'edit') {
                $this->handleEdit($page);
                exit();
            } else if ($action === 'destroy') {
                $this->handleDestroy($page);
            }
        }
    }

    /**
     * Handle requests for creating a new page.
     */
    public function handleCreate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pageRepository = new PageRepository;
           $last_id = DB::table('pagebuilder__pages')->orderBy('id', 'desc')->first()->id;
            $target_id = $last_id + 1;
            $page = $pageRepository->create($_POST);
            if ($page) {
                phpb_redirect(phpb_url('pagebuilder', ['page' => $target_id]) , [
                    'message-type' => 'success',
                    'message' => phpb_trans('website-manager.page-created')
                ]);
            }
        }

        phpb_redirect(route('overview'));
    }

    /**
     * Handle requests for editing the given page.
     *
     * @param PageContract $page
     */
    public function handleEdit(PageContract $page)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pageRepository = new PageRepository;
            $success = $pageRepository->update($page, $_POST);
            if ($success) {
                phpb_redirect(route('overview'), [
                    'message-type' => 'success',
                    'message' => phpb_trans('website-manager.page-updated')
                ]);
            }
        }

        phpb_redirect(route('overview'));
    }

    /**
     * Handle requests to destroy the given page.
     *
     * @param PageContract $page
     */
    public function handleDestroy(PageContract $page)
    {
        $pageRepository = new PageRepository;
        $pageRepository->destroy($page->getId());
        phpb_redirect(route('overview'), [
            'message-type' => 'success',
            'message' => phpb_trans('website-manager.page-deleted')
        ]);
    }

}
