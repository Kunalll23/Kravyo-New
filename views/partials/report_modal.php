<!-- Report Issue Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url('/customer/report/submit') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="kitchen_id" id="reportKitchenId" value="">
                <input type="hidden" name="order_id" id="reportOrderId" value="">
                <input type="hidden" name="target_type" id="reportTargetType" value="kitchen">
                <input type="hidden" name="target_id" id="reportTargetId" value="">

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger" id="reportModalLabel"><i class="bi bi-flag-fill me-2"></i>Report an Issue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Your report will be reviewed by the admin team.</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                        <select name="reason" class="form-select" required>
                            <option value="">-- Select a reason --</option>
                            <option value="Hygiene or Food Quality">Hygiene or Food Quality</option>
                            <option value="Missing or Incorrect Items">Missing or Incorrect Items</option>
                            <option value="Late or Unprofessional Delivery">Late or Unprofessional Delivery</option>
                            <option value="Inappropriate Behavior">Inappropriate Behavior</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Details <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Please provide specific details..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reportModal = document.getElementById('reportModal');
    if (reportModal) {
        reportModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const kitchenId = button.getAttribute('data-kitchen-id');
            const orderId = button.getAttribute('data-order-id');
            const targetType = button.getAttribute('data-target-type');
            const targetId = button.getAttribute('data-target-id');
            
            document.getElementById('reportKitchenId').value = kitchenId || '';
            document.getElementById('reportOrderId').value = orderId || '';
            document.getElementById('reportTargetType').value = targetType || 'kitchen';
            document.getElementById('reportTargetId').value = targetId || '';
        });
    }
});
</script>
