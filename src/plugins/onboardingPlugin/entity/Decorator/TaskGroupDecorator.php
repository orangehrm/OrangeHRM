<?php

namespace OrangeHRM\Entity\Decorator;

use OrangeHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use OrangeHRM\Entity\Task;
use OrangeHRM\Entity\TaskAssignment;
use OrangeHRM\Entity\TaskGroup;

class TaskGroupDecorator
{
    use EntityManagerHelperTrait;

    private TaskGroup $taskGroup;

    public function __construct(TaskGroup $task)
    {
        $this->taskGroup = $task;
    }


    public function setTaskAssignmentById(?int $id): void
    {
        if (!$id) {
            return;
        }
        /** @var TaskAssignment|null $taskAssignment */
        $taskAssignment = $this->getReference(TaskAssignment::class, $id);
        $this->getTaskGroup()->setTaskAssignment($taskAssignment);
    }

    public function setTaskById(?int $id): void
    {
        if (!$id) {
            return;
        }
        /** @var Task|null $task */
        $task = $this->getReference(Task::class, $id);
        $this->getTaskGroup()->setTask($task);
    }

    /**
     * @return TaskGroup
     */
    public function getTaskGroup(): TaskGroup
    {
        return $this->taskGroup;
    }
}