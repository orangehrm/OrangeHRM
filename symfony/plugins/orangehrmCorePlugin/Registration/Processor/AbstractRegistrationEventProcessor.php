<?php
/**
 * OrangeHRM Enterprise is a closed sourced comprehensive Human Resource Management (HRM)
 * System that captures all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM Inc is the owner of the patent, copyright, trade secrets, trademarks and any
 * other intellectual property rights which subsist in the Licensed Materials. OrangeHRM Inc
 * is the owner of the media / downloaded OrangeHRM Enterprise software files on which the
 * Licensed Materials are received. Title to the Licensed Materials and media shall remain
 * vested in OrangeHRM Inc. For the avoidance of doubt title and all intellectual property
 * rights to any design, new software, new protocol, new interface, enhancement, update,
 * derivative works, revised screen text or any other items that OrangeHRM Inc creates for
 * Customer shall remain vested in OrangeHRM Inc. Any rights not expressly granted herein are
 * reserved to OrangeHRM Inc.
 *
 * Please refer http://www.orangehrm.com/Files/OrangeHRM_Commercial_License.pdf for the license which includes terms and conditions on using this software.
 *
 */

namespace OrangeHRM\Core\Registration\Processor;

use DateTime;
use Exception;
use MarketplaceDao;
use OrangeHRM\Admin\Service\OrganizationService;
use OrangeHRM\Core\Exception\CoreServiceException;
use OrangeHRM\Core\Registration\Dao\RegistrationEventQueueDao;
use OrangeHRM\Core\Registration\Service\RegistrationAPIClientService;
use OrangeHRM\Core\Service\ConfigService;
use OrangeHRM\Entity\Employee;
use OrangeHRM\Entity\RegistrationEventQueue;
use sysConf;

abstract class AbstractRegistrationEventProcessor
{
    public sysConf $sysConf;
    public RegistrationEventQueueDao $registrationEventQueueDao;
    public ConfigService $configService;
    public RegistrationAPIClientService $registrationAPIClientService;
    public OrganizationService $organizationService;
    public MarketplaceDao $marketplaceDao;

    /**
     * @return RegistrationEventQueueDao
     */
    public function getRegistrationEventQueueDao(): RegistrationEventQueueDao
    {
        if (!($this->registrationEventQueueDao instanceof RegistrationEventQueueDao)) {
            $this->registrationEventQueueDao = new RegistrationEventQueueDao();
        }
        return $this->registrationEventQueueDao;
    }

    /**
     * @return MarketplaceDao
     */
    public function getMarketplaceDao(): MarketplaceDao
    {
        if (!isset($this->marketplaceDao)) {
            $this->marketplaceDao = new MarketplaceDao();
        }
        return $this->marketplaceDao;
    }

    /**
     * @return ConfigService
     */
    private function getConfigService(): ConfigService
    {
        if (!($this->configService instanceof ConfigService)) {
            $this->configService = new ConfigService();
        }
        return $this->configService;
    }

    /**
     * @return RegistrationAPIClientService
     */
    public function getRegistrationAPIClientService(): RegistrationAPIClientService
    {
        if (!($this->registrationAPIClientService instanceof RegistrationAPIClientService)) {
            $this->registrationAPIClientService = new RegistrationAPIClientService();
        }
        return $this->registrationAPIClientService;
    }

    /**
     * @return OrganizationService
     */
    public function getOrganizationService(): OrganizationService
    {
        if (!($this->organizationService instanceof OrganizationService)) {
            $this->organizationService = new OrganizationService();
        }
        return $this->organizationService;
    }

    /**
     * @return sysConf
     */
    public function getSysConf(): sysConf
    {
        if (!defined('ROOT_PATH')) {
            $rootPath = realpath(dirname(__FILE__));
            define('ROOT_PATH', $rootPath);
        }
        require_once(ROOT_PATH . '/lib/confs/sysConf.php');
        if (is_null($this->sysConf)) {
            $this->sysConf = new sysConf();
        }
        return $this->sysConf;
    }

