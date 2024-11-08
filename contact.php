<?php
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $message = htmlspecialchars($_POST['message']);

    $to = "contact.zcsco@gmail.com";
    $subject = "New Contact Form Submission";
    $body = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message";
    $headers = "From: $email";

    if (mail($to, $subject, $body, $headers)) {
        $notification = "Message sent successfully!";
        $notificationType = "success";
    } else {
        $notification = "Failed to send message. Please try again later.";
        $notificationType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us & FAQ - NCC Cadets</title>
    
    <!-- External CSS Libraries -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fc;
            color: #2c3e50;
        }

        .container {
            max-width: 1200px;
        }

        .section-title {
            font-size: 32px;
            font-weight: 600;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 40px;
        }

        .contact-form, .faq-section {
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .contact-form h4, .faq-section h4 {
            font-weight: 500;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .form-control {
            border-radius: 5px;
        }

        .btn-submit {
            background-color: #28a745;
            color: #ffffff;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 5px;
        }

        .btn-submit:hover {
            background-color: #218838;
        }

        /* FAQ styles */
        .accordion-button {
            font-weight: 500;
            color: #2c3e50;
        }

        .accordion-button:after {
            font-family: "Font Awesome 5 Free";
            content: "\f107"; /* Down arrow icon */
            font-weight: 900;
        }

        .accordion-button.collapsed:after {
            content: "\f105"; /* Right arrow icon */
        }

        .accordion-item {
            border: none;
        }

        /* Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: #ffffff;
            font-weight: 500;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 9999;
        }

        .notification.success {
            background-color: #28a745;
        }

        .notification.danger {
            background-color: #dc3545;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .section-title {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <!-- Contact Form Section -->
    <h2 class="section-title">Contact Us</h2>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="contact-form">
                <h4>We'd love to hear from you</h4>
                <form action="contact.php" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-submit">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <h2 class="section-title">Frequently Asked Questions</h2>

    <div class="faq-section">
        <h4>Find answers to common questions</h4>
        <div class="accordion" id="faqAccordion">
            <!-- FAQ Item 1 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="faqHeading1">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1">
                        <i class="fas fa-question-circle me-2"></i>What is NCC?
                    </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse show" aria-labelledby="faqHeading1" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        The National Cadet Corps (NCC) is a youth development organization that provides students with opportunities for personal growth, discipline, and leadership skills.
                    </div>
                </div>
            </div>

            <!-- FAQ Item 2 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="faqHeading2">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
                        <i class="fas fa-question-circle me-2"></i>How can I join NCC?
                    </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        You can join NCC through your school or college by applying to the NCC unit associated with it. Contact the institution's administration for more information.
                    </div>
                </div>
            </div>

            <!-- FAQ Item 3 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="faqHeading3">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">
                        <i class="fas fa-question-circle me-2"></i>What activities are conducted in NCC?
                    </button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        NCC conducts activities including drill practice, adventure camps, leadership training, physical training, and social service programs.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Container -->
<div class="notification <?php echo isset($notificationType) ? $notificationType : ''; ?>" id="notification">
    <?php echo isset($notification) ? $notification : ''; ?>
</div>

<!-- External JS Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0-alpha1/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
    // Display notification if there is a message
    $(document).ready(function() {
        <?php if (isset($notification)) { ?>
            $('#notification').fadeIn().delay(3000).fadeOut();
        <?php } ?>
    });
</script>

</body>
</html>
