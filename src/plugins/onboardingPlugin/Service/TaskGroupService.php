<?php

namespace OrangeHRM\Onboarding\Service;

use OrangeHRM\Core\Exception\DaoException;
use OrangeHRM\Entity\Task;
use OrangeHRM\Onboarding\Dao\TaskGroupDao;
use OrangeHRM\Onboarding\Dto\TaskGroupSearchFilterParams;

class TaskGroupService
{
    protected ?TaskGroupDao $taskGroupDao = null;

    /**
     * @return TaskGroupDao|null
     */
    public function getTaskGroupDao(): ?TaskGroupDao
    {
        if (is_null($this->taskGroupDao)) {
            $this->taskGroupDao = new TaskGroupDao();
        }
        return $this->taskGroupDao;
    }

    public function getTaskGroupList(TaskGroupSearchFilterParams $filterParams): array
    {
        return $this->getTaskGroupDao()->getTaskGroupList($filterParams);
    }

    public function getTaskGroupListCount(TaskGroupSearchFilterParams $filterParams): int
    {
        return $this->getTaskGroupDao()->getTaskGroupListCount($filterParams);
    }

    /**
     * @throws DaoException
     */
    public function deleteTaskGroup(array $ids): int
    {
        return $this->getTaskGroupDao()->deleteTaskGroupById($ids);
    }
}