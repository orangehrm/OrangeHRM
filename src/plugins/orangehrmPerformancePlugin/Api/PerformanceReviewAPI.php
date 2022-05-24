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

namespace OrangeHRM\Performance\Api;

use OrangeHRM\Core\Api\CommonParams;
use OrangeHRM\Core\Api\V2\CrudEndpoint;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\EndpointResult;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Core\Traits\Service\DateTimeHelperTrait;
use OrangeHRM\Entity\PerformanceReview;
use OrangeHRM\Performance\Api\Model\PerformanceReviewModel;
use OrangeHRM\Performance\Exception\ReviewServiceException;
use OrangeHRM\Performance\Traits\Service\PerformanceReviewServiceTrait;
use OrangeHRM\Pim\Traits\Service\EmployeeServiceTrait;

class PerformanceReviewAPI extends Endpoint implements CrudEndpoint
{
    use PerformanceReviewServiceTrait;
    use EmployeeServiceTrait;
    use DateTimeHelperTrait;

    public const PARAMETER_REVIEWER_EMP_NUMBER = 'reviewerEmpNumber';
    public const PARAMETER_PERIOD_START_DATE = 'startDate';
    public const PARAMETER_PERIOD_END_DATE = 'endDate';
    public const PARAMETER_DUE_DATE = 'dueDate';
    public const PARAMETER_ACTIVATE = 'activate';

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
     */
    public function create(): EndpointResult
    {
        $performanceReview = new PerformanceReview();
        $this->setReviewParams($performanceReview);
        $reviewerEmpNumber = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_REVIEWER_EMP_NUMBER
        );
        if ($this->getRequestParams()->getBooleanOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_ACTIVATE) == true) {
            try {
                $performanceReview->setActivatedDate($this->getDateTimeHelper()->getNow());
                $performanceReview->setStatusId(PerformanceReview::STATUS_ACTIVATED);
                $this->getPerformanceReviewService()->activateReview($performanceReview, $reviewerEmpNumber);
            } catch (ReviewServiceException $e) {
                throw $this->getBadRequestException($e->getMessage());
            }
        } else {
            $performanceReview->setStatusId(PerformanceReview::STATUS_INACTIVE);
            $this->getPerformanceReviewService()->getPerformanceReviewDao()->createReview($performanceReview, $reviewerEmpNumber);
        }
        return new EndpointResourceResult(PerformanceReviewModel::class, $performanceReview);
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            ...$this->getCommonValidationRules()
        );
    }

    /**
     * @return array
     */
    protected function getCommonValidationRules(): array
    {
        return [
            new ParamRule(
                CommonParams::PARAMETER_EMP_NUMBER,
                new Rule(Rules::IN_ACCESSIBLE_EMP_NUMBERS)
            ),
            new ParamRule(
                self::PARAMETER_REVIEWER_EMP_NUMBER,
                new Rule(Rules::IN_ACCESSIBLE_EMP_NUMBERS)
            ),
            new ParamRule(
                self::PARAMETER_PERIOD_START_DATE,
                new Rule(Rules::API_DATE)
            ),
            new ParamRule(
                self::PARAMETER_PERIOD_END_DATE,
                new Rule(Rules::API_DATE)
            ),
            new ParamRule(
                self::PARAMETER_DUE_DATE,
                new Rule(Rules::API_DATE)
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_ACTIVATE,
                    new Rule(Rules::BOOL_VAL)
                )
            ),
        ];
    }

    /**
     * @param PerformanceReview $performanceReview
     * @return void
     */
    private function setReviewParams(PerformanceReview $performanceReview): void
    {
        $empNumber = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_BODY,
            CommonParams::PARAMETER_EMP_NUMBER
        );
        $performanceReview->getDecorator()->setEmployeeByEmpNumber($empNumber);
        $performanceReview->setReviewPeriodStart(
            $this->getRequestParams()->getDateTime(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_PERIOD_START_DATE)
        );
        $performanceReview->setReviewPeriodEnd(
            $this->getRequestParams()->getDateTime(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_PERIOD_END_DATE)
        );
        $performanceReview->setDueDate(
            $this->getRequestParams()->getDateTime(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_DUE_DATE)
        );
        $employee = $this->getEmployeeService()->getEmployeeDao()->getEmployeeByEmpNumber($empNumber);
        $performanceReview->setJobTitle($employee->getJobTitle());
        $performanceReview->setSubunit($employee->getSubDivision());
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
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, CommonParams::PARAMETER_ID);
        $review = $this->getPerformanceReviewService()->getPerformanceReviewDao()->getEditableReviewById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($review, PerformanceReview::class);
        return new EndpointResourceResult(PerformanceReviewModel::class, $review);
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                CommonParams::PARAMETER_ID,
                new Rule(Rules::POSITIVE)
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function update(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, CommonParams::PARAMETER_ID);
        $review = $this->getPerformanceReviewService()->getPerformanceReviewDao()->getEditableReviewById($id);
        $this->throwRecordNotFoundExceptionIfNotExist($review, PerformanceReview::class);
        $this->setReviewParams($review);
        $reviewerEmpNumber = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_REVIEWER_EMP_NUMBER
        );
        if ($this->getRequestParams()->getBooleanOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_ACTIVATE) == true) {
            try {
                $review->setActivatedDate($this->getDateTimeHelper()->getNow());
                $review->setStatusId(PerformanceReview::STATUS_ACTIVATED);
                $this->getPerformanceReviewService()->updateActivateReview($review,$reviewerEmpNumber);
            } catch (ReviewServiceException $e) {
                throw $this->getBadRequestException($e->getMessage());
            }
        } else {
            $review->setStatusId(PerformanceReview::STATUS_INACTIVE);
            $this->getPerformanceReviewService()->getPerformanceReviewDao()->updateReview($review,$reviewerEmpNumber);
        }
        return new EndpointResourceResult(PerformanceReviewModel::class, $review);
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(CommonParams::PARAMETER_ID, new Rule(Rules::POSITIVE)),
            ...$this->getCommonValidationRules()
        );
    }
}