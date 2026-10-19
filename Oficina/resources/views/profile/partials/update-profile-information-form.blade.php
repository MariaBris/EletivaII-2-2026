<section aria-labelledby="titulo-dados">
    <h2 id="titulo-dados" class="h5">Dados da conta</h2>
    <p class="text-muted">Atualize seu nome e seu e-mail.</p>

    {{-- Formulário separado para reenvio da verificação, se habilitada na model. --}}
    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
    <form id="send-verification" method="POST" action="{{ route('verification.send') }}">@csrf</form>
    @endif

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')
        <div class="mb-3">
            <label for="profile-name" class="form-label">Nome</label>
            <input id="profile-name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $user->name) }}" maxlength="255" autocomplete="name" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="profile-email" class="form-label">E-mail</label>
            <input id="profile-email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', $user->email) }}" maxlength="255" autocomplete="username" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="alert alert-warning">
            Seu e-mail ainda não foi verificado.
            <button type="submit" form="send-verification" class="btn btn-link p-0 align-baseline">Reenviar verificação</button>
        </div>
        @endif
        @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success" role="status">Um novo link de verificação foi enviado para seu e-mail.</div>
        @endif
        @if (session('status') === 'profile-updated')
        <div class="alert alert-success" role="status">Dados atualizados com sucesso!</div>
        @endif
        <button type="submit" class="btn btn-primary">Salvar dados</button>
    </form>
</section>
