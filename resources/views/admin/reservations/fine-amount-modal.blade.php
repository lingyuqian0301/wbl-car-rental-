<!-- Fine Amount Edit Modal -->
<div class="modal fade" id="editFineAmountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Fine Amount</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editFineAmountForm">
                <div class="modal-body">
                    <input type="hidden" id="fine-amount-booking-id" name="booking_id">
                    <div class="mb-3">
                        <label class="form-label">Original Deposit Amount</label>
                        <input type="text" id="fine-amount-original" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fine Amount (RM) <span class="text-danger">*</span></label>
                        <input type="number" id="fine-amount-input" name="fine_amount" step="0.01" min="0" class="form-control" required>
                        <small class="text-muted">Enter the fine amount to be deducted from the deposit</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Refund Amount (RM)</label>
                        <input type="text" id="fine-amount-refund" class="form-control" readonly>
                        <small class="text-muted">Calculated automatically: Original Deposit - Fine Amount</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>


