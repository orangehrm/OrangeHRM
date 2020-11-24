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

use Orangehrm\Rest\Api\Exception\BadRequestException;
use Orangehrm\Rest\Api\Exception\InvalidParamException;
use Orangehrm\Rest\Http\Request;
use Orangehrm\Rest\Http\Response;

/**
 * @group API
 */
class ApiEmployeePunchStatusAPITest extends PHPUnit\Framework\TestCase
{
    /**
     * @var Request
     */
    private $request = null;

    protected function setUp()
    {
        $sfEvent = new sfEventDispatcher();
        $sfRequest = new sfWebRequest($sfEvent);
        $this->request = new Request($sfRequest);
    }

    public function testGetStatusDetailsWhenPunchedInState()
    {
        $lastrecord = new AttendanceRecord();
        $lastrecord->setId(1);
        $lastrecord->setEmployeeId(1);
        $lastrecord->setPunchInUtcTime('2021-01-28 11:04:00');
        $lastrecord->setPunchInNote('PUNCH IN NOTE');
        $lastrecord->setPunchInTimeOffset(-2.5);
        $lastrecord->setPunchInUserTime("2021-01-28 08:34");
        $lastrecord->setState(PluginAttendanceRecord::STATE_PUNCHED_IN);
        $attendanceService = $this->getMockBuilder(
            'AttendanceService'
        )
            ->setMethods(
                [
                    'getLatestPunchInRecord',
                    'GetLoggedInEmployeeNumber'
                ]
            )
            ->getMock();
        $attendanceService->expects($this->once())
            ->method('GetLoggedInEmployeeNumber')
            ->will($this->returnValue(1));
        $attendanceService->expects($this->once())
            ->method('getLatestPunchInRecord')
            ->with(1,PluginAttendanceRecord::STATE_PUNCHED_IN)
            ->will($this->returnValue($lastrecord));

        $employeePunchStatusApi = $this->getMockBuilder('Orangehrm\Rest\Api\User\EmployeePunchStatusAPI')
            ->setMethods(['checkValidEmployee', 'getPunchTimeEditable'])
            ->setConstructorArgs([$this->request])
            ->getMock();
        $employeePunchStatusApi->setAttendanceService($attendanceService);
        $employeePunchStatusApi->expects($this->once())
            ->method('getPunchTimeEditable')
            ->will($this->returnValue(['editable'=>true,'serverUtcTime'=> '2020-11-11 11:35']));
        $employeePunchStatusApi->expects($this->once())
            ->method('checkValidEmployee')
            ->will($this->returnValue(true));
        $actual=$employeePunchStatusApi->getStatusDetails();
        $expected = new Response(
            array(
                'punchTime' => '2021-01-28 08:34',
                'punchNote' => 'PUNCH IN NOTE',
                'PunchTimeZoneOffset' => -2.5,
                'dateTimeEditable' => true,
                'currentUtcDateTime' => '2020-11-11 11:35',
                'punchState' => PluginAttendanceRecord::STATE_PUNCHED_IN
            )
        );
        $this->assertEquals($expected,$actual);
    }


    public function testGetStatusDetailsWhenPunchedOutState()
    {
        $attendanceService = $this->getMockBuilder(
            'AttendanceService'
        )
            ->setMethods(
                [
                    'getLatestPunchInRecord',
                    'GetLoggedInEmployeeNumber'
                ]
            )
            ->getMock();
        $attendanceService->expects($this->once())
            ->method('GetLoggedInEmployeeNumber')
            ->will($this->returnValue(1));
        $attendanceService
            ->method('getLatestPunchInRecord')
            ->will(
                $this->returnCallback(
                    function ($id,$status) {
                        if($id==1 and $status=='PUNCHED IN')
                            return null;
                        else if ($id==1 and $status=='PUNCHED OUT'){
                            $lastrecord = new AttendanceRecord();
                            $lastrecord->setId(1);
                            $lastrecord->setEmployeeId(1);
                            $lastrecord->setPunchOutUtcTime('2021-01-28 11:04:00');
                            $lastrecord->setPunchOutNote('PUNCH OUT NOTE');
                            $lastrecord->setPunchOutTimeOffset(3.5);
                            $lastrecord->setPunchOutUserTime("2021-01-28 08:34");
                            $lastrecord->setState(PluginAttendanceRecord::STATE_PUNCHED_IN);
                            return $lastrecord;
                        }
                    }
                )
            );

        $employeePunchStatusApi = $this->getMockBuilder('Orangehrm\Rest\Api\User\EmployeePunchStatusAPI')
            ->setMethods(['checkValidEmployee', 'getPunchTimeEditable'])
            ->setConstructorArgs([$this->request])
            ->getMock();
        $employeePunchStatusApi->setAttendanceService($attendanceService);
        $employeePunchStatusApi->expects($this->once())
            ->method('getPunchTimeEditable')
            ->will($this->returnValue(['editable'=>true,'serverUtcTime'=> '2020-11-11 11:35']));
        $employeePunchStatusApi->expects($this->once())
            ->method('checkValidEmployee')
            ->will($this->returnValue(true));
        $actual=$employeePunchStatusApi->getStatusDetails();
        $expected = new Response(
            array(
                'punchTime' => '2021-01-28 08:34',
                'punchNote' => 'PUNCH OUT NOTE',
                'PunchTimeZoneOffset' =>3.5 ,
                'dateTimeEditable' => true,
                'currentUtcDateTime' => '2020-11-11 11:35',
                'punchState' => PluginAttendanceRecord::STATE_PUNCHED_OUT
            )
        );
        $this->assertEquals($actual,$expected);
    }

    public function testGetStatusDetailsWhenNoRecords()
    {

        $attendanceService = $this->getMockBuilder(
            'AttendanceService'
        )
            ->setMethods(
                [
                    'getLatestPunchInRecord',
                    'GetLoggedInEmployeeNumber'
                ]
            )
            ->getMock();
        $attendanceService->expects($this->once())
            ->method('GetLoggedInEmployeeNumber')
            ->will($this->returnValue(1));
        $attendanceService
            ->method('getLatestPunchInRecord')
            ->will($this->returnValue(null));

        $employeePunchStatusApi = $this->getMockBuilder('Orangehrm\Rest\Api\User\EmployeePunchStatusAPI')
            ->setMethods(['checkValidEmployee', 'getPunchTimeEditable'])
            ->setConstructorArgs([$this->request])
            ->getMock();
        $employeePunchStatusApi->setAttendanceService($attendanceService);
        $employeePunchStatusApi->expects($this->once())
            ->method('getPunchTimeEditable')
            ->will($this->returnValue(['editable'=>true,'serverUtcTime'=> '2020-11-11 11:35']));
        $employeePunchStatusApi->expects($this->once())
            ->method('checkValidEmployee')
            ->will($this->returnValue(true));
        $actual=$employeePunchStatusApi->getStatusDetails();
        $expected = new Response(
            array(
                'punchTime' => null,
                'punchNote' => null,
                'PunchTimeZoneOffset' => null,
                'dateTimeEditable' => true,
                'currentUtcDateTime' => '2020-11-11 11:35',
                'punchState' => AttendanceRecord::STATE_INITIAL
            )
        );
        $this->assertEquals($expected,$actual);
    }
}
