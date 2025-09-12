<?php

namespace XD\Events\Model;

use Page;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\HasManyList;
use XD\Events\Form\EventDateTimeGridField;

/**
 * Class EventPage
 *
 * @author Bram de Leeuw
 * @package XD\Events\Model
 *
 * @property string Summary
 * @method Image FeaturedImage()
 * @method HasManyList DateTimes()
 */
class EventPage extends Page
{
    private static $table_name = 'EventPage';

    private static $db = [
        'Summary' => 'HTMLText'
    ];

    private static $default_sort = "Created DESC";

    private static $has_one = [
        'FeaturedImage' => Image::class
    ];

    private static $owns = [
        'FeaturedImage'
    ];

    private static $defaults = [
        'ShowInMenus' => false,
        'InheritSideBar' => true
    ];

    private static $has_many = [
        'DateTimes' => EventDateTime::class
    ];

    private static $summary_fields = [
        'Title',
        'StartDate' => 'Date'
    ];

    private static $casting = [
        'UpcomingStartDate' => 'DBDatetime',
        'StartDate' => 'DBDatetime',
    ];

    private static $can_be_root = false;

    private static $show_in_sitetree = false;

    private static $allowed_children = [];

    private static $icon = 'xddesigners/silverstripe-events:client/images/calendar-event.svg';

    public function getCMSFields()
    {


        $this->beforeUpdateCMSFields(function ($fields) {

            $fields->removeByName(['Summary','DateTimes']);

            $summary = HtmlEditorField::create('Summary', false);
            $summary->setRows(5);
            $summary->setDescription(_t(
                __CLASS__ . '.SummaryDescription',
                'If no summary is specified the first 30 words will be used.'
            ));

            $summaryHolder = ToggleCompositeField::create(
                'CustomSummary',
                _t(__CLASS__ . '.CustomSummary', 'Add A Custom Summary'), [
                    $summary
                ]
            )->setHeadingLevel(4)->addExtraClass('custom-summary');

            $uploadField = UploadField::create('FeaturedImage', _t(__CLASS__ . '.FeaturedImage', 'Featured Image'));
            $uploadField->getValidator()->setAllowedExtensions(['jpg', 'jpeg', 'png', 'gif']);

            $fields->insertBefore('Metadata', $uploadField );
            $fields->insertBefore('Metadata', $summaryHolder );

            $dateTimesDescription = _t(__CLASS__ . '.DateTimesDescription', 'You can add multiple dates for a event.');
            $fields->addFieldsToTab('Root.Date', [
                GridField::create('DateTimes', 'DateTimes', $this->DateTimes()->sort('StartDate DESC'), EventDateTimeGridField::create()),
                LiteralField::create('DateTimesDescription', "<p class='description'>{$dateTimesDescription}</p>")
            ]);

        });

        return parent::getCMSFields();
    }

    /**
     * Get the upcoming date
     * Used in the grid field summary
     *
     * @return \SilverStripe\ORM\FieldType\DBField|string
     */
    public function getUpcomingDate()
    {
        return $this->getUpcomingDates()->first();
    }

    public function getUpcomingDates()
    {
        return EventDateTime::get()->filter([
            'EventID' => $this->owner->ID,
            'StartDate:GreaterThanOrEqual' => DBDatetime::now()->getValue()
        ]);
    }

    public function getUpcomingStartDate()
    {
        if ($date = $this->getUpcomingDate()) {
            return $date->dbObject('StartDate');
        }

        return _t(__CLASS__ . '.NoUpcomingDates', 'No upcoming dates');
    }

    public function getStartDate()
    {
        if ($recentDate = $this->DateTimes()->first()) {
            return $recentDate->dbObject('StartDate');
        }

        return _t(__CLASS__ . '.NoStartDates', 'No start date');
    }
}
