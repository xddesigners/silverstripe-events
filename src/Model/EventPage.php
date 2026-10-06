<?php

namespace XD\Events\Model;

use Page;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\ORM\DB;
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
        'Summary' => 'HTMLText',
        // Combined date + time range (first occurrence) so each event page can be
        // filtered/plotted by the calendar view on the Events tab. Real columns
        // because the calendar view queries them via the ORM; kept in sync from
        // the occurrences (see refreshCalendarRange() / EventDateTime).
        'CalendarStart' => 'Datetime',
        'CalendarEnd' => 'Datetime'
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

    private static $cms_icon_class = 'font-icon-p-event-alt';

    public function getCMSFields()
    {


        $this->beforeUpdateCMSFields(function ($fields) {

            // CalendarStart/CalendarEnd are derived from the occurrences (see
            // refreshCalendarRange()); keep them out of the CMS.
            $fields->removeByName(['Summary','DateTimes','CalendarStart','CalendarEnd']);

            $summary = HTMLEditorField::create('Summary', false);
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

    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->assignCalendarRange();
    }

    /**
     * Set CalendarStart/CalendarEnd (in memory) from the first occurrence so the
     * event can be plotted/filtered by the Events tab calendar view.
     */
    protected function assignCalendarRange(): void
    {
        $first = $this->DateTimes()->sort(['StartDate' => 'ASC', 'StartTime' => 'ASC'])->first();
        if ($first) {
            $this->CalendarStart = $first->getStartDateTime()->getValue();
            $this->CalendarEnd = $first->getEndDateTime()->getValue();
        } else {
            $this->CalendarStart = null;
            $this->CalendarEnd = null;
        }
    }

    /**
     * Recompute and persist the calendar range. Called from EventDateTime after an
     * occurrence is written/deleted (relations save after the parent, so the value
     * has to be refreshed then). Only writes when something actually changed.
     */
    public function refreshCalendarRange(): void
    {
        $before = [$this->CalendarStart, $this->CalendarEnd];
        $this->assignCalendarRange();
        if ([$this->CalendarStart, $this->CalendarEnd] !== $before) {
            $this->write();
        }
    }

    /**
     * All-day flag for the calendar item, from the first occurrence. A getter is
     * fine here because the calendar view reads it as a property, not via the ORM.
     */
    public function getCalendarAllDay(): bool
    {
        $first = $this->DateTimes()->sort(['StartDate' => 'ASC', 'StartTime' => 'ASC'])->first();
        return $first ? (bool) $first->AllDay : false;
    }

    /**
     * Point the calendar item at this event page's CMS edit link instead of the
     * default GridField item link (invoked by the calendar view's data feed).
     */
    public function updateGridFieldCalendarData(&$data): void
    {
        $data['url'] = $this->CMSEditLink();
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        // Backfill the calendar range for events saved before these columns existed.
        $count = 0;
        foreach (EventPage::get()->filter(['CalendarStart' => null]) as $page) {
            if (!$page->DateTimes()->exists()) {
                continue;
            }
            $page->write();
            $count++;
        }

        if ($count > 0) {
            DB::alteration_message("Backfilled CalendarStart/CalendarEnd on {$count} EventPage(s)", 'changed');
        }
    }
}
