<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate - GREENTRACK</title>
    <link rel="stylesheet" href="Donation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="LOGO.png" class="logo" />
            <h1>GREENTRACK</h1>
        </div>
        <a href="Homepage.php" class="home-btn">Home</a>
    </header>

    <div class="container">
        <div class="donation-section">
            <h2>Make a Donation</h2>
            <p>Help us plant more trees and protect our environment.</p>

            <div class="donation-form">
                <h3>Select Amount</h3>
                <div class="amount-options">
                    <button class="amount-btn">₱100</button>
                    <button class="amount-btn">₱500</button>
                    <button class="amount-btn">₱1,000</button>
                </div>
                <input type="number" placeholder="Other Amount" class="custom-amount">

                <h3>Payment Method</h3>
                <div class="payment-options">
                    <label>
                        <input type="radio" name="payment" value="gcash">
                        GCash
                    </label>
                    <label>
                        <input type="radio" name="payment" value="paymaya">
                        PayMaya
                    </label>
                    <label>
                        <input type="radio" name="payment" value="paypal">
                        PayPal
                    </label>
                </div>

                <h3>Your Information</h3>
                <input type="text" placeholder="Full Name" required>
                <input type="email" placeholder="Email Address" required>
                <textarea placeholder="Message (Optional)"></textarea>

                <button class="donate-btn">Donate Now</button>
            </div>

            <div class="info-section">
                <div class="info-box">
                    <h3>Why Donate?</h3>
                    <p>Your donation helps us plant more trees.</p>
                </div>
                <div class="info-box">
                    <h3>Impact</h3>
                    <p>Every donation makes a difference.</p>
                </div>
                <div class="info-box">
                    <h3>Security</h3>
                    <p>All transactions are secure.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const amountBtns = document.querySelectorAll('.amount-btn');
            amountBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    amountBtns.forEach(b => b.classList.remove('selected'));
                    btn.classList.add('selected');
                });
            });
        });
    </script>
</body>
</html> 