<?php

namespace XD\Events\Model;

use Page;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\FieldType\DBDatetime;
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

    private static $icon = 'xddesigners/silverstripe-events:client/images/calendar.svg';

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
        $joinTable = Versioned::get_stage() === Versioned::LIVE ? 'EventPage_Live' : 'EventPage';
        $events = EventDateTime::get()->filter([
            'StartDate:GreaterThanOrEqual' => DBDatetime::now()->getValue(),
            'Event.ParentID' => $this->ID
        ])->innerJoin($joinTable, "\"$joinTable\".\"ID\" = \"EventDateTime\".\"EventID\"");

        $this->extend('updateEvents', $events);

        return $events;
    }
}
