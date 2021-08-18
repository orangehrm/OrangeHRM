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

namespace OrangeHRM\Entity\Decorator;

use OrangeHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use OrangeHRM\Entity\Employee;
use OrangeHRM\Entity\Leave;
use OrangeHRM\Entity\LeaveComment;
use OrangeHRM\Entity\User;

class LeaveCommentDecorator
{
    use EntityManagerHelperTrait;

    /**
     * @var LeaveComment
     */
    private LeaveComment $leaveComment;

    /**
     * @param LeaveComment $leaveComment
     */
    public function __construct(LeaveComment $leaveComment)
    {
        $this->leaveComment = $leaveComment;
    }

    /**
     * @return LeaveComment
     */
    protected function getLeaveComment(): LeaveComment
    {
        return $this->leaveComment;
    }

    /**
     * @param int $empNumber
     */
    public function setCreatedByEmployeeByEmpNumber(int $empNumber): void
    {
        /** @var Employee|null $employee */
        $employee = $this->getReference(Employee::class, $empNumber);
        $this->getLeaveComment()->setCreatedByEmployee($employee);
    }

    /**
     * @param int $userId
     */
    public function setCreatedByUserById(int $userId): void
    {
        /** @var User|null $user */
        $user = $this->getReference(User::class, $userId);
        $this->getLeaveComment()->setCreatedBy($user);
    }

    /**
     * @param int $id
     */
    public function setLeaveById(int $id): void
    {
        /** @var Leave|null $leave */
        $leave = $this->getReference(Leave::class, $id);
        $this->getLeaveComment()->setLeave($leave);
    }
}
