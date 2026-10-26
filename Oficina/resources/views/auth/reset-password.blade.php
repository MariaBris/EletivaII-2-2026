@extends('layout')

@section('title', 'Redefinir senha - Mazotto')

@section('content')
<div class="row justify-content-center align-items-center py-4">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">

                <div class="text-center mb-4">
                    <i class="bi bi-key display-4" style="color: var(--azul-mazotto);"></i>
                    <h1 class="h3 mt-3">Redefina sua senha</h1>
                    <p class="text-muted">Informe seu e-mail e escolha uma nova senha.</p>
                </div>

                <form method="POST" action="{{ route('password.store') }}">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email Address -->
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $request->email) }}" autocomplete="username" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Nova senha</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmar nova senha</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" autocomplete="new-password" required>
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i> Redefinir senha</button>

                    <div class="text-center mt-4">
                        <a href="{{ route('login') }}" class="link-dark">Voltar ao login</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
