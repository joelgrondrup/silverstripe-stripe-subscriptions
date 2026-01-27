<section>
    <div id="intro" class="bg-image" style="background: linear-gradient(45deg, #68d0ff, #453cf5 100%); height: 100vh;">
        <div class="mask">
            <div class="container h-100 my-5">
                <div class="row align-items-center text-white">
                    <div class="col-md-12 my-5 mb-0">
                        <h1>$Title</h1>
                        $Content
                        <hr class="my-4" />
                    </div>
                </div>
                <div class="row text-white my-3 mb-5">
                    <div class="col-12">
                        <!--

                            INSERT YOUR CONTENT HERE WHICH IS AVAILABLE TO THE CURRENT USER 

                        -->
                        
                    </div>
                </div>
                <hr class="my-4 text-white" />
                <div class="row text-white">
                    <div class="col-6">
                        <h2>Abonnementer</h2>
                        <% if $CurrentUser %>
                            Welcome back, $CurrentUser.FirstName!
                            Status: $CurrentUser.SubscriptionStatus
                        <% end_if %>
                        <p>Her kan du ændre i dit abonnement.</p>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-warning">Rediger</button>
                        </div>
                    </div>
                    <div class="col-6">
                        <h2>Fakturaer</h2>
                        <p>Her kan du se seneste faktura for dit abonnement.</p>
                        <% if $CurrentUser.LastStripeInvoice %>
                            <div class="invoice-box">
                                <h3>Latest Billing</h3>
                                <p>
                                    <strong>Date:</strong> $CurrentUser.LastStripeInvoice.created.Format('d/m/Y') <br>
                                    <strong>Amount:</strong> $CurrentUser.LastInvoiceAmount DKK <br>
                                    <strong>Status:</strong> $CurrentUser.LastStripeInvoice.status
                                </p>
                                
                                <a href="{$CurrentUser.LastStripeInvoice.hosted_invoice_url}" target="_blank" class="button">
                                    View / Download PDF Invoice
                                </a>
                            </div>
                        <% else %>
                            <p>No recent invoices found.</p>
                        <% end_if %>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary">Se alle</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>