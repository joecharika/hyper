<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Application\Controllers {


    use Exception;
    use Hyper\{Application\Annotations\action,
        Application\Http\HttpMessage,
        Application\Http\HttpMessageType,
        Application\Http\Request,
        Functions\Arr,
        Functions\Debug,
        Functions\Str,
        QueryBuilder\Query};

    /**
     * Class CRUDController
     * @package Hyper\Application
     * @extends \Hyper\Application\BaseController
     */
    class CRUDController extends BaseController
    {
        #region CONFIG
        public ?string
            /**
             * @var string
             */
            $controller;

        public array
            /**
             * Configure view using action key
             * All post* have no views use redirects instead
             * @var array
             */
            $views = [],
            /**
             * Configure post* after redirects
             * Redirects to previous url on failure
             * @var array
             */
            $redirects = [];

        #endregion

        /**
         * CRUDController constructor.
         */
        public function __construct()
        {
            parent::__construct();
            $this->controller = $this->controller ?? $this->name;
        }

        #region CREATE: Create/Add/Insert

        /**
         * Create view page
         * @get /{controller}/create
         * @action
         * @param Request $request Current request object. See @class Hyper\Http\Request Current request object. See @class Hyper\Http\Request
         * @param ?object $model
         * @param ?string|HttpMessage $message
         * @return string
         */
        public function create(Request $request, ?object $model = null, $message = null)
        {
            return $this->view(
                @$this->views['create'] ?? "{$this->controller}.create",
                $model,
                $message
            );
        }

        /**
         * Execute create action
         * @post /{controller}/create
         * @action
         * @param Request $request Current request object. See @class Hyper\Http\Request
         * @return string
         * @throws Exception
         */
        public function postCreate(Request $request)
        {
            $model = $request->bind(new $this->model(), $request);

            if ($this->db->add($model))
                return $request->redirect(
                    (@$this->redirects['create'] ?? "{$this->controller}/create"),
                    'Added successfully'
                );

            return $this->create($request, $model, new HttpMessage('Failed to save', HttpMessageType::WARNING));
        }

        #endregion

        #region READ: Read/Details/View

        /**
         * Display view page
         * ***********************************
         * @post /{controller}/read/{id}
         * @action
         * @param Request $request Current request object. See @class Hyper\Http\Request
         * @return string
         */
        public function read(Request $request)
        {
            return $this->view(
                @$this->views['read'] ?? "{$this->controller}.read",
                $request->fromParam()
            );
        }

        #endregion

        #region UPDATE: Edit/Put/Patch

        /**
         * Update view page
         * @get /{controller}/edit/{id}
         * @action
         * @param Request $request Current request object. See @class Hyper\Http\Request
         * @param $id
         * @param string $column
         * @return string
         */
        public function edit(Request $request, $id, $column = 'id')
        {
            return $this->view(
                @$this->views['edit'] ?? "{$this->controller}.edit",
                $this->db->first($column ?? 'id', $id)
            );
        }

        /**
         * Execute update action
         * @post /{controller}/edit/{id}
         * @action
         * @param Request $request Current request object. See @class Hyper\Http\Request
         * @return string
         */
        public function postEdit(Request $request)
        {
            $model = $request->bind(new $this->model(), $request);

            try {
                if ($this->db->update($model)) {
                    $params = Arr::spread((array)$request->params, false, '.');
                    return $request->redirect(
                        (@$this->redirects['edit'] ?? "{$this->controller}.edit.{$params}"),
                        'Successfully updated'
                    );
                }
            } catch (Exception $e) {
            }

            return $request->redirect(
                $request->previousUrl,
                new HttpMessage('Failed to update', HttpMessageType::DANGER)
            );
        }

        #endregion

        #region DELETE: Remove/Delete/Extinguish

        /**
         * Delete view page
         * @get /{controller}/delete/{id}
         * @action
         * @param Request $request Current request object. See @class Hyper\Http\Request
         * @return string
         */
        public function delete(Request $request)
        {
            return $this->view(
                @$this->views['delete'] ?? "{$this->controller}.delete",
                $request->fromParam()
            );
        }

        /**
         * Execute delete action
         * @post /{controller}/delete/{id}
         * @action
         * @param Request $request Current request object. See @class Hyper\Http\Request
         * @return string
         * @throws Exception
         */
        public function postDelete(Request $request)
        {
            $model = $request->fromParam();

            if ($this->db->delete($model)) {
                $params = Arr::spread((array)$request->params, false, '.');
                return $request->redirect(
                    (@$this->redirects['delete'] ?? "{$this->controller}.delete.{$params}"),
                    'Successfully deleted'
                );
            }

            $m = $request->currentRoute->isModular() ? '.' : '';

            return $request->redirect("{$request->currentRoute->module}{$m}{$this->controller}.delete", 'Failed to delete');
        }

        /**
         * Delete everything
         * @get /{controller}/delete-all
         * @action
         * @param Request $request Current request object. See @class Hyper\Http\Request
         * @return string
         */
        public function deleteAll(Request $request)
        {
            return $this->view(
                @$this->views['delete-all'] ?? "{$this->controller}.delete-all",
                $this->db->all()->toList(),
            );
        }

        /**
         * Delete everything
         * @post /{controller}/delete-all
         * @action
         * @param Request $request Current request object. See @class Hyper\Http\Request
         * @return string
         * @throws Exception
         */
        public function postDeleteAll(Request $request)
        {
            if ($this->db->delete(null, true, true))
                return $request->redirect(
                    @$this->views['delete-all'] ?? "{$this->controller}.",
                    'Successfully deleted all'
                );
            $m = $request->currentRoute->isModular() ? '.' : '';

            return $request->redirect("{$request->currentRoute->module}{$m}{$this->controller}.delete-all", 'Failed to delete');
        }

        #endregion

        #region PERK: Recycle/Restore

        /**
         * Recycle view page
         * @get /{controller}/recycle
         * @action
         * @param Request $request Current request object. See @class Hyper\Http\Request
         * @return string
         */
        public function recycle(Request $request)
        {
            $recycleBin = $this->db->recycleBin();

            if (empty($recycleBin))
                return $request->redirect(
                    $request->previousUrl,
                    new HttpMessage('Nothing to restore here', HttpMessageType::WARNING)
                );

            return $this->view(
                @$this->views['recycle-bin'] ?? "{$this->controller}.recycle-bin",
                $recycleBin
            );
        }

        /**
         * Restore object
         * @post /{controller}/recycle/{id}
         * @action
         * @param Request $request Current request object. See @class Hyper\Http\Request
         * @return string
         * @throws Exception
         */
        public function postRecycle(Request $request)
        {
            $entity = (new Query)
                ->selectFrom(Str::pluralize($request->currentRoute->controllerName))
                ->where('id', $request->data->id)
                ->exec($this->model)
                ->getResult();

            if ($this->db->recycle($entity))
                return $request->redirect(
                    @$this->views['recycle-bin'] ?? "{$this->controller}.recycle-bin",
                    'Restored item'
                );

            $m = $request->currentRoute->isModular() ? '.' : '';

            return $request->redirect("{$request->currentRoute->module}{$m}{$this->controller}.restore", 'Failed to restore');
        }
        #endregion
    }
}
