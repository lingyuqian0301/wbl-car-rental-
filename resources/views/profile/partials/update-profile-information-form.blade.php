<section>

        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and documents.") }}
        </p>


    {{-- Display Warning Message at the top --}}
    @if (session('warning'))
        <div class="mt-4 p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300" role="alert">
            <span class="font-medium">Notice:</span> {{ session('warning') }}
        </div>
    @endif

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name">
                {{ __('Full Name') }} <span class="text-red-600">*</span>
            </x-input-label>
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="address" :value="__('Address')" />
            <textarea id="address" name="address" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('address', $profileData['address'] ?? '') }}</textarea>
        </div>

        <div>
            <x-input-label for="email">
                {{ __('Email') }} <span class="text-red-600"></span>
            </x-input-label>
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" readonly />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="phone_number">
                {{ __('Phone Number') }} <span class="text-red-600">*</span>
            </x-input-label>
            <x-text-input id="phone_number" name="phone_number" type="tel" class="mt-1 block w-full" :value="old('phone_number', $profileData['phone_number'] ?? '')" />
            <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
        </div>

        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200" x-data="{ type: '{{ old('identity_type', $profileData['identity_type'] ?? 'ic') }}' }">
            <h3 class="text-md font-medium text-gray-900 mb-4">Identity Verification</h3>
            
            <div>
                <x-input-label for="identity_type">
                    {{ __('Document Type') }} <span class="text-red-600">*</span>
                </x-input-label>
                <select id="identity_type" name="identity_type" x-model="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="ic">IC (Identity Card)</option>
                    <option value="passport">Passport</option>
                </select>
            </div>
            
            <div class="mt-4">
                <x-input-label for="identity_value">
                    <span x-text="type === 'ic' ? 'IC Number' : 'Passport Number'"></span> <span class="text-red-600">*</span>
                </x-input-label>
                <x-text-input id="identity_value" name="identity_value" type="text" class="mt-1 block w-full" :value="old('identity_value', $profileData['identity_value'] ?? '')" />
                <x-input-error class="mt-2" :messages="$errors->get('identity_value')" />
            </div>

            <div class="mt-4">
                <x-input-label for="file_identity">
                    <span x-text="type === 'ic' ? 'Upload IC (PDF/Image)' : 'Upload Passport (PDF/Image)'"></span> <span class="text-red-600">*</span>
                </x-input-label>
                <input id="file_identity" name="file_identity" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                <x-input-error class="mt-2" :messages="$errors->get('file_identity')" />
            </div>

            <div class="mt-4">
                <x-input-label for="state" x-text="type === 'ic' ? 'State of Origin' : 'Country of Origin'" />
                <select id="state" name="state" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">-- Select State/Country --</option>
                    @foreach(config('utm.states', []) as $stateName)
                        <option value="{{ $stateName }}" {{ old('state', $profileData['state'] ?? '') == $stateName ? 'selected' : '' }}>
                            {{ $stateName }}
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('state')" />
            </div>
        </div>

        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
             <h3 class="text-md font-medium text-gray-900 mb-4">Student Information (If Applicable)</h3>
            
            <div>
                <x-input-label for="matric_number">
                    {{ __('Matric Number') }} <span class="text-red-600">*</span>
                </x-input-label>
                <x-text-input id="matric_number" name="matric_number" type="text" class="mt-1 block w-full" :value="old('matric_number', $profileData['matric_number'] ?? '')" />
                <x-input-error class="mt-2" :messages="$errors->get('matric_number')" />
            </div>

            <div class="mt-4">
                <x-input-label for="file_matric">
                    {{ __('Upload Matric Card') }} <span class="text-red-600">*</span>
                </x-input-label>
                <input id="file_matric" name="file_matric" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                <x-input-error class="mt-2" :messages="$errors->get('file_matric')" />
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="college">
                        {{ __('College') }} <span class="text-red-600">*</span>
                    </x-input-label>
                    <select id="college" name="college" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">-- Select College --</option>
                        @foreach(config('utm.colleges', []) as $code => $name)
                            <option value="{{ $code }}" {{ old('college', $profileData['college'] ?? '') == $code ? 'selected' : '' }}>
                                {{ $code }} - {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="faculty">
                        {{ __('Faculty') }} <span class="text-red-600">*</span>
                    </x-input-label>
                    <select id="faculty" name="faculty" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">-- Select Faculty --</option>
                        @foreach(config('utm.faculties', []) as $code => $data)
                            <option value="{{ $code }}" {{ old('faculty', $profileData['faculty'] ?? '') == $code ? 'selected' : '' }}>
                                {{ $data['name'] ?? $code }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
             <div class="mt-4">
                <x-input-label for="program">
                    {{ __('Program') }} <span class="text-red-600">*</span>
                </x-input-label>
                <select id="program" name="program" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">-- Select Faculty First --</option>
                    @php
                        $currentFaculty = old('faculty', $profileData['faculty'] ?? '');
                        $currentProgram = old('program', $profileData['program'] ?? '');
                    @endphp
                    @if($currentFaculty && isset(config('utm.faculties')[$currentFaculty]))
                        @foreach(config('utm.faculties')[$currentFaculty]['programs'] as $programCode)
                            <option value="{{ $programCode }}" {{ $currentProgram == $programCode ? 'selected' : '' }}>
                                {{ $programCode }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>

        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h3 class="text-md font-medium text-gray-900 mb-4">Driving License</h3>
            
            <div>
                <x-input-label for="customer_license">
                    {{ __('License Expiry Date') }} <span class="text-red-600">*</span>
                </x-input-label>
                <x-text-input id="customer_license" name="customer_license" type="date" class="mt-1 block w-full" :value="old('customer_license', $profileData['customer_license'] ?? '')" />
                <x-input-error class="mt-2" :messages="$errors->get('customer_license')" />
            </div>

            <div class="mt-4">
                <x-input-label for="file_license">
                    {{ __('Upload License') }} <span class="text-red-600">*</span>
                </x-input-label>
                <input id="file_license" name="file_license" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                <x-input-error class="mt-2" :messages="$errors->get('file_license')" />
            </div>
        </div>

        <div>
            <x-input-label for="emergency_contact_number">
                {{ __('Emergency Contact') }} <span class="text-red-600">*</span>
            </x-input-label>
            <div class="flex gap-4 mt-1">
                <div class="w-1/3">
                    <select id="emergency_relationship" name="emergency_relationship" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Relationship</option>
                        @foreach(['Mother', 'Father', 'Relative', 'Other'] as $rel)
                            <option value="{{ $rel }}" {{ old('emergency_relationship', $profileData['emergency_relationship'] ?? '') == $rel ? 'selected' : '' }}>
                                {{ $rel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-2/3">
                    <x-text-input id="emergency_contact_number" name="emergency_contact_number" type="tel" class="block w-full" :value="old('emergency_contact_number', $profileData['emergency_contact_number'] ?? '')" placeholder="Phone Number" />
                </div>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('emergency_contact_number')" />
            <x-input-error class="mt-2" :messages="$errors->get('emergency_relationship')" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="bank_name">
                    {{ __('Bank Name') }} <span class="text-red-600">*</span>
                </x-input-label>
                <x-text-input id="bank_name" name="bank_name" type="text" class="mt-1 block w-full" :value="old('bank_name', $profileData['bank_name'] ?? '')" placeholder="e.g. Maybank" />
                <x-input-error class="mt-2" :messages="$errors->get('bank_name')" />
            </div>
            <div>
                <x-input-label for="bank_account_number">
                    {{ __('Bank Account Number') }} <span class="text-red-600">*</span>
                </x-input-label>
                <x-text-input id="bank_account_number" name="bank_account_number" type="text" class="mt-1 block w-full" :value="old('bank_account_number', $profileData['bank_account_number'] ?? '')" />
                <x-input-error class="mt-2" :messages="$errors->get('bank_account_number')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

<script>
    document.getElementById('faculty').addEventListener('change', function() {
        const facultyCode = this.value;
        const programSelect = document.getElementById('program');
        
        programSelect.innerHTML = '<option value="">Loading...</option>';
        
        if (!facultyCode) {
            programSelect.innerHTML = '<option value="">-- Select Faculty First --</option>';
            return;
        }
        
        // Ensure this API endpoint exists or use data attributes
        fetch(`/api/programs/${facultyCode}`)
            .then(response => response.json())
            .then(data => {
                programSelect.innerHTML = '<option value="">-- Select Program --</option>';
                data.forEach(programCode => {
                    const option = document.createElement('option');
                    option.value = programCode;
                    option.textContent = programCode;
                    programSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching programs:', error);
                programSelect.innerHTML = '<option value="">Error loading programs</option>';
            });
    });
</script>