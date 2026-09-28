<?php

namespace XD\Events\Model;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\Tab;
use SilverStripe\Lumberjack\Model\Lumberjack;
use SilverStripe\View\Requirements;
use WebbuildersGroup\GridFieldCalendarView\Forms\GridField\GridFieldCalendarView;

/**
 * This class is responsible for filtering the SiteTree when necessary and also overlaps into
 * filtering only published posts.
 */
class EventsLumberjack extends Lumberjack
{
    public function updateCMSFields(FieldList $fields)
    {
        // todo get by filter upcoming
        $pages = EventPage::get()->filter([
            'ParentID' => $this->owner->ID
        ]);

        $config = $this->getLumberjackGridFieldConfig();

        // Add a calendar view toggle to the Events list. The list view stays exactly
        // the same; the toggle adds a full-width month calendar plotting each event
        // page (by its first occurrence). Default view stays the list.
        if (class_exists(GridFieldCalendarView::class)) {
            $calendar = GridFieldCalendarView::create('CalendarStart', 'CalendarEnd');
            $calendar->setTitleField('Title');
            $calendar->setAllDayField('CalendarAllDay');
            $calendar->setCustomOptions(['height' => 'auto']);
            $config->addComponent($calendar);

            Requirements::customCSS(EventDateTime::CALENDAR_VIEW_CSS, 'xd-events-calendar-fullwidth');
            Requirements::customScript(EventDateTime::CALENDAR_VIEW_JS, 'xd-events-calendar-resize');
        }

        $gridField = GridField::create(
            'ChildPages',
            $this->getLumberjackTitle(),
            $pages,
            $config
        );

        $tab = Tab::create('ChildPages', $this->getLumberjackTitle(), $gridField);

        $fields->insertBefore('Main', $tab);
    }

    protected function getLumberjackTitle()
    {
        return _t(self::class . '.TabTitle', 'Events');
    }
}
