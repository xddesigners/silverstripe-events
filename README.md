# Simple events calendar for SilverStripe

This module provides an EventsPage where you can add Events to.
An event can have multiple dates so the same event can occur in your list at multiple dates.

## Installation
Install the module trough composer `composer require xddesigners/silverstripe-events`

## Calendar view

The Events holder (`EventsPage`) has a **Calendar** tab in the CMS, next to the Main tab,
showing all event occurrences on a month calendar instead of a flat list. It is powered by
[`webbuilders-group/silverstripe-gridfield-calendar-view`](https://github.com/webbuilders-group/silverstripe-gridfield-calendar-view)
and is enabled automatically when that module is installed (it ships as a dependency).

Each `EventPage` keeps two derived `Datetime` columns, `CalendarStart` and `CalendarEnd`, that
the calendar queries via the ORM. They are kept in sync automatically from the event's first
occurrence (`DateTimes()`):

- recomputed in `EventPage::onBeforeWrite()` whenever the page is saved;
- refreshed from `EventDateTime` after an occurrence is added, changed or removed (relations
  save after the parent, so the range is updated then);
- backfilled on `dev/build` for events created before these columns existed.

Calendar items link straight to the event page's CMS edit screen, carry an all-day flag derived
from the first occurrence, and the view is rendered full-width. Occurrences belong to a specific
event, so the holder's calendar allows editing/deleting existing dates but not adding orphan ones.

If the calendar-view module is not installed the tab is simply omitted; the rest of the module
works unchanged.

###Maintainers
- [XD designers](https://www.xd.nl/)
- [Bram de Leeuw](https://www.twitter.com/bramdeleeuw)
