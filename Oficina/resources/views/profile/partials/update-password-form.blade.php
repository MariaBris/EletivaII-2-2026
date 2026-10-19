<section aria-labelledby="titulo-senha">
    <h2 id="titulo-senha" class="h5">Alterar senha</h2>
    <p class="text-muted">Informe sua senha atual e confirme a nova senha.</p>
    {{-- O Breeze usa a lista de erros updatePassword para este formulário. --}}
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="update-current_password" class="form-label">Senha atual</label>
            <input id="update-current_password" name="current_password" type="password" autocomplete="current-password" required
                class="form-control @error('current_password', 'updatePassword') is-invalid @enderror">
            @error('current_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="update-password" class="form-label">Nova senha</label>
            <input id="update-password" name="password" type="password" autocomplete="new-password" required
                class="form-control @error('password', 'updatePassword') is-invalid @enderror">
            @error('password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="update-password_confirmation" class="form-label">Confirmar nova senha</label>
            <input id="update-password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror">
            @error('password_confirmation', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        @if (session('status') === 'password-updated')
        <div class="alert alert-success" role="status">Senha atualizada com sucesso!</div>
        @endif
        <button type="submit" class="btn btn-primary">Salvar senha</button>
    </form>
</section>
