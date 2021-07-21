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

namespace OrangeHRM\Tests\Core\Service;

use OrangeHRM\Admin\Dao\EmailConfigurationDao;
use OrangeHRM\Admin\Service\EmailConfigurationService;
use OrangeHRM\Core\Service\ConfigService;
use OrangeHRM\Core\Service\EmailService;
use OrangeHRM\Entity\EmailConfiguration;
use OrangeHRM\Framework\Util\Mailer;
use OrangeHRM\Framework\Util\MailTransport;
use OrangeHRM\Tests\Util\TestCase;

/**
 * @group Admin
 * @group Service
 */
class EmailServiceTest extends TestCase
{
    private EmailConfigurationService $emailConfigurationService;
    private EmailService $emailService;

    protected function setUp(): void
    {
        $this->emailConfigurationService = new EmailConfigurationService();
        $this->emailService = new EmailService();
    }

    public function testGetEmailConfigurationDao()
    {
        $this->assertTrue(
            $this->emailConfigurationService->getEmailConfigurationDao() instanceof EmailConfigurationDao
        );
    }

    public function testGetEmailService()
    {
        $this->assertTrue($this->emailConfigurationService->getEmailService() instanceof EmailService);
    }

    public function testSendTestMail(): void
    {
        $emailService = $this->getMockBuilder(EmailService::class)
            ->onlyMethods(['sendTestEmail'])
            ->getMock();

        $emailService->expects($this->once())
            ->method('sendTestEmail')
            ->with('test1@orangehrm.com')
            ->willReturn(true);

        $this->emailConfigurationService->setEmailService($emailService);
        $result = $this->emailConfigurationService->sendTestMail('test1@orangehrm.com');
        $this->assertEquals(true, $result);
    }

    public function testGetConfigService()
    {
        $this->assertTrue($this->emailService->getConfigService() instanceof ConfigService);
    }

    public function testSetConfigService()
    {
        $this->emailService->setConfigService(new ConfigService());
        $this->assertTrue($this->emailService->getConfigService() instanceof ConfigService);
    }

    public function LoadConfiguration()
    { //TODO:: need to be completed
        $emailConfiguration = new EmailConfiguration();
        $emailConfiguration->setId(1);
        $emailConfiguration->setMailType("smtp");
        $emailConfiguration->setSentAs("test@orangehrm.com");
        $emailConfiguration->setSmtpHost("smtp.gmail.com");
        $emailConfiguration->setSmtpPort(587);
        $emailConfiguration->setSmtpUsername("testUN");
        $emailConfiguration->setSmtpPassword("testPW");
        $emailConfiguration->setSmtpAuthType("login");
        $emailConfiguration->setSmtpSecurityType("tls");

        $emailConfigDao = $this->getMockBuilder(EmailConfigurationDao::class)
            ->onlyMethods(['getEmailConfiguration'])
            ->getMock();

        $emailConfigDao->expects($this->once())
            ->method('getEmailConfiguration')
            ->willReturn($emailConfiguration);

        $emailConfigService = $this->getMockBuilder(EmailConfigurationService::class)
            ->onlyMethods(['getEmailConfigurationDao'])
            ->getMock();

        $emailConfigService->expects($this->once())
            ->method('getEmailConfigurationDao')
            ->willReturn($emailConfigDao);

        $emailService = $this->getMockBuilder(EmailService::class)
            ->onlyMethods(['getEmailConfigurationService', 'getConfigService'])
            ->getMock();

        $emailService->expects($this->once())
            ->method('getEmailConfigurationService')
            ->willReturn($emailConfigService);

        $configService = $this->getMockBuilder(ConfigService::class)
            ->onlyMethods(['getSendmailPath'])
            ->getMock();

        $configService->expects($this->once())
            ->method('getSendmailPath')
            ->willReturn('test path');

        $emailService = $this->getMockBuilder(EmailService::class)
            ->onlyMethods(['getConfigService', 'getEmailConfig'])
            ->getMock();

        $emailService->expects($this->once())
            ->method('getConfigService')
            ->willReturn($configService);

        $this->assertEquals('smtp', $emailService->getEmailConfig()->getMailType());
    }

    public function testGetMailer()
    {
        $transport = new MailTransport('smtp.gmail.com', 587);

        $emailService = $this->getMockBuilder(EmailService::class)
            ->onlyMethods(['getTransport'])
            ->getMock();

        $emailService->expects($this->once())
            ->method('getTransport')
            ->willReturn($transport);
        $this->assertTrue($emailService->getMailer() instanceof Mailer);
    }

    public function GetTransport()
    { //TODO:: need to be completed
        $emailConfiguration = new EmailConfiguration();
        $emailConfiguration->setId(1);
        $emailConfiguration->setMailType("smtp");
        $emailConfiguration->setSentAs("test@orangehrm.com");
        $emailConfiguration->setSmtpHost("smtp.gmail.com");
        $emailConfiguration->setSmtpPort(587);
        $emailConfiguration->setSmtpUsername("testUN");
        $emailConfiguration->setSmtpPassword("testPW");
        $emailConfiguration->setSmtpAuthType("login");
        $emailConfiguration->setSmtpSecurityType("tls");

        $emailService = $this->getMockBuilder(EmailService::class)
            ->onlyMethods(['getEmailConfig'])
            ->getMock();

        $emailService->expects($this->once())
            ->method('getEmailConfig')
            ->willReturn($emailConfiguration);

        $this->assertTrue($emailService->getTransport() instanceof MailTransport);
    }
}
