<?php

namespace OrangeHRM\Onboarding\Api\Model;

use OrangeHRM\Core\Api\V2\Serializer\ModelTrait;
use OrangeHRM\Core\Api\V2\Serializer\Normalizable;
use OrangeHRM\Entity\GroupAssignment;

class GroupAssignmentDetailModel implements Normalizable
{
    use ModelTrait;

    public function __construct(GroupAssignment $groupAssignment)
    {
        $this->setEntity($groupAssignment);

        $this->setFilters([
            'id',
            'notes',
            'name',
            'startDate',
            'endDate',
            'completed',
            'dueDate',
            'submittedAt',
            'getProgress',
            ['getTaskTypes', ['getId', 'getName']],
            [
                'getTaskGroups',
                [
                    'getId',
                    'isCompleted',
                    'getDueDate',
                    'getPriority',
                    ['getTask', 'getId', 'getTitle', 'getNotes']
                ]
            ],
            ['getSupervisor', 'getFullName'],
            ['getCreatedBy', 'getFullName'],
        ]);

        $this->setAttributeNames([
            'id',
            'notes',
            'name',
            'startDate',
            'endDate',
            'completed',
            'dueDate',
            'submittedAt',
            'progress',
            ['taskTypes', ['id', 'name']],
            [
                'taskGroups',
                [
                    'id',
                    'isCompleted',
                    'dueDate',
                    'priority',
                    ['task', 'id', 'title', 'notes'],
                ]
            ],
            ['supervisor', 'name'],
            ['creator', 'name'],
        ]);
    }
}