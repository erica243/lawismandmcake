<!-- Masthead-->
<header class="masthead">
    <div class="container h-100">
        <div class="row h-100 align-items-center justify-content-center text-center">
            <div class="col-lg-10 align-self-center mb-4 page-title">
                <h1 class="text-white">About Us</h1>
                <hr class="divider my-4 bg-dark" />
            </div>
        </div>
    </div>
</header>

<section class="page-section">
    <div class="container">
        <!-- About Us content -->
        <?php echo html_entity_decode($_SESSION['setting_about_content']); ?>

        <!-- Contact Information -->
        <div class="contact-info mt-5">
            <h2>Contact the Owner</h2>
            <p> 09158259643</p>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Location:</strong> Poblacion Madridejos Cebu</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Email:</strong> <a href="mailto:mandmcakeorderingsystem@gmail.com">mandmcakeorderingsystem@gmail.com</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
