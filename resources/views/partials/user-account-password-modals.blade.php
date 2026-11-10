{{-- User Accounts → Edit → Change Password: one modal per patient account,
     same shape as the Staff Accounts version. Never server-rendered as
     already-open — the shared reopen script in partials/flash-toasts re-opens
     the modal that was submitted when a validation error or "current
     password is incorrect" comes back; form_source keeps the error styling on
     this row only. Expects $accounts. --}}
@foreach ($accounts as $acc)
    @php
        $pwdFailed = ($errors->any() || session('password_error')) && old('form_source') === 'pwd_' . $acc->UserID;
    @endphp
    <div class="modal fade" id="changePasswordModal{{ $acc->UserID }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-semibold">Change Password</h5>
                        <div class="small text-muted">{{ trim(($acc->patientInfo->FirstName ?? '') . ' ' . ($acc->patientInfo->LastName ?? '')) ?: $acc->Email }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('userAcc.password.update', $acc->UserID) }}">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="form_source" value="pwd_{{ $acc->UserID }}">
                        <div class="mb-3">
                            <label class="form-label">Current password</label>
                            <div class="pw-field">
                                <input type="checkbox" class="pw-toggle-checkbox" id="pwUserCur{{ $acc->UserID }}">
                                <div class="input-icon {{ $pwdFailed && session('password_error') ? 'has-error' : '' }}"><i class="bi bi-lock"></i><input type="text"
                                        name="current_password" class="form-control pw-mask" placeholder="••••••••" required value="{{ $pwdFailed ? old('current_password') : '' }}" autocomplete="current-password"></div>
                                <label for="pwUserCur{{ $acc->UserID }}" class="pw-eye-btn" aria-label="Show or hide password"><i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i></label>
                            </div>
                            @if ($pwdFailed && session('password_error'))
                                <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('password_error') }}</div>
                            @endif
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">New password</label>
                                <div class="pw-field">
                                    <input type="checkbox" class="pw-toggle-checkbox" id="pwUserNew{{ $acc->UserID }}">
                                    <div class="input-icon {{ $pwdFailed && $errors->has('password') ? 'has-error' : '' }}"><i class="bi bi-key"></i><input type="text" name="password"
                                            class="form-control pw-mask" placeholder="••••••••" minlength="8" required value="{{ $pwdFailed ? old('password') : '' }}" autocomplete="new-password"></div>
                                    <label for="pwUserNew{{ $acc->UserID }}" class="pw-eye-btn" aria-label="Show or hide password"><i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i></label>
                                </div>
                                @if ($pwdFailed && $errors->has('password'))
                                    <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('password') }}</div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm new password</label>
                                <div class="pw-field">
                                    <input type="checkbox" class="pw-toggle-checkbox" id="pwUserConfirm{{ $acc->UserID }}">
                                    <div class="input-icon {{ $pwdFailed && $errors->has('password') ? 'has-error' : '' }}"><i class="bi bi-shield-lock"></i><input type="text"
                                            name="password_confirmation" class="form-control pw-mask" placeholder="••••••••" required value="{{ $pwdFailed ? old('password_confirmation') : '' }}" autocomplete="new-password"></div>
                                    <label for="pwUserConfirm{{ $acc->UserID }}" class="pw-eye-btn" aria-label="Show or hide password"><i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-brand">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
