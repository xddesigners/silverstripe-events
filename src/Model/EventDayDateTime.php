<?php

namespace XD\Events\Model;

use SilverStripe\Forms\FieldGroup;
use SilverStripe\ORM\DataObject;

class EventDayDateTime extends DataObject
{
    private static $table_name = 'EventDayDateTime';

    private static $db = [
        'StartDate' => 'Date',
        'StartTime' => 'Time',
        'EndTime' => 'Time',
    ];

    private static $has_one = [
        'EventDateTime' => EventDateTime::class
    ];

    private static $summary_fields = [
        'StartDate',
        'StartTime',
        'EndTime',
    ];

    private static $default_sort = 'StartDate ASC, StartTime ASC, EndTime ASC';

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields) {
            $group = FieldGroup::create([
                $fields->dataFieldByName('StartTime'),
                $fields->dataFieldByName('EndTime')
            ]);

            $fields->removeByName(['StartTime','EndTime','EventDateTimeID']);
            $fields->insertAfter('StartDate', $group);
        });

        return parent::getCMSFields();
    }
}
