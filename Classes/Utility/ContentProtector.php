<?php
namespace Digitalwerk\DwContentProtector\Utility;

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

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use FluidTYPO3\Flux\Service\ContentService;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Content protector protects content elements to be moved/copied to disallowed Page/Grid columns
 */
class ContentProtector
{
    /**
     * Content table name
     */
    const CONTENT_TABLE_NAME = 'tt_content';

    /**
     * Fluid content CType
     */
    const FLUID_CONTENT_CTYPE = 'fluidcontent_content';

    /**
     * DataHandler
     *
     * @var DataHandler
     */
    protected $dataHandler= null;

    /**
     * ObjectManager
     *
     * @var ObjectManager
     */
    protected $objectManager = null;

    /**
     * ContentService
     *
     * @var null
     */
    protected $contentService = null;

    /**
     * ProviderResolver
     *
     * @var ProviderResolver
     */
    protected $providerResolver = null;

    /**
     * ContentProtector constructor.
     */
    public function __construct(DataHandler $dataHandler)
    {
        $this->dataHandler = $dataHandler;
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->contentService = $this->objectManager->get(ContentService::class);
        $this->providerResolver = $this->objectManager->get(ProviderResolver::class);
    }

    /**
     * DataHandler datamap check
     */
    public function checkDataContentElements()
    {
        if ($this->dataHandler->datamap[self::CONTENT_TABLE_NAME]) {
            foreach ($this->dataHandler->datamap[self::CONTENT_TABLE_NAME] as $id => $data) {
                if (!$id) {
                    continue;
                }
                if (!$this->canBeElementUpdated($id, $data)) {
                    unset($this->dataHandler->datamap[self::CONTENT_TABLE_NAME][$id]);
                    unset($this->dataHandler->cmdmap[self::CONTENT_TABLE_NAME][$id]);
                }
            }
        }
    }

    /**
     * DataHandler cmdmap check
     */
    public function checkCmdContentElements()
    {
        if ($this->dataHandler->cmdmap[self::CONTENT_TABLE_NAME]) {
            foreach ($this->dataHandler->cmdmap[self::CONTENT_TABLE_NAME] as $id => $command) {
                if (!$id) {
                    continue;
                }
                $data = [];
                $cmd = \key($command);
                if (isset($command[$cmd]['update'])) {
                    $data = $command[$cmd]['update'];
                }
                if (isset($command[$cmd]['target'])) {
                    $data['pid'] = $command[$cmd]['target'];
                }
                if (!$this->canBeElementUpdated($id, $data)) {
                    unset($this->dataHandler->cmdmap[self::CONTENT_TABLE_NAME][$id]);
                }
            }
        }
    }

    /**
     * Checks if user may update a record with uid=$id from $table
     *
     * @param string $table Record table
     * @param int $id Record UID
     * @param array|bool $data Record data
     * @param mixed $otherHookGrantedAccess
     * @return bool Returns TRUE if the user may update the record given by $table and $id
     */
    public function canBeElementUpdated($id, $dataToUpdate = [])
    {
        if (MathUtility::canBeInterpretedAsInteger($id)) {
            $record = BackendUtility::getRecord(self::CONTENT_TABLE_NAME, $id);
        } else {
            $record = $dataToUpdate;
        }
        $record = \array_merge($record, $dataToUpdate);

        if ((int)$record['colPos'] > ContentService::COLPOS_FLUXCONTENT) {
            list($txFluxParent, $txFluxColumn) = $this->contentService->getTargetAreaStoredInSession($record['colPos']);
            $record['colPos'] = ContentService::COLPOS_FLUXCONTENT;
            $record['tx_flux_parent'] = $txFluxParent;
            $record['tx_flux_column'] = $txFluxColumn;
        }

        //Resolve pid if pid < 0
        $record['pid'] = $this->dataHandler->resolvePid(self::CONTENT_TABLE_NAME, $record['pid']);

        if ($this->isElementDenied($record)) {
            $this->objectManager
                ->get(FlashMessageService::class)
                ->getMessageQueueByIdentifier()->enqueue(
                    new FlashMessage(
                        "Move or copy of element tt_content:{$record['uid']} here is not allowed",
                        'Not allowed',
                        FlashMessage::ERROR,
                        true
                    )
                );
            return false;
        }

        return true;
    }

    /**
     * Check if element is denied
     *
     * @param array $record
     * @return bool
     */
    protected function isElementDenied($record)
    {
        $colPos = (int)$record['colPos'];

        if ($colPos == ContentService::COLPOS_FLUXCONTENT) {
            $gridElement = BackendUtility::getRecord(self::CONTENT_TABLE_NAME, $record['tx_flux_parent']);
            $provider = $this->providerResolver->resolvePrimaryConfigurationProvider(self::CONTENT_TABLE_NAME, null, $gridElement);
            foreach ($provider->getGrid($gridElement)->getRows() as $row) {
                foreach ($row->getColumns() as $column) {
                    if ($column->getName() === $record['tx_flux_column']) {
                        $blackWhiteList = $this->getWhiteBlackList($column->getVariables());
                    }
                }
            }
        } else {
            $page = BackendUtility::getRecord('pages', $record['pid']);
            $provider = $this->providerResolver->resolvePrimaryConfigurationProvider('pages', 'tx_fed_page_flexform', $page);
            foreach ($provider->getGrid($page)->getRows() as $row) {
                foreach ($row->getColumns() as $column) {
                    if ($column->getColumnPosition() === $colPos) {
                        $blackWhiteList = $this->getWhiteBlackList($column->getVariables());
                    }
                }
            }
        }

        list($whiteList, $blackList) = $blackWhiteList;
        if ($this->isRecordFluidContent($record)) {
            if (!empty($blackList) and \in_array($record['tx_fed_fcefile'], $blackList)) {
                return true;
            }
            if (!empty($whiteList) and !\in_array($record['tx_fed_fcefile'], $whiteList)) {
                return true;
            }
        } else {
            if (!empty($blackList) and \in_array($record['CType'], $blackList)) {
                return true;
            }
            if (!empty($whiteList) and !\in_array($record['CType'], $whiteList)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get BlackList and WhileList
     *
     * @param array $variables
     * @return array
     */
    protected function getWhiteBlackList($variables)
    {
        $whiteList = $blackList = [];
        if (isset($variables['Fluidcontent'])) {
            list($whiteList, $blackList) = $this->getWhiteBlackList($variables['Fluidcontent']);
        }
        if (isset($variables['allowedContentTypes'])) {
            $whiteList = \array_merge($whiteList, GeneralUtility::trimExplode(',', $variables['allowedContentTypes']));
        }
        if (isset($variables['deniedContentTypes'])) {
            $blackList = \array_merge($blackList, GeneralUtility::trimExplode(',', $variables['deniedContentTypes']));
        }

        return [$whiteList, $blackList];
    }

    /**
     * Check if $record is Fluid content type
     * @param array $record
     * @return bool
     */
    protected function isRecordFluidContent($record)
    {
        return $record['CType'] === self::FLUID_CONTENT_CTYPE;
    }
}
