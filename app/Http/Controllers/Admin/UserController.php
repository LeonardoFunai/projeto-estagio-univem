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
    $currentUser = auth()->user(); 

    // Lógica de Ordenação
    $sortableColumns = ['name', 'email', 'ra', 'role', 'created_at', 'curso_id']; 
    $sortBy = in_array($request->input('sort_by'), $sortableColumns) ? $request->input('sort_by') : 'name';
    $sortDirection = in_array($request->input('sort_direction'), ['asc', 'desc']) ? $request->input('sort_direction') : 'asc';

    // Lógica de Filtros da View
    $search = $request->input('search');
    $role = $request->input('role');
    $curso_id = $request->input('curso_id');
    $cpf = $request->input('cpf');
    $ra = $request->input('ra');

    // Inicia a query com a ordenação dinâmica
    $query = User::with('curso', 'cursosCoordenados')->orderBy($sortBy, $sortDirection);

    // --- LÓGICA DE PERMISSÃO (FILTRO BASE) ---
    // Esta é a restrição principal que sempre será aplicada
    if ($currentUser->role === 'coordenador') {
        $coordenadorCursosIds = $currentUser->cursosCoordenados->pluck('id')->toArray();
        $query->where('role', 'aluno')->whereIn('curso_id', $coordenadorCursosIds);
    } elseif ($currentUser->role === 'napex') {
        $query->where('role', 'aluno');
    }

    // --- FILTROS DO USUÁRIO ---
    // Estes filtros agora operam sobre a query já restrita pelas permissões
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
        // Esta condição agora não causará conflito, pois o dropdown de roles
        // para napex/coordenador só conterá 'aluno'.
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
    
    // --- LÓGICA PARA PREENCHER OS DROPDOWNS DO FILTRO ---
    // Esta parte é crucial para evitar o conflito na interface
    if ($currentUser->role === 'coordenador') {
        $cursos = $currentUser->cursosCoordenados()->orderBy('nome')->get();
        $roles = ['aluno']; // Só pode filtrar por aluno
    } elseif ($currentUser->role === 'napex') {
        $cursos = Curso::orderBy('nome')->get();
        $roles = ['aluno']; // Só pode filtrar por aluno
    }
    else { // Admin vê tudo
        $cursos = Curso::orderBy('nome')->get();
        $roles = User::select('role')->distinct()->pluck('role');
    }

    return view('admin.users.index', compact('users', 'roles', 'cursos', 'search', 'role', 'curso_id', 'cpf', 'ra', 'sortBy', 'sortDirection'));
}

    public function create()
    {
        $this->authorize('create', User::class);
        $cursos = [];
        $user = auth()->user();

        // Correção: Compara a propriedade 'role' diretamente
        if ($user->role === 'admin' || $user->role === 'napex') {
            $cursos = Curso::all();
        } 
        elseif ($user->role === 'coordenador') {
            // A relação aqui deve ser a que você definiu para os cursos que um coordenador gerencia
            $cursos = $user->cursosCoordenados; 
        }

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

        if ($currentUser->role === 'coordenador') {
            $request->merge(['role' => 'aluno']);
            
            $coordenadorCursosIds = $currentUser->cursosCoordenados->pluck('id')->toArray();
            
            $alunoRules['curso_id'] = ['required', 'exists:cursos,id', Rule::in($coordenadorCursosIds)];
            
            $rules = array_merge($baseRules, $alunoRules, ['role' => ['required', Rule::in(['aluno'])]]);

        } elseif ($currentUser->role === 'napex') {
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

        $validated = $request->validate($rules);
        $validated['password'] = Hash::make($validated['password']);
        
        $user = User::create($validated);

        if ($user->role === 'coordenador' && isset($validated['cursos_coordenados'])) {
            $user->cursosCoordenados()->sync($validated['cursos_coordenados']);
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $currentUser = auth()->user();
        $cursos = [];
        $roles = [];

        if ($currentUser->role === 'admin') {
            $cursos = Curso::orderBy('nome')->get();
            $roles = ['aluno', 'professor', 'coordenador', 'napex', 'admin'];
        } elseif ($currentUser->role === 'napex') {
            $cursos = Curso::orderBy('nome')->get();
            $roles = ['aluno']; 
        } elseif ($currentUser->role === 'coordenador') {
            $cursos = $currentUser->cursosCoordenados()->orderBy('nome')->get();
            $roles = ['aluno']; 
        }

        $cursos_coordenados = $user->cursosCoordenados->pluck('id')->toArray();
        
        return view('admin.users.edit', compact('user', 'cursos', 'roles', 'cursos_coordenados'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $currentUser = auth()->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];

        if ($currentUser->role === 'admin') {
            $rules = array_merge($rules, [
                'role' => ['required', 'string', Rule::in(['aluno', 'professor', 'admin', 'napex', 'coordenador'])],
                'cursos_coordenados' => ['nullable', 'array', 'required_if:role,coordenador'],
                'cursos_coordenados.*' => ['exists:cursos,id'],
                'cpf' => ['nullable', 'required_if:role,aluno', 'string', 'max:14', Rule::unique('users')->ignore($user->id)],
                'ra' => ['nullable', 'required_if:role,aluno', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
                'curso_id' => ['nullable', 'required_if:role,aluno', 'exists:cursos,id'],
            ]);
        } else {
            $alunoRules = [
                'cpf' => ['nullable', 'string', 'max:14', Rule::unique('users')->ignore($user->id)],
                'ra' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
                'curso_id' => ['required', 'exists:cursos,id'],
            ];

            if ($currentUser->role === 'coordenador') {
                $coordenadorCursosIds = $currentUser->cursosCoordenados->pluck('id')->toArray();
                $alunoRules['curso_id'][] = Rule::in($coordenadorCursosIds);
            }
            $rules = array_merge($rules, $alunoRules);
        }
        
        $validated = $request->validate($rules);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        if ($currentUser->role === 'admin' && $user->role === 'coordenador') {
            $cursos_coordenados = $request->input('cursos_coordenados', []);
            $user->cursosCoordenados()->sync($cursos_coordenados);
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuário atualizado com sucesso!');
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