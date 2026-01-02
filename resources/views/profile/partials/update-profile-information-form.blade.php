<section>

        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("") }}
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
            <x-input-label for="matric_number" :value="__('Matric Number')" />
            <x-text-input id="matric_number" name="matric_number" type="text" class="mt-1 block w-full" :value="old('matric_number', $user->matric_number)" autocomplete="matric-number" />
            <x-input-error class="mt-2" :messages="$errors->get('matric_number')" />
        </div>

        <div>
            <x-input-label for="identification_card" :value="__('Identification Card (IC)')" />
            <x-text-input id="identification_card" name="identification_card" type="text" class="mt-1 block w-full" :value="old('identification_card', $user->identification_card)" autocomplete="identification-card" />
            <x-input-error class="mt-2" :messages="$errors->get('identification_card')" />
        </div>

        <div>
            <x-input-label for="college" :value="__('College')" />
            <x-text-input id="college" name="college" type="text" class="mt-1 block w-full" :value="old('college', $user->college)" autocomplete="college" />
            <x-input-error class="mt-2" :messages="$errors->get('college')" />
        </div>

        <div>
            <x-input-label for="faculty" :value="__('Faculty')" />
            <x-text-input id="faculty" name="faculty" type="text" class="mt-1 block w-full" :value="old('faculty', $user->faculty)" autocomplete="faculty" />
            <x-input-error class="mt-2" :messages="$errors->get('faculty')" />
        </div>

        <div>
            <x-input-label for="program" :value="__('Program')" />
            <x-text-input id="program" name="program" type="text" class="mt-1 block w-full" :value="old('program', $user->program)" autocomplete="program" />
            <x-input-error class="mt-2" :messages="$errors->get('program')" />
        </div>

        <div>
            <x-input-label for="address" :value="__('Address')" />
            <textarea id="address" name="address" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('address', $user->address) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="city" :value="__('City')" />
                <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $user->city)" autocomplete="address-level2" />
                <x-input-error class="mt-2" :messages="$errors->get('city')" />
            </div>

            <div>
                <x-input-label for="state" :value="__('State')" />
                <x-text-input id="state" name="state" type="text" class="mt-1 block w-full" :value="old('state', $user->state)" autocomplete="address-level1" />
                <x-input-error class="mt-2" :messages="$errors->get('state')" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="region" :value="__('Region')" />
                <x-text-input id="region" name="region" type="text" class="mt-1 block w-full" :value="old('region', $user->region)" autocomplete="region" />
                <x-input-error class="mt-2" :messages="$errors->get('region')" />
            </div>

            <div>
                <x-input-label for="postcode" :value="__('Postcode')" />
                <x-text-input id="postcode" name="postcode" type="text" class="mt-1 block w-full" :value="old('postcode', $user->postcode)" autocomplete="postal-code" />
                <x-input-error class="mt-2" :messages="$errors->get('postcode')" />
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
