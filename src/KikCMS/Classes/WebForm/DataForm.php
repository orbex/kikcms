<?php

namespace KikCMS\Classes\WebForm;


use KikCMS\Classes\Datatable\DataTable;
use KikCMS\Classes\DbWrapper;
use Phalcon\Http\Response;

/**
 * @property DbWrapper $dbWrapper
 */
class DataForm extends WebForm
{
    /** @var string */
    protected $table;

    /** @var string */
    protected $tableKey = 'id';

    /**
     * @param string $table
     */
    public function __construct(string $table)
    {
        parent::__construct();

        $this->table = $table;
    }

    /**
     * @param array $input
     * @return ErrorContainer
     */
    public function validate(array $input): ErrorContainer
    {
        return new ErrorContainer();
    }

    /**
     * @param array $input
     * @return void
     */
    public function successAction(array $input)
    {
        $saveDataSuccess = $this->saveData($input);

        if($saveDataSuccess){
            $this->flash->success($this->translator->tl('dataForm.saveSuccess'));
        } else {
            $this->flash->success($this->translator->tl('dataForm.saveFailure'));
        }
    }

    /**
     * @param array $editData
     * @return Response|string
     */
    public function renderWithData(array $editData)
    {
        foreach ($this->fields as $key => &$element) {
            if (array_key_exists($key, $editData)) {
                $element->setDefault($editData[$key]);
            }
        }

        return $this->render();
    }

    /**
     * @param array $input
     */
    private function saveData(array $input)
    {
        $insertUpdateData = [];

        foreach ($this->fields as $key => $field) {
            if (in_array($key, $this->getSystemFields())) {
                continue;
            }

            if (isset($input[$key])) {
                $insertUpdateData[$key] = $input[$key];
            }
        }

        if (isset($input[DataTable::EDIT_ID])) {
            $editId = $input[DataTable::EDIT_ID];
            return $this->dbWrapper->update($this->table, $insertUpdateData, [$this->tableKey => $editId]);
        }
    }

    /**
     * Get an array of formFields that are used by the system and don't contain user input
     *
     * @return array
     */
    private function getSystemFields()
    {
        return [WebForm::WEB_FORM_ID, DataTable::EDIT_ID, DataTable::INSTANCE];
    }
}