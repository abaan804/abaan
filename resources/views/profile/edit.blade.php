@if (auth()->user()->isSuperAdmin())
    <x-admin-layout>
        @section('title', 'Profile')
        @include('profile.partials.profile-content')
    </x-admin-layout>
@else
    <x-app-layout>
        @section('title', 'Profile')
        @include('profile.partials.profile-content')
    </x-app-layout>
@endif