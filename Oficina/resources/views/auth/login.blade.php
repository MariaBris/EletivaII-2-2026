@extends('layout')

@section('title', 'Login - Mazotto')

@section('content')
<div class="row justify-content-center align-items-center py-4">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">

                <div class="text-center mb-4">
                    <i class="bi bi-person-circle display-4" style="color: var(--azul-mazotto);"></i>
                    <h1 class="h3 mt-3">Faça login</h1>
                    <p class="text-muted">Informe seus dados para acessar o sistema.</p>
                </div>

                @if (session('status'))
                <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" autocomplete="username" required autofocus>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="current-password" required>
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" id="remember" name="remember" value="1" class="form-check-input" @checked(old('remember'))>
                        <label for="remember" class="form-check-label">Lembre-se de mim</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Entrar</button>

                    <div class="text-center mt-3">
                        @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="link-secondary">Esqueci minha senha</a>
                        @endif
                    </div>

                    @if (Route::has('register'))
                    <div class="text-center mt-4">
                        <span class="text-muted">Ainda não tem conta?</span>
                        <a href="{{ route('register') }}" class="link-dark">Cadastre-se</a>
                    </div>
                    @endif
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
