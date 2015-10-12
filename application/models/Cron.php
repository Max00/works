<?php

class Application_Model_Cron extends Zend_Db_Table_Abstract {
    protected $_name = 'cron';
    
    public function getLastAutoPriosUpdateDate() {
        $queryStr =<<< EOT
SELECT option_value
FROM cron
WHERE option_name = "last_auto_prios_update";
EOT;
        $result = $this->_db->fetchRow($queryStr);
        return reset($result);
    }

    private function setLastAutoPriosUpdateToday() {
        $today = date('Y-m-d');
        $this->update(
            array(
                'option_value' => date('Y-m-d'),
            ),
            'option_name = "last_auto_prios_update"');
    }

    public function setAutoWorksPrios() {
        $travauxTable = new Application_Model_Travaux();
        $travauxTable->setAutoWorksPrios();

        $this->setLastAutoPriosUpdateToday();
    }
}