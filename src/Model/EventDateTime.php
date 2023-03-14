<?php

namespace XD\Events\Model;

use SilverStripe\Assets\FileNameFilter;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use XD\Events\GridField\GridFieldConfig_EventDayDateTimes;
use XD\Events\Model\EventDayDateTime;

/**
 * Class EventDateTime
 *
 * @author Bram de Leeuw
 * @package gemeentesecretaris
 *
 * @property string StartDate
 * @property string EndDate
 * @property string StartTime
 * @property string EndTime
 * @property boolean AllDay
 *
 * @method SiteTree Event()
 */
class EventDateTime extends DataObject
{
    private static $table_name = 'EventDateTime';

    private static $db = [
        'StartDate' => 'Date',
        'EndDate' => 'Date',
        'StartTime' => 'Time',
        'EndTime' => 'Time',
        'Pinned' => 'Boolean'
    ];

    private static $default_sort = 'Pinned DESC, StartDate ASC, StartTime ASC, EndDate ASC';

    private static $has_one = [
        'Event' => EventPage::class
    ];

    private static $has_many = [
        'DayDateTimes' => EventDayDateTime::class
    ];

    private static $searchable_fields = [
        'Event.Title' => [
            'title' => 'Event',
            'field' => TextField::class,
            'filter' => 'PartialMatchFilter',
        ],
        'DateAfter' => [
            'title' => 'Datum na',
            'field' => DateField::class,
            'filter' => 'GreaterThanOrEqualFilter',
            'match_any' => [
                'StartDate',
                'EndDate'
            ]
        ],
        'DateBefore' => [
            'title' => 'Datum voor',
            'field' => DateField::class,
            'filter' => 'LessThanOrEqualFilter',
            'match_any' => [
                'Startdatum',
                'EndDate'
            ]
        ]
    ];

    private static $summary_fields = [
        'StartDate',
        'EndDate',
        'StartTime',
        'EndTime',
        'Pinned',
    ];

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields) {
            $fields->addFieldsToTab('Root.Main', [
                CheckboxField::create('Pinned', _t(__CLASS__ . '.Pinned', 'Pinned'))
            ]);

            if ($this->exists()) {
                $fields->addFieldToTab('Root.Main', GridField::create(
                    'DayDateTimes',
                    _t(__CLASS__ . '.DayDateTimes', 'Days'),
                    $this->DayDateTimes(),
                    GridFieldConfig_EventDayDateTimes::create()
                ));
            }
        });

        $fields = parent::getCMSFields();
        $fields->removeByName(['StartDate', 'EndDate', 'StartTime', 'EndTime']);
        return $fields;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        // force change to trigger onAfterWrite()
        $this->LastEdited = DBDatetime::now()->Rfc2822();
        $this->syncDayDateTimesToDateTime();
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        $this->syncDateTimeToDayDateTimes();
    }

    public function validate()
    {
        $result = parent::validate();
        if ($result->isValid() && empty($this->StartDate)) {
            $result->addError(_t(__CLASS__ . '.StartDateError', 'You need to set a start date'));
        }

        return $result;
    }

    public function getStartDatetime() : DBDatetime
    {
        return $this->dateTimeFromParts([
            $this->StartDate,
            $this->StartTime,
        ]);
    }

    public function getEndDatetime() : DBDatetime
    {
        return $this->dateTimeFromParts([
            $this->EndDate,
            $this->EndTime,
        ]);
    }

    protected function dateTimeFromParts(array $parts) : DBDatetime
    {
        $dateTime = DBDatetime::create();
        $dateTimeStr = trim(implode(' ', $parts));
        if ($dateTimeStr) {
            $dateTime->setValue($dateTimeStr);
        }

        return $dateTime;
    }

    public function getTitle()
    {
        $eventTitle = '';
        if ($event = $this->Event()) {
            $eventTitle = $event->getTitle();
        }
        
        if ($this->EndDate) {
            $startDate = $this->dbObject('StartDate')->Format('d MMM');
            $endDate = $this->dbObject('EndDate')->Nice();
            return "{$eventTitle}, {$startDate} — {$endDate}";
        } else {
            return "{$eventTitle}, {$this->dbObject('StartDate')->Nice()}";
        }
    }

    public function Link()
    {
        return Controller::join_links(
            $this->Event()->Link('date'),
            $this->ID,
            $this->dbObject('StartDate')->URLDate(),
            $this->dbObject('EndDate')->URLDate(),
        );
    }

    public function AbsoluteLink()
    {
        return Director::absoluteURL($this->Link());
    }

    public function ics()
    {   
        return $this->renderWith(__CLASS__ . '_ics', [
            'ICSStartDate' => gmdate("Ymd\THis\Z", $this->getStartDatetime()->Time()),
            'ICSEndDate' => gmdate("Ymd\THis\Z", $this->getEndDatetime()->Time()),
            'ICSTimeStamp' => gmdate("Ymd\THis\Z"),
        ]);
    }

    protected function syncDayDateTimesToDateTime()
    {
        $days = $this->DayDateTimes();
        if ($days->exists()) {
            $firstDate = $days->first();
            if ($firstDate && $firstDate->StartDate) {
                $this->StartDate = $firstDate->StartDate;
                $this->StartTime = $firstDate->StartTime;
                $this->EndTime = $firstDate->EndTime;
            }

            $lastDate = $days->last();
            if ($lastDate && $firstDate->ID !== $lastDate->ID) {
                $this->EndDate = $lastDate->StartTime;
                $this->EndTime = $lastDate->EndTime;
            }
        }
    }

    protected function syncDateTimeToDayDateTimes()
    {
        $days = $this->DayDateTimes();
        // auto create first day
        if (!$days->exists() && $this->StartDate) {
            EventDayDateTime::create([
                'EventDateTimeID' => $this->ID,
                'StartDate' => $this->StartDate,
                'StartTime' => $this->StartTime,
                'EndTime' => $this->EndTime
            ])->write();
        }
    }
}
