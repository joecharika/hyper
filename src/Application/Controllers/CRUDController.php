<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Controllers;


use Hyper\Annotations\action;
use Hyper\Functions\Arr;
use Hyper\Functions\Str;
use Hyper\Http\{HttpMessage, HttpMessageType, Request};
use Hyper\QueryBuilder\Query;

/**
 * Class CRUDController
 * @package Hyper\Application
 * @extends \Hyper\Application\BaseController
 */
class CRUDController extends BaseController
{
    #region CONFIG
    public
        /**
         * @var string
         */
        $controller,
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
     * @param object $model
     * @param string|HttpMessage $message
     * @return string
     */
    public function create(Request $request, $model = null, $message = null)
    {
        return $this->view(
            @$this->views['create'] ?? "/{$this->controller}/create",
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
     */
    public function postCreate(Request $request)
    {
        $model = $request->bind(new $this->model());

        if ($this->db->add($model))
            return $request->redirect(
                (@$this->redirects['create'] ?? "/{$this->controller}/create"),
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
            @$this->views['create'] ?? "/{$this->controller}/create",
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
     * @param object $model
     * @param null $message
     * @return string
     */
    public function edit(Request $request, object $model = null, $message = null)
    {
        return $this->view(
            @$this->views['edit'] ?? "/{$this->controller}/edit",
            $model ?? $request->fromParam(),
            $message
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
        $model = $request->bind(new $this->model());

        if ($this->db->update($model)) {
            $params = Arr::spread((array)$request->params, false, '.');
            return $request->redirect(
                (@$this->redirects['edit'] ?? "/{$this->controller}/edit/{$params}"),
                'Successfully updated'
            );
        }

        return $this->edit($request, $model, 'Failed to update');
    }

    #endregion

    #region DELETE: Remove/Delete/Extinguish

    /**
     * Delete view page
     * @get /{controller}/delete/{id}
     * @action
     * @param Request $request Current request object. See @class Hyper\Http\Request
     * @param object|null $model
     * @param null $message
     * @return string
     */
    public function delete(Request $request, object $model = null, $message = null)
    {
        return $this->view(
            @$this->views['edit'] ?? "/{$this->controller}/edit",
            $model ?? $request->fromParam()
        );
    }

    /**
     * Execute delete action
     * @post /{controller}/delete/{id}
     * @action
     * @param Request $request Current request object. See @class Hyper\Http\Request
     * @return string
     */
    public function postDelete(Request $request)
    {
        $model = $request->fromParam();
        if ($this->db->delete($model)) {
            $params = Arr::spread((array)$request->params, false, '.');
            return $request->redirect(
                (@$this->redirects['delete'] ?? "/{$this->controller}/delete.{$params}"),
                'Successfully deleted'
            );
        }

        return $this->delete($request, $model, 'Failed to delete');
    }

    /**
     * Delete everything
     * @get /{controller}/delete-all
     * @action
     * @param Request $request Current request object. See @class Hyper\Http\Request
     * @param null $model
     * @param null $message
     * @return string
     */
    public function deleteAll(Request $request, $model = null, $message = null)
    {
        return $this->view(
            @$this->views['delete-all'] ?? "/{$this->controller}/delete-all",
            $model ?? $this->db->all()->toList(),
            $message
        );
    }

    /**
     * Delete everything
     * @post /{controller}/delete-all
     * @action
     * @param Request $request Current request object. See @class Hyper\Http\Request
     * @return string
     */
    public function postDeleteAll(Request $request)
    {
        if ($this->db->delete(null, true, true))
            return $request->redirect(
                @$this->views['delete-all'] ?? "/{$this->controller}/",
                'Successfully deleted all'
            );

        return $this->deleteAll($request, null, 'Failed to delete');
    }

    #endregion

    #region PERK: Recycle/Restore

    /**
     * Recycle view page
     * @get /{controller}/recycle
     * @action
     * @param Request $request Current request object. See @class Hyper\Http\Request
     * @param string|HttpMessage $message
     * @return string
     */
    public function recycle(Request $request, $message = null)
    {
        $recycleBin = $this->db->recycleBin();

        if (empty($recycleBin))
            return $request->redirect(
                $request->previousUrl,
                new HttpMessage('Nothing to restore here', HttpMessageType::WARNING)
            );

        return $this->view(
            @$this->views['recycle-bin'] ?? "/{$this->controller}/recycle-bin",
            $recycleBin
        );
    }

    /**
     * Restore object
     * @post /{controller}/recycle/{id}
     * @action
     * @param Request $request Current request object. See @class Hyper\Http\Request
     * @return string
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
                @$this->views['recycle-bin'] ?? "/{$this->controller}/recycle-bin",
                'Restored item'
            );

        return $this->recycle($request, 'Failed to restore');
    }
    #endregion
}
