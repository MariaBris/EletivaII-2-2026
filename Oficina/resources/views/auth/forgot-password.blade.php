@extends('layout')

@section('title', 'Recuperar senha - Mazotto')

@section('content')
<div class="row justify-content-center align-items-center py-4">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">

                <div class="text-center mb-4">
                    <i class="bi bi-envelope display-4" style="color: var(--azul-mazotto);"></i>
                    <h1 class="h3 mt-3">Esqueceu sua senha?</h1>
                    <p class="text-muted">Informe o e-mail da sua conta para solicitar um link de redefinição de senha.</p>
                </div>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" autocomplete="email" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-envelope me-1"></i> Enviar link de recuperação</button>

                    <div class="text-center mt-4">
                        <a href="{{ route('login') }}" class="link-dark">Voltar ao login</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
