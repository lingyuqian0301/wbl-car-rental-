<footer style="background:#991b1b; color:#ffffff; margin:0; padding:0;">

    <!-- Review CTA (Newsletter-style layout) -->
    <div style="
        max-width:1280px;
        margin:0 auto;
        padding:3rem 2rem;
        display:grid;
        grid-template-columns:1.4fr 2fr;
        gap:3rem;
        border-bottom:1px solid #7f1d1d;
    ">

        <!-- Left: Review Message -->
        <div>
            <p style="
                font-size:0.9rem;
                color:#fecaca;
                margin-bottom:0.4rem;
                letter-spacing:0.02em;
            ">
                Stay connected with HASTA
            </p>

            <h2 style="
                font-size:2rem;
                font-weight:700;
                line-height:1.2;
                margin-bottom:1.5rem;
                color:#ffffff;
            ">
                Love your ride?<br>
                Share a quick review.
            </h2>

            <a href="https://www.google.com/search?sca_esv=189c82b39954af99&sxsrf=ANbL-n5Anp80h8dYhG2xKm29JoOjA_C3Zw:1767841767647&si=AL3DRZEsmMGCryMMFSHJ3StBhOdZ2-6yYkXd_doETEE1OR-qOXyCFa9BmMH0fGKt5MubrOT1JEHrQ0TPniYENBBGrBFLfRgjvbeReC2xOMTT6mEGYvM8guDbTO_ry31RsTNkKyT8Hj1GpBJ4BLResCU80OD7zcPEYjfWprqYwQS0Pm9kcyxNIc0h9S3iNQthbDiEjoEq5TTA&q=Hasta+Travel+%26+Tours+Sdn+Bhd+%28Car+Rental+UTM,+Johor%29+Reviews"
               target="_blank"
               style="
                    display:inline-block;
                    background:#ffffff;
                    color:#991b1b;
                    font-weight:700;
                    font-size:0.9rem;
                    padding:0.7rem 1.8rem;
                    border-radius:0.6rem;
                    text-decoration:none;
               ">
                Share Your Review
            </a>
        </div>

        <!-- Right: Footer Links -->
        <div style="
            display:grid;
            grid-template-columns:repeat(3,1fr);
            gap:2rem;
        ">
            <div>
                <h4 style="margin-bottom:0.8rem;">HASTA</h4>
                <ul style="list-style:none; padding:0; font-size:0.85rem;">
                    <li><a href="/" style="color:#fee2e2; text-decoration:none;">Home</a></li>
                    <li><a href="/bookings" style="color:#fee2e2; text-decoration:none;">My Bookings</a></li>
                    <li><a href="<?php echo e(auth()->check() ? route('loyalty.show') : route('login')); ?>" style="color:#fee2e2; text-decoration:none;">Loyalty Card</a></li>
                </ul>
            </div>

            <div>
                <h4 style="margin-bottom:0.8rem;">Support</h4>
                <ul style="list-style:none; padding:0; font-size:0.85rem;">
                    <li><a href="#" style="color:#fee2e2; text-decoration:none;">Contact Us</a></li>
                    <li><a href="#" style="color:#fee2e2; text-decoration:none;">Terms & Conditions</a></li>
                    <li><a href="#" style="color:#fee2e2; text-decoration:none;">Privacy Policy</a></li>
                </ul>
            </div>

            <div>
                <h4 style="margin-bottom:0.8rem;">Contact</h4>
                <p style="font-size:0.85rem; color:#fee2e2;">📍 Malaysia</p>
                <p style="font-size:0.85rem; color:#fee2e2;">✉ support@hasta.com</p>
                <p style="font-size:0.85rem; color:#fee2e2;">☎ +60 12-345 6789</p>
            </div>
        </div>

    </div>

    <!-- Bottom Bar -->
    <div style="
        text-align:center;
        padding:1rem;
        font-size:0.8rem;
        color:#fecaca;
        border-top:1px solid #7f1d1d;
    ">
        © <?php echo e(date('Y')); ?> HASTA Travel. All rights reserved.
    </div>

</footer>
<?php /**PATH C:\xampp\htdocs\wbl-car-rental-\resources\views/components/footer.blade.php ENDPATH**/ ?>