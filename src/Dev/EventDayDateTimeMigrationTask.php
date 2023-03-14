<?php

namespace XD\Events\Dev;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Dev\MigrationTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use XD\Events\Model\EventDateTime;

class EventDayDateTimeMigrationTask extends MigrationTask
{
    private static $segment = 'event-day-date-time-migration-task';
    
    protected $title = 'Migrate EventDayDateTime table name';
    
    protected $description = 'Migrate EventDayDateTime from AttendableEvents to Events module.';

    public function run($request)
    {
        if ($request && $request->getVar('direction') == 'down') {
            $this->down();
        } else {
            $this->up();
        }
    }

    // upgrade to new version
    public function up()
    {
        $this->changeTable('AttendableEvents_EventDayDateTime', 'EventDayDateTime');
    }

    // Revert to old version
    public function down()
    {
        $this->changeTable('EventDayDateTime', 'AttendableEvents_EventDayDateTime');
    }

    protected function changeTable($from, $to)
    {
        if (ClassInfo::hasTable($to)) {
            if (DB::query("SELECT 1 FROM $to")->numRecords()) {
                return;
            }

            // empty so drop table
            DB::query("DROP TABLE $to");
        }

        if (!ClassInfo::hasTable($from)) {
            $from = '_obsolete_' . $from;
        }

        if (!ClassInfo::hasTable($from)) {
            return;
        }

        DB::get_conn()->withTransaction(function() use ($from, $to) {
            DB::query("ALTER TABLE $from RENAME TO $to");
            DB::get_schema()->alterationMessage("Altered table '$from' renamed to '$to'", 'changed');
        } , function () use ($from) {
            DB::get_schema()->alterationMessage("Failed to alter table '$from'", 'error');
        }, false, true);
    }
}
