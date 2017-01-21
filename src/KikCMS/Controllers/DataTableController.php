<?php

namespace KikCMS\Controllers;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\DbService;
use KikCMS\Classes\Exceptions\SessionExpiredException;
use KikCMS\Classes\Model\Model;
use KikCMS\Classes\WebForm\Fields\Autocomplete;

/**
 * @property DbService dbService
 */
class DataTableController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->view->disable();
    }

    /**
     * @return string
     */
    public function addAction()
    {
        $dataTable = $this->getDataTable();

        $this->view->form = $dataTable->renderAddForm($this->getParentEditId());

        return json_encode([
            'window' => $this->view->getRender('data-table', 'add')
        ]);
    }

    /**
     * @return string
     */
    public function deleteAction()
    {
        $dataTable = $this->getDataTable();
        $filters   = $this->getFilters();

        $ids = $this->request->getPost('ids');

        $dataTable->delete($ids);

        return json_encode([
            'table'      => $dataTable->renderTable($filters),
            'pagination' => $dataTable->renderPagination($filters[DataTable::FILTER_PAGE]),
        ]);
    }

    /**
     * @return string
     */
    public function editAction()
    {
        $editId    = $this->getEditId();
        $dataTable = $this->getDataTable();

        $this->view->form = $dataTable->renderEditForm($editId);

        return json_encode([
            'window' => $this->view->getRender('data-table', 'edit')
        ]);
    }

    /**
     * @return string
     */
    public function getAutocompleteDataAction()
    {
        //todo: move to webFormController
        $fieldKey  = $this->request->getPost('field');
        $dataTable = $this->getDataTable();

        // initialize, so we know about any autocomplete fields
        $dataTable->initializeDatatable();

        /** @var Autocomplete $field */
        $field = $dataTable->getForm()->getField($fieldKey);

        /** @var Model $model */
        $model = $field->getSourceModel();

        return json_encode($model::getNameList());
    }

    /**
     * @return string
     */
    public function saveAction()
    {
        $editId       = $this->getEditId();
        $dataTable    = $this->getDataTable();
        $parentEditId = $this->getParentEditId();

        if ($editId === null) {
            $this->view->form = $dataTable->renderAddForm($parentEditId);
            $view             = 'add';

            // if the form was succesfully saved, an edit id can be fetched
            $editId = $dataTable->getEditId();

            // if the datatable has a unsaved parent, cache the new id
            if ($dataTable->hasParent() && $parentEditId === 0 && $editId) {
                $dataTable->cacheNewId($editId);
            }
        } else {
            $this->view->form = $dataTable->renderEditForm($editId);
            $view             = 'edit';
        }

        return json_encode([
            'table'    => $dataTable->renderTable($this->getFilters()),
            'window'   => $this->view->getRender('data-table', $view),
            'editedId' => $editId,
        ]);
    }

    /**
     * @return string
     */
    public function pageAction()
    {
        $dataTable = $this->getDataTable();
        $filters   = $this->getFilters();

        return json_encode([
            'table'      => $dataTable->renderTable($filters),
            'pagination' => $dataTable->renderPagination($filters[DataTable::FILTER_PAGE]),
        ]);
    }

    /**
     * @return string
     */
    public function searchAction()
    {
        $dataTable = $this->getDataTable();
        $filters   = $this->getFilters();

        $filters[DataTable::FILTER_PAGE] = 1;

        return json_encode([
            'table'      => $dataTable->renderTable($filters),
            'pagination' => $dataTable->renderPagination(1),
        ]);
    }

    /**
     * @return string
     */
    public function sortAction()
    {
        $dataTable = $this->getDataTable();
        $filters   = $this->getFilters();

        $filters[DataTable::FILTER_PAGE] = 1;

        return json_encode([
            'table'      => $dataTable->renderTable($filters),
            'pagination' => $dataTable->renderPagination(1),
        ]);
    }

    /**
     * @return DataTable
     * @throws SessionExpiredException
     */
    private function getDataTable()
    {
        $instanceName = $this->request->getPost(DataTable::INSTANCE);

        if ( ! $this->session->has(DataTable::SESSION_KEY) ||
            ! array_key_exists($instanceName, $this->session->get(DataTable::SESSION_KEY))
        ) {
            throw new SessionExpiredException();
        }

        $instanceClass = $this->session->get(DataTable::SESSION_KEY)[$instanceName]['class'];

        /** @var DataTable $dataTable */
        $dataTable = new $instanceClass();
        $dataTable->setInstanceName($instanceName);

        return $dataTable;
    }

    /**
     * @return int|null
     */
    private function getEditId()
    {
        return $this->request->getPost(DataTable::EDIT_ID);
    }

    /**
     * @return int|null
     */
    private function getParentEditId()
    {
        $parentEditId = $this->request->getPost(DataTable::FILTER_PARENT_EDIT_ID);

        // cast to int
        if ($parentEditId !== null) {
            $parentEditId = (int) $parentEditId;
        }

        return $parentEditId;
    }

    /**
     * @return array
     */
    private function getFilters(): array
    {
        $filters = [];

        // get page filter
        $filters[DataTable::FILTER_PAGE] = $this->request->getPost(DataTable::FILTER_PAGE);

        // get search filter
        $search = $this->request->getPost(DataTable::FILTER_SEARCH);

        if ( ! empty($search)) {
            $filters[DataTable::FILTER_SEARCH] = $search;
        }

        // get sort filter
        if ($this->request->hasPost(DataTable::FILTER_SORT_COLUMN)) {
            $filters[DataTable::FILTER_SORT_COLUMN]    = $this->request->getPost(DataTable::FILTER_SORT_COLUMN);
            $filters[DataTable::FILTER_SORT_DIRECTION] = $this->request->getPost(DataTable::FILTER_SORT_DIRECTION);
        }

        // get parent edit id filter
        if ($this->request->hasPost(DataTable::FILTER_PARENT_EDIT_ID)) {
            $filters[DataTable::FILTER_PARENT_EDIT_ID] = $this->getParentEditId();
        }

        return $filters;
    }
}