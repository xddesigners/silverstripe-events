<section class="event-content">
    <article class="grid-container">
        <header class="grid-x grid-padding-x">
            <div class="cell">
                <h1>$Title</h1>
            </div>
        </header>
        <section class="grid-x grid-padding-x page-content">
            <div class="cell">
                $Content
                $Form
            </div>
        </section>
    </article>
</section>
<section class="event-list">
    <div class="grid-container">
        <div class="grid-x grid-padding-x">
            <div class="cell">
                <% if $PaginatedList %>
                    <ul class="news-events-list news-events-list--news">
                        <% loop $PaginatedList %>
                            <li class="news-events-list__item">
                                <% include XD\\Events\\EventSummary %>
                            </li>
                        <% end_loop %>
                    </ul>
                    <% with $PaginatedList %>
                        <% include XD\\Events\\Pagination %>
                    <% end_with %>
                <% end_if %>
            </div>
        </div>
    </div>
</section>