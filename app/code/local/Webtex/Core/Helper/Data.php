<?php
class Webtex_Core_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_validWebtexPrefix = 'Webtex_';
    protected $_validMagextPrefix = 'MagExt_';
    
    public function getModuleList()
    {
        $modules = (array)Mage::getConfig()->getNode('modules')->children();
        foreach ($modules as $moduleId=>$moduleInfo) {
        	if (!$this->isValidModule($moduleId))
                unset($modules[$moduleId]);
            else
                $modules[$moduleId]->id = $moduleId;
        }
        return $modules;
    }
    
    public function isValidModule($moduleId)
    {
        if (0 === strpos($moduleId,$this->_validWebtexPrefix) || 0 === strpos($moduleId,$this->_validMagextPrefix))
        {
            return true;
        }
        return false;
    }
}


