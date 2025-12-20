<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniTrack - Contact Us</title>
    <link rel="stylesheet" href="style.css" />
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- NAVBAR -->
<?php include 'includes/navbar.php'; ?>

<!-- MAIN CONTENT -->
<div class="container my-5">
    <h2 class="mb-4">Contact Us</h2>
    <p>Have questions or need support? Reach out to our team.</p>

    <form action="https://httpbin.org/get" 
          method="get" 
          onsubmit="validateForm(event)" 
          class="row g-3">

        <div class="col-md-6">
            <label for="name" class="form-label">Full Name:</label>
            <input type="text" id="name" name="name" class="form-control">
            <span id="nameError" class="text-danger"></span>
        </div>

        <div class="col-md-6">
            <label for="email" class="form-label">Email:</label>
            <input type="text" id="email" name="email" class="form-control">
            <span id="emailError" class="text-danger"></span>
        </div>

        <div class="col-md-6">
            <label for="user-type" class="form-label">I am a:</label>
            <select id="user-type" name="user_type" class="form-select">
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
                <option value="admin">Administrator</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="col-md-6">
            <label for="subject" class="form-label">Subject:</label>
            <input type="text" id="subject" name="subject" class="form-control">
            <span id="subjectError" class="text-danger"></span>
        </div>

        <div class="col-12">
            <label for="message" class="form-label">Message:</label>
            <textarea id="message" name="message" rows="5" class="form-control"></textarea>
            <span id="messageError" class="text-danger"></span>
        </div>

        <div class="col-12">
            <label class="form-label">Urgency:</label><br>

            <input class="form-check-input me-1" type="radio" name="urgency" id="low" value="low">
            <label class="form-check-label me-3" for="low">Low</label>

            <input class="form-check-input me-1" type="radio" name="urgency" id="medium" value="medium">
            <label class="form-check-label me-3" for="medium">Medium</label>

            <input class="form-check-input me-1" type="radio" name="urgency" id="high" value="high">
            <label class="form-check-label me-3" for="high">High</label>

            <br>
            <span id="urgencyError" class="text-danger"></span>
        </div>

        <div class="col-12">
            <input type="submit" value="Send Message" class="btn btn-primary me-2">
            <input type="reset" value="Clear Form" class="btn btn-secondary">
        </div>

    </form>

    <h3 class="mt-5">Other Contact Methods</h3>
    <p>Email: <a href="mailto:s151920@student.squ.edu.om">s151920@student.squ.edu.om</a></p>
    <p>Phone: +268 99279866</p>
</div>

<!-- FOOTER -->
<footer class="text-center py-3 mt-5 bg-light border-top">
    &copy; 2025 UniTrack - Student Registrar System. COMP3700 Project.
</footer>

<!-- VALIDATION SCRIPT -->
<script>
function validateForm(event) {
    let valid = true;

    document.getElementById("nameError").innerHTML = "";
    document.getElementById("emailError").innerHTML = "";
    document.getElementById("subjectError").innerHTML = "";
    document.getElementById("messageError").innerHTML = "";
    document.getElementById("urgencyError").innerHTML = "";

    let name = document.getElementById("name").value.trim();
    let email = document.getElementById("email").value.trim();
    let subject = document.getElementById("subject").value.trim();
    let message = document.getElementById("message").value.trim();
    let urgency = document.querySelector('input[name="urgency"]:checked');

    // NAME VALIDATION (letters only allowed)
    let namePattern = /^[A-Za-z\s]+$/;
    if (name === "") {
        document.getElementById("nameError").innerHTML = "Name is required";
        valid = false;
    } 
    else if (!namePattern.test(name)) {
        document.getElementById("nameError").innerHTML = "Only letters allowed";
        valid = false;
    }

    // EMAIL VALIDATION
    let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email === "") {
        document.getElementById("emailError").innerHTML = "Email is required";
        valid = false;
    } 
    else if (!emailPattern.test(email)) {
        document.getElementById("emailError").innerHTML = "Invalid email format";
        valid = false;
    }

    // SUBJECT VALIDATION
    if (subject.length < 3) {
        document.getElementById("subjectError").innerHTML = "Subject must be at least 3 characters long";
        valid = false;
    }

    // MESSAGE LENGTH VALIDATION
    if (message.length < 10) {
        document.getElementById("messageError").innerHTML = "Message must be at least 10 characters";
        valid = false;
    }

    // RADIO VALIDATION
    if (!urgency) {
        document.getElementById("urgencyError").innerHTML = "Select urgency level";
        valid = false;
    }

    if (!valid) {
        event.preventDefault();
    }
}
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
