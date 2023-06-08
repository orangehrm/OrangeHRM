<?php

namespace OrangeHRM\Onboarding\Dao;

use Exception;
use OrangeHRM\Core\Dao\BaseDao;
use OrangeHRM\Core\Exception\DaoException;
use OrangeHRM\Entity\Task;
use OrangeHRM\Onboarding\Dto\TaskSearchFilterParams;
use OrangeHRM\ORM\ListSorter;
use OrangeHRM\ORM\QueryBuilderWrapper;

class TaskDao extends BaseDao
{
    public function getTaskList(TaskSearchFilterParams $taskSearchFilterParams): array
    {
        $qb = $this->getTaskListQueryBuilderWrapper($taskSearchFilterParams)->getQueryBuilder();
        return $qb->getQuery()->execute();
    }

    public function getTaskListCount(TaskSearchFilterParams $taskSearchFilterParams): int
    {
        $qb = $this->getTaskListQueryBuilderWrapper($taskSearchFilterParams)->getQueryBuilder();
        return $this->getPaginator($qb)->count();
    }

    protected function getTaskListQueryBuilderWrapper(TaskSearchFilterParams $taskSearchFilterParams): QueryBuilderWrapper
    {
        $q = $this->createQueryBuilder(Task::class, 'task');
        $q->distinct();
        $q->leftJoin('task.jobTitle', 'jobTitle');

        $this->setSortingAndPaginationParams($q, $taskSearchFilterParams);

        if (!is_null($taskSearchFilterParams->getTitle())) {
            $q->andWhere(
                $q->expr()->orX(
                    $q->expr()->like('task.title', ':title'),
                )
            );
            $q->setParameter('title', '%' . $taskSearchFilterParams->getTitle() . '%');
        }

        if (!is_null($taskSearchFilterParams->getType())) {
            $q->andWhere(
                $q->expr()->orX(
                    $q->expr()->eq('task.type', ':type'),
                )
            );
            $q->setParameter('type', $taskSearchFilterParams->getType());
        }

        if (!is_null($taskSearchFilterParams->getJobTitleId())) {
            $q->andWhere('jobTitle.id = :jobTitleId')
                ->setParameter('jobTitleId', $taskSearchFilterParams->getJobTitleId());
        }

        $q->orderBy('task.id', ListSorter::DESCENDING);

        return $this->getQueryBuilderWrapper($q);
    }

    public function saveTask(Task $task): Task
    {
        $this->persist($task);
        return $task;
    }

    public function findTaskById(int $id)
    {
        return $this->getRepository(Task::class)->find($id);
    }

    public function getNumberOfTasks(bool $includeDisabled = false): int
    {
        $q = $this->createQueryBuilder(Task::class, 't');

        if ($includeDisabled) {
            $q->andWhere(
                $q->expr()->isNull('t.deleted_at')
            );
        }

        return $this->count($q);
    }

    /**
     * @throws DaoException
     */
    public function getTaskById(int $id) : ?Task{
        try {
            $task = $this->getRepository(Task::class)->find($id);
            if ($task instanceof Task) {
                return $task;
            }
            return null;
        } catch (Exception $e) {
            throw new DaoException($e->getMessage());
        }
    }

    /**
     * @throws DaoException
     */
    public function deleteTaskById(array $ids) : int
    {
        try {
            $q = $this->createQueryBuilder(Task::class, 't');
            $q->delete()
                ->where($q->expr()->in('t.id', ':ids'))
                ->setParameter('ids', $ids);
            return $q->getQuery()->execute();
        } catch (Exception $e) {
            throw new DaoException($e->getMessage());
        }
    }
}