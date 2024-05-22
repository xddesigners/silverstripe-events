<?php

namespace XD\Events\Model;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TimeField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Permission;

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
        'AllDay' => 'Boolean',
        'Pinned' => 'Boolean'
    ];

    private static $default_sort = 'Pinned DESC, StartDate ASC, StartTime ASC, EndDate ASC';

    private static $has_one = [
        'Event' => EventPage::class
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
        'AllDay',
        'Pinned',
    ];

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields) {
            $fields->addFieldsToTab('Root.Main', [
                DateField::create('StartDate'),
                DateField::create('EndDate'),
                TimeField::create('StartTime'),
                TimeField::create('EndTime'),
                CheckboxField::create('AllDay'),
                CheckboxField::create('Pinned')
            ]);
        });

        return parent::getCMSFields();
    }

    public function validate()
    {
        $result = parent::validate();
        if ($result->isValid() && empty($this->StartDate)) {
            $result->addError(_t(__CLASS__ . '.StartDateError', 'You need to set a start date'));
        }

        return $result;
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

    public function getStartDateTime()
    {
        $startDate = $this->dbObject('StartDate')->getValue();
        $startTime = $this->dbObject('StartTime')->getValue();
        return DBDatetime::create()->setValue(trim("{$startDate} {$startTime}"));
    }

    public function getEndDateTime()
    {
        $endDate = $this->dbObject('EndDate')->getValue();
        $endTime = $this->dbObject('EndTime')->getValue();
        if (!$endDate) {
            $endDate = $this->dbObject('StartDate')->getValue();
            if ($endTime < $this->dbObject('StartTime')->getValue()) {
                $endDate = date('Y-m-d', strtotime($endDate . ' +1 day'));
            }
        }
        return DBDatetime::create()->setValue(trim("{$endDate} {$endTime}"));
    }

    public function getTimeZone()
    {
        return date_default_timezone_get();
    }

    public function canView($member = null)
    {
        if( $event = $this->Event() ){
            return $event->canView($member);
        }
        return Permission::check('CMS_ACCESS_CMSMain', 'any', $member);
    }

    public function canEdit($member = null)
    {
        if( $event = $this->Event() ){
            return $event->canEdit($member);
        }
        return Permission::check('CMS_ACCESS_CMSMain', 'any', $member);
    }

    public function canDelete($member = null)
    {
        if( $event = $this->Event() ){
            return $event->canDelete($member);
        }
        return Permission::check('CMS_ACCESS_CMSMain', 'any', $member);
    }

    public function canCreate($member = null, $context = [])
    {
        if( $event = $this->Event() ){
            return $event->canCreate($member, $context);
        }
        return Permission::check('CMS_ACCESS_CMSMain', 'any', $member);
    }
    
}
