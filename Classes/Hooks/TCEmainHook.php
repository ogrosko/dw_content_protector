<?php
namespace Digitalwerk\DwContentProtector\Hooks;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Ondrej Grosko <ondrej@digitalwerk.agency>, Digitalwerk
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * TYPO3 TCE hook class used for DataHandler hooks
 */
class TCEmainHook
{

    /**
     * Pre-process DataHandler data before processing starts
     *
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
     * Pre-process Datahandler cmd before processing starts
     *
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
