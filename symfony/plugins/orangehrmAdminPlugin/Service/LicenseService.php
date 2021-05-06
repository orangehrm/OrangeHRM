<?php

/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 */

namespace OrangeHRM\Admin\Service;

use OrangeHRM\Admin\Dao\LicenseDao;
use OrangeHRM\Admin\Dto\LicenseSearchFilterParams;
use OrangeHRM\Core\Exception\DaoException;
use OrangeHRM\Entity\License;

class LicenseService
{
    /**
     * @var LicenseDao|null
     *
     */
    private ?LicenseDao $licenseDao = null;

    /**
     * @return LicenseDao
     */
    public function getLicenseDao(): LicenseDao
    {
        if (!($this->licenseDao instanceof LicenseDao)) {
            $this->licenseDao = new LicenseDao();
        }

        return $this->licenseDao;
    }

    /**
     * @param $licenseDao
     * @return void
     */
    public function setLicenseDao($licenseDao)
    {
        $this->licenseDao = $licenseDao;
    }

    /**
     * Saves a license
     *
     * Can be used for a new record or updating.
     *
     * @param License $license
     * @return NULL Doesn't return a value
     * @throws \DaoException
     * @version 2.6.12
     */
    public function saveLicense(License $license)
    {
        $this->getLicenseDao()->saveLicense($license);
    }

    /**
     * Retrieves a license by ID
     *
     * @param int $id
     * @return License An instance of License or NULL
     * @throws DaoException
     * @version 2.6.12
     */
    public function getLicenseById(int $id): ?License
    {
        return $this->getLicenseDao()->getLicenseById($id);
    }

    /**
     * Retrieves a license by name
     *
     * Case insensitive
     *
     * @param string $name
     * @return License An instance of License or false
     * @throws DaoException
     * @version 2.6.12
     */
    public function getLicenseByName(string $name): ?License
    {
        return $this->getLicenseDao()->getLicenseByName($name);
    }

    /**
     * @param LicenseSearchFilterParams $licenseSearchParamHolder
     * @return array
     * @throws DaoException
     */
    public function getLicenseList(LicenseSearchFilterParams $licenseSearchParamHolder): array
    {
        return $this->getLicenseDao()->getLicenseList($licenseSearchParamHolder);
    }

    /**
     * @param LicenseSearchFilterParams $licenseSearchParamHolder
     * @return int
     * @throws DaoException
     */
    public function getLicenseCount(LicenseSearchFilterParams $licenseSearchParamHolder): int
    {
        return $this->getLicenseDao()->getLicenseCount($licenseSearchParamHolder);
    }

    /**
     * Deletes licenses
     *
     * @param array $toDeleteIds An array of IDs to be deleted
     * @return int Number of records deleted
     * @throws DaoException
     * @version 2.6.12
     */
    public function deleteLicenses(array $toDeleteIds): int
    {
        return $this->getLicenseDao()->deleteLicenses($toDeleteIds);
    }

    /**
     * Checks whether the given license name exists
     *
     * Case insensitive
     *
     * @param string $licenseName License name that needs to be checked
     * @return Bool
     * @throws DaoException
     * @version 2.6.12
     */
    public function isExistingLicenseName(string $licenseName): bool
    {
        return $this->getLicenseDao()->isExistingLicenseName($licenseName);
    }
}
