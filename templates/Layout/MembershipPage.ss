<div class="pricing-container">
    <% loop $Plans %>
        <div class="plan-card">
            <h3>$Title</h3>
            <p class="price">$Price.Nice</p>
            <div class="description">$Description</div>
            
            <form action="{$Up.Link('checkout')}" method="GET">
                <input type="hidden" name="plan" value="$ID" />
                <% if not $CurrentUser %>
                    <input type="email" name="email" placeholder="Your Email" required />
                <% end_if %>
                <button type="submit">Subscribe Now</button>
            </form>
        </div>
    <% end_loop %>
</div>