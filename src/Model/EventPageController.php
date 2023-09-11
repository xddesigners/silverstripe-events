<?php

namespace XD\Events\Model;

use PageController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\DataObject;
use XD\Events\Extensions\HasICSFeed;

/**
 * Class EventPageController
 * @method EventPage data()
 */
class EventPageController extends PageController
{
    private static $allowed_actions = [
        'date',
    ];

    private static $url_handlers = [
        'date/$ID/$StartDate/$EndDate' => 'date'
    ];

    private static $extensions = [
        HasICSFeed::class
    ];

    /**
     * @return DataObject|\SilverStripe\ORM\FieldType\DBField|string
     */
    public function getCurrentDate()
    {
        if ($date = DataObject::get_by_id(EventDateTime::class, $this->getRequest()->param('ID'))) {
            return $date;
        } elseif ($date = $this->data()->getUpcomingDate()) {
            return $date;
        } else {
            return EventDateTime::get()->filter([
                'EventID' => $this->ID,
            ])->first();
        }
    }
}
