@extends('layout')
@section('title', 'Meu perfil - Mazotto')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 mb-0">Meu perfil</h1>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-dark"><i class="bi bi-arrow-left me-1"></i>Voltar ao painel</a>
</div>
{{-- Cada formulário mantém sua própria rota e suas mensagens. --}}
<div class="row g-4">
    <div class="col-12 col-lg-6"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4">
        @include('profile.partials.update-profile-information-form')
    </div></div></div>
    <div class="col-12 col-lg-6"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4">
        @include('profile.partials.update-password-form')
    </div></div></div>
    <div class="col-12"><div class="card border-danger shadow-sm"><div class="card-body p-4">
        @include('profile.partials.delete-user-form')
    </div></div></div>
</div>
@endsection
