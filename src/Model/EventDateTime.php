<?php

namespace XD\Events\Model;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\TimeField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\FieldType\DBDatetime;

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
        'Pinned' => 'Boolean',
        'PinnedForever' => 'Boolean'
    ];

    private static $default_sort = 'Pinned DESC, PinnedForever DESC, StartDate ASC, StartTime ASC, EndDate ASC';

    private static $has_one = [
        'Event' => EventPage::class
    ];

    private static $summary_fields = [
        'StartDate',
        'EndDate',
        'StartTime',
        'EndTime',
        'AllDay',
        'Pinned',
        'PinnedForever' => 'Forever',
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
                CheckboxField::create('Pinned'),
                CheckboxField::create('PinnedForever'),
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

    public function Link()
    {
        return Controller::join_links(
            $this->Event()->Link('date'),
            $this->ID,
            $this->dbObject('StartDate')->URLDate(),
            $this->dbObject('EndDate')->URLDate()
        );
    }

    public function AbsoluteLink()
    {
        return Director::absoluteURL($this->Link());
    }
}
