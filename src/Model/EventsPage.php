<?php

namespace XD\Events\Model;

use Page;
use SilverStripe\Forms\NumericField;
use SilverStripe\Versioned\Versioned;

/**
 * Class EventsPage
 *
 * @author Bram de Leeuw
 * @package XD\Events\Model
 *
 * @property int PostsPerPage
 */
class EventsPage extends Page
{
    private static $table_name = 'EventsPage';

    private static $db = [
        'PostsPerPage' => 'Int'
    ];

    private static $defaults = [
        'PostsPerPage'    => 10
    ];

    private static $allowed_children = [
        EventPage::class,
    ];

    private static $description = 'Add events to your website.';

    private static $cms_icon_class = 'font-icon-calendar';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Add a "Calendar" tab (second, right after Main) with a month calendar of
        // all date/time occurrences belonging to this holder's events.
        if (class_exists(GridFieldCalendarView::class)) {
            $dateTimes = EventDateTime::get()->filter(['Event.ParentID' => $this->ID]);

            $config = GridFieldConfig_RecordEditor::create();
            // Occurrences belong to a specific EventPage, so don't allow orphan
            // "add new" from the holder — edit/delete existing ones is fine.
            $config->removeComponentsByType(GridFieldAddNewButton::class);

            $calendar = GridFieldCalendarView::create('CalendarStart', 'CalendarEnd');
            $calendar->setTitleField('Title');
            $calendar->setAllDayField('AllDay');
            $calendar->setDefaultView('calendar');
            $calendar->setCustomOptions(['height' => 'auto']); // expand to content (module's fixed box is overridden by CSS below)
            $config->addComponent($calendar);

            $grid = GridField::create('CalendarView', _t(__CLASS__ . '.CalendarTab', 'Calendar'), $dateTimes, $config);

            $fields->insertAfter('Main', Tab::create('Calendar', _t(__CLASS__ . '.CalendarTab', 'Calendar'), $grid));

            Requirements::customCSS(EventDateTime::CALENDAR_VIEW_CSS, 'xd-events-calendar-fullwidth');
        }

        return $fields;
    }

    public function getSettingsFields()
    {
        $fields = parent::getSettingsFields();
        $fields->addFieldToTab(
            'Root.Settings',
            NumericField::create('PostsPerPage', _t(__CLASS__ . '.EventsPerPage', 'Events per page'))
        );

        return $fields;
    }

    /**
     * Get the upcoming events
     *
     * @return \SilverStripe\ORM\DataList
     */
    public function getUpcomingEvents()
    {
        $now = date('Y-m-d');
        $joinTable = Versioned::get_stage() === Versioned::LIVE ? 'EventPage_Live' : 'EventPage';
        $events = EventDateTime::get()
            ->filter(['Event.ParentID' => $this->ID,])
            ->where("(\"StartDate\" >= '$now') OR (\"StartDate\" <= '$now' AND \"EndDate\" >= '$now')")
            ->innerJoin($joinTable, "\"$joinTable\".\"ID\" = \"EventDateTime\".\"EventID\"");

        $this->extend('updateEvents', $events);

        return $events;
    }

    public function getPastEvents()
    {
        $now = date('Y-m-d');
        $joinTable = Versioned::get_stage() === Versioned::LIVE ? 'EventPage_Live' : 'EventPage';
        $events = EventDateTime::get()
            ->filter(['Event.ParentID' => $this->ID,])
            ->where("(\"StartDate\" < '$now') OR (\"StartDate\" < '$now' AND \"EndDate\" < '$now')")
            ->innerJoin($joinTable, "\"$joinTable\".\"ID\" = \"EventDateTime\".\"EventID\"");

        $this->extend('updatePastEvents', $events);

        return $events;
    }

}
