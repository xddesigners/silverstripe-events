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
     * Strict routing for the date action: only a numeric EventDateTime id and
     * valid Y-m-d start/end dates are accepted. Anything else returns a 404
     * instead of silently rendering the event page, which avoids path
     * manipulation false-positives from scanners appending segments such as
     * /admin/ or /APIs/ to the date route.
     *
     * @param HTTPRequest $request
     * @return array|\SilverStripe\Control\HTTPResponse
     */
    public function date(HTTPRequest $request)
    {
        $id = $request->param('ID');
        if ($id !== null && $id !== '' && !ctype_digit((string) $id)) {
            return $this->httpError(404);
        }

        foreach (['StartDate', 'EndDate'] as $param) {
            $value = $request->param($param);
            if ($value !== null && $value !== '' && !$this->isValidIsoDate($value)) {
                return $this->httpError(404);
            }
        }

        return [];
    }

    /**
     * @param string $value
     * @return bool
     */
    private function isValidIsoDate($value)
    {
        $date = \DateTime::createFromFormat('Y-m-d', (string) $value);
        return $date && $date->format('Y-m-d') === (string) $value;
    }

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
