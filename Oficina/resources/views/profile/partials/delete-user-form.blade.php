<section aria-labelledby="titulo-excluir">
    <h2 id="titulo-excluir" class="h5 text-danger">Excluir minha conta</h2>
    <p class="text-muted">A exclusão é permanente. Contas com registros vinculados à oficina não podem ser excluídas por aqui.</p>
    {{-- Os erros de exclusão ficam separados dos erros de alteração de senha. --}}
    @if ($errors->userDeletion->any())
        <div class="alert alert-danger" role="alert"><ul class="mb-0">
            @foreach ($errors->userDeletion->all() as $mensagem)<li>{{ $mensagem }}</li>@endforeach
        </ul></div>
    @endif
    {{-- A confirmação abre sem depender de Alpine ou dos componentes do Breeze. --}}
    <details @if($errors->userDeletion->any()) open @endif>
        <summary class="text-danger mb-3">Quero excluir minha conta</summary>
        <form method="POST" action="{{ route('profile.destroy') }}"
              onsubmit="return confirm('Excluir sua conta permanentemente? Esta ação não pode ser desfeita.');">
            @csrf
            @method('DELETE')
            <div class="mb-3">
                <label for="delete-password" class="form-label">Confirme sua senha atual</label>
                <input id="delete-password" name="password" type="password" autocomplete="current-password" required
                       class="form-control @error('password', 'userDeletion') is-invalid @enderror">
            </div>
            <button type="submit" class="btn btn-danger">Excluir conta definitivamente</button>
        </form>
    </details>
</section>
