<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Booking #{{ $booking->bookingID }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-red: #dc2626;
            --primary-red-dark: #991b1b;
            --primary-red-light: #fee2e2;
        }
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .payment-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .payment-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .payment-header {
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--primary-red-dark) 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        .payment-header h2 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 700;
        }
        .booking-info {
            background: var(--primary-red-light);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(220, 38, 38, 0.1);
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #374151;
        }
        .info-value {
            color: var(--primary-red);
            font-weight: 700;
            font-size: 1.1rem;
        }
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .form-control, .form-select {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
        .file-upload-area {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            background: #f9fafb;
            transition: all 0.3s;
            cursor: pointer;
        }
        .file-upload-area:hover {
            border-color: var(--primary-red);
            background: var(--primary-red-light);
        }
        .file-upload-area.dragover {
            border-color: var(--primary-red);
            background: var(--primary-red-light);
        }
        .btn-submit {
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--primary-red-dark) 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(220, 38, 38, 0.3);
        }
        .file-preview {
            margin-top: 1rem;
            display: none;
        }
        .file-preview.show {
            display: block;
        }
        .file-preview img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
        }
        .required {
            color: var(--primary-red);
        }
    </style>
</head>
<body>
    <div class="container payment-container">
        <!-- Header -->
        <div class="payment-header">
            <h2><i class="bi bi-credit-card"></i> Payment Submission</h2>
            <p class="mb-0 mt-2">Please upload your payment proof and provide bank details for deposit refund</p>
        </div>

        <!-- Booking Information -->
        <div class="payment-card">
            <h4 class="mb-3"><i class="bi bi-info-circle"></i> Booking Information</h4>
            <div class="booking-info">
                <div class="info-row">
                    <span class="info-label">Booking ID:</span>
                    <span class="info-value">#{{ $booking->bookingID }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Vehicle:</span>
                    <span class="info-value">{{ $booking->vehicle->vehicle_brand ?? 'N/A' }} {{ $booking->vehicle->vehicle_model ?? '' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Amount:</span>
                    <span class="info-value">RM {{ number_format($booking->total_amount ?? $booking->total_price ?? 0, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Deposit Required:</span>
                    <span class="info-value">RM {{ number_format($depositAmount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="payment-card">
            <h4 class="mb-3"><i class="bi bi-upload"></i> Payment Details</h4>
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('payment.submit') }}" method="POST" enctype="multipart/form-data" id="paymentForm">
                @csrf
                <input type="hidden" name="bookingID" value="{{ $booking->bookingID }}">
                <input type="hidden" name="amount" value="{{ $depositAmount }}">

                <!-- Payment Proof Upload -->
                <div class="mb-4">
                    <label class="form-label">
                        Payment Proof (Receipt) <span class="required">*</span>
                    </label>
                    <div class="file-upload-area" id="fileUploadArea">
                        <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #9ca3af;"></i>
                        <p class="mt-3 mb-1">
                            <strong>Click to upload</strong> or drag and drop
                        </p>
                        <p class="text-muted small mb-0">
                            PNG, JPG, PDF (Max 5MB)
                        </p>
                        <input type="file" name="receipt_image" id="receiptImage" class="d-none" accept="image/*,application/pdf" required>
                    </div>
                    <div class="file-preview" id="filePreview">
                        <img id="previewImage" src="" alt="Preview">
                        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeFile()">
                            <i class="bi bi-x-circle"></i> Remove
                        </button>
                    </div>
                    <small class="text-muted">Upload your bank transfer receipt or DuitNow QR payment proof</small>
                </div>

                <!-- Bank Name -->
                <div class="mb-4">
                    <label for="bank_name" class="form-label">
                        Bank Name <span class="required">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="bank_name" 
                           name="bank_name" 
                           placeholder="e.g., Maybank, CIMB, Public Bank"
                           value="{{ old('bank_name') }}"
                           required>
                    <small class="text-muted">For deposit refund purposes</small>
                </div>

                <!-- Bank Account Number -->
                <div class="mb-4">
                    <label for="bank_account_number" class="form-label">
                        Bank Account Number <span class="required">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="bank_account_number" 
                           name="bank_account_number" 
                           placeholder="e.g., 1234567890"
                           value="{{ old('bank_account_number') }}"
                           required>
                    <small class="text-muted">For deposit refund purposes</small>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-submit">
                    <i class="bi bi-check-circle"></i> Submit Payment
                </button>
            </form>
        </div>

        <!-- Instructions -->
        <div class="payment-card">
            <h5><i class="bi bi-info-circle"></i> Payment Instructions</h5>
            <ol>
                <li>Transfer the deposit amount to HASTA Travel's bank account</li>
                <li>Take a screenshot or photo of your payment receipt</li>
                <li>Upload the receipt image/PDF above</li>
                <li>Fill in your bank details for deposit refund</li>
                <li>Click "Submit Payment" to complete</li>
            </ol>
            <p class="text-muted small mb-0">
                <strong>Note:</strong> Your payment will be verified by our staff. You will be notified once it's approved.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const fileUploadArea = document.getElementById('fileUploadArea');
        const receiptImage = document.getElementById('receiptImage');
        const filePreview = document.getElementById('filePreview');
        const previewImage = document.getElementById('previewImage');

        // Click to upload
        fileUploadArea.addEventListener('click', () => {
            receiptImage.click();
        });

        // File selection
        receiptImage.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        filePreview.classList.add('show');
                    };
                    reader.readAsDataURL(file);
                } else {
                    // PDF file
                    filePreview.classList.add('show');
                    previewImage.src = '';
                    previewImage.alt = 'PDF File: ' + file.name;
                    previewImage.style.display = 'none';
                    const pdfInfo = document.createElement('p');
                    pdfInfo.textContent = 'PDF: ' + file.name;
                    pdfInfo.className = 'text-center';
                    if (!filePreview.querySelector('p')) {
                        filePreview.insertBefore(pdfInfo, filePreview.querySelector('button'));
                    }
                }
            }
        });

        // Drag and drop
        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('dragover');
        });

        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('dragover');
        });

        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                receiptImage.files = files;
                receiptImage.dispatchEvent(new Event('change'));
            }
        });

        function removeFile() {
            receiptImage.value = '';
            filePreview.classList.remove('show');
            previewImage.src = '';
        }

        // Form validation
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            if (!receiptImage.files.length) {
                e.preventDefault();
                alert('Please upload a payment receipt');
                return false;
            }
        });
    </script>
</body>
</html>

