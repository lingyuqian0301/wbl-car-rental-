<section>
    <h2 class="text-lg font-medium text-gray-900">
        {{ __('Profile Information') }}
    </h2>

    <p class="mt-1 text-sm text-gray-600">
        {{ __("Update your account's profile information.") }}
    </p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" readonly />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="phone_number" :value="__('Phone Number')" />
            <x-text-input id="phone_number" name="phone_number" type="tel" class="mt-1 block w-full" :value="old('phone_number', $profileData['phone_number'] ?? '')" autocomplete="tel" placeholder="e.g., 012-3456789" />
            <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
        </div>

        <div>
            <x-input-label for="matric_number" :value="__('Matric Number')" />
            <x-text-input id="matric_number" name="matric_number" type="text" class="mt-1 block w-full" :value="old('matric_number', $profileData['matric_number'] ?? '')" autocomplete="matric-number" placeholder="e.g., A21EC0001" />
            <x-input-error class="mt-2" :messages="$errors->get('matric_number')" />
        </div>

        <div>
            <x-input-label for="identification_card" :value="__('Identification Card (IC)')" />
            <x-text-input id="identification_card" name="identification_card" type="text" class="mt-1 block w-full" :value="old('identification_card', $profileData['identification_card'] ?? '')" autocomplete="identification-card" placeholder="e.g., 000101-01-0001" />
            <x-input-error class="mt-2" :messages="$errors->get('identification_card')" />
        </div>

        <div>
            <x-input-label for="customer_license" :value="__('Driving License Number')" />
            <x-text-input id="customer_license" name="customer_license" type="text" class="mt-1 block w-full" :value="old('customer_license', $profileData['customer_license'] ?? '')" autocomplete="off" placeholder="e.g., D12345678" />
            <x-input-error class="mt-2" :messages="$errors->get('customer_license')" />
        </div>

        <div>
            <x-input-label for="college" :value="__('College')" />
            <select id="college" name="college" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">-- Select College --</option>
                @foreach(config('utm.colleges') as $code => $name)
                    <option value="{{ $code }}" {{ old('college', $profileData['college'] ?? '') == $code ? 'selected' : '' }}>
                        {{ $code }} - {{ $name }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('college')" />
        </div>

        <div>
            <x-input-label for="faculty" :value="__('Faculty')" />
            <select id="faculty" name="faculty" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">-- Select Faculty --</option>
                @foreach(config('utm.faculties') as $code => $data)
                    <option value="{{ $code }}" {{ old('faculty', $profileData['faculty'] ?? '') == $code ? 'selected' : '' }}>
                        {{ $data['name'] }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('faculty')" />
        </div>

        <div>
            <x-input-label for="program" :value="__('Program')" />
            @php
                $currentFaculty = old('faculty', $profileData['faculty'] ?? '');
                $currentProgram = old('program', $profileData['program'] ?? '');
            @endphp
            <select id="program" name="program" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">-- Select Faculty First --</option>
                @if($currentFaculty && isset(config('utm.faculties')[$currentFaculty]))
                    @foreach(config('utm.faculties')[$currentFaculty]['programs'] as $programCode)
                        <option value="{{ $programCode }}" {{ $currentProgram == $programCode ? 'selected' : '' }}>
                            {{ $programCode }}
                        </option>
                    @endforeach
                @endif
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('program')" />
        </div>

        <div>
            <x-input-label for="address" :value="__('Address')" />
            <textarea id="address" name="address" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Enter your full address">{{ old('address', $profileData['address'] ?? '') }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        <div>
            <x-input-label for="state" :value="__('State of Origin')" />
            <select id="state" name="state" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">-- Select State --</option>
                @foreach(config('utm.states') as $stateName)
                    <option value="{{ $stateName }}" {{ old('state', $profileData['state'] ?? '') == $stateName ? 'selected' : '' }}>
                        {{ $stateName }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('state')" />
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
    // Dynamic program dropdown based on faculty selection
    document.getElementById('faculty').addEventListener('change', function() {
        const facultyCode = this.value;
        const programSelect = document.getElementById('program');
        
        // Clear current options
        programSelect.innerHTML = '<option value="">Loading...</option>';
        
        if (!facultyCode) {
            programSelect.innerHTML = '<option value="">-- Select Faculty First --</option>';
            return;
        }
        
        // Fetch programs for the selected faculty
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
