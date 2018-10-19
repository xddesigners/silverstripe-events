<?php

namespace XD\Events\Model;

use PageController;
use SilverStripe\ORM\DataObject;

/**
 * Class EventPageController
 * @method EventPage data()
 */
class EventPageController extends PageController
{
    private static $allowed_actions = [
        'date'
    ];

    private static $url_handlers = [
        '$Action/$ID/$StartDate/$EndDate' => 'date'
    ];

    /**
     * @return DataObject|\SilverStripe\ORM\FieldType\DBField|string
     */
    public function getCurrentDate()
    {
        if ($date = DataObject::get_by_id(EventDateTime::class, $this->getRequest()->param('ID'))) {
            return $date;
        } else {
            return $this->data()->getUpcomingDate();
        }
    }
}
