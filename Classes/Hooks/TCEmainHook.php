<?php
namespace Digitalwerk\DwContentProtector\Hooks;

/***
 *
 * This file is part of the "Boilerplate" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2017 Ondrej Grosko <ondrej@digitalwerk.agency>, Digitalwerk
 *
 ***/

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
