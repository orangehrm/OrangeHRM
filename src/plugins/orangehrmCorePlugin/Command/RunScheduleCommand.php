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
 * Boston, MA 02110-1301, USA
 */

namespace OrangeHRM\Core\Command;

use DateTimeZone;
use OrangeHRM\Config\Config;
use OrangeHRM\Core\Service\DateTimeHelperService;
use OrangeHRM\Framework\Console\Command;
use OrangeHRM\Framework\Console\Scheduling\Schedule;
use OrangeHRM\Framework\Console\Scheduling\SchedulerConfigurationInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunScheduleCommand extends Command
{
    /**
     * @inheritDoc
     */
    public function getCommandName(): string
    {
        return 'orangehrm:run-schedule';
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pluginConfigs = Config::get('ohrm_plugin_configs');
        $schedule = new Schedule($this->getApplication(), $output);
        foreach (array_values($pluginConfigs) as $pluginConfig) {
            $configClass = new $pluginConfig['classname']();
            if ($configClass instanceof SchedulerConfigurationInterface) {
                $configClass->schedule($schedule);
            }
        }

        $this->getIO()->note('Time: ' . date('Y-m-d H:i'));

        $schedule->setTasks($schedule->getDueTasks(new DateTimeZone(DateTimeHelperService::TIMEZONE_UTC)));
        $this->getIO()->note('Event count: ' . count($schedule->getTasks()));

        foreach ($schedule->getTasks() as $task) {
            $this->getIO()->note('Exit code: ' . $task->start());
        }

        $this->getIO()->success('Success');
        return self::SUCCESS;
    }
}
