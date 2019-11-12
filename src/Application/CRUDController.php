<?php

namespace Hyper\Application;


use Hyper\Database\DatabaseContext;

/**
 * Trait CRUD
 * @package hyper\Database
 * @uses \hyper\Application\ControllerFunctions
 */
class CRUDController
{

    use ControllerFunctions;

    #region CREATE: Create

    /**
     * @param null $model
     */
    public function create($model = null)
    {
        self::view("$this->model.create", $model);
    }

    /**
     *
     */
    public function postCreate()
    {
        $this->db->insert(Request::bind(new $this->model()));
        Request::redirectTo('index', "$this->name", null, "Successfully added new $this->name");
    }

    #endregion

    #region READ: Read/Details/View

    public function detail()
    {
        self::view("$this->name.detail", Request::fromParam());
    }

    #endregion

    #region UPDATE: Edit/Put/Patch

    /**
     * @param null $model
     */
    public function edit($model = null)
    {
        $model = isset($model) ? $model : Request::fromParam();
        self::view("$this->name.edit", $model);
    }

    /**
     *
     */
    public function postEdit()
    {
        if ($this->db->update(Request::bind(new $this->model())))
            Request::redirectTo("index", $this->name, null, "Successfully update $this->name");
        else
            Request::redirectTo("edit", "$this->name", Request::fromParam()->id, "Failed to update $this->name");
    }

    #endregion

    #region DELETE: Remove/Delete/Extinguish

    /**
     *
     */
    public function delete()
    {
        $model = Request::fromParam();
        self::view("$this->name.delete", $model);
    }

    /**
     *
     */
    public function postDelete()
    {
        if ($this->db->delete(Request::fromParam()) === 1)
            Request::redirectTo("index", "$this->name", null, "Successfully deleted new $this->name");
        else
            Request::redirectTo("delete", "$this->name", Request::fromParam()->id, "Failed to delete $this->name");
    }

    /**
     *
     */
    public function deleteAll()
    {
        $model = (new DatabaseContext($this->name))->select()->toList();
        self::view('shared.delete', $model);
    }

    /**
     *
     */
    public function postDeleteAll()
    {
        if ($this->db->deleteAll())
            Request::redirectTo("index", "$this->name", null, "Successfully deleted all $this->name");
        else
            Request::redirectTo("delete", "$this->name", Request::fromParam()->id, "Failed to delete $this->name");
    }

    #endregion
}
