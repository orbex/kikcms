<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\Translator;
use KikCMS\Forms\LanguageForm;
use KikCMS\Models\Language;

/**
 * @property Translator $translator
 */
class Languages extends DataTable
{
    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return LanguageForm::class;
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): string
    {
        return 'dataTables.language';
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Language::class;
    }

    /**
     * @inheritdoc
     */
    protected function getTableFieldMap(): array
    {
        return [
            'id'   => $this->translator->tl('id'),
            'code' => $this->translator->tl('code'),
            'name' => $this->translator->tl('name'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        // nothing here...
    }
}