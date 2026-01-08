@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary-blue: #3b5998;
        --primary-dark-blue: #2d4373;
        --border-color: #e2e8f0;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --bg-light: #f8fafc;
    }

    .inspection-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1.5rem;
    }

    .inspection-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .inspection-header h1 {
        font-size: 1.75rem;
        color: var(--text-primary);
        margin: 0;
    }

    .breadcrumb {
        color: var(--text-secondary);
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .breadcrumb a {
        color: var(--text-secondary);
        text-decoration: none;
    }

    .breadcrumb a:hover {
        text-decoration: underline;
    }

    .btn-generate-pdf {
        background: var(--primary-blue);
        color: white;
        padding: 0.6rem 1.2rem;
        border-radius: 6px;
        border: none;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s;
    }

    .btn-generate-pdf:hover {
        background: var(--primary-dark-blue);
    }

    .inspection-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        padding: 2rem;
        margin-bottom: 1.5rem;
    }

    .section-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 1.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .photos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .photo-upload {
        text-align: center;
    }

    .photo-box {
        border: 2px dashed var(--border-color);
        border-radius: 8px;
        padding: 2rem 1rem;
        background: var(--bg-light);
        min-height: 200px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.5rem;
        overflow: hidden;
    }

    .photo-box img {
        max-width: 100%;
        max-height: 180px;
        object-fit: contain;
    }

    .photo-label {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .btn-upload {
        background: white;
        color: var(--primary-blue);
        border: 1px solid var(--primary-blue);
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-upload:hover {
        background: var(--primary-blue);
        color: white;
    }

    .btn-add-new {
        background: var(--primary-blue);
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 6px;
        font-size: 0.9rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-left: auto;
    }

    .btn-add-new:hover {
        background: var(--primary-dark-blue);
    }

    .date-fuel-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-label {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .form-input {
        padding: 0.7rem;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 0.95rem;
        color: var(--text-primary);
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary-blue);
    }

    .fuel-gauge-section {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .fuel-slider-container {
        width: 100%;
        margin-bottom: 1rem;
    }

    .fuel-slider {
        width: 100%;
        height: 8px;
        border-radius: 5px;
        background: #ddd;
        outline: none;
        -webkit-appearance: none;
        appearance: none;
        cursor: pointer;
    }

    .fuel-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--primary-blue);
        cursor: pointer;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .fuel-slider::-moz-range-thumb {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--primary-blue);
        cursor: pointer;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .remarks-textarea {
        width: 100%;
        min-height: 120px;
        padding: 0.8rem;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 0.95rem;
        font-family: inherit;
        resize: vertical;
    }

    .remarks-textarea:focus {
        outline: none;
        border-color: var(--primary-blue);
    }

    .confirmation-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .confirmation-checkbox input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-top: 0.3rem;
        cursor: pointer;
        accent-color: var(--primary-blue);
        flex-shrink: 0;
    }

    .confirmation-checkbox label {
        cursor: pointer;
        flex: 1;
        margin: 0;
    }

    .confirmation-text {
        color: var(--text-primary);
        font-size: 1rem;
        line-height: 1.6;
    }

    .btn-submit {
        background: var(--primary-blue);
        color: white;
        border: none;
        padding: 0.8rem 2rem;
        border-radius: 6px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-submit:hover:not(:disabled) {
        background: var(--primary-dark-blue);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 89, 152, 0.3);
    }

    .btn-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    @media (max-width: 768px) {
        .inspection-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        .date-fuel-section {
            grid-template-columns: 1fr;
        }
        .photos-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<x-booking-stepper />

<div class="inspection-container">
    <div class="breadcrumb">
    </div>

    <div class="inspection-header">
        <h1>Return Car</h1>
    </div>

    <form method="POST" action="{{ route('return.confirm', $booking) }}" id="inspectionForm" enctype="multipart/form-data">
        @csrf

        <div class="inspection-card">
            <div class="section-title">Photos</div>
            <div class="photos-grid">
                <div class="photo-upload">
                    <div class="photo-label">Front Image</div>
                    <div class="photo-box" id="frontImageBox">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 150'%3E%3Crect fill='%23f0f0f0' width='200' height='150'/%3E%3Cpath fill='%23999' d='M20,50 L60,30 L180,30 L180,90 L60,90 L20,70 Z M60,30 L60,90 M100,20 L100,100 M140,20 L140,100'/%3E%3C/svg%3E" alt="Front view">
                    </div>
                    <input type="file" id="front_image" name="front_image" accept="image/*" style="display: none;">
                    <button type="button" class="btn-upload" onclick="document.getElementById('front_image').click()">Upload</button>
                </div>

                <div class="photo-upload">
                    <div class="photo-label">Back Image</div>
                    <div class="photo-box" id="backImageBox">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 150'%3E%3Crect fill='%23f0f0f0' width='200' height='150'/%3E%3Cpath fill='%23999' d='M20,50 L60,30 L180,30 L180,90 L60,90 L20,70 Z M100,20 L100,100 M60,40 L180,40 M60,80 L180,80'/%3E%3C/svg%3E" alt="Back view">
                    </div>
                    <input type="file" id="back_image" name="back_image" accept="image/*" style="display: none;">
                    <button type="button" class="btn-upload" onclick="document.getElementById('back_image').click()">Upload</button>
                </div>

                <div class="photo-upload">
                    <div class="photo-label">Left Image</div>
                    <div class="photo-box" id="leftImageBox">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 150'%3E%3Crect fill='%23f0f0f0' width='200' height='150'/%3E%3Cpath fill='%23999' d='M40,50 L160,50 L160,100 L40,100 Z M70,40 L70,110 M130,40 L130,110 M40,65 L160,65 M40,85 L160,85'/%3E%3C/svg%3E" alt="Left view">
                    </div>
                    <input type="file" id="left_image" name="left_image" accept="image/*" style="display: none;">
                    <button type="button" class="btn-upload" onclick="document.getElementById('left_image').click()">Upload</button>
                </div>

                <div class="photo-upload">
                    <div class="photo-label">Right Image</div>
                    <div class="photo-box" id="rightImageBox">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 150'%3E%3Crect fill='%23f0f0f0' width='200' height='150'/%3E%3Cpath fill='%23999' d='M40,50 L160,50 L160,100 L40,100 Z M70,40 L70,110 M130,40 L130,110 M40,65 L160,65 M40,85 L160,85'/%3E%3C/svg%3E" alt="Right view">
                    </div>
                    <input type="file" id="right_image" name="right_image" accept="image/*" style="display: none;">
                    <button type="button" class="btn-upload" onclick="document.getElementById('right_image').click()">Upload</button>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin: 1.5rem 0 1rem;">
                <div class="section-title" style="margin: 0;">Additional Images</div>
                <button type="button" class="btn-add-new" id="addImageBtn">
                    <span>+</span> Add new
                </button>
            </div>
            <div id="additionalImagesContainer"></div>
        </div>

        <div class="inspection-card">
            <div class="date-fuel-section">
                <div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label class="form-label">Date Check</label>
                        <input type="datetime-local" name="date_check" class="form-input" value="{{ date('Y-m-d\TH:i') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mileage</label>
                        <input type="number" name="mileage" class="form-input" placeholder="Enter mileage">
                    </div>
                </div>

                <div class="fuel-gauge-section">
                    <label class="form-label">FUEL</label>
                    <div class="fuel-slider-container">
                        <input type="range" id="fuelSlider" name="fuel_level" min="0" max="100" value="0" class="fuel-slider">
                        <div style="font-weight: 600; margin: 0.5rem 0; font-size: 1.1rem;">
                            <span id="fuelPercentage">0</span>%
                        </div>
                    </div>
                    
                    <div style="margin: 1rem 0;">
                        <svg width="200" height="120" viewBox="0 0 200 120">
                            <path d="M 30 100 Q 30 40, 100 40 Q 170 40, 170 100" stroke="#ddd" stroke-width="20" fill="none" stroke-linecap="round"/>
                            <circle cx="100" cy="100" r="8" fill="#333"/>
                            <line id="fuelNeedle" x1="100" y1="100" x2="30" y2="100" stroke="#333" stroke-width="3"/>
                        </svg>
                    </div>

                    <div style="margin-top: 1rem;">
                        <div class="form-label">Fuel Image</div>
                        <input type="file" id="fuel_image" name="fuel_image" accept="image/*" style="display: none;">
                        <button type="button" class="btn-upload" onclick="document.getElementById('fuel_image').click()">Upload</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="inspection-card">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="remarks-textarea" placeholder="Enter any additional notes or observations..."></textarea>
        </div>

        <div class="inspection-card">
            <div class="confirmation-checkbox">
                <input type="checkbox" id="confirmReturn" name="confirm_return" value="on">
                <label for="confirmReturn">
                    <div class="confirmation-text">
                        I confirm the vehicle has been returned in good condition and understand any damages will be assessed by HASTA Travel & Tours staff.
                    </div>
                </label>
            </div>
        </div>

        <div>
            <button type="submit" class="btn-submit" id="submitBtn" disabled>Complete Return</button>
        </div>
    </form>
</div>

<script>
    function setupImagePreview(inputId, boxId) {
        const input = document.getElementById(inputId);
        const box = document.getElementById(boxId);
        
        input.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    box.innerHTML = `<img src="${event.target.result}" alt="Preview">`;
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }

    setupImagePreview('front_image', 'frontImageBox');
    setupImagePreview('back_image', 'backImageBox');
    setupImagePreview('left_image', 'leftImageBox');
    setupImagePreview('right_image', 'rightImageBox');

    let additionalImageCount = 0;
    document.getElementById('addImageBtn').addEventListener('click', function() {
        additionalImageCount++;
        const container = document.getElementById('additionalImagesContainer');
        const newImageDiv = document.createElement('div');
        newImageDiv.className = 'photo-upload';
        newImageDiv.style.marginTop = '1rem';
        newImageDiv.innerHTML = `
            <div class="photo-label">Additional Image ${additionalImageCount}</div>
            <div class="photo-box" id="additionalBox${additionalImageCount}">
                <svg width="60" height="60" fill="#ccc" viewBox="0 0 16 16">
                    <path d="M10.5 8.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                    <path d="M2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4H2zm.5 2a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1zm9 2.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0z"/>
                </svg>
            </div>
            <input type="file" id="additional${additionalImageCount}" name="additional_images[]" accept="image/*" style="display: none;">
            <button type="button" class="btn-upload" onclick="document.getElementById('additional${additionalImageCount}').click()">Upload</button>
        `;
        container.appendChild(newImageDiv);
        setupImagePreview(`additional${additionalImageCount}`, `additionalBox${additionalImageCount}`);
    });

    const fuelSlider = document.getElementById('fuelSlider');
    const fuelPercentage = document.getElementById('fuelPercentage');
    const fuelNeedle = document.getElementById('fuelNeedle');

    fuelSlider.addEventListener('input', function() {
        const percentage = parseInt(this.value);
        fuelPercentage.textContent = percentage;
        updateFuelGauge(percentage);
    });

    function updateFuelGauge(percentage) {
        const angle = (percentage / 100) * 140 - 70;
        const radians = (angle * Math.PI) / 180;
        const needleLength = 40;
        const centerX = 100;
        const centerY = 100;
        const endX = centerX + needleLength * Math.cos(radians - Math.PI / 2);
        const endY = centerY + needleLength * Math.sin(radians - Math.PI / 2);
        fuelNeedle.setAttribute('x2', endX);
        fuelNeedle.setAttribute('y2', endY);
    }

    updateFuelGauge(0);

    const confirmCheckbox = document.getElementById('confirmReturn');
    const submitBtn = document.getElementById('submitBtn');

    confirmCheckbox.addEventListener('change', function() {
        submitBtn.disabled = !this.checked;
    });
</script>

@endsection