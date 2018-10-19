<div class="event-summary">
    <header class="event-summary__header">
        <h3><a href="$Link">$Event.Title</a></h3>
        <p class="event-summary__meta">
            <% if $EndDate %>
                <time>$StartDate.Format(d MMM)</time>
                &mdash;
                <time>$EndDate.Nice</time>
            <% else %>
                <time>$StartDate.Nice</time>
            <% end_if %>
        </p>
    </header>
    <section class="event-summary__content">
        <% if $Event.Summary %>
            $Event.Summary
        <% else %>
            <p>$Event.Content.Summary</p>
        <% end_if %>
    </section>
    <footer class="event-summary__footer">
        <a href="$Link" class="button"><%t XD\\Events.ReadMore "Read more" %></a>
    </footer>
</div>
