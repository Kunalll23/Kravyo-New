<div class="container py-5">
    <div class="row mb-5 text-center">
        <div class="col-12">
            <h1 class="fw-bold mb-3">Contact Us</h1>
            <p class="lead text-muted max-w-700 mx-auto">Have questions, feedback, or need help? We'd love to hear from you.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-5 mb-4 mb-md-0">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h4 class="fw-bold mb-3">Get in Touch</h4>
                <p class="text-muted small mb-4">Our support team is available from 8:00 AM to 10:00 PM every day to assist you.</p>

                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-geo-alt-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Headquarters</div>
                        <div class="text-muted small">Surat, Gujarat, India</div>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-envelope-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Email Support</div>
                        <div class="text-muted small">support@kravyo.com</div>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-telephone-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Helpline</div>
                        <div class="text-muted small">+91 98765 43210</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h4 class="fw-bold mb-3">Send us a Message</h4>
                <form onsubmit="event.preventDefault(); alert('Thank you for contacting Kravyo! We will get back to you shortly.'); this.reset();">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Your Name</label>
                        <input type="text" class="form-control" required placeholder="Enter your full name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Email Address</label>
                        <input type="email" class="form-control" required placeholder="Enter your email address">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Subject</label>
                        <input type="text" class="form-control" required placeholder="What is this about?">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Message</label>
                        <textarea class="form-control" rows="4" required placeholder="Write your message here..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-kravyo-primary w-100 py-2 fw-semibold">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</div>
