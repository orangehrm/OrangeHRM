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

namespace OrangeHRM\Leave\Dao;

use Doctrine\ORM\Tools\Pagination\Paginator;
use OrangeHRM\Core\Dao\BaseDao;
use OrangeHRM\Entity\Leave;
use OrangeHRM\Leave\Dto\LeaveListSearchFilterParams;
use OrangeHRM\Leave\Traits\Service\LeaveListServiceTrait;

class LeaveListDao extends BaseDao
{
    use LeaveListServiceTrait;

    /**
     * @param LeaveListSearchFilterParams $leaveListSearchFilterParams
     * @return array
     */
    public function getEmployeeOnLeaveList(LeaveListSearchFilterParams $leaveListSearchFilterParams): array
    {
        return $this->getLeaveListPaginator($leaveListSearchFilterParams)->getQuery()->execute();
    }

    /**
     * @param LeaveListSearchFilterParams $leaveListSearchFilterParams
     * @return Paginator
     */
    public function getLeaveListPaginator(LeaveListSearchFilterParams $leaveListSearchFilterParams): Paginator
    {
        $q = $this->createQueryBuilder(Leave::class, 'leaveList');
        $q->leftJoin('leaveList.employee', 'employee');
        $this->setSortingAndPaginationParams($q, $leaveListSearchFilterParams);

        $q->select(
            'leaveList.id',
            'leaveList.date',
            'leaveList.lengthHours',
            'leaveList.status',
            'employee.empNumber',
            'employee.firstName',
            'employee.lastName',
            'employee.employeeId',
        );
        $q->andWhere('leaveList.date = :date')->setParameter('date', $leaveListSearchFilterParams->getDate());

        return $this->getPaginator($q);
    }
}