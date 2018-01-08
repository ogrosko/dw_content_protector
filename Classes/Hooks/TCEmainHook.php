<?php
namespace Digitalwerk\DwContentProtector\Hooks;

/**
 * Class TCEmainHook
 * @package Digitalwerk\DwContentProtector\Hooks
 */
class TCEmainHook
{

    /**
     * Checks if user may update a record with uid=$id from $table
     *
     * @param string $table Record table
     * @param int $id Record UID
     * @param array $data Record data
     * @param int $otherHookGrantedAccess
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     * @return bool Returns TRUE if the user may update the record given by $table and $id
     */
    public function checkRecordUpdateAccess($table, $id, $data, $otherHookGrantedAccess, \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler)
    {
        $accessAllowed = $otherHookGrantedAccess;

        if ($table === \Digitalwerk\DwContentProtector\Utility\ContentProtector::CONTENT_TABLE_NAME && $accessAllowed !== 0) {
            $contentProtector = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \Digitalwerk\DwContentProtector\Utility\ContentProtector::class,
                $dataHandler
            );
            $accessAllowed = $contentProtector->getRecordUpdateAccess($table, $id, $data, $accessAllowed);
        }

        return $accessAllowed;
    }

    /**
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     */
    public function processDatamap_beforeStart(\TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler)
    {
        if (!empty($dataHandler->datamap[\Digitalwerk\DwContentProtector\Utility\ContentProtector::CONTENT_TABLE_NAME])) {
            $contentProtector = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \Digitalwerk\DwContentProtector\Utility\ContentProtector::class,
                $dataHandler
            );
            $contentProtector->checkDataContentElements();
        }
    }
}
