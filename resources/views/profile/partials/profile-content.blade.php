<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <h3 class="mb-4">{{ __('Profile Settings') }}</h3>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><strong>{{ __('Profile Information') }}</strong></div>
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><strong>{{ __('Update Password') }}</strong></div>
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="card shadow-sm border-danger">
            <div class="card-header bg-white text-danger"><strong>{{ __('Delete Account') }}</strong></div>
            <div class="card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>