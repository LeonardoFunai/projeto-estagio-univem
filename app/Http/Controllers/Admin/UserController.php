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
     * Exibe uma lista de todos os usuários com filtros e paginação.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');
        $curso_id = $request->input('curso_id');

        // CORREÇÃO: Carrega as duas relações: 'curso' para alunos e 'cursosCoordenados' para coordenadores.
        $query = User::with('curso', 'cursosCoordenados')->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('ra', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        // CORREÇÃO: O filtro de curso agora funciona para alunos e coordenadores.
        if ($curso_id) {
            $query->where(function ($q) use ($curso_id) {
                // Filtra alunos pelo curso_id na tabela users
                $q->where('curso_id', $curso_id)
                  // Ou filtra coordenadores pela relação na tabela curso_user
                  ->orWhereHas('cursosCoordenados', function ($cq) use ($curso_id) {
                      $cq->where('cursos.id', $curso_id);
                  });
            });
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = User::select('role')->distinct()->pluck('role');
        $cursos = Curso::orderBy('nome')->get();

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
        // CORREÇÃO: Simplifica a regra de validação para a role 'coordenador'.
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', Rule::in(['aluno', 'professor', 'admin', 'napex', 'coordenador'])],
            'cursos_coordenados' => ['nullable', 'array', 'required_if:role,coordenador'],
            'cursos_coordenados.*' => ['exists:cursos,id'],
            'ra' => ['nullable', 'required_if:role,aluno', 'string', 'max:20', 'unique:users,ra'],
            'curso_id' => ['nullable', 'required_if:role,aluno', 'exists:cursos,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'ra' => $validated['role'] === 'aluno' ? $validated['ra'] : null,
            'curso_id' => $validated['role'] === 'aluno' ? $validated['curso_id'] : null,
        ]);

        // CORREÇÃO: Salva os cursos do coordenador na tabela pivô.
        if ($user->role === 'coordenador' && !empty($validated['cursos_coordenados'])) {
            $user->cursosCoordenados()->sync($validated['cursos_coordenados']);
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Mostra o formulário para editar um usuário existente.
     */
    public function edit(User $user)
    {
        // Carrega a relação para que os cursos já apareçam selecionados
        $user->load('cursosCoordenados');
        $cursos = Curso::orderBy('nome')->get();
        return view('admin.users.edit', compact('user', 'cursos'));
    }

    /**
     * Atualiza um usuário existente no banco de dados.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', Rule::in(['aluno', 'professor', 'admin', 'napex', 'coordenador'])],
            'cursos_coordenados' => ['nullable', 'array', 'required_if:role,coordenador'],
            'cursos_coordenados.*' => ['exists:cursos,id'],
            'ra' => ['nullable', 'required_if:role,aluno', 'string', 'max:20', Rule::unique('users', 'ra')->ignore($user->id)],
            'curso_id' => ['nullable', 'required_if:role,aluno', 'exists:cursos,id'],
        ]);
        
        $user->fill($validated);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->role === 'aluno') {
            $user->ra = $request->ra;
            $user->curso_id = $request->curso_id;
            $user->cursosCoordenados()->sync([]); // Garante que não haja cursos de coordenador para um aluno
        } else {
            $user->ra = null;
            $user->curso_id = null;
        }

        $user->save();

        // CORREÇÃO: Atualiza os cursos do coordenador na tabela pivô.
        if ($user->role === 'coordenador') {
            $user->cursosCoordenados()->sync($request->cursos_coordenados ?? []);
        } else {
             // Se o usuário deixou de ser coordenador, remove as associações
            $user->cursosCoordenados()->sync([]);
        }

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