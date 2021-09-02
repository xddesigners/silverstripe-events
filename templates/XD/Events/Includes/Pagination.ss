<% if $MoreThanOnePage %>
    <ul class="pagination">
        <% if $NotFirstPage %>
            <li class="pagination__item pagination__item--link pagination__item--prev">
                <a href="{$PrevLink}" class="pagination__arrow pagination__arrow--left button small white">
                    <i class="fas fa-angle-left"></i>
                </a>
            </li>
        <% else %>
            <li class="pagination__item pagination__item--current pagination__item--prev">
                <span class="pagination__arrow pagination__arrow--current pagination__arrow--left button small white">
                    <i class="fas fa-angle-left"></i>
                </span>
            </li>
        <% end_if %>

        <% loop $PaginationSummary(3) %>
            <% if $CurrentBool %>
                <li class="pagination__item pagination__item--current">
                    <span class="button small">$PageNum</span>
                </li>
            <% else %>
                <% if $Link %>
                    <li class="pagination__item pagination__item--link">
                        <a href="$Link" class=" button small white">$PageNum</a>
                    </li>
                <% else %>
                    <li class="pagination__item pagination__item--hellip">
                        <span class="button small white">&hellip;</span>
                    </li>
                <% end_if %>
            <% end_if %>
        <% end_loop %>

        <% if $NotLastPage %>
            <li class="pagination__item pagination__item--link pagination__item--next">
                <a href="{$NextLink}" class="pagination__arrow pagination__arrow--right button small white">
                    <i class="fas fa-angle-right"></i>
                </a>
            </li>
        <% else %>
            <li class="pagination__item pagination__item--current pagination__item--next">
                <span class="pagination__arrow pagination__arrow--current pagination__arrow--right button small white">
                    <i class="fas fa-angle-right"></i>
                </span>
            </li>
        <% end_if %>
    </ul>
<% end_if %>
