<?php

namespace XD\Events\Model;

use Page;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
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
        'UpcomingStartDate' => 'Upcoming date'
    ];

    private static $casting = [
        'UpcomingStartDate' => 'DBDatetime'
    ];

    private static $can_be_root = false;

    private static $show_in_sitetree = false;

    private static $allowed_children = [];

    private static $icon = 'xddesigners/silverstripe-events:client/images/calendar-event.svg';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
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

        $fields->addFieldsToTab('Root.Date', [
            GridField::create('DateTimes', 'DateTimes', $this->DateTimes()->sort('StartDate DESC'), EventDateTimeGridField::create())
                ->setDescription(_t(__CLASS__ . '.DateTimesDescription', 'You can add multiple dates for a event.'))
        ]);

        return $fields;
    }

    /**
     * Remove all date times if the page is deleted
     */
    public function onBeforeArchive()
    {
        parent::onBeforeArchive();
        $this->DateTimes()->removeAll();
    }

    /**
     * Get the upcoming date
     * Used in the grid field summary
     *
     * @return \SilverStripe\ORM\FieldType\DBField|string
     */
    public function getUpcomingDate()
    {
        return EventDateTime::get()->filter([
            'EventID' => $this->ID,
            'StartDate:GreaterThanOrEqual' => DBDatetime::now()->getValue()
        ])->first();
    }

    public function getUpcomingStartDate()
    {
        if ($date = $this->getUpcomingDate()) {
            return $date->dbObject('StartDate');
        }

        return _t(__CLASS__ . '.NoUpcomingDates', 'No upcoming dates');
    }
}
