<?php

class slWarehouseStoresRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'slWarehouseStores';
    public $classKey = 'slWarehouseStores';
    public $languageTopics = ['shoplogistic'];
    //public $permission = 'remove';


    /**
     * @return array|string
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        $ids = $this->modx->fromJSON($this->getProperty('ids'));
        if (empty($ids)) {
            return $this->failure($this->modx->lexicon('shoplogistic_warehousestore_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var shopLogisticItem $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('shoplogistic_warehousestore_err_nf'));
            }

            $object->remove();
        }

        return $this->success();
    }

}

return 'slWarehouseStoresRemoveProcessor';