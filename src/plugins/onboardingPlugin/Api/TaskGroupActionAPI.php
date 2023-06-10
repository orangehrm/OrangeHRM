<?php

namespace OrangeHRM\Onboarding\Api;

use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\EndpointResult;
use OrangeHRM\Core\Api\V2\Model\ArrayModel;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\ResourceEndpoint;
use OrangeHRM\Core\Api\V2\Serializer\NormalizeException;
use OrangeHRM\Core\Exception\DaoException;
use OrangeHRM\Onboarding\Api\Validation\TaskGroupActionValidation;
use OrangeHRM\Onboarding\Traits\Service\TaskGroupServiceTrait;

class TaskGroupActionAPI extends Endpoint implements ResourceEndpoint
{
    use TaskGroupActionValidation, TaskGroupServiceTrait;

    public const ACTION_TOGGLE_COMPLETE = 'toggle_complete';
    public const ALLOWED_ACTIONS = [
        self::ACTION_TOGGLE_COMPLETE
    ];
    public const PARAMETER_ACTION = 'action';
    public const PARAMETER_GROUP_ASSIGNMENT_ID = 'groupAssignmentId';
    public const PARAMETER_TASK_GROUP_ID = 'taskGroupId';

    public function getOne(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @throws DaoException
     * @throws NormalizeException
     */
    public function update(): EndpointResult
    {
        $action = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, TaskGroupActionAPI::PARAMETER_ACTION);
        $groupAssignmentId = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, TaskGroupActionAPI::PARAMETER_GROUP_ASSIGNMENT_ID);
        $taskGroupId = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, TaskGroupActionAPI::PARAMETER_TASK_GROUP_ID);
        $results = null;

        switch ($action) {
            case self::ACTION_TOGGLE_COMPLETE:
                $results = $this->getTaskGroupService()->toggleTaskGroupComplete($groupAssignmentId, $taskGroupId);
                break;
        }
        return new EndpointResourceResult(ArrayModel::class, [$results]);
    }

    public function delete(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }
}