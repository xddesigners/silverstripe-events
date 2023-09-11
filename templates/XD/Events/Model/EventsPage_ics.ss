BEGIN:VCALENDAR
PRODID:-//{$Host}//{$ContentLocale}
VERSION:2.0
METHOD:PUBLISH
<% loop $UpcomingEvents %><% include XD\\Events\\EventDateTimeICS %><% end_loop %>
END:VCALENDAR