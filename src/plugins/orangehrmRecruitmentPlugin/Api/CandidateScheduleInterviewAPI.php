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

namespace OrangeHRM\Recruitment\Api;

use Exception;
use OrangeHRM\Core\Api\V2\CrudEndpoint;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\EndpointResult;
use OrangeHRM\Core\Api\V2\Exception\BadRequestException;
use OrangeHRM\Core\Api\V2\Exception\ForbiddenException;
use OrangeHRM\Core\Api\V2\Exception\RecordNotFoundException;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use OrangeHRM\Core\Traits\Service\DateTimeHelperTrait;
use OrangeHRM\Core\Traits\UserRoleManagerTrait;
use OrangeHRM\Entity\Candidate;
use OrangeHRM\Entity\CandidateHistory;
use OrangeHRM\Entity\CandidateVacancy;
use OrangeHRM\Entity\Interview;
use OrangeHRM\Entity\WorkflowStateMachine;
use OrangeHRM\ORM\Exception\TransactionException;
use OrangeHRM\Recruitment\Api\Model\CandidateInterviewModel;
use OrangeHRM\Recruitment\Service\CandidateService;
use OrangeHRM\Recruitment\Traits\Service\CandidateServiceTrait;

class CandidateScheduleInterviewAPI extends Endpoint implements CrudEndpoint
{
    use CandidateServiceTrait;
    use UserRoleManagerTrait;
    use AuthUserTrait;
    use DateTimeHelperTrait;
    use EntityManagerHelperTrait;

    public const PARAMETER_CANDIDATE_ID = 'candidateId';
    public const PARAMETER_INTERVIEW_NAME = 'interviewName';
    public const PARAMETER_INTERVIEW_DATE = 'interviewDate';
    public const PARAMETER_INTERVIEW_TIME = 'interviewTime';
    public const PARAMETER_NOTE = 'note';
    public const PARAMETER_INTERVIEWERS = 'interviewerEmpNumbers';

    public const PARAMETER_RULE_INTERVIEW_NAME_MAX_LENGTH = 100;
    public const PARAMETER_RULE_INTERVIEWERS_MIN_COUNT = 1;
    public const PARAMETER_RULE_INTERVIEWERS_MAX_COUNT = 5;
    public const PARAMETER_RULE_NOTE_MAX_LENGTH = 100;

    public const MAXIMUM_ALLOWED_INTERVIEWS_COUNT = 2;

