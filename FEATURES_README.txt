CAR RENTAL PORTAL — NEW FEATURES ADDED
========================================

6 new features added on top of the original PHPGurukul Car Rental Portal:

1. ONLINE PAYMENT (simulated Razorpay/Stripe-style checkout)
   - After booking a car, you're taken to payment.php instead of straight
     to My Bookings.
   - Shows an order summary (car, dates, amount) and a "Pay Now" button
     with Card / UPI / Net Banking options.
   - IMPORTANT: This is a SIMULATION for demo purposes — no real payment
     gateway (Razorpay/Stripe) is called, and no money moves. It fakes a
     transaction ID and marks the booking as paid, exactly the way a real
     gateway callback would, so the flow can be demonstrated end-to-end
     without needing live API keys.

2. INVOICE / PDF DOWNLOAD
   - invoice.php shows a clean, printable invoice for any booking.
   - Click "Print / Save as PDF" — this uses the browser's native print
     dialog (Ctrl+P / Cmd+P -> Save as PDF), so no extra PHP PDF library
     is required.
   - Linked from My Bookings ("Download Invoice" button, once paid).

3. REVIEWS & RATINGS
   - On each vehicle's details page, logged-in users can leave a 1-5 star
     rating with a written review.
   - Average rating (stars) shows on both the car listing page and the
     vehicle details page.

4. BOOKING CONFIRMATION NOTIFICATIONS (simulated Email + SMS)
   - When payment succeeds, a confirmation "email" and "SMS" are logged
     to the new tblnotifications table.
   - NOTE: Local XAMPP has no real mail/SMS gateway configured by default,
     so these are logged/simulated rather than actually delivered. This
     keeps the feature demonstrable without needing SMTP or a paid SMS API.

5. CAR AVAILABILITY CALENDAR
   - On the vehicle details page, a small calendar shows the current
     month with already-booked dates in red and free dates in green,
     based on real existing bookings for that car.

6. WISHLIST / FAVOURITES
   - A heart icon on the car listing page and vehicle details page lets
     a logged-in user save/unsave a car.
   - "My Wishlist" (new link in the account sidebar) lists all saved cars.


SETUP STEPS (XAMPP)
====================
1. Copy the "carrental" folder into your XAMPP htdocs folder, same as
   the original project.

2. Start Apache and MySQL in XAMPP.

3. Open phpMyAdmin -> Import:
   a) First import "SQL File/carrental.sql" (the original database) —
      skip this step if you already have the carrental database set up.
   b) Then import "SQL File/feature_updates.sql" — this adds the new
      tables and columns needed for the 6 features above. This step is
      required even if you already had the project running before.

4. Open http://localhost/carrental in your browser.
   Test login: test@gmail.com / Test@123

5. Try it end-to-end:
   - Go to Car Listing -> tap the heart icon to wishlist a car.
   - Open a car's details page -> see the availability calendar and
     reviews section -> submit a review.
   - Book the car -> you'll land on the simulated payment page ->
     choose a payment method -> Pay Now.
   - Go to My Bookings -> see PAID status -> Download Invoice.


FILES ADDED / CHANGED
======================
New files:
  carrental/payment.php
  carrental/invoice.php
  carrental/wishlist-action.php
  carrental/my-wishlist.php
  carrental/review-submit.php
  SQL File/feature_updates.sql

Modified files:
  carrental/vehical-details.php   (calendar, reviews, wishlist heart, payment redirect)
  carrental/car-listing.php       (rating stars, wishlist heart)
  carrental/my-booking.php        (payment status, Pay Now / Download Invoice buttons)
  carrental/includes/sidebar.php  (added "My Wishlist" link)

No admin files were changed — the admin panel works exactly as before.
