<?php

declare(strict_types=1);

namespace QAlliance\CrontabManager;

use QAlliance\CrontabManager\CommandLine\Crontab;

/**
 * Writer.
 *
 * @author Ante Crnogorac <ante@q-software.com>
 * @author Mario Blazek <mario.b@netgen.hr>
 */
class Writer extends CrontabAware
{
    public const PLACEHOLDER_STRING = '{PLACEHOLDER}';

    /** @var Reader */
    private $reader;

    private $template = '
#CTMSTART
{PLACEHOLDER}
#CTMEND
';

    public function __construct(Reader $reader, Crontab $crontab)
    {
        parent::__construct($crontab);
        $this->reader = $reader;
    }

    public function updateManagedCrontab(array $newCronJobs): bool
    {
        $crontab = $this->reader->getCrontabAsString();

        if ($this->reader->hasManagedBlock()) {
            $managedCrontabJobs = $this->reader->getManagedCronJobsAsString();
            $crontab = $this->removeManagedCronJobs($crontab, $managedCrontabJobs);
        } else {
            $crontab .= $this->template;
        }

        $newCronJobsAsString = PHP_EOL . implode(PHP_EOL, $newCronJobs) . PHP_EOL;

        $crontab = str_replace(
            self::PLACEHOLDER_STRING,
            $newCronJobsAsString,
            $crontab
        );

        return $this->writeToCrontab($crontab);
    }

    protected function removeManagedCronJobs($crontab, $managedCrontabJobs): string
    {
        return str_replace(
            $managedCrontabJobs,
            self::PLACEHOLDER_STRING,
            $crontab
        );
    }

    private function writeToCrontab($crontab): bool
    {
        return $this->crontab->write($crontab);
    }
}