    /**
     * @inheritDoc
     */
    public function getAll(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     * @throws TransactionException
     * @throws BadRequestException
     */
    public function create(): EndpointResult
    {
        $this->beginTransaction();
        try {
            $candidateId = $this->getCandidateId();
            $candidateVacancy = $this->getCandidateService()
                ->getCandidateDao()
                ->getCandidateVacancyByCandidateId($candidateId);
            $this->throwRecordNotFoundExceptionIfNotExist($candidateVacancy, CandidateVacancy::class);
            $allowedWorkflowItems = $this->getUserRoleManager()->getAllowedActions(
                WorkflowStateMachine::FLOW_RECRUITMENT,
                $candidateVacancy->getStatus()
            );
            if (
                !in_array(
                    WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_SHEDULE_INTERVIEW,
                    array_keys($allowedWorkflowItems)
                )
            ) {
                throw $this->getForbiddenException();
            }

            $numberOfInterviewsScheduled = $this->getCandidateService()
                ->getCandidateDao()
                ->getInterviewCountByCandidateId($candidateId);

            if ($numberOfInterviewsScheduled >= self::MAXIMUM_ALLOWED_INTERVIEWS_COUNT) {
                throw $this->getBadRequestException('You Can not Schedule More Than Two Interviews Per Candidate');
            }

            $interview = new Interview();
            $this->setInterview($interview, $candidateVacancy);
            $interview = $this->getCandidateService()->getCandidateDao()->saveCandidateInterview($interview);

            $candidateVacancy->setStatus(
                CandidateService::STATUS_MAP[WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_SHEDULE_INTERVIEW]
            );
            $this->getCandidateService()->getCandidateDao()->saveCandidateVacancy($candidateVacancy);

            $candidateHistory = new CandidateHistory();
            $this->setCandidateHistory($candidateHistory, $candidateVacancy, $interview);
            $this->getCandidateService()->getCandidateDao()->saveCandidateHistory($candidateHistory);

            $this->commitTransaction();
            return new EndpointResourceResult(CandidateInterviewModel::class, $interview);
        } catch (RecordNotFoundException|ForbiddenException|BadRequestException $e) {
            $this->rollBackTransaction();
            throw $e;
        } catch (Exception $e) {
            $this->rollBackTransaction();
            throw new TransactionException($e);
        }
    }

    /**
     * @return int
     */
    private function getCandidateId(): int
    {
        return $this->getRequestParams()->getIntOrNull(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            self::PARAMETER_CANDIDATE_ID
        );
    }

    /**
     * @param Interview $interview
     * @param CandidateVacancy $candidateVacancy
     */
    private function setInterview(Interview $interview, CandidateVacancy $candidateVacancy)
    {
        $interview->getDecorator()->setCandidateById($this->getCandidateId());
        $interview->getDecorator()->setCandidateVacancyById($candidateVacancy->getId());
        $interview->setInterviewName(
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_INTERVIEW_NAME
            )
        );
        $interview->setInterviewDate(
            $this->getRequestParams()->getDateTime(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_INTERVIEW_DATE
            )
        );
        $interview->setInterviewTime(
            $this->getRequestParams()->getDateTimeOrNull(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_INTERVIEW_TIME
            )
        );
        $interview->setNote(
            $this->getRequestParams()->getStringOrNull(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_NOTE
            )
        );
        $interview->getDecorator()->setInterviewerByEmpNumbers(
            $this->getRequestParams()->getArray(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_INTERVIEWERS
            )
        );
    }

    /**
     * @param CandidateHistory $candidateHistory
     * @param CandidateVacancy $candidateVacancy
     * @param Interview $interview
     */
    private function setCandidateHistory(
        CandidateHistory $candidateHistory,
        CandidateVacancy $candidateVacancy,
        Interview $interview
    ) {
        $candidateHistory->getDecorator()->setCandidateById($candidateVacancy->getCandidate()->getId());
        $candidateHistory->getDecorator()->setVacancyById($candidateVacancy->getVacancy()->getId());
        $candidateHistory->setCandidateVacancyName($candidateVacancy->getVacancy()->getName());
        $candidateHistory->setAction(WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_SHEDULE_INTERVIEW);
        $candidateHistory->getDecorator()->setInterviewByInterviewId($interview->getId());
        $candidateHistory->getDecorator()->setPerformedBy($this->getAuthUser()->getEmpNumber());
        $candidateHistory->setPerformedDate($this->getDateTimeHelper()->getNow());
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            ...$this->getCommonBodyValidationRules(),
        );
    }

    protected function getCommonBodyValidationRules(): array
    {
        return [
            new ParamRule(
                self::PARAMETER_CANDIDATE_ID,
                new Rule(Rules::POSITIVE),
                new Rule(Rules::ENTITY_ID_EXISTS, [Candidate::class])
            ),
            new ParamRule(
                self::PARAMETER_INTERVIEW_NAME,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::LENGTH, [null, self::PARAMETER_RULE_INTERVIEW_NAME_MAX_LENGTH])
            ),
            new ParamRule(
                self::PARAMETER_INTERVIEW_DATE,
                new Rule(Rules::API_DATE)
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_INTERVIEW_TIME,
                    new Rule(Rules::TIME)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_NOTE,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAMETER_RULE_NOTE_MAX_LENGTH])
                )
            ),
            new ParamRule(
                self::PARAMETER_INTERVIEWERS,
                new Rule(Rules::ARRAY_TYPE),
                new Rule(
                    Rules::LENGTH,
                    [self::PARAMETER_RULE_INTERVIEWERS_MIN_COUNT, self::PARAMETER_RULE_INTERVIEWERS_MAX_COUNT]
                )
            )
        ];
    }

    /**
     * @inheritDoc
     */
    public function delete(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getOne(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function update(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }
}
