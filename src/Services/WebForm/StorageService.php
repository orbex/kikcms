<?php

namespace KikCMS\Services\WebForm;

use Exception;
use InvalidArgumentException;
use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\WebForm\DataForm\StorageData;
use KikCMS\Services\ModelService;
use KikCmsCore\Services\DbService;
use KikCMS\Classes\WebForm\DataForm\Events\StoreEvent;
use KikCMS\Services\TranslationService;
use Monolog\Logger;
use Phalcon\Di\Injectable;

/**
 * Service for handling a DataForms' Storage, using the StorageData object
 *
 * @property DbService $dbService
 * @property TranslationService $translationService
 * @property RelationKeyService $relationKeyService
 * @property ModelService $modelService
 */
class StorageService extends Injectable
{
    /** @var StorageData */
    private $storageData;

    /**
     * @return StorageData
     */
    public function getStorageData(): StorageData
    {
        return $this->storageData;
    }

    /**
     * @param StorageData $storageData
     * @return StorageService|$this
     */
    public function setStorageData(StorageData $storageData): StorageService
    {
        $this->storageData = $storageData;
        return $this;
    }

    /**
     * Store a DataForm's input data wrapped inside a StorageData object
     *
     * @return bool
     */
    public function store(): bool
    {
        if ( ! $this->storageData) {
            throw new InvalidArgumentException("property storageData must be set");
        }

        $this->db->begin();

        try {
            $this->executeBeforeStoreEvents();
            $this->storeMain();
            $this->executeAfterStoreEvents();
        } catch (Exception $exception) {
            $this->logger->log(Logger::ERROR, $exception);
            $this->db->rollback();
            return false;
        }

        if ($success = $this->db->commit()) {
            foreach ($this->storageData->getDataTableFieldMap() as $field) {
                $field->getDataTable()->removeNewIdCache();
            }
        }

        return $success;
    }

    /**
     * If a temporary key is inserted, fk checks needs to be disabled for insert
     */
    private function disableForeignKeysForTempKeys()
    {
        if ($this->storageData->hasTempParentEditId()) {
            $this->dbService->setForeignKeyChecks(false);
        }
    }

    /**
     * If a temporary key is inserted, fk checks needs to be re-enabled after insert
     */
    private function enableForeignKeysForTempKeys()
    {
        if ($this->storageData->hasTempParentEditId()) {
            $this->dbService->setForeignKeyChecks(true);
        }
    }

    /**
     * @param string $eventType
     */
    private function executeEvents(string $eventType)
    {
        if ( ! array_key_exists($eventType, $this->storageData->getEvents())) {
            return;
        }

        $events = $this->storageData->getEvents()[$eventType];

        foreach ($events as $event) {
            $event($this->storageData);
        }
    }

    /**
     * Execute after the main insert/update is completed
     */
    private function executeAfterMainEvents()
    {
        $this->executeEvents(StoreEvent::BEFORE_MAIN_STORE);
    }

    /**
     * Execute after the main insert/update is completed
     */
    private function executeAfterStoreEvents()
    {
        $this->executeEvents(StoreEvent::AFTER_STORE);
    }

    /**
     * Execute after the main insert/update is completed
     */
    private function executeBeforeStoreEvents()
    {
        $this->executeEvents(StoreEvent::BEFORE_STORE);
    }

    /**
     * Execute after the main insert/update is completed
     */
    private function executeBeforeMainEvents()
    {
        $this->executeEvents(StoreEvent::BEFORE_MAIN_STORE);
    }

    /**
     * Store the main forms' table row
     */
    private function storeMain()
    {
        $mainInput = $this->storageData->getMainInput();
        $langCode  = $this->storageData->getLanguageCode();
        $object    = $this->storageData->getObject();

        // set objects' properties
        foreach ($mainInput as $key => $value) {
            if ($this->relationKeyService->isRelationKey($key)) {
                $this->relationKeyService->set($object, $key, $value, $langCode);
            } else {
                $object->$key = $this->dbService->toStorage($value);
            }
        }

        // set subdatatables
        $this->setSubDataTableData();
        $this->executeBeforeMainEvents();

        if (property_exists($object, DataTable::TABLE_KEY)) {
            $object->save();
        } else {
            $this->disableForeignKeysForTempKeys();
            $object->save();
            $this->enableForeignKeysForTempKeys();
        }

        $this->storageData->setEditId((int) $object->id);

        $this->executeAfterMainEvents();
    }

    /**
     * Store data after the main forms' table row is inserted/updated
     */
    private function setSubDataTableData()
    {
        foreach ($this->storageData->getDataTableFieldMap() as $key => $field) {
            $keysToUpdate = $field->getDataTable()->getCachedNewIds();
            $relatedModel = $field->getDataTable()->getModel();

            $this->storageData->getObject()->$key = $this->modelService->getObjects($relatedModel, $keysToUpdate);
        }
    }
}