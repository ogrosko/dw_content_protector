<?php
namespace Digitalwerk\DwContentProtector\Hooks;

/**
 * Class TCEmainHook
 * @package Digitalwerk\DwContentProtector\Hooks
 */
class TCEmainHook
{

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

    /**
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     */
    public function processCmdmap_beforeStart(\TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler)
    {
        if (!empty($dataHandler->cmdmap[\Digitalwerk\DwContentProtector\Utility\ContentProtector::CONTENT_TABLE_NAME])) {
            $contentProtector = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \Digitalwerk\DwContentProtector\Utility\ContentProtector::class,
                $dataHandler
            );
            $contentProtector->checkCmdContentElements();
        }
    }
}