    /**
     * @return string
     * @throws CoreServiceException
     */
    private function getInstanceIdentifier(): string
    {
        return $this->getConfigService()->getInstanceIdentifier();
    }

    /**
     * @return RegistrationEventQueue|void
     */
    public function saveRegistrationEvent()
    {
        if ($this->getEventToBeSavedOrNot()) {
            $registrationEvent = $this->processRegistrationEventToSave();
            return $this->getRegistrationEventQueueDao()->saveRegistrationEvent($registrationEvent);
        }
    }

    /**
     * @return array
     */
    public function getRegistrationEventGeneralData(): array
    {
        $registrationData = [];
        try {
            $adminEmployee = $this->getMarketplaceDao()->getAdmin();
            $language = $this->getConfigService()->getAdminLocalizationDefaultLanguage() ? $this->getConfigService(
            )->getAdminLocalizationDefaultLanguage() : 'Not captured';
            $country = $this->getOrganizationService()->getOrganizationGeneralInformation()->getCountry(
            ) ? $this->getOrganizationService()->getOrganizationGeneralInformation()->getCountry() : null;
            $instanceIdentifier = $this->getInstanceIdentifier();
            $organizationName = $this->getOrganizationService()->getOrganizationGeneralInformation()->getName();
            $systemDetails = '';
            $organizationEmail = '';
            $adminFirstName = '';
            $adminLastName = '';
            $adminContactNumber = '';
            $username = '';
            if ($adminEmployee instanceof Employee) {
                $organizationEmail = $adminEmployee->getWorkEmail();
                $adminFirstName = $adminEmployee->getFirstName();
                $adminLastName = $adminEmployee->getLastName();
                $adminContactNumber = $adminEmployee->getWorkTelephone();
                $username = $adminEmployee->getUsers() ? $adminEmployee->getUsers()->get(0)->getUserName() : '';
            }

            return array(
                'username' => $username,
                'email' => $organizationEmail,
                'telephone' => $adminContactNumber,
                'admin_first_name' => $adminFirstName,
                'admin_last_name' => $adminLastName,
                'timezone' => 'Not captured',
                'language' => $language,
                'country' => $country,
                'organization_name' => $organizationName,
                'instance_identifier' => $instanceIdentifier,
                'system_details' => $systemDetails
            );
        } catch (Exception $ex) {
            return $registrationData;
        }
    }

    /**
     * @return RegistrationEventQueue
     */
    public function processRegistrationEventToSave(): RegistrationEventQueue
    {
        $registrationData = $this->getEventData();
        $registrationEvent = new RegistrationEventQueue();
        $registrationEvent->setEventTime(new DateTime());
        $registrationEvent->setEventType($this->getEventType());
        $registrationEvent->setData($registrationData);
        return $registrationEvent;
    }

    public function publishRegistrationEvents()
    {
        $mode = $this->getSysConf()->getMode();
        if ($mode === sysConf::PROD_MODE) {
            $eventsToPublish = $this->getRegistrationEventQueueDao()->getUnpublishedRegistrationEvents(
                RegistrationEventQueue::PUBLISH_EVENT_BATCH_SIZE
            );
            if ($eventsToPublish) {
                foreach ($eventsToPublish as $event) {
                    $postData = $this->getRegistrationEventPublishDataPrepared($event);
                    $result = $this->getRegistrationAPIClientService()->publishData($postData);
                    if ($result) {
                        $event->setPublished(1);
                        $event->setPublishTime(new DateTime());
                        $this->getRegistrationEventQueueDao()->saveRegistrationEvent($event);
                    }
                }
            }
        }
    }

    /**
     * @param RegistrationEventQueue $event
     * @return array
     */
    public function getRegistrationEventPublishDataPrepared(RegistrationEventQueue $event): array
    {
        $eventData = $event->getData();
        $eventData['type'] = $event->getEventType();
        $eventData['event_time'] = $event->getEventTime();
        return $eventData;
    }

    /**
     * @return int
     */
    abstract public function getEventType(): int;

    /**
     * @return array
     */
    abstract public function getEventData(): array;

    /**
     * @return bool
     */
    abstract public function getEventToBeSavedOrNot(): bool;

}
