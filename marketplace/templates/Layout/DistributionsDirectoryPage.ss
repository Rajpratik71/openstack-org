<div class="grey-bar">
    <div class="container">
        <p class="filter-label">Filter Products</p>
        <input type="text" placeholder="ANY NAME" name="name-term" id="name-term" value="{$Keyword}">
        $ServicesCombo
    </div>
</div>
<div class="container">
    <div id="implementation-list" class="col-lg-8 col-md-8 col-sm-8">
        <% if getImplementations %>
            <% loop getImplementations %>
                <% include DistributionsDirectoryPage_ImplementationBox ApplianceLink=$Top.Link(appliance),  DistroLink=$Top.Link(distribution) %>
            <% end_loop %>
        <% else %>
            &nbsp;
        <% end_if %>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-4">
        <% include MarketPlaceHelpLinks %>
        <div class="add-your-course">
            <p>
                Does your company offer distributions or appliances for OpenStack? Be listed here!
                <a href="mailto:ecosystem@openstack.org">Email us for details</a>
            </p>
        </div>
    </div>
</div>
