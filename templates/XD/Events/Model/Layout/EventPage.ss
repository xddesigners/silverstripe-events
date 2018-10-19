<section class="event">
    <article class="grid-container">
        <header class="grid-x grid-padding-x">
            <div class="cell">
                <h1>$Title</h1>
                <p class="event__meta">
                    <% with $CurrentDate %>
                    <% if $EndDate %>
                        <time>$StartDate.Format(d MMM)</time>
                        &mdash;
                        <time>$EndDate.Nice</time>
                    <% else %>
                        <time>$StartDate.Nice</time>
                    <% end_if %>
                    <% end_with %>
                </p>
            </div>
        </header>
        <section class="grid-x grid-padding-x event-content">
            <div class="cell">
                $Content
                $Form
            </div>
        </section>
    </article>
</section>