<?php

namespace App\Http\Controllers\Admin;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Imports\UsersImport; 


class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');
        $curso_id = $request->input('curso_id');
        $cpf = $request->input('cpf');
        $ra = $request->input('ra');

        $query = User::with('curso', 'cursosCoordenados')->orderBy('name');

        if ($search) {

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($cpf) {
            $query->where('cpf', 'like', "%{$cpf}%");
        }

        if ($ra) {
            $query->where('ra', 'like', "%{$ra}%");
        }

        if ($role) {
            $query->where('role', $role);
        }

        if ($curso_id) {
            $query->where(function ($q) use ($curso_id) {
                $q->where('curso_id', $curso_id)
                  ->orWhereHas('cursosCoordenados', function ($cq) use ($curso_id) {
                      $cq->where('cursos.id', $curso_id);
                  });
            });
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = User::select('role')->distinct()->pluck('role');
        $cursos = Curso::orderBy('nome')->get();

        return view('admin.users.index', compact('users', 'roles', 'cursos', 'search', 'role', 'curso_id', 'cpf', 'ra'));
    }

    public function create()
    {
        $cursos = Curso::orderBy('nome')->get();
        return view('admin.users.create', compact('cursos'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        $currentUser = auth()->user();

        $baseRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];

        $alunoRules = [
            'cpf' => ['nullable', 'string', 'max:14', 'unique:users,cpf'],
            'ra' => ['required', 'string', 'max:20', 'unique:users,ra'],
            'data_nascimento' => ['nullable', 'date'],
            'curso_id' => ['required', 'exists:cursos,id'],
        ];

        if (in_array($currentUser->role, ['napex', 'coordenador'])) {
            $request->merge(['role' => 'aluno']);
            $rules = array_merge($baseRules, $alunoRules, ['role' => ['required', Rule::in(['aluno'])]]);
        } else {
            $rules = array_merge($baseRules, [
                'role' => ['required', 'string', Rule::in(['aluno', 'professor', 'admin', 'napex', 'coordenador'])],
                'cursos_coordenados' => ['nullable', 'array', 'required_if:role,coordenador'],
                'cursos_coordenados.*' => ['exists:cursos,id'],
                'cpf' => ['nullable', 'required_if:role,aluno', 'string', 'max:14', 'unique:users,cpf'],
                'ra' => ['nullable', 'required_if:role,aluno', 'string', 'max:20', 'unique:users,ra'],
                'data_nascimento' => ['nullable', 'required_if:role,aluno', 'date'],
                'curso_id' => ['nullable', 'required_if:role,aluno', 'exists:cursos,id'],
            ]);
        }

        $validated = $request->validate($rules, $this->validationMessages());
        $validated['password'] = Hash::make($validated['password']);
        
        $user = User::create($validated);

        if ($user->role === 'coordenador' && isset($validated['cursos_coordenados'])) {
            $user->cursosCoordenados()->sync($validated['cursos_coordenados']);
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function edit(User $user)
    {
        $user->load('cursosCoordenados');
        $cursos = Curso::orderBy('nome')->get();
        return view('admin.users.edit', compact('user', 'cursos'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];

        $currentUser = auth()->user();

        if ($currentUser->role === 'admin' && $currentUser->id !== $user->id) {
            $rules['role'] = ['required', 'string', Rule::in(['aluno', 'professor', 'admin', 'napex', 'coordenador'])];
        }

        if ($user->role === 'aluno' || $request->input('role') === 'aluno') {
            $rules['cpf'] = ['nullable', 'string', 'max:14', Rule::unique('users', 'cpf')->ignore($user->id)];
            $rules['ra'] = ['required', 'string', 'max:20', Rule::unique('users', 'ra')->ignore($user->id)];
            $rules['data_nascimento'] = ['nullable', 'date'];
            $rules['curso_id'] = ['required', 'exists:cursos,id'];
        }

        if ($request->input('role') === 'coordenador') {
            $rules['cursos_coordenados'] = ['nullable', 'array'];
            $rules['cursos_coordenados.*'] = ['exists:cursos,id'];
        }

        // Valida os dados usando as mensagens de erro padronizadas
        $validated = $request->validate($rules, $this->validationMessages());

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->fill($validated);

        if ($user->role !== 'aluno') {
            $user->ra = null;
            $user->curso_id = null;
            $user->cpf = null;
            $user->data_nascimento = null;
        }
        
        if ($user->role !== 'coordenador' && $currentUser->role === 'admin') {
            $user->cursosCoordenados()->sync([]);
        }

        $user->save();
        
        if ($user->role === 'coordenador' && $currentUser->role === 'admin') {
            $user->cursosCoordenados()->sync($request->cursos_coordenados ?? []);
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    private function validationMessages()
    {
        return [
            'name.required' => 'O campo nome é obrigatório.',
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'Por favor, insira um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado no sistema.',
            'password.required' => 'O campo senha é obrigatório.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'password.min' => 'A senha deve ter no mínimo :min caracteres.',
            'role.required' => 'A seleção de um perfil é obrigatória.',
            'ra.required' => 'O campo R.A. é obrigatório para alunos.',
            'ra.unique' => 'Este R.A. já está cadastrado no sistema.',
            'cpf.unique' => 'Este CPF já está cadastrado no sistema.',
            'curso_id.required' => 'A seleção de um curso é obrigatória para alunos.',
            'required_if' => 'O campo :attribute é obrigatório.',
        ];
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        if (auth()->id() === $user->id) {
            return back()->with('error', 'Você não pode excluir sua própria conta.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Usuário excluído com sucesso.');
    }
    public function showImportForm()
    {
        // Apenas autoriza se o usuário pode criar novos usuários
        $this->authorize('create', User::class);
        return view('admin.users.import');
    }

    /**
     * Processa o arquivo Excel para importar usuários.
     */
     public function import(Request $request)
    {
        $this->authorize('create', User::class);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new UsersImport, $request->file('file'));

        } catch (ValidationException $e) {
             $failures = $e->failures();
             $errorMessages = [];
             foreach ($failures as $failure) {
                 $errorMessages[] = "Erro na linha " . $failure->row() . ": " . implode(', ', $failure->errors());
             }
             // Retorna com os erros de validação detalhados
             return back()->with('error', 'Foram encontrados erros na sua planilha: <br><br>' . implode('<br>', $errorMessages));
        
        } catch (\Exception $e) {
            // Captura qualquer outro erro que possa acontecer durante a importação
            return back()->with('error', 'Ocorreu um erro inesperado. Verifique se as colunas do arquivo estão corretas e se os dados são válidos. <br><br><strong>Detalhe técnico:</strong> ' . $e->getMessage());
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuários importados com sucesso!');
    }

}