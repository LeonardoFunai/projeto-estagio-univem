<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;


class UserController extends Controller
{
    /**
     * Exibe uma lista de todos os usuários com paginação.
     */
// app/Http/Controllers/Admin/UserController.php



    public function index(Request $request)
    {
        // Pega os valores de busca e filtro da URL
        $search = $request->input('search');
        $role = $request->input('role');
        $curso_id = $request->input('curso_id'); // Novo filtro de curso

        // Inicia a query para buscar usuários
        $query = User::with('curso')->orderBy('name');

        // Se um termo de busca foi fornecido, aplica o filtro
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('cpf', 'like', "%{$search}%")
                ->orWhere('ra', 'like', "%{$search}%");
            });
        }

        // Se uma role foi selecionada, filtra por ela
        if ($role) {
            $query->where('role', $role);
        }

        // Se um curso foi selecionado, filtra por ele
        if ($curso_id) {
            $query->where('curso_id', $curso_id);
        }

        // Executa a query com paginação
        $users = $query->paginate(15)->withQueryString();

        // Obtém dados para os filtros
        $roles = User::select('role')->distinct()->pluck('role');
        $cursos = Curso::orderBy('nome')->get(); // Busca todos os cursos

        // Retorna a view com os dados
        return view('admin.users.index', compact('users', 'roles', 'cursos', 'search', 'role', 'curso_id'));
    }

    /**
     * Mostra o formulário para criar um novo usuário.
     */
    public function create()
    {
        $cursos = Curso::orderBy('nome')->get();
        return view('admin.users.create', compact('cursos'));
    }

    /**
     * Armazena um novo usuário no banco de dados.
     */
    public function store(Request $request)
    {
        // CORREÇÃO APLICADA AQUI
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'], // Removido o 'ignore'
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', Rule::in(['aluno', 'professor', 'admin', 'napex', 'coordenador_adm', 'coordenador_cc', 'coordenador_contabeis', 'coordenador_design', 'coordenador_direito', 'coordenador_producao', 'coordenador_si'])],
            
            'cpf' => ['nullable', 'required_if:role,aluno', 'string', 'max:14', 'unique:users,cpf'], // Removido o 'ignore' e especificado a coluna
            'ra' => ['nullable', 'required_if:role,aluno', 'string', 'max:20', 'unique:users,ra'], // Removido o 'ignore' e especificado a coluna
            'data_nascimento' => ['nullable', 'required_if:role,aluno', 'date'],
            'curso_id' => ['nullable', 'required_if:role,aluno', 'exists:cursos,id'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'cpf' => $request->role === 'aluno' ? $request->cpf : null,
            'ra' => $request->role === 'aluno' ? $request->ra : null,
            'data_nascimento' => $request->role === 'aluno' ? $request->data_nascimento : null,
            'curso_id' => $request->role === 'aluno' ? $request->curso_id : null,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Mostra o formulário para editar um usuário existente.
     */
    public function edit(User $user)
    {
        $cursos = Curso::orderBy('nome')->get();
        return view('admin.users.edit', compact('user', 'cursos'));
    }

    /**
     * Atualiza um usuário existente no banco de dados.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', Rule::in(['aluno', 'professor', 'admin', 'napex', 'coordenador_adm', 'coordenador_cc', 'coordenador_contabeis', 'coordenador_design', 'coordenador_direito', 'coordenador_producao', 'coordenador_si'])],

            'cpf' => ['nullable', 'required_if:role,aluno', 'string', 'max:14', Rule::unique('users','cpf')->ignore($user->id)],
            'ra' => ['nullable', 'required_if:role,aluno', 'string', 'max:20', Rule::unique('users','ra')->ignore($user->id)],
            'data_nascimento' => ['nullable', 'required_if:role,aluno', 'date'],
            'curso_id' => ['nullable', 'required_if:role,aluno', 'exists:cursos,id'],
        ]);

        $user->fill([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->role === 'aluno') {
            $user->cpf = $request->cpf;
            $user->ra = $request->ra;
            $user->data_nascimento = $request->data_nascimento;
            $user->curso_id = $request->curso_id;
        } else {
            $user->cpf = null;
            $user->ra = null;
            $user->data_nascimento = null;
            $user->curso_id = null;
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove um usuário do banco de dados.
     */
    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Você não pode excluir sua própria conta.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuário excluído com sucesso.');
    }
}