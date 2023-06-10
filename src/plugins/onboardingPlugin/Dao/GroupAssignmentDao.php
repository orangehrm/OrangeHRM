<?php

namespace OrangeHRM\Onboarding\Dao;

use Exception;
use OrangeHRM\Core\Dao\BaseDao;
use OrangeHRM\Core\Exception\DaoException;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Entity\GroupAssignment;
use OrangeHRM\Onboarding\Dto\GroupAssignmentSearchFilterParams;
use OrangeHRM\ORM\ListSorter;
use OrangeHRM\ORM\QueryBuilderWrapper;

class GroupAssignmentDao extends BaseDao
{
    use AuthUserTrait;

    public function saveTaskAssignment(GroupAssignment $groupAssignment): GroupAssignment
    {
        $this->persist($groupAssignment);
        return $groupAssignment;
    }

    // My group assignments
    public function getMyGroupAssignments(GroupAssignmentSearchFilterParams $filterParams): array
    {
        $qb = $this->getGroupAssignmentListQueryBuilderWrapper($filterParams)->getQueryBuilder();
        $qb->andWhere('g.empNumber = :employeeId')
            ->setParameter('employeeId', $this->getAuthUser()->getEmpNumber());
        return $qb->getQuery()->execute();
    }

    public function getMyGroupAssignmentsCount(GroupAssignmentSearchFilterParams $filterParams): int
    {
        $qb = $this->getGroupAssignmentListQueryBuilderWrapper($filterParams)->getQueryBuilder();
        $qb->andWhere('g.empNumber = :employeeId')
            ->setParameter('employeeId', $this->getAuthUser()->getEmpNumber());
        return $this->getPaginator($qb)->count();
    }

    // Employee Group Assignments
    public function getEmployeeAssignments(GroupAssignmentSearchFilterParams $filterParams): array
    {
        $qb = $this->getGroupAssignmentListQueryBuilderWrapper($filterParams)->getQueryBuilder();
        $qb->andWhere('g.supervisorNumber = :employeeId')
            ->setParameter('employeeId', $this->getAuthUser()->getEmpNumber());
        return $qb->getQuery()->execute();
    }

    public function getEmployeeAssignmentsCount(GroupAssignmentSearchFilterParams $filterParams): int
    {
        $qb = $this->getGroupAssignmentListQueryBuilderWrapper($filterParams)->getQueryBuilder();
        $qb->andWhere('g.supervisorNumber = :employeeId')
            ->setParameter('employeeId', $this->getAuthUser()->getEmpNumber());
        return $this->getPaginator($qb)->count();
    }

    // All Group Assignments
    public function getGroupAssignments(GroupAssignmentSearchFilterParams $filterParams): array
    {
        $qb = $this->getGroupAssignmentListQueryBuilderWrapper($filterParams)->getQueryBuilder();
        return $qb->getQuery()->execute();
    }

    public function getGroupAssignmentsCount(GroupAssignmentSearchFilterParams $filterParams): int
    {
        $qb = $this->getGroupAssignmentListQueryBuilderWrapper($filterParams)->getQueryBuilder();
        return $this->getPaginator($qb)->count();
    }

    protected function getGroupAssignmentListQueryBuilderWrapper(GroupAssignmentSearchFilterParams $filterParams): QueryBuilderWrapper
    {
        $q = $this->createQueryBuilder(GroupAssignment::class, 'g');
        $q->distinct();

        $this->setSortingAndPaginationParams($q, $filterParams);

        $q->orderBy('g.id', ListSorter::DESCENDING);

        return $this->getQueryBuilderWrapper($q);
    }

    /**
     * @throws DaoException
     */
    public function getGroupAssignmentById(int $id): ?GroupAssignment
    {
        try {
            $groupAssignment = $this->getRepository(GroupAssignment::class)->find($id);
            if ($groupAssignment instanceof GroupAssignment) {
                return $groupAssignment;
            }
            return null;
        } catch (Exception $e) {
            throw new DaoException($e->getMessage());
        }
    }

    /**
     * @throws DaoException
     */
    public function deleteGroupAssignmentById(array $ids): int
    {
        $q = $this->createQueryBuilder(GroupAssignment::class, 'g');
        $q->distinct();
        $q->andWhere(
            $q->expr()->in('g.id', ':ids')
        )->setParameter('ids', $ids);
        $q->andWhere('g.creatorId = :creatorId')
            ->setParameter('creatorId', $this->getAuthUser()->getEmpNumber());

        $results = $q->getQuery()->execute();

        $idsToDelete = array_map(fn(GroupAssignment $groupAssignment) => $groupAssignment->getId(), $results);

        if (count($idsToDelete) === 0) {
            return 0;
        }

        try {
            $q = $this->createQueryBuilder(GroupAssignment::class, 'g');
            $q->delete()
                ->where($q->expr()->in('g.id', ':ids'))
                ->setParameter('ids', $idsToDelete);
            return $q->getQuery()->execute();
        } catch (Exception $e) {
            throw new DaoException($e->getMessage());
        }
    }
}