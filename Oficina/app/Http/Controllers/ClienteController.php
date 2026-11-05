<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Endereco;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->autorizarAcesso($request);

        $validacao = Validator::make($request->query(), [
            'busca' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
        ], [
            'busca.string' => 'Informe um texto para pesquisar.',
            'busca.max' => 'A pesquisa deve ter até 255 caracteres.',
            'page.integer' => 'A página deve ser um número inteiro.',
            'page.min' => 'A página deve ser maior que zero.',
        ]);

        if ($validacao->fails()) {
            return redirect()->route('clientes.index')
                ->withErrors($validacao)
                ->withInput($request->except('page'));
        }

        $dados = $validacao->validated();
        $busca = trim($dados['busca'] ?? '');

        $consulta = Cliente::query();

        if ($busca !== '') {
            $documentoBusca = strtoupper(
                preg_replace('/[^A-Za-z0-9]/', '', $busca)
            );

            $consulta->where(function ($consultaBusca) use ($busca, $documentoBusca) {
                $consultaBusca->where('nome', 'like', '%' . $busca . '%')
                    ->orWhere('telefone', 'like', '%' . $busca . '%')
                    ->orWhere('email', 'like', '%' . $busca . '%');

                if ($documentoBusca !== '') {
                    $consultaBusca->orWhere(
                        'cpf_cnpj',
                        'like',
                        '%' . $documentoBusca . '%'
                    );
                }
            });
        }

        $clientes = $consulta->orderBy('nome')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('clientes.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->autorizarAcesso($request);

        $validacao = Validator::make(
            $request->query(),
            $this->regrasRetorno()
        );

        if ($validacao->fails()) {
            return redirect()->route('clientes.index')
                ->withErrors($validacao);
        }

        return view('clientes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->autorizarAcesso($request);

        $retorno = $request->validate($this->regrasRetorno());
        $dados = $this->validar($request);

        $cliente = DB::transaction(function () use ($dados) {
            $cliente = Cliente::create($this->dadosCliente($dados));

            $this->salvarEndereco($cliente, $dados);

            return $cliente;
        }, 3);

        if (($retorno['origem'] ?? null) === 'veiculo') {
            $parametros = [
                'cliente_id' => $cliente->id,
                'retomar_cliente' => 1,
            ];

            if (!empty($retorno['origem_veiculo'])) {
                $parametros['origem'] = $retorno['origem_veiculo'];
            }

            if (
                ($retorno['origem_veiculo'] ?? null) === 'agendamento'
                && !empty($retorno['agendamento_id'])
            ) {
                $parametros['agendamento_id'] = $retorno['agendamento_id'];
            }

            return redirect()->route('veiculos.create', $parametros)
                ->with('mensagem', 'Cliente cadastrado! Continue preenchendo o veículo.');
        }

        return redirect()->route('clientes.index')
            ->with('mensagem', 'Cliente cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $cliente = Cliente::with('endereco')->findOrFail($id);

        return view('clientes.show', compact('cliente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $cliente = Cliente::with('endereco')->findOrFail($id);

        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        $dados = $this->validar($request);

        DB::transaction(function () use ($id, $dados) {
            $cliente = Cliente::lockForUpdate()->findOrFail($id);

            $cliente->update($this->dadosCliente($dados));

            $this->salvarEndereco($cliente, $dados);
        }, 3);

        return redirect()->route('clientes.index')
            ->with('mensagem', 'Cliente atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $this->autorizarAcesso($request);

        try {
            DB::transaction(function () use ($id) {
                $cliente = Cliente::lockForUpdate()->findOrFail($id);

                // Remove somente os endereços próprios do cliente,
                // sem vínculo com conta de usuário.
                $cliente->enderecos()->whereNull('user_id')->delete();

                $cliente->delete();
            }, 3);
        } catch (QueryException $erro) {
            if (($erro->errorInfo[1] ?? null) == 1451) {
                return redirect()->route('clientes.index')
                    ->with('erro', 'Este cliente possui registros vinculados e não pode ser excluído.');
            }

            throw $erro;
        }

        return redirect()->route('clientes.index')
            ->with('mensagem', 'Cliente excluído com sucesso!');
    }

    private function autorizarAcesso(Request $request)
    {
        abort_unless(
            $request->user()
                && in_array($request->user()->role, ['ADM', 'FUNC'], true),
            403
        );
    }

    private function regrasRetorno(): array
    {
        return [
            'origem' => [
                'nullable',
                'string',
                Rule::in(['veiculo']),
            ],
            'origem_veiculo' => [
                'nullable',
                'string',
                Rule::in(['os', 'agendamento']),
            ],
            'agendamento_id' => [
                'nullable',
                'integer',
                'exists:agendamentos,id',
            ],
        ];
    }

    private function dadosCliente(array $dados): array
    {
        return [
            'nome' => $dados['nome'],
            'telefone' => $dados['telefone'],
            'email' => $dados['email'] ?? null,
            'cpf_cnpj' => $dados['cpf_cnpj'] ?? null,
        ];
    }

    private function salvarEndereco(Cliente $cliente, array $dados): void
    {
        $campos = [
            'endereco',
            'numero',
            'complemento',
            'bairro',
            'cidade',
            'estado',
            'cep',
        ];

        $dadosEndereco = [];
        $temEndereco = false;

        foreach ($campos as $campo) {
            $valor = trim($dados[$campo] ?? '');

            $dadosEndereco[$campo] = $valor === '' ? null : $valor;

            if ($valor !== '') {
                $temEndereco = true;
            }
        }

        $endereco = $cliente->endereco()->lockForUpdate()->first();

        if ($endereco) {
            $endereco->update($dadosEndereco);
        } elseif ($temEndereco) {
            $cliente->enderecos()->create([
                ...$dadosEndereco,
                'user_id' => null,
            ]);
        }
    }

    private function validar(Request $request)
    {
        if (is_string($request->input('cpf_cnpj'))) {
            $documento = strtoupper(
                str_replace(
                    ['.', '-', '/', ' '],
                    '',
                    trim($request->input('cpf_cnpj'))
                )
            );

            $request->merge([
                'cpf_cnpj' => $documento === '' ? null : $documento,
            ]);
        }

        if (is_string($request->input('estado'))) {
            $request->merge([
                'estado' => strtoupper(trim($request->input('estado'))),
            ]);
        }

        return $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'cpf_cnpj' => [
                'nullable',
                'string',
                'regex:/^(?:[0-9]{11}|[A-Z0-9]{12}[0-9]{2})$/D',
            ],
            'endereco' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:100'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'estado' => [
                'nullable',
                'string',
                Rule::in(Endereco::UFS),
            ],
            'cep' => [
                'nullable',
                'string',
                'regex:/^[0-9]{5}-?[0-9]{3}$/D',
            ],
        ], [
            'nome.required' => 'Informe o nome do cliente.',
            'nome.string' => 'O nome deve ser um texto.',
            'nome.max' => 'O nome deve ter até 255 caracteres.',

            'telefone.required' => 'Informe um telefone para contato.',
            'telefone.string' => 'O telefone deve ser informado como texto.',
            'telefone.max' => 'O telefone deve ter até 20 caracteres.',

            'email.email' => 'Informe um e-mail válido.',
            'email.max' => 'O e-mail deve ter até 255 caracteres.',

            'cpf_cnpj.regex' => 'Informe um CPF com 11 dígitos ou um CNPJ com 14 caracteres.',
            'cep.regex' => 'Informe um CEP com 8 dígitos, com ou sem hífen.',
            'estado.in' => 'Selecione uma UF válida.',

            'string' => 'O campo :attribute deve ser um texto.',
            'max' => 'O campo :attribute deve ter até :max caracteres.',
        ], [
            'cpf_cnpj' => 'CPF/CNPJ',
            'endereco' => 'rua/avenida',
            'numero' => 'número',
            'complemento' => 'complemento',
            'bairro' => 'bairro',
            'cidade' => 'cidade',
            'estado' => 'UF',
            'cep' => 'CEP',
        ]);
    }
}
