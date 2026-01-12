@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary-orange: #dc2626;
        --primary-dark-orange: #991b1b;
        --border-color: #e2e8f0;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
    }

    .agreement-container {
        max-width: 1000px;
        margin: 2rem auto;
        padding: 0 1.5rem;
    }

    .agreement-header {
        margin-bottom: 2rem;
    }

    .agreement-header h1 {
        font-size: 2rem;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .agreement-header p {
        color: var(--text-secondary);
        font-size: 1.05rem;
    }

    .agreement-preview-wrapper {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 2rem;
        border: 2px solid var(--border-color);
    }

    .agreement-preview-frame {
        width: 100%;
        height: 600px;
        border: none;
        display: block;
    }

    .agreement-actions {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        padding: 2rem;
        border: 2px solid var(--border-color);
    }

    .agreement-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding: 1.2rem;
        background: #f9fafb;
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }

    .agreement-checkbox input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-top: 0.3rem;
        cursor: pointer;
        accent-color: var(--primary-orange);
        flex-shrink: 0;
    }

    .agreement-checkbox label {
        cursor: pointer;
        flex: 1;
        margin: 0;
    }

    .agreement-checkbox-text {
        color: var(--text-primary);
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .agreement-error {
        background: #fee;
        border: 2px solid var(--primary-orange);
        color: var(--primary-orange);
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: none;
    }

    .agreement-error.show {
        display: block;
    }

    .agreement-success {
        background: #dcfce7;
        border: 2px solid #22c55e;
        color: #166534;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: none;
    }

    .agreement-success.show {
        display: block;
    }

    .agreement-buttons {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }

    .btn {
        padding: 0.8rem 1.5rem;
        border-radius: 8px;
        border: none;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-secondary {
        background: #f3f4f6;
        color: var(--text-primary);
        border: 2px solid var(--border-color);
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-orange) 0%, var(--primary-dark-orange) 100%);
        color: white;
    }

    .btn-primary:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
    }

    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .upload-section {
        margin-top: 1.5rem;
        padding: 1.5rem;
        background: #fffbeb;
        border: 2px dashed #f59e0b;
        border-radius: 8px;
    }

    .upload-section-title {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .upload-section-desc {
        font-size: 0.9rem;
        color: var(--text-secondary);
        margin-bottom: 1rem;
    }

    .upload-box {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-upload {
        background: #f59e0b;
        color: white;
        padding: 0.6rem 1.2rem;
        border-radius: 6px;
        border: none;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-upload:hover {
        background: #d97706;
    }

    .upload-preview {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #059669;
        font-size: 0.9rem;
    }

    .upload-preview img {
        max-width: 60px;
        max-height: 60px;
        border-radius: 4px;
        border: 1px solid var(--border-color);
    }

    .upload-required {
        color: #dc2626;
        font-size: 0.85rem;
        margin-top: 0.5rem;
    }

    @media (max-width: 768px) {
        .agreement-container {
            padding: 0 1rem;
        }

        .agreement-preview-frame {
            height: 400px;
        }

        .agreement-buttons {
            flex-direction: column-reverse;
        }

        .btn {
            width: 100%;
        }
    }
</style>

<x-booking-stepper /> {{-- Auto-detects Agreement step --}}

<div class="agreement-container">
    <div class="agreement-header">
        <h1>Rental Agreement</h1>
        <p>Please review the rental agreement and accept the terms to proceed with your booking</p>
    </div>

    @if ($errors->any())
        <div class="agreement-error show">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="agreement-success" id="successMessage">
        <strong>Success!</strong> Agreement downloaded successfully. You may proceed to vehicle pickup.
    </div>

    <div class="agreement-preview-wrapper">
        <iframe class="agreement-preview-frame" src="{{ route('agreement.preview', $booking) }}" title="Rental Agreement"></iframe>
    </div>

    <div class="agreement-actions">
        <form method="POST" action="{{ route('agreement.download', $booking) }}" id="agreementForm">
            @csrf

            <div class="agreement-checkbox">
                <input type="checkbox" id="agreeCheckbox" name="agree" value="on">
                <label for="agreeCheckbox">
                    <div class="agreement-checkbox-text">
                        I have read and agree to the <strong>Rental Agreement Terms & Conditions</strong>. I understand my responsibilities as a renter and agree to comply with all the terms outlined in this agreement.
                    </div>
                </label>
            </div>

            <div class="agreement-buttons" style="margin-bottom: 1.5rem;">
                <a href="{{ route('bookings.index') }}" class="btn btn-secondary">Back to My Bookings</a>
                <button type="submit" class="btn btn-primary" id="downloadBtn" disabled>
                    Download Agreement
                </button>
            </div>
        </form>

        {{-- Upload Signed Agreement Section --}}
        <div class="upload-section">
            <div class="upload-section-title">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                    <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                </svg>
                Upload Signed Rental Agreement
            </div>
            <div class="upload-section-desc">
                After downloading and signing the agreement, please upload a photo/scan of the signed document to proceed to vehicle pickup.
            </div>
            
            <form method="POST" action="{{ route('agreement.upload', $booking) }}" id="uploadForm" enctype="multipart/form-data">
                @csrf
                <div class="upload-box">
                    <input type="file" id="signed_agreement" name="signed_agreement" accept="image/*,.pdf" style="display: none;">
                    <button type="button" class="btn-upload" id="uploadBtn" onclick="document.getElementById('signed_agreement').click()">
                        Choose File
                    </button>
                    <div class="upload-preview" id="uploadPreview" style="display: none;">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                        </svg>
                        <span id="fileName">File selected</span>
                    </div>
                    <button type="submit" class="btn btn-primary" id="submitUploadBtn" disabled style="margin-left: auto;">
                        Upload & Continue to Pickup
                    </button>
                </div>
                <div class="upload-required" id="uploadRequired">
                    * You must upload the signed agreement before proceeding to pickup
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const agreeCheckbox = document.getElementById('agreeCheckbox');
    const downloadBtn = document.getElementById('downloadBtn');
    const agreementForm = document.getElementById('agreementForm');
    const successMessage = document.getElementById('successMessage');
    
    // Upload elements
    const signedAgreementInput = document.getElementById('signed_agreement');
    const uploadPreview = document.getElementById('uploadPreview');
    const fileName = document.getElementById('fileName');
    const submitUploadBtn = document.getElementById('submitUploadBtn');
    const uploadRequired = document.getElementById('uploadRequired');

    // Update download button state based on checkbox
    agreeCheckbox.addEventListener('change', function() {
        downloadBtn.disabled = !this.checked;
    });

    // Handle download form submission
    agreementForm.addEventListener('submit', function(e) {
        e.preventDefault();

        downloadBtn.disabled = true;
        downloadBtn.textContent = 'Downloading...';

        // Show success message after download starts
        setTimeout(function() {
            downloadBtn.textContent = 'Download Agreement';
            downloadBtn.disabled = !agreeCheckbox.checked;

            // Show success message
            successMessage.classList.add('show');

            // Auto-hide success message after 5 seconds
            setTimeout(function() {
                successMessage.classList.remove('show');
            }, 5000);
        }, 2000);

        // Actually submit the form to download
        setTimeout(function() {
            agreementForm.submit();
        }, 100);
    });

    // Handle file selection for upload
    signedAgreementInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            fileName.textContent = file.name;
            uploadPreview.style.display = 'flex';
            submitUploadBtn.disabled = false;
            uploadRequired.style.display = 'none';
        } else {
            uploadPreview.style.display = 'none';
            submitUploadBtn.disabled = true;
            uploadRequired.style.display = 'block';
        }
    });

    // Handle upload form submission
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        if (!signedAgreementInput.files || !signedAgreementInput.files[0]) {
            e.preventDefault();
            alert('Please select a file to upload');
            return;
        }
        submitUploadBtn.disabled = true;
        submitUploadBtn.textContent = 'Uploading...';
    });
</script>

@endsection

