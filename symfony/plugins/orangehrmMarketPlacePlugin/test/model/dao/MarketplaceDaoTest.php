<?php
/*
 *
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
 *
 */

/**
 * @group Marketplace
 */
class MarketplaceDaoTest extends PHPUnit_Framework_TestCase {

    private $marketplaceDao;
    private $fixture;

    protected function setUp() {
        $this->marketplaceDao = new MarketplaceDao();
        $this->fixture = sfConfig::get('sf_plugins_dir') . '/orangehrmMarketPlacePlugin/test/fixtures/MarketplaceDao.yml';
        TestDataService::populate($this->fixture);
    }

    public function testUpdateStatusRequestAddon() {
        $paidAddonNames = array("LDAP");
        $result = $this->marketplaceDao->updateStatusRequestAddon($paidAddonNames);
        $this->assertEquals($result, true);

    }

    public function testGetPaidAddonIds() {
        $result = $this->marketplaceDao->getPaidAddonIds();
        $this->assertEquals(count($result), 1);
    }

}