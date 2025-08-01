@extends('layout.app')
@section('title', __('profile.restaurant_profile_page_title'))
@section('content')
    {{-- {{$user}} --}}

    <div class="d-flex flex-column mb-5">
        <div class="container-fluid profile-heading"></div>
        <div class="profile-content d-flex flex-column align-items-center">
            <div class="profile-image-container mb-4">
                <form action="{{ route('update.profile.image') }}" method="POST" enctype="multipart/form-data" id="profile-image-form">
                    @csrf
                    <label for="profile-image-input" style="cursor:pointer;">
                        <img class="profile-image" src="{{ $user->restaurant->image_url_resto ? asset('' . $user->restaurant->image_url_resto) : asset('assets/example/sayfood_profile.png') }}" alt="{{ __('profile.profile_image_alt') }}" id="profile-image-preview">
                        <input type="file" name="profile_image" id="profile-image-input" accept="image/*" style="display:none;" onchange="document.getElementById('profile-image-form').submit();">
                    </label>
                </form>
            </div>
            <div class="profile-details container-fluid flex-1 flex-column align-items-center justify-content-center">
                @error('profile_image')
                    <div class="text-danger text-center">{{ $message }}</div>
                @enderror
                <form action=" {{ route('update.profile.restaurant') }} " method="POST" class="d-flex flex-column justify-content-between align-items-center">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="username" class="oswald">{{ __('profile.username_label') }}</label>
                        <input type="text" class="oswald form-control" id="username" name="username" autocomplete="on" required value="{{ $user->username }}">
                        @error('username')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="restaurant_name" class="oswald">{{ __('profile.restaurant_name_field_label') }}</label>
                        <input type="text" class="oswald form-control" id="restaurant_name" name="restaurant_name" autocomplete="on" required value="{{ $user->restaurant->name }}">
                        @error('restaurant_name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="address" class="oswald">{{ __('profile.address_label') }}</label>
                        <input type="text" class="oswald form-control" id="address" name="address" autocomplete="on" value="{{ $user->restaurant->address }}">
                        @error('address')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    @error('profile-update')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror

                    <div class="profile-update-btn-container d-flex flex-row justify-content-center align-items-center py-3">
                        <div class="row justify-content-around">
                            <a href="{{ route('profile') }}" class="col-sm-5 btn oswald profile-update-btn" dusk="cancel-changes-btn">{{ __('profile.cancel_changes_button') }}</a>
                            <button type="submit" class="col-sm-5 btn oswald profile-update-btn" dusk="save-changes-btn">{{ __('profile.save_changes_button') }}</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="container-fluid d-flex justify-content-center align-items-center">
                <div class="profile-options justify-content-around row mt-5">
                    <form id="login-as-customer-form" method="POST" action="{{ route('login.as.customer') }}" style="display: none;">@csrf</form>
                    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">@csrf</form>
                    <form id="delete-account-form" method="POST" action="{{ route('delete.account') }}" style="display: none;">@csrf</form>

                    <a href="{{ route('login.as.customer') }}"
                    onclick="
                        event.preventDefault();
                        document.getElementById('login-as-customer-form').submit();"
                    class="profile-option mb-2 col-md-5 d-flex flex-row align-items-center justify-content-start px-0">
                        <img src="{{ asset('assets/profile_option_login_as_restaurant.png') }}" class="p-2" alt="icon">
                        <p class="oswald">{{ __('profile.login_as_customer_button') }}</p>
                    </a>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"
                    class="profile-option mb-2 col-md-5 d-flex flex-row align-items-center justify-content-start px-0" dusk="logout-btn">
                        <img src="{{ asset('assets/profile_option_logout.png') }}" class="p-2" alt="icon">
                        <p class="oswald">{{ __('profile.logout_button') }}</p>
                    </a>
                    <a href="{{ route('password.request') }}"
                    class="profile-option mb-2 col-md-5 d-flex flex-row align-items-center justify-content-start px-0">
                        <img src="{{ asset('assets/profile_option_reset_password.png') }}" class="p-2" alt="icon">
                        <p class="oswald">{{ __('profile.reset_password_button') }}</p>
                    </a>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#deleteAccountModal"
                    class="profile-option mb-2 col-md-5 d-flex flex-row align-items-center justify-content-start px-0" dusk="delete-account-btn">
                        <img src="{{ asset('assets/profile_option_delete.png') }}" class="p-2" alt="icon">
                        <p class="oswald">{{ __('profile.delete_account_button') }}</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">{{ __('profile.logout_modal_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('profile.close') }}"></button>
                </div>

                <div class="modal-body">
                    <p>{{ __('profile.logout_modal_body') }}</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('foods.cancel') }}
                    </button>
                    <button type="button" class="btn btn-confirm"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();" dusk="btn-logout">
                        {{ __('profile.logout_button') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">{{ __('profile.delete_account_modal_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('profile.close') }}"></button>
                </div>

                <div class="modal-body">
                    <p>{{ __('profile.delete_account_modal_body') }}</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('foods.cancel') }}
                    </button>
                    <button type="button" class="btn btn-confirm"
                        onclick="
                            event.preventDefault();
                            document.getElementById('delete-account-form').submit()"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();" dusk="btn-delete-account">
                        {{ __('profile.delete_account_button') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endpush